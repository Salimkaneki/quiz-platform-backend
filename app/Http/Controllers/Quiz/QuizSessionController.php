<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\AuthorizationTrait;
use App\Http\Requests\StoreQuizSessionRequest;
use App\Http\Requests\UpdateQuizSessionRequest;
use App\Models\QuizSession;
use App\Models\Student;
use App\Services\PlatformNotificationService;
use App\Models\PlatformNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class QuizSessionController extends Controller
{
    use AuthorizationTrait;

    public function index()
    {
        $teacher = $this->getAuthenticatedTeacher();

        $query = QuizSession::where('teacher_id', $teacher->id)
            ->with(['quiz.subject']);

        // Filtres optionnels
        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        if (request()->has('quiz_id')) {
            $query->where('quiz_id', request('quiz_id'));
        }

        $sessions = $query->latest()
            ->paginate(request()->get('per_page', 15));

        return response()->json([
            'sessions' => $sessions->items(),
            'pagination' => [
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
            ]
        ]);
    }

    public function store(StoreQuizSessionRequest $request)
    {
        $teacher = $this->getAuthenticatedTeacher();
        $validated = $request->validated();

        // Validation des étudiants si fournis
        if (!empty($validated['allowed_students'])) {
            $this->validateStudentsInstitution($validated['allowed_students'], $teacher);
        }

        // Vérification des conflits d'horaires
        $this->checkScheduleConflicts($teacher, $validated['starts_at'], $validated['ends_at']);

        // Vérifier les doublons
        $exists = QuizSession::where('teacher_id', $teacher->id)
            ->where('title', $validated['title'])
            ->where('starts_at', $validated['starts_at'])
            ->where('ends_at', $validated['ends_at'])
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Une session avec le même titre et les mêmes dates existe déjà.'
            ], 422);
        }

        $session = new QuizSession($validated);
        $session->teacher_id = $teacher->id;
        $session->status = 'scheduled';
        $session->generateSessionCode();
        $session->save();

        // Notifier les étudiants
        $notificationService = app(PlatformNotificationService::class);
        $studentsQuery = Student::active()->where('institution_id', $teacher->institution_id);

        if (!empty($session->allowed_students)) {
            $studentsQuery->whereIn('id', $session->allowed_students);
        }

        $students = $studentsQuery->with('user')->get();
        $users = $students->pluck('user')->filter();

        if ($users->isNotEmpty()) {
            $notificationService->createBulkNotifications(
                $users,
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
            'message' => 'Session créée avec succès',
            'session' => $session->load('quiz.subject')
        ], 201);
    }

    public function show($id)
    {
        $teacher = $this->getAuthenticatedTeacher();
        $session = QuizSession::with(['quiz.subject', 'results.student'])->findOrFail($id);

        $this->authorizeTeacherResource($session, 'session');

        return response()->json($session);
    }

    public function update(UpdateQuizSessionRequest $request, $id)
    {
        $teacher = $this->getAuthenticatedTeacher();
        $session = QuizSession::findOrFail($id);

        $this->authorizeTeacherResource($session, 'session');

        if (in_array($session->status, ['active', 'completed'])) {
            return response()->json([
                'error' => 'Impossible de modifier une session active ou terminée'
            ], 400);
        }

        $validated = $request->validated();

        // Validation des étudiants si fournis
        if (!empty($validated['allowed_students'])) {
            $this->validateStudentsInstitution($validated['allowed_students'], $teacher);
        }

        // Vérification des conflits d'horaires (exclure la session actuelle)
        $this->checkScheduleConflicts($teacher, $validated['starts_at'], $validated['ends_at'], $session->id);

        // Vérifier les doublons sauf la session courante
        $exists = QuizSession::where('teacher_id', $teacher->id)
            ->where('title', $validated['title'])
            ->where('starts_at', $validated['starts_at'])
            ->where('ends_at', $validated['ends_at'])
            ->where('id', '!=', $session->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Une autre session avec le même titre et les mêmes dates existe déjà.'
            ], 422);
        }

        $session->update($validated);

        return response()->json([
            'message' => 'Session mise à jour avec succès',
            'session' => $session->fresh()->load('quiz.subject')
        ]);
    }

    // Actions simplifiées avec 3 statuts seulement
    public function activate($id)
    {
        return $this->changeStatus($id, 'scheduled', 'active', 'activée', 'activated_at');
    }

    public function complete($id)
    {
        return $this->changeStatus($id, 'active', 'completed', 'terminée', 'completed_at');
    }

    public function cancel($id)
    {
        return $this->changeStatus($id, ['scheduled', 'active'], 'cancelled', 'annulée');
    }

    // CORRECTION: Amélioration de la méthode changeStatus
    private function changeStatus($id, $expectedStatus, $newStatus, $successMessage, $timestampField = null)
    {
        try {
            $teacher = $this->getAuthenticatedTeacher();
            $session = QuizSession::findOrFail($id);

            $this->authorizeTeacherResource($session, 'session');

            $expected = (array)$expectedStatus;
            if (!in_array($session->status, $expected)) {
                return response()->json([
                    'error' => "Impossible de {$successMessage} une session ayant le statut '{$session->status}'. Statut attendu : " . implode(' ou ', $expected)
                ], 400);
            }

            $updateData = ['status' => $newStatus];
            if ($timestampField) {
                $updateData[$timestampField] = now();
            }

            $session->update($updateData);

            // IMPORTANT: Recharger la session avec les relations
            $session = $session->fresh()->load('quiz.subject');

            return response()->json([
                'message' => "Session $successMessage avec succès",
                'session' => $session
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur lors du changement de statut de session", [
                'session_id' => $id,
                'action' => $successMessage,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Une erreur est survenue lors du changement de statut'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $teacher = $this->getAuthenticatedTeacher();
            $session = QuizSession::find($id);

            if (!$session) {
                return response()->json([
                    'error' => 'Session non trouvée'
                ], 404);
            }

            $this->authorizeTeacherResource($session, 'session');

            // Empêcher la suppression de sessions actives
            if ($session->status === 'active') {
                return response()->json([
                    'error' => 'Impossible de supprimer une session active. Veuillez d\'abord la terminer ou l\'annuler.'
                ], 400);
            }

            $session->delete();

            return response()->json([
                'message' => 'Session supprimée avec succès'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de session', [
                'session_id' => $id,
                'teacher_id' => $teacher->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Une erreur est survenue lors de la suppression'
            ], 500);
        }
    }
}