<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QuizSession;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentSessionController extends Controller
{
    /**
     * Voir les détails d'une session pour un étudiant (s'il y participe)
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!$user || $user->account_type !== 'student') {
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
        }

        // Récupérer le profil étudiant
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json(['error' => 'Profil étudiant non trouvé'], 403);
        }

        // Vérifier que l'étudiant participe à cette session
        $result = Result::where('quiz_session_id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$result) {
            return response()->json(['error' => 'Vous ne participez pas à cette session'], 403);
        }

        $session = QuizSession::with('quiz.subject')->findOrFail($id);

        return response()->json([
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'session_code' => $session->session_code,
                'status' => $session->status,
                'starts_at' => $session->starts_at,
                'ends_at' => $session->ends_at,
                'max_participants' => $session->max_participants,
                'quiz' => [
                    'id' => $session->quiz->id,
                    'title' => $session->quiz->title,
                    'subject' => $session->quiz->subject ? [
                        'id' => $session->quiz->subject->id,
                        'name' => $session->quiz->subject->name
                    ] : null,
                    'duration_minutes' => $session->quiz->duration_minutes
                ]
            ],
            'result' => [
                'id' => $result->id,
                'status' => $result->status,
                'total_points' => $result->total_points,
                'max_points' => $result->max_points,
                'percentage' => $result->percentage,
                'started_at' => $result->started_at,
                'submitted_at' => $result->submitted_at
            ]
        ]);
    }

    /**
     * Lister les sessions d'examen disponibles pour l'étudiant
     */

/**
 * Lister les sessions d'examen disponibles pour l'étudiant
 */
public function index()
{
    $user = Auth::user();
    if (!$user || $user->account_type !== 'student') {
        return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
    }

    // Récupérer le profil étudiant
    $student = Student::where('user_id', $user->id)->first();
    if (!$student) {
        return response()->json(['error' => 'Profil étudiant non trouvé'], 403);
    }

    // Récupérer les sessions actives (l'heure est vérifiée lors de la tentative de rejoindre)
    $sessions = QuizSession::with('quiz.subject')
        ->where('status', 'active')
        ->get()
        ->map(function($session) use ($user, $student) {
            $now = now();
            if ($now->lt($session->starts_at)) {
                $join_status = 'à venir';
            } elseif ($now->gt($session->ends_at)) {
                $join_status = 'terminée';
            } else {
                $join_status = 'disponible';
            }

            // Vérifier si l'étudiant a déjà rejoint cette session
            $has_joined = Result::where('quiz_session_id', $session->id)
                ->where('student_id', $student->id)
                ->exists();

            return [
                'id' => $session->id,
                'title' => $session->title,
                'session_code' => $session->session_code,
                'status' => $session->status,
                'starts_at' => $session->starts_at,
                'ends_at' => $session->ends_at,
                'max_participants' => $session->max_participants,
                'join_status' => $join_status,
                'has_joined' => $has_joined,
                'quiz' => [
                    'id' => $session->quiz->id,
                    'title' => $session->quiz->title,
                    'subject' => $session->quiz->subject ? [
                        'id' => $session->quiz->subject->id,
                        'name' => $session->quiz->subject->name
                    ] : null,
                    'duration_minutes' => $session->quiz->duration_minutes
                ]
            ];
        });

    return response()->json([
        'sessions' => $sessions,
        'total' => $sessions->count()
    ]);
}

    /**
     * Rejoindre une session via son code
     */
    public function joinSession(Request $request)
    {
        $request->validate([
            'session_code' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user || $user->account_type !== 'student') {
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
        }

        // Récupérer le profil étudiant
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json(['error' => 'Profil étudiant non trouvé'], 403);
        }

        // Chercher la session par code
        $session = QuizSession::where('session_code', $request->session_code)
            ->with('quiz.questions')
            ->first();

        if (!$session) {
            return response()->json(['error' => 'Session introuvable'], 404);
        }

        // Vérifier que la session est active ou programmée
        if (!in_array($session->status, ['scheduled', 'active'])) {
            return response()->json(['error' => 'Session non disponible'], 400);
        }

        // Vérifier les horaires
        if (now()->lt($session->starts_at)) {
            return response()->json(['error' => 'La session n\'a pas encore commencé'], 400);
        }

        if (now()->gt($session->ends_at)) {
            return response()->json(['error' => 'La session est terminée'], 400);
        }

        // Vérifier l'accès de l'étudiant si session restreinte
        if ($session->access_type === 'restricted') {
            $allowedIds = $session->allowed_students ?? [];
            if (!in_array($user->id, $allowedIds)) {
                return response()->json(['error' => 'Vous n\'êtes pas autorisé à rejoindre cette session'], 403);
            }
        }

        // Vérifier la limite de participants
        if ($session->max_participants) {
            $currentCount = Result::where('quiz_session_id', $session->id)->count();
            if ($currentCount >= $session->max_participants) {
                return response()->json(['error' => 'Nombre maximum de participants atteint'], 400);
            }
        }

        // Créer ou récupérer le Result de l'étudiant
        $result = Result::firstOrCreate(
            [
                'quiz_session_id' => $session->id,
                'student_id' => $student->id
            ],
            [
                'status' => Result::STATUS_IN_PROGRESS,
                'total_points' => 0,
                'max_points' => $session->quiz->questions->sum('points'),
                'percentage' => 0,
                'total_questions' => $session->quiz->questions->count(),
                'correct_answers' => 0,
                'started_at' => now(),
                'detailed_stats' => []
            ]
        );

        return response()->json([
            'message' => 'Session rejointe avec succès',
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'quiz' => [
                    'id' => $session->quiz->id,
                    'title' => $session->quiz->title,
                    'questions_count' => $session->quiz->questions->count(),
                    'shuffle_questions' => $session->quiz->shuffle_questions
                ],
                'status' => $session->status,
                'started_at' => $session->starts_at,
                'ends_at' => $session->ends_at,
            ],
            'result_id' => $result->id
        ], 200);
    }

    /**
     * Récupérer les questions d'une session pour l'étudiant
     * GET /api/student/session/{sessionId}/questions
     */
    public function getQuestions($sessionId)
    {
        $user = Auth::user();
        if (!$user || $user->account_type !== 'student') {
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
        }

        // Récupérer le profil étudiant
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json(['error' => 'Profil étudiant non trouvé'], 403);
        }

        // Vérifier que l'étudiant participe à cette session
        $result = Result::where('quiz_session_id', $sessionId)
            ->where('student_id', $student->id)
            ->first();

        if (!$result) {
            return response()->json(['error' => 'Vous ne participez pas à cette session'], 403);
        }

        $session = QuizSession::with(['quiz.questions' => function($query) {
            $query->ordered();
        }])->findOrFail($sessionId);

        // Vérifier que la session est accessible
        if (!in_array($session->status, ['scheduled', 'active'])) {
            return response()->json(['error' => 'Session non disponible'], 400);
        }

        // Formater les questions pour l'étudiant (sans les bonnes réponses)
        $questions = $session->quiz->questions->map(function($question) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'type' => $question->type,
                'points' => $question->points,
                'order' => $question->order,
                'image_url' => $question->image_url,
                'time_limit' => $question->time_limit,
                'options' => $question->type === 'multiple_choice' ? 
                    collect($question->options)->map(function($option, $index) {
                        return [
                            'id' => $index,
                            'text' => $option['text'] ?? ''
                        ];
                    })->values()->toArray() : null
            ];
        });

        return response()->json([
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'status' => $session->status,
                'starts_at' => $session->starts_at,
                'ends_at' => $session->ends_at,
                'duration_minutes' => $session->quiz->duration_minutes,
            ],
            'questions' => $questions,
            'total_questions' => $questions->count(),
            'result_id' => $result->id
        ]);
    }

    /**
     * Récupérer une question spécifique
     * GET /api/student/session/{sessionId}/questions/{questionId}
     */
    public function getQuestion($sessionId, $questionId)
    {
        $user = Auth::user();
        if (!$user || $user->account_type !== 'student') {
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
        }

        // Récupérer le profil étudiant
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json(['error' => 'Profil étudiant non trouvé'], 403);
        }

        // Vérifier que l'étudiant participe à cette session
        $result = Result::where('quiz_session_id', $sessionId)
            ->where('student_id', $student->id)
            ->first();

        if (!$result) {
            return response()->json(['error' => 'Vous ne participez pas à cette session'], 403);
        }

        $question = \App\Models\Question::whereHas('quiz.sessions', function($query) use ($sessionId) {
            $query->where('id', $sessionId);
        })->findOrFail($questionId);

        // Vérifier si l'étudiant a déjà répondu à cette question
        $existingResponse = \App\Models\StudentResponse::where('quiz_session_id', $sessionId)
            ->where('student_id', $student->id)
            ->where('question_id', $questionId)
            ->first();

        return response()->json([
            'question' => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'type' => $question->type,
                'points' => $question->points,
                'order' => $question->order,
                'image_url' => $question->image_url,
                'time_limit' => $question->time_limit,
                'options' => $question->type === 'multiple_choice' ? 
                    collect($question->options)->map(function($option, $index) {
                        return [
                            'id' => $index,
                            'text' => $option['text'] ?? ''
                        ];
                    })->values()->toArray() : null
            ],
            'has_answered' => $existingResponse ? true : false,
            'student_answer' => $existingResponse ? $existingResponse->answer : null,
            'result_id' => $result->id
        ]);
    }

    /**
     * Récupérer le statut de progression de l'étudiant
     * GET /api/student/session/{sessionId}/progress
     */
    public function getProgress($sessionId)
    {
        $user = Auth::user();
        if (!$user || $user->account_type !== 'student') {
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
        }

        // Récupérer le profil étudiant
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json(['error' => 'Profil étudiant non trouvé'], 403);
        }

        $result = Result::where('quiz_session_id', $sessionId)
            ->where('student_id', $student->id)
            ->with('quizSession.quiz.questions')
            ->firstOrFail();

        $totalQuestions = $result->quizSession->quiz->questions->count();
        $answeredQuestions = \App\Models\StudentResponse::where('quiz_session_id', $sessionId)
            ->where('student_id', $student->id)
            ->count();

        $progress = [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'remaining_questions' => $totalQuestions - $answeredQuestions,
            'percentage_complete' => $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 1) : 0,
            'is_completed' => $result->isCompleted(),
            'time_elapsed' => $result->started_at ? now()->diffInMinutes($result->started_at) : 0,
            'session_duration' => $result->quizSession->quiz->duration_minutes,
        ];

        return response()->json([
            'progress' => $progress,
            'result' => [
                'id' => $result->id,
                'status' => $result->status,
                'total_points' => $result->total_points,
                'max_points' => $result->max_points,
                'percentage' => $result->percentage,
            ]
        ]);
    }

    /**
     * Vérifier si l'étudiant participe à une session
     * GET /api/student/sessions/{id}/check-participation
     */
    public function checkParticipation($id)
    {
        $user = Auth::user();
        if (!$user || $user->account_type !== 'student') {
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
        }

        // Récupérer le profil étudiant
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json(['error' => 'Profil étudiant non trouvé'], 403);
        }

        $result = Result::where('quiz_session_id', $id)
            ->where('student_id', $student->id)
            ->first();

        return response()->json([
            'has_joined' => $result ? true : false,
            'result_id' => $result ? $result->id : null,
            'status' => $result ? $result->status : null
        ]);
    }
}

