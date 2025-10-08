<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\QuizSession;
use App\Models\Administrator;

class SessionResultsReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $quizSession;
    protected $results;
    protected $administrator;

    /**
     * Create a new notification instance.
     */
    public function __construct(QuizSession $quizSession, $results, Administrator $administrator)
    {
        $this->quizSession = $quizSession;
        $this->results = $results;
        $this->administrator = $administrator;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = "Rapport des résultats - {$this->quizSession->title}";
        $institutionName = $this->administrator->institution->name ?? 'Institution';

        // Statistiques générales
        $totalParticipants = count($this->results);
        $submittedCount = collect($this->results)->where('status', 'submitted')->count();
        $gradedCount = collect($this->results)->where('status', 'graded')->count();
        $publishedCount = collect($this->results)->where('status', 'published')->count();
        $averageScore = collect($this->results)->where('status', 'published')->avg('percentage') ?? 0;

        // Construction du contenu HTML du rapport
        $htmlContent = $this->buildResultsTable();

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Rapport des résultats d'examen")
            ->line("**Session :** {$this->quizSession->title}")
            ->line("**Quiz :** {$this->quizSession->quiz->title}")
            ->line("**Date :** {$this->quizSession->starts_at->format('d/m/Y H:i')} - {$this->quizSession->ends_at->format('d/m/Y H:i')}")
            ->line("**Enseignant :** {$this->quizSession->teacher->user->name}")
            ->line("")
            ->line("**Statistiques générales :**")
            ->line("• Total participants : {$totalParticipants}")
            ->line("• Réponses soumises : {$submittedCount}")
            ->line("• Résultats corrigés : {$gradedCount}")
            ->line("• Résultats publiés : {$publishedCount}")
            ->line("• Score moyen : " . round($averageScore, 2) . "%")
            ->line("")
            ->line("**Détail des résultats :**")
            ->line($htmlContent)
            ->salutation("Cordialement,")
            ->salutation("L'équipe {$institutionName}");
    }

    /**
     * Construction du tableau HTML des résultats
     */
    private function buildResultsTable()
    {
        $html = "<table style='border-collapse: collapse; width: 100%;'>
            <thead>
                <tr style='background-color: #f8f9fa;'>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Étudiant</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>Classe</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>Statut</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>Score (%)</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>Note/20</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>Soumis le</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($this->results as $result) {
            $studentName = $result->student->full_name ?? 'N/A';
            $className = $result->student->classe->name ?? 'N/A';
            $status = $this->getStatusLabel($result->status);
            $score = $result->status === 'published' ? round($result->percentage, 2) . '%' : '-';
            $grade = $result->status === 'published' ? round($result->grade, 2) : '-';
            $submittedAt = $result->submitted_at ? $result->submitted_at->format('d/m/Y H:i') : '-';

            $html .= "<tr>
                <td style='border: 1px solid #dee2e6; padding: 8px;'>{$studentName}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>{$className}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>{$status}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>{$score}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>{$grade}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>{$submittedAt}</td>
            </tr>";
        }

        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * Traduction des statuts
     */
    private function getStatusLabel($status)
    {
        return match($status) {
            'in_progress' => 'En cours',
            'submitted' => 'Soumis',
            'graded' => 'Corrigé',
            'published' => 'Publié',
            default => ucfirst($status)
        };
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'quiz_session_id' => $this->quizSession->id,
            'session_title' => $this->quizSession->title,
            'quiz_title' => $this->quizSession->quiz->title,
            'total_participants' => count($this->results),
            'submitted_count' => collect($this->results)->where('status', 'submitted')->count(),
            'published_count' => collect($this->results)->where('status', 'published')->count(),
            'average_score' => collect($this->results)->where('status', 'published')->avg('percentage') ?? 0,
        ];
    }
}