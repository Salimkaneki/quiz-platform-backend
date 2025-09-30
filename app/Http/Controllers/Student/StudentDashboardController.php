<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\QuizSession;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::user()->student;

        if (!$student) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }

        // Statistiques générales
        $stats = $this->getStudentStats($student);

        // Résultats récents (derniers 10)
        $recentResults = $this->getRecentResults($student);

        // Sessions actives
        $activeSessions = $this->getActiveSessions($student);

        // Sessions à venir
        $upcomingSessions = $this->getUpcomingSessions($student);

        // Progression par matière
        $subjectProgress = $this->getSubjectProgress($student);

        // Quiz en cours (non terminés)
        $inProgressQuizzes = $this->getInProgressQuizzes($student);

        return response()->json([
            'stats' => $stats,
            'recent_results' => $recentResults,
            'active_sessions' => $activeSessions,
            'upcoming_sessions' => $upcomingSessions,
            'subject_progress' => $subjectProgress,
            'in_progress_quizzes' => $inProgressQuizzes,
        ]);
    }

    private function getStudentStats(Student $student)
    {
        $results = Result::where('student_id', $student->id)
            ->where('status', 'published')
            ->get();

        $totalQuizzes = $results->count();
        $averageScore = $totalQuizzes > 0 ? $results->avg('percentage') : 0;
        $highestScore = $results->max('percentage') ?? 0;
        $lowestScore = $results->min('percentage') ?? 0;

        // Temps total passé
        $totalTimeSpent = $results->sum('time_spent_total');

        // Répartition des notes
        $scoreRanges = [
            'excellent' => $results->where('percentage', '>=', 90)->count(),
            'good' => $results->whereBetween('percentage', [80, 89])->count(),
            'average' => $results->whereBetween('percentage', [60, 79])->count(),
            'poor' => $results->where('percentage', '<', 60)->count(),
        ];

        return [
            'total_quizzes_taken' => $totalQuizzes,
            'average_score' => round($averageScore, 2),
            'highest_score' => round($highestScore, 2),
            'lowest_score' => round($lowestScore, 2),
            'total_time_spent' => $totalTimeSpent,
            'score_distribution' => $scoreRanges,
        ];
    }

    private function getRecentResults(Student $student)
    {
        return Result::with(['quizSession.quiz.subject', 'quizSession.quiz'])
            ->where('student_id', $student->id)
            ->where('status', 'published')
            ->orderBy('submitted_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($result) {
                return [
                    'id' => $result->id,
                    'quiz_title' => $result->quizSession->quiz->title,
                    'subject_name' => $result->quizSession->quiz->subject->name,
                    'score' => round($result->percentage, 2),
                    'grade' => round($result->grade, 2),
                    'submitted_at' => $result->submitted_at,
                    'time_spent' => $result->time_spent_total,
                    'total_questions' => $result->total_questions,
                    'correct_answers' => $result->correct_answers,
                ];
            });
    }

    private function getActiveSessions(Student $student)
    {
        return QuizSession::with(['quiz.subject', 'results'])
            ->where('status', 'active')
            ->whereHas('results', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                      ->where('status', 'in_progress');
            })
            ->get()
            ->map(function ($session) use ($student) {
                $result = $session->results()->where('student_id', $student->id)->first();

                return [
                    'id' => $session->id,
                    'session_code' => $session->session_code,
                    'quiz_title' => $session->quiz->title,
                    'subject_name' => $session->quiz->subject->name,
                    'starts_at' => $session->starts_at,
                    'ends_at' => $session->ends_at,
                    'time_remaining' => $session->ends_at ? now()->diffInMinutes($session->ends_at, false) : null,
                    'progress' => $result ? [
                        'total_questions' => $result->total_questions,
                        'answered_questions' => $result->studentResponses()->count(),
                        'percentage_complete' => $result->total_questions > 0 ?
                            round(($result->studentResponses()->count() / $result->total_questions) * 100, 2) : 0,
                    ] : null,
                ];
            });
    }

    private function getUpcomingSessions(Student $student)
    {
        // Sessions actives où l'étudiant peut encore rejoindre
        return QuizSession::with(['quiz.subject'])
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->whereDoesntHave('results', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->orderBy('starts_at')
            ->limit(5)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'session_code' => $session->session_code,
                    'quiz_title' => $session->quiz->title,
                    'subject_name' => $session->quiz->subject->name,
                    'starts_at' => $session->starts_at,
                    'ends_at' => $session->ends_at,
                    'time_until_start' => now()->diffInMinutes($session->starts_at, false),
                ];
            });
    }

    private function getSubjectProgress(Student $student)
    {
        return Result::select(
                'subjects.name as subject_name',
                DB::raw('COUNT(results.id) as quizzes_taken'),
                DB::raw('AVG(results.percentage) as average_score'),
                DB::raw('MAX(results.percentage) as best_score'),
                DB::raw('MIN(results.percentage) as worst_score')
            )
            ->join('quiz_sessions', 'results.quiz_session_id', '=', 'quiz_sessions.id')
            ->join('quizzes', 'quiz_sessions.quiz_id', '=', 'quizzes.id')
            ->join('subjects', 'quizzes.subject_id', '=', 'subjects.id')
            ->where('results.student_id', $student->id)
            ->where('results.status', 'published')
            ->groupBy('subjects.id', 'subjects.name')
            ->orderBy('average_score', 'desc')
            ->get()
            ->map(function ($subject) {
                return [
                    'subject_name' => $subject->subject_name,
                    'quizzes_taken' => $subject->quizzes_taken,
                    'average_score' => round($subject->average_score, 2),
                    'best_score' => round($subject->best_score, 2),
                    'worst_score' => round($subject->worst_score, 2),
                    'performance_level' => $this->getPerformanceLevel($subject->average_score),
                ];
            });
    }

    private function getInProgressQuizzes(Student $student)
    {
        return Result::with(['quizSession.quiz.subject'])
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->get()
            ->map(function ($result) {
                $answeredCount = $result->studentResponses()->count();
                $totalQuestions = $result->quizSession->quiz->questions()->count();

                return [
                    'result_id' => $result->id,
                    'session_id' => $result->quiz_session_id,
                    'quiz_title' => $result->quizSession->quiz->title,
                    'subject_name' => $result->quizSession->quiz->subject->name,
                    'started_at' => $result->started_at,
                    'time_spent' => $result->time_spent_total,
                    'progress' => [
                        'answered' => $answeredCount,
                        'total' => $totalQuestions,
                        'percentage' => $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100, 2) : 0,
                    ],
                    'time_remaining' => $result->quizSession->ends_at ?
                        now()->diffInMinutes($result->quizSession->ends_at, false) : null,
                ];
            });
    }

    private function getPerformanceLevel($averageScore)
    {
        if ($averageScore >= 90) return 'excellent';
        if ($averageScore >= 80) return 'good';
        if ($averageScore >= 70) return 'average';
        if ($averageScore >= 60) return 'below_average';
        return 'poor';
    }
}