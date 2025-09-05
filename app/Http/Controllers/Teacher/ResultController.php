<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\StudentResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    /**
     * Liste des résultats d’une session donnée
     * GET /api/results/session/{quizSessionId}
     */
    public function index($quizSessionId)
    {
        $results = Result::with('student')
            ->where('quiz_session_id', $quizSessionId)
            ->get();

        return response()->json($results);
    }

    /**
     * Détails du résultat d’un étudiant, avec réponses
     * GET /api/results/{id}
     */
    public function show($id)
    {
        $result = Result::with('student')->findOrFail($id);

        $studentResponses = StudentResponse::where('student_id', $result->student_id)
                            ->where('quiz_session_id', $result->quiz_session_id)
                            ->with('question.options')
                            ->get();

        $result->student_responses = $studentResponses;

        return response()->json($result);
    }

    /**
     * Récupère tous les résultats pour un quiz avec les réponses des étudiants
     * GET /api/results/quiz/{quizId}
     */
    // Dans ResultController.php
    public function allResultsForQuiz($quizSessionId)
    {
        $results = Result::with(['student', 'studentResponses'])
                        ->where('quiz_session_id', $quizSessionId)
                        ->get();

        return response()->json($results);
    }

    /**
     * Corriger / mettre à jour un résultat global (points, feedback, etc.)
     * PUT /api/results/{id}
     */
    public function update(Request $request, $id)
    {
        $result = Result::findOrFail($id);

        $result->update([
            'total_points'     => $request->input('total_points', $result->total_points),
            'max_points'       => $request->input('max_points', $result->max_points),
            'percentage'       => $request->input('percentage', $result->percentage),
            'grade'            => $request->input('grade', $result->grade),
            'teacher_feedback' => $request->input('teacher_feedback', $result->teacher_feedback),
        ]);

        return response()->json([
            'message' => 'Résultat mis à jour avec succès',
            'result'  => $result
        ]);
    }

    /**
     * Corriger la réponse d’un étudiant à une question
     * PUT /api/results/{resultId}/responses/{responseId}
     */
    public function updateResponse(Request $request, $resultId, $responseId)
    {
        $response = StudentResponse::where('quiz_session_id', function ($query) use ($resultId) {
                $query->select('quiz_session_id')
                      ->from('results')
                      ->where('id', $resultId);
            })
            ->where('id', $responseId)
            ->firstOrFail();

        $response->update([
            'is_correct'      => $request->input('is_correct', $response->is_correct),
            'points_earned'   => $request->input('points_earned', $response->points_earned),
            'teacher_comment' => $request->input('teacher_comment', $response->teacher_comment),
        ]);

        return response()->json([
            'message'  => 'Réponse corrigée avec succès',
            'response' => $response
        ]);
    }

    /**
     * Marquer un résultat comme corrigé (graded)
     * POST /api/results/{id}/mark-graded
     */
    public function markAsGraded($id)
    {
        $result = Result::findOrFail($id);
        $result->markAsGraded();

        return response()->json([
            'message' => 'Résultat marqué comme corrigé',
            'result'  => $result
        ]);
    }

    /**
     * Publier un résultat pour l’étudiant
     * POST /api/results/{id}/publish
     */
    public function publish($id)
    {
        $result = Result::findOrFail($id);
        $result->markAsPublished();

        return response()->json([
            'message' => 'Résultat publié',
            'result'  => $result
        ]);
    }
}
