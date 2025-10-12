<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\AuthorizationTrait;
use App\Models\Quiz;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

class QuizController extends Controller
{
    use AuthorizationTrait;

    public function index(Request $request)
    {
        $teacher = $this->getAuthenticatedTeacher();

        $query = Quiz::where('teacher_id', $teacher->id)
            ->with(['subject', 'questions']);

        // Filtres optionnels
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Recherche par titre
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $quizzes = $query->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'quizzes' => $quizzes->items(),
            'pagination' => [
                'current_page' => $quizzes->currentPage(),
                'last_page' => $quizzes->lastPage(),
                'per_page' => $quizzes->perPage(),
                'total' => $quizzes->total(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $teacher = $this->getAuthenticatedTeacher();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|integer|exists:subjects,id',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'total_points' => 'nullable|integer|min:1|max:100',
            'shuffle_questions' => 'boolean',
            'show_results_immediately' => 'boolean',
            'allow_review' => 'boolean',
            'status' => 'in:draft,published',
            'settings' => 'nullable|array',
        ]);

        // Vérifier que le sujet appartient à l'institution de l'enseignant
        $subject = Subject::find($validated['subject_id']);
        if (!$subject || $subject->formation->institution_id !== $teacher->institution_id) {
            return response()->json([
                'status' => 404,
                'error' => 'Le sujet demandé n\'existe pas ou n\'appartient pas à votre institution.'
            ], 404);
        }

        // Vérifier les doublons pour ce teacher_id, title et subject_id
        $existingQuiz = Quiz::where('teacher_id', $teacher->id)
            ->where('title', $validated['title'])
            ->where('subject_id', $validated['subject_id'])
            ->first();

        if ($existingQuiz) {
            return response()->json([
                'status' => 409,
                'error' => 'Un quiz avec le même titre et sujet existe déjà.'
            ], 409);
        }

        $validated['teacher_id'] = $teacher->id;

        try {
            $quiz = Quiz::create($validated);
            return response()->json($quiz->load(['subject', 'questions']), 201);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Erreur lors de la création du quiz.'
            ], 500);
        }
    }

    public function show($id)
    {
        $teacher = $this->getAuthenticatedTeacher();
        $quiz = Quiz::where('teacher_id', $teacher->id)->findOrFail($id);
        return response()->json($quiz->load(['subject', 'questions']));
    }

    public function getQuestions($quizId)
    {
        $teacher = $this->getAuthenticatedTeacher();
        $quiz = Quiz::where('teacher_id', $teacher->id)->findOrFail($quizId);
        
        return response()->json([
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'total_questions' => $quiz->questions->count()
            ],
            'questions' => $quiz->questions
        ]);
    }

    public function update(Request $request, $id)
    {
        $teacher = $this->getAuthenticatedTeacher();
        $quiz = Quiz::where('teacher_id', $teacher->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'sometimes|required|integer|exists:subjects,id',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'total_points' => 'nullable|integer|min:1|max:100',
            'shuffle_questions' => 'boolean',
            'show_results_immediately' => 'boolean',
            'allow_review' => 'boolean',
            'status' => 'in:draft,published',
            'settings' => 'nullable|array',
        ]);

        // Vérifier que le subject existe et appartient à l'institution si fourni
        if (isset($validated['subject_id'])) {
            $subject = Subject::find($validated['subject_id']);
            if (!$subject || $subject->formation->institution_id !== $teacher->institution_id) {
                return response()->json([
                    'status' => 404,
                    'error' => 'Le sujet demandé n\'existe pas ou n\'appartient pas à votre institution.'
                ], 404);
            }
        }

        // Vérifier doublon uniquement si title ou subject_id est modifié
        if (isset($validated['title']) || isset($validated['subject_id'])) {
            $checkTitle = $validated['title'] ?? $quiz->title;
            $checkSubject = $validated['subject_id'] ?? $quiz->subject_id;

            $existingQuiz = Quiz::where('teacher_id', $teacher->id)
                ->where('title', $checkTitle)
                ->where('subject_id', $checkSubject)
                ->where('id', '<>', $quiz->id)
                ->first();

            if ($existingQuiz) {
                return response()->json([
                    'status' => 409,
                    'error' => 'Un quiz avec le même titre et sujet existe déjà.'
                ], 409);
            }
        }

        $quiz->update($validated);
        return response()->json($quiz->load(['subject', 'questions']));
    }

    public function destroy($id)
    {
        $teacher = $this->getAuthenticatedTeacher();
        $quiz = Quiz::where('teacher_id', $teacher->id)->findOrFail($id);

        // Vérifier s'il y a des sessions actives
        if ($quiz->sessions()->whereIn('status', ['active', 'scheduled'])->exists()) {
            return response()->json([
                'status' => 409,
                'error' => 'Impossible de supprimer ce quiz car des sessions sont encore actives ou planifiées.'
            ], 409);
        }

        $quiz->delete();
        return response()->json(['message' => 'Quiz supprimé avec succès']);
    }
}
