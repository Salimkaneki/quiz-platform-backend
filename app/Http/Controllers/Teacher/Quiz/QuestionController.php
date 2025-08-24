<?php

namespace App\Http\Controllers\Teacher\Quiz;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    // Récupérer le teacher ID à partir de l'utilisateur connecté
    private function getTeacherId()
    {
        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();
        return $teacher->id;
    }

    // Lister les questions d'un quiz
    public function index($quizId)
    {
        $teacherId = $this->getTeacherId();
        $quiz = Quiz::where('teacher_id', $teacherId)->findOrFail($quizId);
        return response()->json($quiz->questions()->ordered()->get());
    }

    // Voir une question spécifique
    public function show($quizId, $questionId)
    {
        $teacherId = $this->getTeacherId();
        $quiz = Quiz::where('teacher_id', $teacherId)->findOrFail($quizId);
        $question = $quiz->questions()->findOrFail($questionId);
        return response()->json($question);
    }

    // Créer une nouvelle question
    public function store(Request $request, $quizId)
    {
        $teacherId = $this->getTeacherId();
        $quiz = Quiz::where('teacher_id', $teacherId)->findOrFail($quizId);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false,open_ended,fill_blank',
            'options' => 'nullable|array',
            'correct_answer' => 'nullable|string',
            'points' => 'nullable|integer',
            'order' => 'nullable|integer',
            'explanation' => 'nullable|string',
            'image_url' => 'nullable|string',
            'time_limit' => 'nullable|integer',
            'metadata' => 'nullable|array',
        ]);

        $question = $quiz->questions()->create($validated);

        return response()->json($question, 201);
    }

    // Créer plusieurs questions en une seule requête
    public function batchStore(Request $request, $quizId)
    {
        $teacherId = $this->getTeacherId();
        $quiz = Quiz::where('teacher_id', $teacherId)->findOrFail($quizId);

        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,true_false,open_ended,fill_blank',
            'questions.*.options' => 'nullable|array',
            'questions.*.correct_answer' => 'nullable|string',
            'questions.*.points' => 'nullable|integer',
            'questions.*.order' => 'nullable|integer',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.image_url' => 'nullable|string',
            'questions.*.time_limit' => 'nullable|integer',
            'questions.*.metadata' => 'nullable|array',
        ]);

        $createdQuestions = [];

        foreach ($validated['questions'] as $questionData) {
            $createdQuestions[] = $quiz->questions()->create($questionData);
        }

        return response()->json([
            'message' => 'Questions créées avec succès',
            'questions' => $createdQuestions
        ], 201);
    }

    // Mettre à jour une question
    public function update(Request $request, $quizId, $questionId)
    {
        $teacherId = $this->getTeacherId();
        $quiz = Quiz::where('teacher_id', $teacherId)->findOrFail($quizId);
        $question = $quiz->questions()->findOrFail($questionId);

        $validated = $request->validate([
            'question_text' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:multiple_choice,true_false,open_ended,fill_blank',
            'options' => 'nullable|array',
            'correct_answer' => 'nullable|string',
            'points' => 'nullable|integer',
            'order' => 'nullable|integer',
            'explanation' => 'nullable|string',
            'image_url' => 'nullable|string',
            'time_limit' => 'nullable|integer',
            'metadata' => 'nullable|array',
        ]);

        $question->update($validated);

        return response()->json($question);
    }

    // Supprimer une question
    public function destroy($quizId, $questionId)
    {
        $teacherId = $this->getTeacherId();
        $quiz = Quiz::where('teacher_id', $teacherId)->findOrFail($quizId);
        $question = $quiz->questions()->findOrFail($questionId);
        $question->delete();

        return response()->json(['message' => 'Question supprimée']);
    }
}