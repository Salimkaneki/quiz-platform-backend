<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Administrator;

class PeriodicResultsReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reportData;
    protected $period;
    protected $administrator;

    /**
     * Create a new notification instance.
     */
    public function __construct($reportData, $period, Administrator $administrator)
    {
        $this->reportData = $reportData;
        $this->period = $period;
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
        $periodLabel = $this->getPeriodLabel($this->period);
        $subject = "Rapport périodique des résultats - {$periodLabel}";
        $institutionName = $this->administrator->institution->name ?? 'Institution';

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Rapport périodique des résultats d'examen")
            ->line("**Période :** {$periodLabel}")
            ->line("**Institution :** {$institutionName}")
            ->line("")
            ->line("**Statistiques globales :**")
            ->line("• Sessions terminées : {$this->reportData['period']['total_sessions']}")
            ->line("• Total participants : {$this->reportData['period']['total_participants']}")
            ->line("• Score moyen : {$this->reportData['period']['average_score']}%")
            ->line("• Score le plus élevé : {$this->reportData['period']['highest_score']}%")
            ->line("• Score le plus bas : {$this->reportData['period']['lowest_score']}%")
            ->line("")
            ->line("**Détail des sessions :**")
            ->line($this->buildSessionsTable())
            ->line("")
            ->line("**Top 10 des meilleurs résultats :**")
            ->line($this->buildTopPerformersTable())
            ->salutation("Cordialement,")
            ->salutation("L'équipe {$institutionName}");
    }

    /**
     * Construction du tableau des sessions
     */
    private function buildSessionsTable()
    {
        $html = "<table style='border-collapse: collapse; width: 100%;'>
            <thead>
                <tr style='background-color: #f8f9fa;'>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Session</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Quiz</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Enseignant</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>Participants</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>Score moyen</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>Terminée le</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($this->reportData['sessions'] as $session) {
            $html .= "<tr>
                <td style='border: 1px solid #dee2e6; padding: 8px;'>{$session['title']}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px;'>{$session['quiz_title']}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px;'>{$session['teacher']}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>{$session['participants_count']}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>" . round($session['average_score'], 1) . "%</td>
                <td style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>" . ($session['completed_at'] ? $session['completed_at']->format('d/m/Y') : '-') . "</td>
            </tr>";
        }

        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * Construction du tableau des meilleurs résultats
     */
    private function buildTopPerformersTable()
    {
        $html = "<table style='border-collapse: collapse; width: 100%;'>
            <thead>
                <tr style='background-color: #f8f9fa;'>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Étudiant</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Classe</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Session</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>Score (%)</th>
                    <th style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>Note/20</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($this->reportData['top_performers'] as $performer) {
            $html .= "<tr>
                <td style='border: 1px solid #dee2e6; padding: 8px;'>{$performer['student_name']}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px;'>{$performer['class_name']}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px;'>{$performer['session_title']}</td>
                <td style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>{$performer['score']}%</td>
                <td style='border: 1px solid #dee2e6; padding: 8px; text-align: center;'>{$performer['grade']}</td>
            </tr>";
        }

        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * Traduction des périodes
     */
    private function getPeriodLabel($period)
    {
        return match($period) {
            'daily' => 'Quotidien',
            'weekly' => 'Hebdomadaire',
            'monthly' => 'Mensuel',
            default => ucfirst($period)
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
            'report_type' => 'periodic',
            'period' => $this->period,
            'total_sessions' => $this->reportData['period']['total_sessions'],
            'total_participants' => $this->reportData['period']['total_participants'],
            'average_score' => $this->reportData['period']['average_score'],
        ];
    }
}