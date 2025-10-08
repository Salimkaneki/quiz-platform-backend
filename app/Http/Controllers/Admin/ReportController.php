<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizSession;
use App\Models\Administrator;
use App\Models\Result;
use App\Services\PlatformNotificationService;

class ReportController extends Controller
{
    protected $notificationService;

    public function __construct(PlatformNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Envoyer un rapport des résultats d'une session à tous les administrateurs
     */
    public function sendSessionReport(Request $request, $sessionId)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['error' => 'Accès réservé aux administrateurs pédagogiques'], 403);
        }

        try {
            // Vérifier que la session existe et appartient à l'institution
            $session = QuizSession::findOrFail($sessionId);

            if ($session->teacher->institution_id !== $admin->institution_id) {
                return response()->json(['error' => 'Session non trouvée dans votre institution'], 404);
            }

            // Vérifier qu'il y a des résultats
            $resultsCount = $session->results()->count();
            if ($resultsCount === 0) {
                return response()->json(['error' => 'Aucun résultat trouvé pour cette session'], 404);
            }

            // Déterminer les administrateurs à notifier
            $administratorIds = [];
            if ($request->has('administrator_ids') && is_array($request->administrator_ids)) {
                // Administrateurs spécifiques
                $administratorIds = $request->administrator_ids;
            }

            // Dispatcher le job
            SendSessionReport::dispatch($sessionId, $administratorIds);

            // Créer des notifications de plateforme pour les administrateurs
            $administrators = Administrator::with('user')
                ->where('institution_id', $admin->institution_id)
                ->get();

            $sessionData = [
                'session_id' => $sessionId,
                'title' => $session->title,
                'quiz_title' => $session->quiz->title,
                'completed_at' => $session->completed_at,
                'total_results' => $resultsCount,
            ];

            $this->notificationService->notifySessionCompleted(
                $administrators->pluck('user'),
                $sessionData
            );

            return response()->json([
                'message' => 'Rapport en cours d\'envoi en arrière-plan',
                'session_id' => $sessionId,
                'results_count' => $resultsCount,
                'platform_notifications_sent' => $administrators->count(),
                'status' => 'queued'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dispatch rapport session: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors de la mise en file d\'attente du rapport'
            ], 500);
        }
    }

    /**
     * Générer un rapport périodique pour toutes les sessions terminées
     */
    public function sendPeriodicReport(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['error' => 'Accès réservé aux administrateurs pédagogiques'], 403);
        }

        $request->validate([
            'period' => 'required|in:daily,weekly,monthly',
            'date' => 'nullable|date'
        ]);

        try {
            $date = $request->date ? \Carbon\Carbon::parse($request->date) : now();

            // Déterminer la période
            switch ($request->period) {
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

            // Récupérer les sessions terminées dans la période
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
            ->whereHas('teacher', function($query) use ($admin) {
                $query->where('institution_id', $admin->institution_id);
            })
            ->get();

            if ($sessions->isEmpty()) {
                return response()->json([
                    'message' => 'Aucune session terminée trouvée pour cette période'
                ]);
            }

            // Agréger les statistiques
            $reportData = $this->generatePeriodicReportData($sessions);

            // Récupérer les administrateurs
            $administrators = Administrator::with('user')
                ->where('institution_id', $admin->institution_id)
                ->get();

            // Envoyer le rapport périodique
            $sentCount = 0;
            foreach ($administrators as $administrator) {
                try {
                    Notification::send(
                        $administrator->user,
                        new PeriodicResultsReportNotification($reportData, $request->period, $administrator)
                    );
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error("Erreur envoi rapport périodique à {$administrator->user->email}: " . $e->getMessage());
                }
            }

            return response()->json([
                'message' => 'Rapport périodique envoyé avec succès',
                'period' => $request->period,
                'date_range' => [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')],
                'sessions_count' => $sessions->count(),
                'sent_to' => $sentCount
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur génération rapport périodique: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors de la génération du rapport périodique'
            ], 500);
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

    /**
     * Lister les sessions disponibles pour les rapports
     */
    public function getAvailableSessions()
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['error' => 'Accès réservé aux administrateurs pédagogiques'], 403);
        }

        $sessions = QuizSession::with(['quiz.subject', 'teacher.user'])
            ->whereHas('teacher', function($query) use ($admin) {
                $query->where('institution_id', $admin->institution_id);
            })
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function($session) {
                $resultsCount = $session->results()->count();
                $publishedCount = $session->results()->where('status', 'published')->count();

                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'quiz_title' => $session->quiz->title,
                    'teacher_name' => $session->teacher->user->name,
                    'completed_at' => $session->completed_at,
                    'total_results' => $resultsCount,
                    'published_results' => $publishedCount,
                    'completion_rate' => $resultsCount > 0 ? round(($publishedCount / $resultsCount) * 100, 1) : 0,
                ];
            });

        return response()->json([
            'sessions' => $sessions,
            'total' => $sessions->count()
        ]);
    }

    /**
     * Vérification des permissions pédagogiques
     */
    private function checkPedagogicalPermissions()
    {
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->account_type !== 'admin') {
            return null;
        }

        return Administrator::where('user_id', $currentUser->id)
            ->where('type', 'pedagogique')
            ->first();
    }
}