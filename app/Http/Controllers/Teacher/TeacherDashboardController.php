<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizSession;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TeacherDashboardController extends Controller
{
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;

        // KPIs
        $kpis = $this->getKPIs($teacher);

        // Quizzes
        $quizzes = $this->getQuizzes($teacher);

        // Upcoming evaluations
        $upcomingEvaluations = $this->getUpcomingEvaluations($teacher);

        return response()->json([
            'kpis' => $kpis,
            'quizzes' => $quizzes,
            'upcoming_evaluations' => $upcomingEvaluations,
        ]);
    }

    private function getKPIs($teacher)
    {
        // Nombre d'élèves (étudiants dans la même institution que l'enseignant)
        $studentsCount = Student::where('institution_id', $teacher->institution_id)->count();

        // Évaluations complétées (sessions terminées)
        $completedEvaluations = QuizSession::where('teacher_id', $teacher->user_id)
            ->where('status', 'completed')
            ->count();

        // Taux de réussite (moyenne des pourcentages des résultats publiés)
        $successRate = Result::whereHas('quizSession', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->user_id);
        })
        ->where('status', 'published')
        ->avg('percentage');

        // Nouvelles inscriptions (étudiants créés cette semaine dans l'institution)
        $newRegistrations = Student::where('institution_id', $teacher->institution_id)
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->count();

        return [
            [
                'label' => 'Nombre d\'élèves',
                'value' => $studentsCount,
                'trend' => 'positive', // À calculer si nécessaire
                'period' => 'Depuis le mois dernier'
            ],
            [
                'label' => 'Évaluations complétées',
                'value' => $completedEvaluations,
                'trend' => 'negative', // À calculer
                'period' => 'Cette semaine'
            ],
            [
                'label' => 'Taux de réussite',
                'value' => $successRate ? round($successRate, 1) . '%' : '0%',
                'trend' => 'positive',
                'period' => 'Ce trimestre'
            ],
            [
                'label' => 'Nouvelles inscriptions',
                'value' => $newRegistrations,
                'trend' => 'positive',
                'period' => 'Aujourd\'hui'
            ],
        ];
    }

    private function getQuizzes($teacher)
    {
        return Quiz::with(['subject', 'questions'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($quiz) {
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'class_name' => $quiz->subject->name ?? 'N/A',
                    'questions' => $quiz->questions->count(),
                    'created_at' => $quiz->created_at->format('Y-m-d'),
                ];
            });
    }

    private function getUpcomingEvaluations($teacher)
    {
        return QuizSession::where('starts_at', '>', Carbon::now())
            ->orderBy('starts_at', 'asc')
            ->take(5)
            ->get()
            ->map(function($session) {
                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'date' => $session->starts_at->format('Y-m-d'),
                    'time' => $session->starts_at->format('H:i'),
                    'class_name' => 'N/A',
                ];
            });
    }
}