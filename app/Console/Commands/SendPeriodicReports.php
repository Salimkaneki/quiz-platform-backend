<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Administrator;
use App\Models\QuizSession;
use App\Notifications\PeriodicResultsReportNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class SendPeriodicReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-periodic 
                            {period=daily : Période du rapport (daily, weekly, monthly)}
                            {--date= : Date spécifique pour le rapport (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoyer des rapports périodiques des résultats aux administrateurs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->argument('period');
        $date = $this->option('date') ? \Carbon\Carbon::parse($this->option('date')) : now();

        $this->info("Génération du rapport {$period} pour la date {$date->format('Y-m-d')}");

        // Validation de la période
        if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
            $this->error("Période invalide. Utilisez: daily, weekly, ou monthly");
            return 1;
        }

        try {
            // Déterminer la période
            switch ($period) {
                case 'daily':
                    $startDate = $date->copy()->startOfDay();
                    $endDate = $date->copy()->endOfDay();
                    break;
                case 'weekly':
                    $startDate = $date->copy()->startOfWeek();
                    $endDate = $date->copy()->endOfWeek();
                    break;
                case 'monthly':
                    $startDate = $date->copy()->startOfMonth();
                    $endDate = $date->copy()->endOfMonth();
                    break;
            }

            $this->info("Période: {$startDate->format('Y-m-d H:i')} - {$endDate->format('Y-m-d H:i')}");

            // Récupérer toutes les institutions avec des sessions terminées
            $institutions = Administrator::distinct()
                ->whereHas('institution.quizSessions', function($query) use ($startDate, $endDate) {
                    $query->where('status', 'completed')
                          ->whereBetween('ends_at', [$startDate, $endDate]);
                })
                ->with('institution')
                ->get()
                ->pluck('institution')
                ->unique();

            if ($institutions->isEmpty()) {
                $this->info('Aucune institution avec des sessions terminées trouvée pour cette période.');
                return 0;
            }

            $totalReports = 0;

            foreach ($institutions as $institution) {
                $this->info("Traitement de l'institution: {$institution->name}");

                // Récupérer les sessions terminées pour cette institution
                $sessions = QuizSession::with([
                    'quiz.subject',
                    'teacher.user',
                    'results' => function($query) {
                        $query->where('status', 'published');
                    },
                    'results.student.classe'
                ])
                ->where('status', 'completed')
                ->whereBetween('ends_at', [$startDate, $endDate])
                ->whereHas('teacher', function($query) use ($institution) {
                    $query->where('institution_id', $institution->id);
                })
                ->get();

                if ($sessions->isEmpty()) {
                    $this->info("  Aucune session trouvée pour {$institution->name}");
                    continue;
                }

                // Générer les données du rapport
                $reportData = $this->generatePeriodicReportData($sessions);

                // Récupérer les administrateurs de l'institution
                $administrators = Administrator::with('user')
                    ->where('institution_id', $institution->id)
                    ->get();

                if ($administrators->isEmpty()) {
                    $this->warn("  Aucun administrateur trouvé pour {$institution->name}");
                    continue;
                }

                // Envoyer le rapport à tous les administrateurs
                $sentCount = 0;
                foreach ($administrators as $administrator) {
                    try {
                        Notification::send(
                            $administrator->user,
                            new PeriodicResultsReportNotification($reportData, $period, $administrator)
                        );
                        $sentCount++;
                    } catch (\Exception $e) {
                        $this->error("Erreur envoi à {$administrator->user->email}: " . $e->getMessage());
                        Log::error("Erreur envoi rapport périodique à {$administrator->user->email}: " . $e->getMessage());
                    }
                }

                $this->info("  Rapport envoyé à {$sentCount} administrateur(s) pour {$institution->name}");
                $totalReports += $sentCount;
            }

            $this->info("Rapports périodiques envoyés avec succès à {$totalReports} administrateur(s) au total.");
            return 0;

        } catch (\Exception $e) {
            $this->error('Erreur lors de l\'envoi des rapports périodiques: ' . $e->getMessage());
            Log::error('Erreur commande rapports périodiques: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Générer les données du rapport périodique
     */
    private function generatePeriodicReportData($sessions)
    {
        $totalSessions = $sessions->count();
        $totalParticipants = $sessions->sum(function($session) {
            return $session->results->count();
        });

        $allResults = collect();
        foreach ($sessions as $session) {
            $allResults = $allResults->merge($session->results);
        }

        $averageScore = $allResults->avg('percentage') ?? 0;
        $highestScore = $allResults->max('percentage') ?? 0;
        $lowestScore = $allResults->min('percentage') ?? 0;

        return [
            'period' => [
                'total_sessions' => $totalSessions,
                'total_participants' => $totalParticipants,
                'average_score' => round($averageScore, 2),
                'highest_score' => round($highestScore, 2),
                'lowest_score' => round($lowestScore, 2),
            ],
            'sessions' => $sessions->map(function($session) {
                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'quiz_title' => $session->quiz->title,
                    'teacher' => $session->teacher->user->name,
                    'participants_count' => $session->results->count(),
                    'average_score' => $session->results->avg('percentage') ?? 0,
                    'completed_at' => $session->completed_at,
                ];
            }),
            'top_performers' => $allResults->sortByDesc('percentage')->take(10)->map(function($result) {
                return [
                    'student_name' => $result->student->full_name,
                    'class_name' => $result->student->classe->name ?? 'N/A',
                    'session_title' => $result->quizSession->title,
                    'score' => round($result->percentage, 2),
                    'grade' => round($result->grade, 2),
                ];
            })
        ];
    }
}