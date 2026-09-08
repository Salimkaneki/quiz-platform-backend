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

        // Une seule requête pour toutes les réponses, puis regroupement en mémoire
        // (auparavant : une requête SQL par résultat).
        $responses = StudentResponse::with('question')
            ->whereIn('student_id', $results->pluck('student_id'))
            ->whereIn('quiz_session_id', $results->pluck('quiz_session_id'))
            ->get()
            ->groupBy(fn ($r) => $r->student_id . '-' . $r->quiz_session_id);

        foreach ($results as $result) {
            $result->student_responses = $responses
                ->get($result->student_id . '-' . $result->quiz_session_id, collect())
                ->values();
        }

        return response()->json($results);
    }

    /**
     * Export CSV des résultats publiés d'une session.
     * Le front appelait déjà cette route ; elle n'avait jamais été implémentée.
     *
     * @param int $quizSessionId L'ID de la session de quiz
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function export($quizSessionId)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['error' => 'Accès réservé aux administrateurs pédagogiques'], 403);
        }

        $results = Result::with(['student', 'quizSession.quiz'])
            ->where('quiz_session_id', $quizSessionId)
            ->where('status', 'published')
            ->whereHas('quizSession.teacher', function ($query) use ($admin) {
                $query->where('institution_id', $admin->institution_id);
            })
            ->get();

        $filename = 'resultats-session-' . $quizSessionId . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($results) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 : sans lui Excel affiche mal les accents.
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Matricule', 'Nom', 'Prenom', 'Quiz', 'Points obtenus',
                'Points max', 'Pourcentage', 'Note /20', 'Statut', 'Soumis le',
            ], ';');

            foreach ($results as $result) {
                fputcsv($handle, [
                    $result->student->student_number ?? '',
                    $result->student->last_name ?? '',
                    $result->student->first_name ?? '',
                    $result->quizSession->quiz->title ?? '',
                    $result->total_points,
                    $result->max_points,
                    $result->percentage,
                    $result->grade,
                    $result->status,
                    optional($result->submitted_at)->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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