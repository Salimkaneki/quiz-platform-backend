<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\QuizSession;
use App\Models\Administrator;
use App\Notifications\SessionResultsReportNotification;
use App\Services\PlatformNotificationService;

class SendSessionReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sessionId;
    protected $administratorIds;

    /**
     * Create a new job instance.
     */
    public function __construct($sessionId, array $administratorIds = [])
    {
        $this->sessionId = $sessionId;
        $this->administratorIds = $administratorIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Récupérer la session avec ses résultats
            $session = QuizSession::with([
                'quiz.subject',
                'teacher.user',
                'results.student.classe'
            ])->find($this->sessionId);

            if (!$session) {
                Log::error("Session not found for report: {$this->sessionId}");
                return;
            }

            // Récupérer tous les résultats de la session
            $results = $session->results;

            if ($results->isEmpty()) {
                Log::info("No results found for session: {$this->sessionId}");
                return;
            }

            // Déterminer les administrateurs à notifier
            if (empty($this->administratorIds)) {
                // Tous les administrateurs de l'institution
                $administrators = Administrator::with('user')
                    ->where('institution_id', $session->teacher->institution_id)
                    ->get();
            } else {
                // Administrateurs spécifiques
                $administrators = Administrator::with('user')
                    ->whereIn('id', $this->administratorIds)
                    ->get();
            }

            if ($administrators->isEmpty()) {
                Log::warning("No administrators found for session report: {$this->sessionId}");
                return;
            }

            // Envoyer les notifications par email
            $emailSentCount = 0;
            foreach ($administrators as $administrator) {
                try {
                    $administrator->user->notify(
                        new SessionResultsReportNotification($session, $results, $administrator)
                    );
                    $emailSentCount++;
                    Log::info("Email report sent to administrator: {$administrator->user->email}");
                } catch (\Exception $e) {
                    Log::error("Error sending email report to {$administrator->user->email}: " . $e->getMessage());
                }
            }

            // Créer des notifications de plateforme
            $notificationService = app(PlatformNotificationService::class);
            $platformNotificationCount = $notificationService->notifySessionCompleted(
                $administrators->pluck('user'),
                [
                    'session_id' => $session->id,
                    'title' => $session->title,
                    'quiz_title' => $session->quiz->title,
                    'completed_at' => $session->completed_at,
                    'total_results' => $results->count(),
                ]
            );

            Log::info("Session report job completed. Session: {$this->sessionId}, Email sent to: {$emailSentCount} administrators, Platform notifications: {$platformNotificationCount}");

        } catch (\Exception $e) {
            Log::error("Error in SendSessionReport job: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendSessionReport job failed: " . $exception->getMessage());
    }
}