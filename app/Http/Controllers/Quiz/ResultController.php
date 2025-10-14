<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\StudentResponse;
use Illuminate\Http\Request;

/**
 * Contrôleur pour la gestion des résultats de quiz et des réponses des étudiants.
 * Permet aux enseignants de consulter, corriger et publier les résultats.
 */
class ResultController extends Controller
{
    /**
     * Liste des résultats d'une session donnée.
     *
     * @param int $quizSessionId L'ID de la session de quiz
     * @return \Illuminate\Http\JsonResponse Liste des résultats avec informations sur les étudiants
     */
    public function index($quizSessionId)
    {
        $results = Result::with('student')
            ->where('quiz_session_id', $quizSessionId)
            ->whereHas('quizSession', function($query) {
                $query->where('teacher_id', auth()->id());
            })
            ->get();

        return response()->json($results);
    }

    /**
     * Détails du résultat d'un étudiant, avec ses réponses.
     *
     * @param int $id L'ID du résultat
     * @return \Illuminate\Http\JsonResponse Détails du résultat et réponses de l'étudiant
     */
    public function show($id)
    {
        $result = Result::with('student')
            ->whereHas('quizSession', function($query) {
                $query->where('teacher_id', auth()->id());
            })
            ->findOrFail($id);

        $studentResponses = StudentResponse::where('student_id', $result->student_id)
                            ->where('quiz_session_id', $result->quiz_session_id)
                            ->with('question')
                            ->get();

        $result->student_responses = $studentResponses;

        return response()->json($result);
    }

    /**
     * Récupère tous les résultats pour une session de quiz avec les réponses des étudiants.
     *
     * @param int $quizSessionId L'ID de la session de quiz
     * @return \Illuminate\Http\JsonResponse Liste des résultats avec réponses
     */
    public function allResultsForQuiz($quizSessionId)
    {
        $results = Result::with('student')
                        ->where('quiz_session_id', $quizSessionId)
                        ->whereHas('quizSession', function($query) {
                            $query->where('teacher_id', auth()->id());
                        })
                        ->get();

        // Attacher manuellement les réponses pour chaque résultat
        foreach ($results as $result) {
            $studentResponses = StudentResponse::where('student_id', $result->student_id)
                                ->where('quiz_session_id', $result->quiz_session_id)
                                ->with('question')
                                ->get();
            $result->student_responses = $studentResponses;
        }

        return response()->json($results);
    }

    /**
     * Corriger / mettre à jour un résultat global (points, feedback, etc.).
     *
     * @param Request $request La requête contenant les données de mise à jour
     * @param int $id L'ID du résultat
     * @return \Illuminate\Http\JsonResponse Message de succès et résultat mis à jour
     */
    public function update(Request $request, $id)
    {
        $result = Result::whereHas('quizSession', function($query) {
                $query->where('teacher_id', auth()->id());
            })
            ->findOrFail($id);

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
     * Corriger la réponse d'un étudiant à une question spécifique.
     *
     * @param Request $request La requête contenant les corrections
     * @param int $resultId L'ID du résultat
     * @param int $responseId L'ID de la réponse
     * @return \Illuminate\Http\JsonResponse Message de succès et réponse corrigée
     */
    public function updateResponse(Request $request, $resultId, $responseId)
    {
        $response = StudentResponse::where('quiz_session_id', function ($query) use ($resultId) {
                $query->select('quiz_session_id')
                      ->from('results')
                      ->where('id', $resultId)
                      ->whereHas('quizSession', function($subQuery) {
                          $subQuery->where('teacher_id', auth()->id());
                      });
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
     * Marquer un résultat comme corrigé (status: graded).
     *
     * @param int $id L'ID du résultat
     * @return \Illuminate\Http\JsonResponse Message de succès et résultat marqué
     */
    public function markAsGraded($id)
    {
        $result = Result::whereHas('quizSession', function($query) {
                $query->where('teacher_id', auth()->id());
            })
            ->findOrFail($id);
        $result->markAsGraded();

        return response()->json([
            'message' => 'Résultat marqué comme corrigé',
            'result'  => $result
        ]);
    }

    /**
     * Publier un résultat pour que l'étudiant puisse le voir (status: published).
     *
     * @param int $id L'ID du résultat
     * @return \Illuminate\Http\JsonResponse Message de succès et résultat publié
     */
    public function publish($id)
    {
        $result = Result::whereHas('quizSession', function($query) {
                $query->where('teacher_id', auth()->id());
            })
            ->findOrFail($id);
        $result->markAsPublished();

        return response()->json([
            'message' => 'Résultat publié',
            'result'  => $result
        ]);
    }
}
