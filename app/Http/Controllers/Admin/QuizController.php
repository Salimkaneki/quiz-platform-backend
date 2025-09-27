<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    /**
     * Récupérer l'institution ID de l'admin connecté
     */
    private function getInstitutionId()
    {
        $admin = Administrator::where('user_id', Auth::id())->firstOrFail();
        return $admin->institution_id;
    }

    /**
     * Liste de tous les quiz des enseignants de l'institution de l'admin
     * GET /api/admin/quizzes
     */
    public function index(Request $request)
    {
        $institutionId = $this->getInstitutionId();

        $query = Quiz::with(['teacher.user', 'subject', 'questions'])
            ->whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            });

        // Filtres optionnels
        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Tri par défaut : date de création décroissante
        $query->orderBy('created_at', 'desc');

        $quizzes = $query->get();

        return response()->json([
            'quizzes' => $quizzes,
            'total' => $quizzes->count(),
            'filters' => [
                'institution_id' => $institutionId,
                'teacher_id' => $request->teacher_id,
                'subject_id' => $request->subject_id,
                'status' => $request->status,
            ]
        ]);
    }

    /**
     * Détails d'un quiz spécifique
     * GET /api/admin/quizzes/{id}
     */
    public function show($id)
    {
        $institutionId = $this->getInstitutionId();

        $quiz = Quiz::with(['teacher.user', 'subject', 'questions.options', 'sessions'])
            ->whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })
            ->findOrFail($id);

        return response()->json($quiz);
    }

    /**
     * Liste des quiz par enseignant
     * GET /api/admin/quizzes/by-teacher/{teacherId}
     */
    public function getByTeacher($teacherId)
    {
        $institutionId = $this->getInstitutionId();

        // Vérifier que l'enseignant appartient à l'institution de l'admin
        $teacher = \App\Models\Teacher::where('id', $teacherId)
            ->where('institution_id', $institutionId)
            ->firstOrFail();

        $quizzes = Quiz::with(['subject', 'questions'])
            ->where('teacher_id', $teacherId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'teacher' => $teacher->load('user'),
            'quizzes' => $quizzes,
            'total_quizzes' => $quizzes->count()
        ]);
    }

    /**
     * Liste des quiz par matière
     * GET /api/admin/quizzes/by-subject/{subjectId}
     */
    public function getBySubject($subjectId)
    {
        $institutionId = $this->getInstitutionId();

        $quizzes = Quiz::with(['teacher.user', 'questions'])
            ->where('subject_id', $subjectId)
            ->whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $subject = \App\Models\Subject::findOrFail($subjectId);

        return response()->json([
            'subject' => $subject,
            'quizzes' => $quizzes,
            'total_quizzes' => $quizzes->count()
        ]);
    }

    /**
     * Statistiques des quiz pour l'institution
     * GET /api/admin/quizzes/statistics
     */
    public function getStatistics()
    {
        $institutionId = $this->getInstitutionId();

        $stats = [
            'total_quizzes' => Quiz::whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })->count(),

            'published_quizzes' => Quiz::whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })->where('status', 'published')->count(),

            'draft_quizzes' => Quiz::whereHas('teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })->where('status', 'draft')->count(),

            'quizzes_by_teacher' => \App\Models\Teacher::where('institution_id', $institutionId)
                ->withCount('quizzes')
                ->get()
                ->map(function($teacher) {
                    return [
                        'teacher_id' => $teacher->id,
                        'teacher_name' => $teacher->user->name,
                        'total_quizzes' => $teacher->quizzes_count
                    ];
                }),

            'quizzes_by_subject' => \App\Models\Subject::whereHas('quizzes.teacher', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })
            ->withCount(['quizzes' => function($q) use ($institutionId) {
                $q->whereHas('teacher', function($subQ) use ($institutionId) {
                    $subQ->where('institution_id', $institutionId);
                });
            }])
            ->get()
            ->map(function($subject) {
                return [
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->name,
                    'total_quizzes' => $subject->quizzes_count
                ];
            })
        ];

        return response()->json($stats);
    }
}