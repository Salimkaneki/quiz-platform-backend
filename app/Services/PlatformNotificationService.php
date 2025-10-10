<?php

namespace App\Services;

use App\Models\PlatformNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class PlatformNotificationService
{
    /**
     * Créer une notification pour un utilisateur (par ID)
     */
    public function createNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = [],
        $expiresAt = null
    ): PlatformNotification {
        return PlatformNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Créer une notification pour plusieurs utilisateurs (par IDs)
     */
    public function createBulkNotifications(
        array $userIds,
        string $type,
        string $title,
        string $message,
        array $data = [],
        $expiresAt = null
    ): int {
        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data),
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return PlatformNotification::insert($notifications);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(PlatformNotification $notification): bool
    {
        return $notification->update(['read_at' => now()]);
    }

    /**
     * Marquer plusieurs notifications comme lues
     */
    public function markBulkAsRead(Collection $notifications): int
    {
        return PlatformNotification::whereIn('id', $notifications->pluck('id'))
            ->update(['read_at' => now()]);
    }

    /**
     * Supprimer les notifications expirées
     */
    public function cleanupExpired(): int
    {
        return PlatformNotification::expired()->delete();
    }

    /**
     * Obtenir les notifications actives d'un utilisateur
     */
    public function getUserNotifications(User $user, int $limit = 50): Collection
    {
        return PlatformNotification::where('user_id', $user->id)
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Compter les notifications non lues d'un utilisateur
     */
    public function countUnread(User $user): int
    {
        return PlatformNotification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Créer une notification de rapport disponible
     */
    public function notifyReportAvailable(User $user, array $reportData): PlatformNotification
    {
        $title = "Rapport de résultats disponible";
        $message = "Un nouveau rapport de résultats est disponible pour consultation.";

        return $this->createNotification(
            $user,
            PlatformNotification::TYPE_REPORT_AVAILABLE,
            $title,
            $message,
            $reportData,
            now()->addDays(30) // Expire dans 30 jours
        );
    }

    /**
     * Créer une notification de session terminée
     */
    public function notifySessionCompleted(Collection $administrators, array $sessionData): int
    {
        $title = "Session d'examen terminée";
        $message = "La session '{$sessionData['title']}' s'est terminée. Les résultats sont disponibles.";

        return $this->createBulkNotifications(
            $administrators->pluck('user_id')->toArray(),
            PlatformNotification::TYPE_SESSION_COMPLETED,
            $title,
            $message,
            $sessionData,
            now()->addDays(7) // Expire dans 7 jours
        );
    }
}