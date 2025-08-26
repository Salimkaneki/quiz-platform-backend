<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizSessionRequest;
use App\Http\Requests\UpdateQuizSessionRequest;
use App\Models\QuizSession;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class QuizSessionController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json(['error' => 'Accès réservé aux enseignants'], 403);
        }

        $sessions = QuizSession::where('teacher_id', $teacher->id)
            ->with('quiz')
            ->latest()
            ->get();

        return response()->json($sessions);
    }

    public function store(StoreQuizSessionRequest $request)
    {
        $teacher = Auth::user()->teacher;
        $validated = $request->validated();

        if (!empty($validated['allowed_students'])) {
            $this->validateStudentsInstitution($validated['allowed_students'], $teacher);
        }

        // 🔎 Vérifier les doublons
        $exists = QuizSession::where('teacher_id', $teacher->id)
            ->where('title', $validated['title'])
            ->where('starts_at', $validated['starts_at'])
            ->where('ends_at', $validated['ends_at'])
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Une session avec le même titre et les mêmes dates existe déjà.'
            ], 422);
        }

        $session = new QuizSession($validated);
        $session->teacher_id = $teacher->id;
        $session->status = 'scheduled';
        $session->generateSessionCode();
        $session->save();

        return response()->json([
            'message' => 'Session créée avec succès',
            'session' => $session->load('quiz')
        ], 201);
    }

    public function show($id)
    {
        $teacher = Auth::user()->teacher;
        $session = QuizSession::with('quiz')->findOrFail($id);

        if (!$teacher || $session->teacher_id !== $teacher->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        return response()->json($session);
    }

    public function update(UpdateQuizSessionRequest $request, $id)
    {
        $teacher = Auth::user()->teacher;
        $session = QuizSession::findOrFail($id);

        if (!$teacher || $session->teacher_id !== $teacher->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if (in_array($session->status, ['active', 'completed', 'cancelled'])) {
            return response()->json([
                'error' => 'Impossible de modifier une session active, terminée ou annulée'
            ], 400);
        }

        $validated = $request->validated();

        if (!empty($validated['allowed_students'])) {
            $this->validateStudentsInstitution($validated['allowed_students'], $teacher);
        }

        // 🔎 Vérifier les doublons sauf la session courante
        $exists = QuizSession::where('teacher_id', $teacher->id)
            ->where('title', $validated['title'])
            ->where('starts_at', $validated['starts_at'])
            ->where('ends_at', $validated['ends_at'])
            ->where('id', '!=', $session->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Une autre session avec le même titre et les mêmes dates existe déjà.'
            ], 422);
        }

        $session->update($validated);

        return response()->json([
            'message' => 'Session mise à jour avec succès',
            'session' => $session->fresh()->load('quiz')
        ]);
    }

    public function activate($id)
    {
        return $this->changeStatus($id, 'scheduled', 'active', 'activée');
    }

    public function complete($id)
    {
        return $this->changeStatus($id, ['active', 'paused'], 'completed', 'terminée', 'completed_at');
    }

    public function pause($id)
    {
        return $this->changeStatus($id, 'active', 'paused', 'mise en pause');
    }

    public function resume($id)
    {
        return $this->changeStatus($id, 'paused', 'active', 'reprise');
    }

    public function cancel($id)
    {
        return $this->changeStatus($id, ['scheduled', 'active', 'paused'], 'cancelled', 'annulée');
    }

    private function changeStatus($id, $expectedStatus, $newStatus, $successMessage, $timestampField = null)
    {
        $teacher = Auth::user()->teacher;
        $session = QuizSession::findOrFail($id);

        if (!$teacher || $session->teacher_id !== $teacher->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $expected = (array)$expectedStatus;
        if (!in_array($session->status, $expected)) {
            return response()->json([
                'error' => "Statut invalide pour cette opération."
            ], 400);
        }

        $updateData = ['status' => $newStatus];
        if ($timestampField) {
            $updateData[$timestampField] = now();
        }

        $session->update($updateData);

        return response()->json([
            'message' => "Session $successMessage avec succès",
            'session' => $session
        ]);
    }

    private function validateStudentsInstitution($studentIds, $teacher)
    {
        $validStudents = Student::whereIn('id', $studentIds)
            ->where('is_active', true)
            ->get();

        if ($validStudents->count() !== count($studentIds)) {
            $foundIds = $validStudents->pluck('id')->toArray();
            $missingIds = array_diff($studentIds, $foundIds);

            throw ValidationException::withMessages([
                'allowed_students' => [
                    'Les étudiants suivants sont introuvables ou inactifs: ' . implode(', ', $missingIds)
                ]
            ]);
        }

        Log::info('Students validation passed', [
            'student_ids' => $studentIds,
            'teacher_id' => $teacher->id
        ]);
    }
}
