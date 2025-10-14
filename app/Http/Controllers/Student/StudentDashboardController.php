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
    /**
     * Afficher le tableau de bord complet de l'étudiant avec toutes ses données
     */
    public function showDashboard(Request $request)
    {
        $authenticatedStudent = Auth::user()->student;

        if (!$authenticatedStudent) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }

        // Calculer les statistiques de performance globales
        $overallPerformanceStats = $this->calculateOverallPerformanceStatistics($authenticatedStudent);

        // Récupérer les 10 derniers résultats publiés
        $latestPublishedResults = $this->fetchLatestPublishedResults($authenticatedStudent);

        // Récupérer les sessions où l'étudiant a des quiz en cours
        $ongoingQuizSessions = $this->fetchOngoingQuizSessions($authenticatedStudent);

        // Trouver les sessions disponibles pour rejoindre
        $availableSessionsToJoin = $this->findAvailableSessionsToJoin($authenticatedStudent);

        // Analyser la progression académique par matière
        $academicProgressBySubject = $this->analyzeAcademicProgressBySubject($authenticatedStudent);

        // Récupérer les quiz commencés mais non terminés
        $incompleteStartedQuizzes = $this->fetchIncompleteStartedQuizzes($authenticatedStudent);

        return response()->json([
            'performance_statistics' => $overallPerformanceStats,
            'latest_results' => $latestPublishedResults,
            'ongoing_sessions' => $ongoingQuizSessions,
            'available_sessions' => $availableSessionsToJoin,
            'subject_progress' => $academicProgressBySubject,
            'incomplete_quizzes' => $incompleteStartedQuizzes,
        ]);
    }

    /**
     * Calculer les statistiques de performance globales de l'étudiant
     */
    private function calculateOverallPerformanceStatistics(Student $authenticatedStudent)
    {
        $publishedResultsCollection = Result::where('student_id', $authenticatedStudent->id)
            ->where('status', 'published')
            ->get();

        $totalCompletedQuizzes = $publishedResultsCollection->count();
        $calculatedAverageScore = $totalCompletedQuizzes > 0 ? $publishedResultsCollection->avg('percentage') : 0;
        $bestPerformanceScore = $publishedResultsCollection->max('percentage') ?? 0;
        $worstPerformanceScore = $publishedResultsCollection->min('percentage') ?? 0;

        // Calculer le temps total investi dans les quiz
        $totalTimeInvestedInQuizzes = $publishedResultsCollection->sum('time_spent_total');

        // Analyser la répartition des performances par niveau
        $performanceDistributionByLevel = [
            'excellent_performances' => $publishedResultsCollection->where('percentage', '>=', 90)->count(),
            'good_performances' => $publishedResultsCollection->whereBetween('percentage', [80, 89])->count(),
            'average_performances' => $publishedResultsCollection->whereBetween('percentage', [60, 79])->count(),
            'poor_performances' => $publishedResultsCollection->where('percentage', '<', 60)->count(),
        ];

        return [
            'total_quizzes_completed' => $totalCompletedQuizzes,
            'overall_average_score' => round($calculatedAverageScore, 2),
            'best_score_achieved' => round($bestPerformanceScore, 2),
            'lowest_score_recorded' => round($worstPerformanceScore, 2),
            'total_study_time_invested' => $totalTimeInvestedInQuizzes,
            'performance_level_distribution' => $performanceDistributionByLevel,
        ];
    }

    /**
     * Récupérer les derniers résultats publiés de l'étudiant avec leurs détails
     */
    private function fetchLatestPublishedResults(Student $authenticatedStudent)
    {
        return Result::with(['quizSession.quiz.subject', 'quizSession.quiz'])
            ->where('student_id', $authenticatedStudent->id)
            ->where('status', 'published')
            ->orderBy('submitted_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($publishedResult) {
                return [
                    'result_id' => $publishedResult->id,
                    'quiz_title' => $publishedResult->quizSession->quiz->title,
                    'academic_subject' => $publishedResult->quizSession->quiz->subject->name,
                    'achieved_score_percentage' => round($publishedResult->percentage, 2),
                    'numeric_grade' => round($publishedResult->grade, 2),
                    'submission_timestamp' => $publishedResult->submitted_at,
                    'time_invested_in_quiz' => $publishedResult->time_spent_total,
                    'total_questions_count' => $publishedResult->total_questions,
                    'correctly_answered_questions' => $publishedResult->correct_answers,
                ];
            });
    }

    /**
     * Récupérer les sessions où l'étudiant a des quiz en cours de progression
     */
    private function fetchOngoingQuizSessions(Student $authenticatedStudent)
    {
        return QuizSession::with(['quiz.subject', 'results'])
            ->where('status', 'active')
            ->whereHas('results', function ($query) use ($authenticatedStudent) {
                $query->where('student_id', $authenticatedStudent->id)
                      ->where('status', 'in_progress');
            })
            ->get()
            ->map(function ($activeSession) use ($authenticatedStudent) {
                $studentCurrentResult = $activeSession->results()->where('student_id', $authenticatedStudent->id)->first();

                return [
                    'session_id' => $activeSession->id,
                    'unique_session_code' => $activeSession->session_code,
                    'quiz_title' => $activeSession->quiz->title,
                    'academic_subject' => $activeSession->quiz->subject->name,
                    'session_start_time' => $activeSession->starts_at,
                    'session_end_time' => $activeSession->ends_at,
                    'minutes_remaining_until_deadline' => $activeSession->ends_at ? now()->diffInMinutes($activeSession->ends_at, false) : null,
                    'current_progress_status' => $studentCurrentResult ? [
                        'total_questions_in_quiz' => $studentCurrentResult->total_questions,
                        'questions_already_answered' => $studentCurrentResult->studentResponses()->count(),
                        'completion_percentage' => $studentCurrentResult->total_questions > 0 ?
                            round(($studentCurrentResult->studentResponses()->count() / $studentCurrentResult->total_questions) * 100, 2) : 0,
                    ] : null,
                ];
            });
    }

    /**
     * Trouver les sessions actives que l'étudiant peut encore rejoindre
     */
    private function findAvailableSessionsToJoin(Student $authenticatedStudent)
    {
        // Sessions actives où l'étudiant n'a pas encore participé et peut rejoindre
        return QuizSession::with(['quiz.subject'])
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->whereDoesntHave('results', function ($query) use ($authenticatedStudent) {
                $query->where('student_id', $authenticatedStudent->id);
            })
            ->orderBy('starts_at')
            ->limit(5)
            ->get()
            ->map(function ($availableSession) {
                return [
                    'session_id' => $availableSession->id,
                    'unique_session_code' => $availableSession->session_code,
                    'quiz_title' => $availableSession->quiz->title,
                    'academic_subject' => $availableSession->quiz->subject->name,
                    'session_start_time' => $availableSession->starts_at,
                    'session_deadline' => $availableSession->ends_at,
                    'minutes_until_session_starts' => now()->diffInMinutes($availableSession->starts_at, false),
                ];
            });
    }

    /**
     * Analyser la progression académique de l'étudiant par matière
     */
    private function analyzeAcademicProgressBySubject(Student $authenticatedStudent)
    {
        return Result::select(
                'subjects.name as subject_name',
                DB::raw('COUNT(results.id) as total_quizzes_attempted'),
                DB::raw('AVG(results.percentage) as calculated_average_score'),
                DB::raw('MAX(results.percentage) as highest_score_achieved'),
                DB::raw('MIN(results.percentage) as lowest_score_recorded')
            )
            ->join('quiz_sessions', 'results.quiz_session_id', '=', 'quiz_sessions.id')
            ->join('quizzes', 'quiz_sessions.quiz_id', '=', 'quizzes.id')
            ->join('subjects', 'quizzes.subject_id', '=', 'subjects.id')
            ->where('results.student_id', $authenticatedStudent->id)
            ->where('results.status', 'published')
            ->groupBy('subjects.id', 'subjects.name')
            ->orderBy('calculated_average_score', 'desc')
            ->get()
            ->map(function ($subjectPerformanceData) {
                return [
                    'academic_subject_name' => $subjectPerformanceData->subject_name,
                    'total_quizzes_completed' => $subjectPerformanceData->total_quizzes_attempted,
                    'subject_average_score' => round($subjectPerformanceData->calculated_average_score, 2),
                    'best_performance_in_subject' => round($subjectPerformanceData->highest_score_achieved, 2),
                    'weakest_performance_in_subject' => round($subjectPerformanceData->lowest_score_recorded, 2),
                    'overall_performance_level' => $this->determinePerformanceLevel($subjectPerformanceData->calculated_average_score),
                ];
            });
    }

    /**
     * Récupérer les quiz que l'étudiant a commencés mais n'a pas terminés
     */
    private function fetchIncompleteStartedQuizzes(Student $authenticatedStudent)
    {
        return Result::with(['quizSession.quiz.subject'])
            ->where('student_id', $authenticatedStudent->id)
            ->where('status', 'in_progress')
            ->get()
            ->map(function ($incompleteResult) {
                $questionsAlreadyAnswered = $incompleteResult->studentResponses()->count();
                $totalQuestionsInQuiz = $incompleteResult->quizSession->quiz->questions()->count();

                return [
                    'incomplete_result_id' => $incompleteResult->id,
                    'quiz_session_id' => $incompleteResult->quiz_session_id,
                    'quiz_title' => $incompleteResult->quizSession->quiz->title,
                    'academic_subject' => $incompleteResult->quizSession->quiz->subject->name,
                    'quiz_started_at' => $incompleteResult->started_at,
                    'time_already_invested' => $incompleteResult->time_spent_total,
                    'completion_progress' => [
                        'questions_answered_so_far' => $questionsAlreadyAnswered,
                        'total_questions_in_quiz' => $totalQuestionsInQuiz,
                        'completion_percentage' => $totalQuestionsInQuiz > 0 ? 
                            round(($questionsAlreadyAnswered / $totalQuestionsInQuiz) * 100, 2) : 0,
                    ],
                    'minutes_remaining_before_deadline' => $incompleteResult->quizSession->ends_at ?
                        now()->diffInMinutes($incompleteResult->quizSession->ends_at, false) : null,
                ];
            });
    }

    /**
     * Déterminer le niveau de performance basé sur le score moyen
     */
    private function determinePerformanceLevel($calculatedAverageScore)
    {
        if ($calculatedAverageScore >= 90) return 'excellent';
        if ($calculatedAverageScore >= 80) return 'good';
        if ($calculatedAverageScore >= 70) return 'average';
        if ($calculatedAverageScore >= 60) return 'below_average';
        return 'poor';
    }
}