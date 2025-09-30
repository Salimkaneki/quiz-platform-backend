<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\StudentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentResponseController extends Controller
{
    /**
     * Soumettre une ou plusieurs réponses pour une session
     */
    public function submitResponses(Request $request, $resultId)
    {
        \Log::info('Starting submitResponses', ['resultId' => $resultId, 'user' => Auth::id()]);

        $student = Auth::user()->student;
        if (!$student) {
            \Log::info('No student found for user', ['user' => Auth::id()]);
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
        }

        $result = Result::where('id', $resultId)
            ->where('student_id', $student->id)
            ->with('quizSession.quiz')
            ->firstOrFail();

        \Log::info('Result found', ['result' => $result->id, 'status' => $result->status]);

        if ($result->isCompleted()) {
            \Log::info('Result already completed');
            return response()->json(['error' => 'Résultat déjà soumis'], 400);
        }

        $request->validate([
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|integer|exists:questions,id,quiz_id,' . $result->quizSession->quiz->id,
            'responses.*.answer' => 'required'
        ]);

        \Log::info('Validation passed', ['responses_count' => count($request->responses)]);

        \DB::transaction(function() use ($result, $request) {
            \Log::info('Starting transaction');
            foreach ($request->responses as $resp) {
                \Log::info('Processing response', ['question_id' => $resp['question_id']]);
                // Charger la question spécifique au lieu de la chercher dans la collection
                $question = $result->quizSession->quiz->questions()
                    ->where('id', $resp['question_id'])
                    ->first();

                if (!$question) {
                    \Log::error("Question not found", ['question_id' => $resp['question_id']]);
                    throw new \Exception("Question introuvable: {$resp['question_id']}");
                }

                $pointsPossible = $question->points ?? 0;
                $isCorrect = null;
                $pointsEarned = 0;

                if (in_array($question->type, ['multiple_choice', 'true_false', 'fill_blank'])) {
                    $isCorrect = $question->checkAnswer($resp['answer']);
                    $pointsEarned = $isCorrect ? $pointsPossible : 0;
                }

                \Log::info('Creating/updating response', [
                    'question_id' => $question->id,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned
                ]);

                StudentResponse::updateOrCreate(
                    [
                        'quiz_session_id' => $result->quiz_session_id,
                        'student_id' => $result->student_id,
                        'question_id' => $question->id,
                    ],
                    [
                        'answer' => $resp['answer'],
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned,
                        'points_possible' => $pointsPossible,
                        'answered_at' => now(),
                    ]
                );
            }

            $result->updateFromResponses();
            $result->markAsSubmitted();
            \Log::info('Transaction completed');
        });

        \Log::info('Returning response', [
            'total_points' => $result->total_points,
            'max_points' => $result->max_points
        ]);

        return response()->json([
            'message' => 'Réponses soumises avec succès',
            'total_points' => $result->total_points,
            'max_points' => $result->max_points,
            'percentage' => $result->percentage,
            'correct_answers' => $result->correct_answers,
        ], 200);
    }

    /**
     * Lister toutes les réponses d’un résultat
     */
    public function index($resultId)
    {
        $student = Auth::user()->student;
        $result = Result::where('id', $resultId)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $responses = StudentResponse::where('quiz_session_id', $result->quiz_session_id)
            ->where('student_id', $student->id)
            ->with('question')
            ->get();

        return response()->json($responses);
    }

    /**
     * Voir une réponse spécifique
     */
    public function show($resultId, $questionId)
    {
        $student = Auth::user()->student;
        $result = Result::where('id', $resultId)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $response = StudentResponse::where('quiz_session_id', $result->quiz_session_id)
            ->where('student_id', $student->id)
            ->where('question_id', $questionId)
            ->with('question')
            ->firstOrFail();

        return response()->json($response);
    }
}
