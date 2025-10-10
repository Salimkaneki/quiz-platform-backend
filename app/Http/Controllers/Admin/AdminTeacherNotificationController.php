<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformNotification;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Administrator;
use App\Services\PlatformNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminTeacherNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(PlatformNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Envoyer une notification à tous les enseignants
     */
    public function sendToAllTeachers(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'type' => 'required|string|in:admin_announcement,schedule_change,maintenance_warning,policy_update,training_required,system_update',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now'
        ]);

        try {
            // Récupérer tous les enseignants de l'institution
            $teachers = Teacher::whereHas('user', function($query) use ($admin) {
                $query->where('account_type', 'teacher');
            })->where('institution_id', $admin->institution_id)->get();

            if ($teachers->isEmpty()) {
                return response()->json(['message' => 'Aucun enseignant trouvé dans cette institution'], 404);
            }

            $teacherUserIds = $teachers->pluck('user_id')->toArray();

            Log::info('AdminTeacherNotificationController@sendToAllTeachers', [
                'admin_id' => $admin->id,
                'teacher_count' => count($teacherUserIds),
                'type' => $data['type'],
                'title' => $data['title']
            ]);

            // Créer les notifications en bulk
            $count = $this->notificationService->createBulkNotifications(
                $teacherUserIds,
                $data['type'],
                $data['title'],
                $data['message'],
                $data['data'] ?? [],
                $data['expires_at'] ?? null
            );

            return response()->json([
                'message' => "Notification envoyée à {$count} enseignants",
                'notification_type' => $data['type'],
                'title' => $data['title'],
                'recipients_count' => $count
            ], 201);

        } catch (\Exception $e) {
            Log::error('AdminTeacherNotificationController@sendToAllTeachers - Erreur', [
                'error' => $e->getMessage(),
                'admin_id' => $admin->id,
                'data' => $data
            ]);

            return response()->json([
                'message' => 'Erreur lors de l\'envoi de la notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer une notification à un enseignant spécifique
     */
    public function sendToSpecificTeacher(Request $request, $teacherId)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'type' => 'required|string|in:admin_announcement,teacher_assignment,schedule_change,maintenance_warning,policy_update,training_required,performance_feedback,system_update',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now'
        ]);

        try {
            // Vérifier que l'enseignant existe et appartient à l'institution
            $teacher = Teacher::where('id', $teacherId)
                ->where('institution_id', $admin->institution_id)
                ->first();

            if (!$teacher) {
                return response()->json(['message' => 'Enseignant non trouvé ou non autorisé'], 404);
            }

            Log::info('AdminTeacherNotificationController@sendToSpecificTeacher', [
                'admin_id' => $admin->id,
                'teacher_id' => $teacherId,
                'type' => $data['type'],
                'title' => $data['title']
            ]);

            // Créer la notification
            $notification = $this->notificationService->createNotification(
                $teacher->user_id,
                $data['type'],
                $data['title'],
                $data['message'],
                $data['data'] ?? [],
                $data['expires_at'] ?? null
            );

            return response()->json([
                'message' => 'Notification envoyée avec succès',
                'notification' => $notification,
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name ?? 'N/A',
                    'email' => $teacher->user->email ?? 'N/A'
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('AdminTeacherNotificationController@sendToSpecificTeacher - Erreur', [
                'error' => $e->getMessage(),
                'admin_id' => $admin->id,
                'teacher_id' => $teacherId,
                'data' => $data
            ]);

            return response()->json([
                'message' => 'Erreur lors de l\'envoi de la notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer une notification à plusieurs enseignants spécifiques
     */
    public function sendToMultipleTeachers(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'required|integer|exists:teachers,id',
            'type' => 'required|string|in:admin_announcement,teacher_assignment,schedule_change,maintenance_warning,policy_update,training_required,performance_feedback,system_update',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now'
        ]);

        try {
            // Vérifier que tous les enseignants appartiennent à l'institution
            $teachers = Teacher::whereIn('id', $data['teacher_ids'])
                ->where('institution_id', $admin->institution_id)
                ->get();

            if ($teachers->count() !== count($data['teacher_ids'])) {
                return response()->json(['message' => 'Un ou plusieurs enseignants ne sont pas autorisés'], 403);
            }

            $teacherUserIds = $teachers->pluck('user_id')->toArray();

            Log::info('AdminTeacherNotificationController@sendToMultipleTeachers', [
                'admin_id' => $admin->id,
                'teacher_ids' => $data['teacher_ids'],
                'teacher_count' => count($teacherUserIds),
                'type' => $data['type'],
                'title' => $data['title']
            ]);

            // Créer les notifications en bulk
            $count = $this->notificationService->createBulkNotifications(
                $teacherUserIds,
                $data['type'],
                $data['title'],
                $data['message'],
                $data['data'] ?? [],
                $data['expires_at'] ?? null
            );

            return response()->json([
                'message' => "Notification envoyée à {$count} enseignants",
                'notification_type' => $data['type'],
                'title' => $data['title'],
                'recipients_count' => $count,
                'recipients' => $teachers->map(function($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->user->name ?? 'N/A',
                        'email' => $teacher->user->email ?? 'N/A'
                    ];
                })
            ], 201);

        } catch (\Exception $e) {
            Log::error('AdminTeacherNotificationController@sendToMultipleTeachers - Erreur', [
                'error' => $e->getMessage(),
                'admin_id' => $admin->id,
                'teacher_ids' => $data['teacher_ids'],
                'data' => $data
            ]);

            return response()->json([
                'message' => 'Erreur lors de l\'envoi de la notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les enseignants disponibles pour les notifications
     */
    public function getAvailableTeachers(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $query = Teacher::with(['user'])
            ->where('institution_id', $admin->institution_id);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function($userQuery) use ($request) {
                    $userQuery->where('name', 'like', "%{$request->search}%")
                              ->orWhere('email', 'like', "%{$request->search}%");
                });
            });
        }

        $teachers = $query->join('users', 'teachers.user_id', '=', 'users.id')
                          ->orderBy('users.name')
                          ->select('teachers.*')
                          ->get();

        return response()->json([
            'teachers' => $teachers->map(function($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name ?? 'N/A',
                    'email' => $teacher->user->email ?? null,
                    'subjects' => [], // TODO: Implement when teacher_subjects table exists
                    'grade' => $teacher->grade,
                    'is_permanent' => $teacher->is_permanent
                ];
            })
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