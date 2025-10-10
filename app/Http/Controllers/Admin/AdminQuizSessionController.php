<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizSessionRequest;
use App\Http\Requests\UpdateQuizSessionRequest;
use App\Models\QuizSession;
use App\Models\Student;
use App\Models\Administrator;
use App\Services\PlatformNotificationService;
use App\Models\PlatformNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AdminQuizSessionController extends Controller
{
    /**
     * Récupérer l'institution ID de l'admin connecté
     */
    private function getInstitutionId()
    {
        $admin = Administrator::where('user_id', Auth::id())->firstOrFail();
        return $admin->institution_id;
    }

    /**
     * Liste de toutes les sessions de l'institution
     * GET /api/admin/quiz-sessions
     */
    public function index(Request $request)
    {
        $institutionId = $this->getInstitutionId();

        $query = QuizSession::with(['quiz.subject', 'teacher.user'])
            ->whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            });

        // Filtres optionnels
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $sessions = $query->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $sessions,
            'filters' => [
                'institution_id' => $institutionId,
                'status' => $request->status,
                'quiz_id' => $request->quiz_id,
                'teacher_id' => $request->teacher_id,
            ]
        ]);
    }

    /**
     * Créer une session d'examen (Admin)
     * POST /api/admin/quiz-sessions
     */
    public function store(StoreQuizSessionRequest $request)
    {
        $institutionId = $this->getInstitutionId();
        $validated = $request->validated();

        // Déterminer l'enseignant pour la session
        $teacherId = $validated['teacher_id'] ?? null;

        // Si pas d'enseignant spécifié, récupérer l'enseignant du quiz
        if (!$teacherId) {
            $quiz = \App\Models\Quiz::find($validated['quiz_id']);
            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz non trouvé'
                ], 404);
            }
            $teacherId = $quiz->teacher_id;
        }

        // Vérifier que l'enseignant appartient à l'institution
        $teacher = \App\Models\Teacher::where('id', $teacherId)
            ->where('institution_id', $institutionId)
            ->first();

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé ou n\'appartenant pas à votre institution'
            ], 404);
        }

        // Vérifier que le quiz appartient à l'enseignant spécifié
        $quiz = \App\Models\Quiz::where('id', $validated['quiz_id'])
            ->where('teacher_id', $teacherId)
            ->where('status', 'published')
            ->first();

        if (!$quiz) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz non trouvé, non publié, ou n\'appartenant pas à l\'enseignant spécifié'
            ], 404);
        }

        // Validation des étudiants si fournis
        if (!empty($validated['allowed_students'])) {
            $this->validateStudentsInstitution($validated['allowed_students'], $institutionId);
        }

        // Vérifier les doublons
        $exists = QuizSession::where('teacher_id', $teacherId)
            ->where('title', $validated['title'])
            ->where('starts_at', $validated['starts_at'])
            ->where('ends_at', $validated['ends_at'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Une session avec le même titre et les mêmes dates existe déjà.'
            ], 422);
        }

        $session = new QuizSession($validated);
        $session->teacher_id = $teacherId; // Utiliser l'enseignant déterminé et validé
        $session->status = 'scheduled';
        $session->generateSessionCode();
        $session->save();

        // Notifier les étudiants
        $notificationService = app(PlatformNotificationService::class);
        $studentsQuery = Student::active()->where('institution_id', $institutionId);

        if (!empty($session->allowed_students)) {
            $studentsQuery->whereIn('id', $session->allowed_students);
        }

        $students = $studentsQuery->with('user')->get();
        $users = $students->pluck('user')->filter();

        if ($users->isNotEmpty()) {
            $notificationService->createBulkNotifications(
                $users->pluck('id')->toArray(),
                PlatformNotification::TYPE_QUIZ_SESSION_CREATED,
                'Nouvelle session d\'examen',
                "Une nouvelle session d'examen '{$session->title}' a été créée pour le {$session->starts_at->format('d/m/Y à H:i')}.",
                [
                    'session_id' => $session->id,
                    'quiz_id' => $session->quiz_id,
                    'starts_at' => $session->starts_at->toISOString(),
                    'ends_at' => $session->ends_at->toISOString(),
                ],
                $session->starts_at->addDays(1) // Expire le jour de la session
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Session créée avec succès',
            'data' => $session->load(['quiz.subject', 'teacher.user'])
        ], 201);
    }

    /**
     * Voir une session spécifique
     * GET /api/admin/quiz-sessions/{id}
     */
    public function show($id)
    {
        $institutionId = $this->getInstitutionId();

        $session = QuizSession::with(['quiz.subject', 'teacher.user', 'results.student.user'])
            ->whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $session
        ]);
    }

    /**
     * Modifier une session
     * PUT /api/admin/quiz-sessions/{id}
     */
    public function update(UpdateQuizSessionRequest $request, $id)
    {
        $institutionId = $this->getInstitutionId();

        $session = QuizSession::whereHas('teacher', function($q) use ($institutionId) {
            $q->where('institution_id', $institutionId);
        })->findOrFail($id);

        if (in_array($session->status, ['active', 'completed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de modifier une session active ou terminée'
            ], 400);
        }

        $validated = $request->validated();

        // Validation des étudiants si fournis
        if (!empty($validated['allowed_students'])) {
            $this->validateStudentsInstitution($validated['allowed_students'], $institutionId);
        }

        // Vérifier les doublons sauf la session courante
        $exists = QuizSession::where('teacher_id', $session->teacher_id)
            ->where('title', $validated['title'])
            ->where('starts_at', $validated['starts_at'])
            ->where('ends_at', $validated['ends_at'])
            ->where('id', '!=', $session->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Une autre session avec le même titre et les mêmes dates existe déjà.'
            ], 422);
        }

        $session->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Session mise à jour avec succès',
            'data' => $session->fresh()->load(['quiz.subject', 'teacher.user'])
        ]);
    }

    /**
     * Activer une session
     * PATCH /api/admin/quiz-sessions/{id}/activate
     */
    public function activate($id)
    {
        return $this->changeStatus($id, 'scheduled', 'active', 'activée', 'activated_at');
    }

    /**
     * Terminer une session
     * PATCH /api/admin/quiz-sessions/{id}/complete
     */
    public function complete($id)
    {
        return $this->changeStatus($id, 'active', 'completed', 'terminée', 'completed_at');
    }

    /**
     * Annuler une session
     * PATCH /api/admin/quiz-sessions/{id}/cancel
     */
    public function cancel($id)
    {
        return $this->changeStatus($id, ['scheduled', 'active'], 'cancelled', 'annulée');
    }

    /**
     * Supprimer une session
     * DELETE /api/admin/quiz-sessions/{id}
     */
    public function destroy($id)
    {
        $institutionId = $this->getInstitutionId();

        $session = QuizSession::whereHas('teacher', function($q) use ($institutionId) {
            $q->where('institution_id', $institutionId);
        })->findOrFail($id);

        // Empêcher la suppression de sessions actives
        if ($session->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une session active. Veuillez d\'abord la terminer ou l\'annuler.'
            ], 400);
        }

        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Session supprimée avec succès'
        ]);
    }

    /**
     * Liste des quiz disponibles pour créer des sessions
     * GET /api/admin/quiz-sessions/available-quizzes
     */
    public function getAvailableQuizzes(Request $request)
    {
        $institutionId = $this->getInstitutionId();

        $query = \App\Models\Quiz::with(['teacher.user', 'subject'])
            ->where('status', 'published')
            ->whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            });

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $quizzes = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $quizzes
        ]);
    }

    /**
     * Liste des enseignants disponibles pour assigner des sessions
     * GET /api/admin/quiz-sessions/available-teachers
     */
    public function getAvailableTeachers()
    {
        $institutionId = $this->getInstitutionId();

        $teachers = \App\Models\Teacher::with('user')
            ->where('institution_id', $institutionId)
            ->orderBy('user.name')
            ->get()
            ->map(function($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                    'grade' => $teacher->grade,
                    'is_permanent' => $teacher->is_permanent,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $teachers
        ]);
    }

    /**
     * Statistiques des sessions
     * GET /api/admin/quiz-sessions/statistics
     */
    public function getStatistics()
    {
        $institutionId = $this->getInstitutionId();

        $stats = [
            'total_sessions' => QuizSession::whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })->count(),

            'scheduled_sessions' => QuizSession::whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })->where('status', 'scheduled')->count(),

            'active_sessions' => QuizSession::whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })->where('status', 'active')->count(),

            'completed_sessions' => QuizSession::whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })->where('status', 'completed')->count(),

            'sessions_by_teacher' => \App\Models\Teacher::where('institution_id', $institutionId)
                ->withCount('quizSessions')
                ->get()
                ->map(function($teacher) {
                    return [
                        'teacher_id' => $teacher->id,
                        'teacher_name' => $teacher->user->name,
                        'total_sessions' => $teacher->quiz_sessions_count
                    ];
                }),

            'upcoming_sessions' => QuizSession::whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })
            ->where('status', 'scheduled')
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->limit(5)
            ->with(['quiz.subject', 'teacher.user'])
            ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Méthode privée pour changer le statut d'une session
     */
    private function changeStatus($id, $expectedStatus, $newStatus, $successMessage, $timestampField = null)
    {
        try {
            $institutionId = $this->getInstitutionId();

            $session = QuizSession::whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })->findOrFail($id);

            $expected = (array)$expectedStatus;
            if (!in_array($session->status, $expected)) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de {$successMessage} une session ayant le statut '{$session->status}'. Statut attendu : " . implode(' ou ', $expected)
                ], 400);
            }

            $updateData = ['status' => $newStatus];
            if ($timestampField) {
                $updateData[$timestampField] = now();
            }

            $session->update($updateData);

            $session = $session->fresh()->load(['quiz.subject', 'teacher.user']);

            return response()->json([
                'success' => true,
                'message' => "Session $successMessage avec succès",
                'data' => $session
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur lors du changement de statut de session", [
                'session_id' => $id,
                'action' => $successMessage,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du changement de statut'
            ], 500);
        }
    }

    /**
     * Validation des étudiants (privée)
     */
    private function validateStudentsInstitution($studentIds, $institutionId)
    {
        $invalidStudents = Student::whereIn('id', $studentIds)
            ->where('institution_id', '!=', $institutionId)
            ->pluck('id');

        if ($invalidStudents->isNotEmpty()) {
            throw ValidationException::withMessages([
                'allowed_students' => ['Certains étudiants n\'appartiennent pas à votre institution: ' . $invalidStudents->join(', ')]
            ]);
        }
    }
}