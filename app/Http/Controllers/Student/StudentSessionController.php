<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QuizSession;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentSessionController extends Controller
{
    /**
     * Rejoindre une session via son code
     */
    public function joinSession(Request $request)
    {
        $request->validate([
            'session_code' => 'required|string',
        ]);

        $student = Auth::user()->student;
        if (!$student) {
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
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

        // Vérifier l'accès de l'étudiant si session restreinte
        if ($session->access_type === 'restricted') {
            $allowedIds = $session->allowed_students ?? [];
            if (!in_array($student->id, $allowedIds)) {
                return response()->json(['error' => 'Vous n’êtes pas autorisé à rejoindre cette session'], 403);
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
}
