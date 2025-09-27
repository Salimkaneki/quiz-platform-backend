<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

class QuizController extends Controller
{
    // Récupérer le teacher ID à partir de l'utilisateur connecté
    private function getTeacherId()
    {
        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();
        return $teacher->id;
    }

    public function index()
    {
        $teacherId = $this->getTeacherId();
        $quizzes = Quiz::where('teacher_id', $teacherId)->get();
        return response()->json($quizzes);
    }

    public function store(Request $request)
    {
        $teacherId = $this->getTeacherId();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|integer',
            'duration_minutes' => 'nullable|integer',
            'total_points' => 'nullable|integer',
            'shuffle_questions' => 'boolean',
            'show_results_immediately' => 'boolean',
            'allow_review' => 'boolean',
            'status' => 'in:draft,published',
            'settings' => 'nullable|array',
        ]);

        // Vérifier que le sujet existe
        if (!Subject::find($validated['subject_id'])) {
            return response()->json([
                'status' => 404,
                'error' => 'Le sujet demandé n’existe pas.'
            ], 404);
        }

        // Vérifier les doublons pour ce teacher_id, title et subject_id
        $existingQuiz = Quiz::where('teacher_id', $teacherId)
            ->where('title', $validated['title'])
            ->where('subject_id', $validated['subject_id'])
            ->first();

        if ($existingQuiz) {
            return response()->json([
                'status' => 409,
                'error' => 'Un quiz avec le même titre et sujet existe déjà.'
            ], 409);
        }

        $validated['teacher_id'] = $teacherId;

        try {
            $quiz = Quiz::create($validated);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Erreur lors de la création du quiz.'
            ], 500);
        }

        return response()->json($quiz, 201);
    }

    public function show($id)
    {
        $teacherId = $this->getTeacherId();
        $quiz = Quiz::where('teacher_id', $teacherId)->findOrFail($id);
        return response()->json($quiz);
    }

    public function update(Request $request, $id)
    {
        $teacherId = $this->getTeacherId();
        $quiz = Quiz::where('teacher_id', $teacherId)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'sometimes|required|integer',
            'duration_minutes' => 'nullable|integer',
            'total_points' => 'nullable|integer',
            'shuffle_questions' => 'boolean',
            'show_results_immediately' => 'boolean',
            'allow_review' => 'boolean',
            'status' => 'in:draft,published',
            'settings' => 'nullable|array',
        ]);

        // Vérifier que le subject existe si fourni
        if (isset($validated['subject_id']) && !Subject::find($validated['subject_id'])) {
            return response()->json([
                'status' => 404,
                'error' => 'Le sujet demandé n’existe pas.'
            ], 404);
        }

        // Vérifier doublon uniquement si title ou subject_id est modifié
        if (isset($validated['title']) || isset($validated['subject_id'])) {
            $checkTitle = $validated['title'] ?? $quiz->title;
            $checkSubject = $validated['subject_id'] ?? $quiz->subject_id;

            $existingQuiz = Quiz::where('teacher_id', $teacherId)
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

        return response()->json($quiz);
    }

    public function destroy($id)
    {
        $teacherId = $this->getTeacherId();
        $quiz = Quiz::where('teacher_id', $teacherId)->findOrFail($id);
        $quiz->delete();

        return response()->json(['message' => 'Quiz supprimé']);
    }
}
