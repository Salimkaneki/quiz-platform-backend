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
            'notifications' => $notifications->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'type_label' => $notification->getTypeLabel(),
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'is_read' => $notification->isRead(),
                    'created_at' => $notification->created_at,
                    'updated_at' => $notification->updated_at,
                    'expires_at' => $notification->expires_at,
                ];
            }),
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
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'required|integer'
        ]);

        $user = auth()->user();

        // Vérifier que toutes les notifications appartiennent à l'utilisateur
        $notificationCount = PlatformNotification::whereIn('id', $request->notification_ids)
            ->where('user_id', $user->id)
            ->count();

        if ($notificationCount !== count($request->notification_ids)) {
            return response()->json([
                'message' => 'Une ou plusieurs notifications n\'existent pas ou ne vous appartiennent pas'
            ], 404);
        }

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
            ->whereNull('read_at')
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