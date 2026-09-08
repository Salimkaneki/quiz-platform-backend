<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\AuthorizationTrait;
use App\Models\Result;
use App\Models\StudentResponse;
use App\Models\QuizSession; // Import ajouté
use Illuminate\Http\Request;

/**
 * Contrôleur pour la gestion des résultats de quiz et des réponses des étudiants.
 * Permet aux enseignants de consulter, corriger et publier les résultats.
 */
class ResultController extends Controller
{
    use AuthorizationTrait;

    /**
     * Liste des résultats d'une session donnée.
     *
     * @param int $quizSessionId L'ID de la session de quiz
     * @return \Illuminate\Http\JsonResponse Liste des résultats avec informations sur les étudiants
     */
    public function index($quizSessionId)
    {
        $teacherId = $this->getAuthenticatedTeacher()->id;

        $results = Result::with('student')
            ->where('quiz_session_id', $quizSessionId)
            ->whereHas('quizSession', function($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->get();

        return response()->json($results);
    }

    /**
     * Liste des sessions terminées pour l'enseignant connecté
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompletedSessions(Request $request)
    {
        $teacherId = $this->getAuthenticatedTeacher()->id;

        $status = $request->query('status', 'finished'); // Paramètre dynamique
        
        $sessions = QuizSession::where('teacher_id', $teacherId)
            ->where('status', $status)
            ->with('quiz')
            ->get();

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * Détails du résultat d'un étudiant, avec ses réponses.
     *
     * @param int $id L'ID du résultat
     * @return \Illuminate\Http\JsonResponse Détails du résultat et réponses de l'étudiant
     */
    public function show($id)
    {
        $teacherId = $this->getAuthenticatedTeacher()->id;

        $result = Result::with('student')
            ->whereHas('quizSession', function($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
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
        $teacherId = $this->getAuthenticatedTeacher()->id;

        $results = Result::with('student')
                        ->where('quiz_session_id', $quizSessionId)
                        ->whereHas('quizSession', function($query) use ($teacherId) {
                            $query->where('teacher_id', $teacherId);
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
        $teacherId = $this->getAuthenticatedTeacher()->id;

        $result = Result::whereHas('quizSession', function($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->findOrFail($id);

        $validated = $request->validate([
            'total_points'     => ['sometimes', 'numeric', 'min:0'],
            'max_points'       => ['sometimes', 'numeric', 'min:0'],
            'percentage'       => ['sometimes', 'numeric', 'between:0,100'],
            'grade'            => ['sometimes', 'nullable', 'string', 'max:10'],
            'teacher_feedback' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        $result->update($validated);

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
        $teacherId = $this->getAuthenticatedTeacher()->id;

        // D'abord vérifier que l'enseignant possède le résultat
        $result = Result::whereHas('quizSession', function($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })->findOrFail($resultId);

        // Récupérer la réponse spécifique
        $response = StudentResponse::where('student_id', $result->student_id)
            ->where('quiz_session_id', $result->quiz_session_id)
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
        $teacherId = $this->getAuthenticatedTeacher()->id;

        $result = Result::whereHas('quizSession', function($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
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
        $teacherId = $this->getAuthenticatedTeacher()->id;

        $result = Result::whereHas('quizSession', function($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->findOrFail($id);
        $result->markAsPublished();

        return response()->json([
            'message' => 'Résultat publié',
            'result'  => $result
        ]);
    }
}