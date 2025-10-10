<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\PlatformNotification;
use App\Services\PlatformNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TeacherNotificationController extends Controller
{
    protected PlatformNotificationService $notificationService;

    public function __construct(PlatformNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Récupérer toutes les notifications de l'enseignant connecté
     */
    public function index(Request $request): JsonResponse
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé'
            ], 404);
        }

        $query = PlatformNotification::where('user_id', $teacher->user_id)
            ->orderBy('created_at', 'desc');

        // Filtrage par type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Filtrage par statut de lecture
        if ($request->has('read')) {
            $read = filter_var($request->read, FILTER_VALIDATE_BOOLEAN);
            $query->where(function ($q) use ($read) {
                if ($read) {
                    $q->whereNotNull('read_at');
                } else {
                    $q->whereNull('read_at');
                }
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $notifications = $query->paginate($perPage);

        // Transformer les données pour inclure les labels
        $notifications->getCollection()->transform(function ($notification) {
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
        });

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(int $id): JsonResponse
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé'
            ], 404);
        }

        $notification = PlatformNotification::where('id', $id)
            ->where('user_id', $teacher->user_id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification non trouvée'
            ], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue',
            'data' => $notification
        ]);
    }

    /**
     * Marquer plusieurs notifications comme lues
     */
    public function markBulkAsRead(Request $request): JsonResponse
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé'
            ], 404);
        }

        try {
            $request->validate([
                'notification_ids' => 'required|array|min:1',
                'notification_ids.*' => 'integer'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $e->errors()
            ], 422);
        }

        // Vérifier que toutes les notifications appartiennent à l'utilisateur
        $notificationCount = PlatformNotification::whereIn('id', $request->notification_ids)
            ->where('user_id', $teacher->user_id)
            ->count();

        if ($notificationCount !== count($request->notification_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Une ou plusieurs notifications n\'existent pas ou ne vous appartiennent pas'
            ], 404);
        }

        $updated = PlatformNotification::whereIn('id', $request->notification_ids)
            ->where('user_id', $teacher->user_id)
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} notification(s) marquée(s) comme lue(s)"
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): JsonResponse
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé'
            ], 404);
        }

        $updated = PlatformNotification::where('user_id', $teacher->user_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Toutes les notifications ont été marquées comme lues ({$updated} notification(s))"
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function destroy(int $id): JsonResponse
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé'
            ], 404);
        }

        $notification = PlatformNotification::where('id', $id)
            ->where('user_id', $teacher->user_id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification non trouvée'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification supprimée avec succès'
        ]);
    }

    /**
     * Récupérer le nombre de notifications non lues
     */
    public function getUnreadCount(): JsonResponse
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé'
            ], 404);
        }

        $unreadCount = PlatformNotification::where('user_id', $teacher->user_id)
            ->whereNull('read_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $unreadCount
            ]
        ]);
    }
}