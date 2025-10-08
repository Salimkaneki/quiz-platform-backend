<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformNotification;
use App\Services\PlatformNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(PlatformNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Récupérer les notifications de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = PlatformNotification::where('user_id', $user->id);

        // Filtrer par statut de lecture
        if ($request->has('read')) {
            if ($request->boolean('read')) {
                $query->read();
            } else {
                $query->unread();
            }
        }

        // Filtrer par type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->active()
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'notifications' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
            'unread_count' => $this->notificationService->countUnread($user),
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($id)
    {
        $user = auth()->user();

        $notification = PlatformNotification::where('user_id', $user->id)
            ->findOrFail($id);

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'message' => 'Notification marquée comme lue',
            'notification' => $notification->fresh()
        ]);
    }

    /**
     * Marquer plusieurs notifications comme lues
     */
    public function markBulkAsRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'required|integer|exists:platform_notifications,id'
        ]);

        $user = auth()->user();

        $notifications = PlatformNotification::where('user_id', $user->id)
            ->whereIn('id', $request->notification_ids)
            ->get();

        $count = $this->notificationService->markBulkAsRead($notifications);

        return response()->json([
            'message' => "{$count} notifications marquées comme lues",
            'unread_count' => $this->notificationService->countUnread($user)
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        $user = auth()->user();

        $count = PlatformNotification::where('user_id', $user->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => "{$count} notifications marquées comme lues",
            'unread_count' => 0
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function destroy($id)
    {
        $user = auth()->user();

        $notification = PlatformNotification::where('user_id', $user->id)
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'message' => 'Notification supprimée',
            'unread_count' => $this->notificationService->countUnread($user)
        ]);
    }

    /**
     * Obtenir le compteur de notifications non lues
     */
    public function getUnreadCount()
    {
        $user = auth()->user();

        return response()->json([
            'unread_count' => $this->notificationService->countUnread($user)
        ]);
    }

    /**
     * Nettoyer les notifications expirées (admin seulement)
     */
    public function cleanupExpired()
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['error' => 'Accès réservé aux administrateurs pédagogiques'], 403);
        }

        $count = $this->notificationService->cleanupExpired();

        return response()->json([
            'message' => "{$count} notifications expirées supprimées"
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

        return \App\Models\Administrator::where('user_id', $currentUser->id)
            ->where('type', 'pedagogique')
            ->first();
    }
}