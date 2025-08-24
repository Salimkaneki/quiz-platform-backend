<?php

namespace App\Http\Controllers\Teacher\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'subject_id' => 'required|exists:subjects,id',
            'duration_minutes' => 'nullable|integer',
            'total_points' => 'nullable|integer',
            'shuffle_questions' => 'boolean',
            'show_results_immediately' => 'boolean',
            'allow_review' => 'boolean',
            'status' => 'in:draft,published',
            'settings' => 'nullable|array',
        ]);

        $validated['teacher_id'] = $teacherId;

        $quiz = Quiz::create($validated);

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
            'subject_id' => 'sometimes|required|exists:subjects,id',
            'duration_minutes' => 'nullable|integer',
            'total_points' => 'nullable|integer',
            'shuffle_questions' => 'boolean',
            'show_results_immediately' => 'boolean',
            'allow_review' => 'boolean',
            'status' => 'in:draft,published',
            'settings' => 'nullable|array',
        ]);

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