<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\StudentResponse;
use App\Models\Administrator;
use Illuminate\Http\Request;

/**
 * Contrôleur pour la gestion des résultats de quiz pour les administrateurs.
 * Permet aux administrateurs pédagogiques de consulter les résultats publiés par les enseignants.
 */
class AdminResultController extends Controller
{
    /**
     * Liste des résultats publiés d'une session donnée.
     *
     * @param int $quizSessionId L'ID de la session de quiz
     * @return \Illuminate\Http\JsonResponse Liste des résultats publiés avec informations sur les étudiants
     */
    public function index($quizSessionId)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['error' => 'Accès réservé aux administrateurs pédagogiques'], 403);
        }

        $results = Result::with('student')
            ->where('quiz_session_id', $quizSessionId)
            ->where('status', 'published')
            ->whereHas('quizSession.teacher', function($query) use ($admin) {
                $query->where('institution_id', $admin->institution_id);
            })
            ->get();

        return response()->json($results);
    }

    /**
     * Détails du résultat publié d'un étudiant, avec ses réponses.
     *
     * @param int $id L'ID du résultat
     * @return \Illuminate\Http\JsonResponse Détails du résultat et réponses de l'étudiant
     */
    public function show($id)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['error' => 'Accès réservé aux administrateurs pédagogiques'], 403);
        }

        $result = Result::with('student')
            ->where('status', 'published')
            ->whereHas('quizSession.teacher', function($query) use ($admin) {
                $query->where('institution_id', $admin->institution_id);
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
     * Récupère tous les résultats publiés pour une session de quiz avec les réponses des étudiants.
     *
     * @param int $quizSessionId L'ID de la session de quiz
     * @return \Illuminate\Http\JsonResponse Liste des résultats publiés avec réponses
     */
    public function allResultsForQuiz($quizSessionId)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['error' => 'Accès réservé aux administrateurs pédagogiques'], 403);
        }

        $results = Result::with('student')
                        ->where('quiz_session_id', $quizSessionId)
                        ->where('status', 'published')
                        ->whereHas('quizSession.teacher', function($query) use ($admin) {
                            $query->where('institution_id', $admin->institution_id);
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
     * Vérification des permissions pédagogiques
     */
    private function checkPedagogicalPermissions()
    {
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->account_type !== 'admin') {
            return null;
        }

        return Administrator::where('user_id', $currentUser->id)
            ->where('type', 'pedagogique')
            ->first();
    }
}