<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Student;

class StudentWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $student;
    protected $plainPassword;

    /**
     * Create a new notification instance.
     */
    public function __construct(Student $student, string $plainPassword)
    {
        $this->student = $student;
        $this->plainPassword = $plainPassword;
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
        $institutionName = $this->student->institution->name ?? 'Université';
        $className = $this->student->classe->name ?? 'Classe';

        return (new MailMessage)
            ->subject("Bienvenue sur la plateforme de quiz - {$institutionName}")
            ->greeting("Bienvenue {$this->student->first_name} !")
            ->line("Votre compte étudiant a été créé avec succès sur la plateforme de quiz de {$institutionName}.")
            ->line("**Informations de connexion :**")
            ->line("• **Email :** {$this->student->email}")
            ->line("• **Mot de passe temporaire :** {$this->plainPassword}")
            ->line("• **Classe :** {$className}")
            ->line("• **Numéro étudiant :** {$this->student->student_number}")
            ->action('Accéder à la plateforme', url('/login'))
            ->line("**⚠️ IMPORTANT :** Changez votre mot de passe dès votre première connexion pour des raisons de sécurité.")
            ->line("**Pour commencer :**")
            ->line("1. Cliquez sur le bouton ci-dessus ou allez sur " . url('/login'))
            ->line("2. Connectez-vous avec vos identifiants")
            ->line("3. Changez votre mot de passe dans les paramètres")
            ->line("4. Découvrez vos quiz disponibles")
            ->salutation("Cordialement,")
            ->salutation("L'équipe pédagogique")
            ->salutation($institutionName);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'student_id' => $this->student->id,
            'student_name' => $this->student->first_name . ' ' . $this->student->last_name,
            'institution' => $this->student->institution->name ?? 'Université',
            'class' => $this->student->classe->name ?? 'Classe',
        ];
    }
}
