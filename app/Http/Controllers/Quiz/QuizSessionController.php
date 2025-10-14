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

        \Log::info('QuizSessionController@index - Teacher info', [
            'teacher_id' => $teacher->id,
            'user_id' => $teacher->user_id,
            'teacher_model' => $teacher
        ]);

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

        \Log::info('QuizSessionController@index - Query results', [
            'total_sessions' => $sessions->total(),
            'sessions_count' => $sessions->count(),
            'sessions_data' => $sessions->items()
        ]);

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

        \Log::info('QuizSessionController@store - Creating session', [
            'teacher_id' => $teacher->id,
            'user_id' => $teacher->user_id,
            'validated_data' => $validated
        ]);

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

        \Log::info('QuizSessionController@store - Session created', [
            'session_id' => $session->id,
            'session_teacher_id' => $session->teacher_id,
            'session_code' => $session->session_code
        ]);

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
                $users->pluck('id')->toArray(), // Convertir la Collection en array d'IDs
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

        // Ajouter les statistiques des résultats
        $session->results_statistics = $this->getSessionResultsStatistics($session);

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
        try {
            $teacher = $this->getAuthenticatedTeacher();
            $session = QuizSession::findOrFail($id);

            $this->authorizeTeacherResource($session, 'session');

            if ($session->status !== 'active') {
                return response()->json([
                    'error' => "Impossible de terminer une session ayant le statut '{$session->status}'. La session doit être active."
                ], 400);
            }

            // Terminer la session
            $session->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            // Publier automatiquement tous les résultats soumis de cette session
            $submittedResults = $session->results()->where('status', 'submitted')->get();
            foreach ($submittedResults as $result) {
                $result->markAsPublished();
            }

            // Recharger la session avec les relations
            $session = $session->fresh()->load(['quiz.subject', 'results.student']);

            return response()->json([
                'message' => 'Session terminée avec succès. Tous les résultats soumis ont été publiés.',
                'session' => $session,
                'published_results_count' => $submittedResults->count(),
                'results_url' => route('teacher.sessions.results', ['id' => $session->id]),
                'statistics' => $this->getSessionResultsStatistics($session),
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur lors de la terminaison de session", [
                'session_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Une erreur est survenue lors de la terminaison de la session'
            ], 500);
        }
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

    /**
     * Calculer les statistiques des résultats d'une session
     */
    private function getSessionResultsStatistics(QuizSession $session)
    {
        $allResults = $session->results;
        $totalParticipants = $allResults->count();

        if ($totalParticipants === 0) {
            return [
                'total_participants' => 0,
                'submitted_results' => 0,
                'graded_results' => 0,
                'published_results' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'completion_rate' => 0,
                'score_distribution' => [
                    'excellent' => 0, // 90-100%
                    'good' => 0,      // 80-89%
                    'average' => 0,   // 60-79%
                    'poor' => 0,      // < 60%
                ],
            ];
        }

        $submittedResults = $allResults->where('status', 'submitted');
        $gradedResults = $allResults->where('status', 'graded');
        $publishedResults = $allResults->where('status', 'published');

        $publishedScores = $publishedResults->pluck('percentage');
        $averageScore = $publishedScores->avg() ?? 0;
        $highestScore = $publishedScores->max() ?? 0;
        $lowestScore = $publishedScores->min() ?? 0;

        // Répartition des scores
        $scoreDistribution = [
            'excellent' => $publishedScores->filter(fn($score) => $score >= 90)->count(),
            'good' => $publishedScores->filter(fn($score) => $score >= 80 && $score < 90)->count(),
            'average' => $publishedScores->filter(fn($score) => $score >= 60 && $score < 80)->count(),
            'poor' => $publishedScores->filter(fn($score) => $score < 60)->count(),
        ];

        return [
            'total_participants' => $totalParticipants,
            'submitted_results' => $submittedResults->count(),
            'graded_results' => $gradedResults->count(),
            'published_results' => $publishedResults->count(),
            'average_score' => round($averageScore, 2),
            'highest_score' => round($highestScore, 2),
            'lowest_score' => round($lowestScore, 2),
            'completion_rate' => round(($submittedResults->count() / $totalParticipants) * 100, 2),
            'score_distribution' => $scoreDistribution,
        ];
    }
}