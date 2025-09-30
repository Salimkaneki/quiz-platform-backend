<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizSession;
use App\Models\Result;
use App\Models\StudentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherHistoryController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json(['error' => 'Teacher profile not found'], 404);
        }

        // Statistiques générales
        $stats = $this->getTeacherStats($teacher);

        // Historique des quiz
        $quizHistory = $this->getQuizHistory($teacher, $request);

        // Historique des sessions
        $sessionHistory = $this->getSessionHistory($teacher, $request);

        // Historique des résultats récents
        $recentResults = $this->getRecentResults($teacher);

        // Activité récente
        $recentActivity = $this->getRecentActivity($teacher);

        return response()->json([
            'stats' => $stats,
            'quiz_history' => $quizHistory,
            'session_history' => $sessionHistory,
            'recent_results' => $recentResults,
            'recent_activity' => $recentActivity,
        ]);
    }

    public function quizHistory(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json(['error' => 'Teacher profile not found'], 404);
        }

        return response()->json([
            'quizzes' => $this->getQuizHistory($teacher, $request)
        ]);
    }

    public function sessionHistory(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json(['error' => 'Teacher profile not found'], 404);
        }

        return response()->json([
            'sessions' => $this->getSessionHistory($teacher, $request)
        ]);
    }

    public function resultsHistory(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json(['error' => 'Teacher profile not found'], 404);
        }

        $results = Result::with(['quizSession.quiz.subject', 'student.user'])
            ->whereHas('quizSession', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->user_id);
            })
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'results' => $results->map(function ($result) {
                return [
                    'id' => $result->id,
                    'student_name' => $result->student->full_name,
                    'student_email' => $result->student->user->email,
                    'quiz_title' => $result->quizSession->quiz->title,
                    'subject_name' => $result->quizSession->quiz->subject->name,
                    'session_code' => $result->quizSession->session_code,
                    'score' => round($result->percentage, 2),
                    'grade' => round($result->grade, 2),
                    'submitted_at' => $result->submitted_at,
                    'published_at' => $result->published_at,
                    'time_spent' => $result->time_spent_total,
                    'total_questions' => $result->total_questions,
                    'correct_answers' => $result->correct_answers,
                ];
            }),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ]
        ]);
    }

    private function getTeacherStats($teacher)
    {
        $userId = $teacher->user_id;

        // Statistiques des quiz
        $totalQuizzes = Quiz::where('teacher_id', $userId)->count();
        $publishedQuizzes = Quiz::where('teacher_id', $userId)->where('status', 'published')->count();
        $draftQuizzes = Quiz::where('teacher_id', $userId)->where('status', 'draft')->count();

        // Statistiques des sessions
        $totalSessions = QuizSession::where('teacher_id', $userId)->count();
        $activeSessions = QuizSession::where('teacher_id', $userId)->where('status', 'active')->count();
        $completedSessions = QuizSession::where('teacher_id', $userId)->where('status', 'completed')->count();

        // Statistiques des résultats
        $totalResults = Result::whereHas('quizSession', function ($query) use ($userId) {
            $query->where('teacher_id', $userId);
        })->count();

        $gradedResults = Result::whereHas('quizSession', function ($query) use ($userId) {
            $query->where('teacher_id', $userId);
        })->where('status', 'graded')->count();

        $publishedResults = Result::whereHas('quizSession', function ($query) use ($userId) {
            $query->where('teacher_id', $userId);
        })->where('status', 'published')->count();

        // Score moyen des étudiants
        $averageScore = Result::whereHas('quizSession', function ($query) use ($userId) {
            $query->where('teacher_id', $userId);
        })->where('status', 'published')->avg('percentage') ?? 0;

        // Temps total passé par les étudiants
        $totalStudentTime = Result::whereHas('quizSession', function ($query) use ($userId) {
            $query->where('teacher_id', $userId);
        })->sum('time_spent_total');

        return [
            'quizzes' => [
                'total' => $totalQuizzes,
                'published' => $publishedQuizzes,
                'drafts' => $draftQuizzes,
            ],
            'sessions' => [
                'total' => $totalSessions,
                'active' => $activeSessions,
                'completed' => $completedSessions,
            ],
            'results' => [
                'total' => $totalResults,
                'graded' => $gradedResults,
                'published' => $publishedResults,
            ],
            'performance' => [
                'average_student_score' => round($averageScore, 2),
                'total_student_time_spent' => $totalStudentTime,
            ],
        ];
    }

    private function getQuizHistory($teacher, Request $request)
    {
        $query = Quiz::with(['subject', 'questions'])
            ->where('teacher_id', $teacher->user_id)
            ->orderBy('created_at', 'desc');

        // Filtres optionnels
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $quizzes = $query->paginate($request->get('per_page', 10));

        return [
            'data' => $quizzes->map(function ($quiz) {
                // Statistiques de sessions pour ce quiz
                $sessionsCount = $quiz->sessions()->count();
                $completedSessions = $quiz->sessions()->where('status', 'completed')->count();
                $totalParticipants = Result::whereHas('quizSession', function ($query) use ($quiz) {
                    $query->where('quiz_id', $quiz->id);
                })->count();

                $averageScore = Result::whereHas('quizSession', function ($query) use ($quiz) {
                    $query->where('quiz_id', $quiz->id);
                })->where('status', 'published')->avg('percentage') ?? 0;

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'subject_name' => $quiz->subject->name,
                    'status' => $quiz->status,
                    'total_questions' => $quiz->questions->count(),
                    'duration_minutes' => $quiz->duration_minutes,
                    'created_at' => $quiz->created_at,
                    'updated_at' => $quiz->updated_at,
                    'statistics' => [
                        'total_sessions' => $sessionsCount,
                        'completed_sessions' => $completedSessions,
                        'total_participants' => $totalParticipants,
                        'average_score' => round($averageScore, 2),
                    ],
                ];
            }),
            'pagination' => [
                'current_page' => $quizzes->currentPage(),
                'last_page' => $quizzes->lastPage(),
                'per_page' => $quizzes->perPage(),
                'total' => $quizzes->total(),
            ]
        ];
    }

    private function getSessionHistory($teacher, Request $request)
    {
        $query = QuizSession::with(['quiz.subject'])
            ->where('teacher_id', $teacher->user_id)
            ->orderBy('created_at', 'desc');

        // Filtres optionnels
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        $sessions = $query->paginate($request->get('per_page', 10));

        return [
            'data' => $sessions->map(function ($session) {
                $participantsCount = $session->results()->count();
                $completedCount = $session->results()->where('status', 'submitted')->count();
                $averageScore = $session->results()->where('status', 'published')->avg('percentage') ?? 0;

                return [
                    'id' => $session->id,
                    'session_code' => $session->session_code,
                    'title' => $session->title,
                    'quiz_title' => $session->quiz->title,
                    'subject_name' => $session->quiz->subject->name,
                    'status' => $session->status,
                    'starts_at' => $session->starts_at,
                    'ends_at' => $session->ends_at,
                    'created_at' => $session->created_at,
                    'activated_at' => $session->activated_at,
                    'completed_at' => $session->completed_at,
                    'statistics' => [
                        'total_participants' => $participantsCount,
                        'completed_participants' => $completedCount,
                        'average_score' => round($averageScore, 2),
                        'completion_rate' => $participantsCount > 0 ?
                            round(($completedCount / $participantsCount) * 100, 2) : 0,
                    ],
                ];
            }),
            'pagination' => [
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
            ]
        ];
    }

    private function getRecentResults($teacher)
    {
        return Result::with(['quizSession.quiz.subject', 'student.user'])
            ->whereHas('quizSession', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->user_id);
            })
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($result) {
                return [
                    'id' => $result->id,
                    'student_name' => $result->student->full_name,
                    'quiz_title' => $result->quizSession->quiz->title,
                    'subject_name' => $result->quizSession->quiz->subject->name,
                    'score' => round($result->percentage, 2),
                    'grade' => round($result->grade, 2),
                    'published_at' => $result->published_at,
                    'time_spent' => $result->time_spent_total,
                ];
            });
    }

    private function getRecentActivity($teacher)
    {
        $activities = collect();

        // Quiz créés récemment
        $recentQuizzes = Quiz::where('teacher_id', $teacher->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($quiz) {
                return [
                    'type' => 'quiz_created',
                    'title' => 'Quiz créé',
                    'description' => "Quiz '{$quiz->title}' créé",
                    'timestamp' => $quiz->created_at,
                    'data' => [
                        'quiz_id' => $quiz->id,
                        'quiz_title' => $quiz->title,
                    ]
                ];
            });

        // Sessions créées récemment
        $recentSessions = QuizSession::with('quiz')
            ->where('teacher_id', $teacher->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($session) {
                return [
                    'type' => 'session_created',
                    'title' => 'Session créée',
                    'description' => "Session '{$session->session_code}' créée pour '{$session->quiz->title}'",
                    'timestamp' => $session->created_at,
                    'data' => [
                        'session_id' => $session->id,
                        'session_code' => $session->session_code,
                        'quiz_title' => $session->quiz->title,
                    ]
                ];
            });

        // Résultats publiés récemment
        $recentPublishedResults = Result::with(['quizSession.quiz', 'student'])
            ->whereHas('quizSession', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->user_id);
            })
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($result) {
                return [
                    'type' => 'result_published',
                    'title' => 'Résultat publié',
                    'description' => "Résultat de {$result->student->full_name} publié ({$result->percentage}%)",
                    'timestamp' => $result->published_at,
                    'data' => [
                        'result_id' => $result->id,
                        'student_name' => $result->student->full_name,
                        'score' => round($result->percentage, 2),
                        'quiz_title' => $result->quizSession->quiz->title,
                    ]
                ];
            });

        // Combiner et trier par timestamp
        $activities = $recentQuizzes
            ->concat($recentSessions)
            ->concat($recentPublishedResults)
            ->sortByDesc('timestamp')
            ->take(15)
            ->values();

        return $activities;
    }
}