<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST DE SOUMISSION DE RÉPONSES ===\n";

// Créer des données de test
$institution = \App\Models\Institution::factory()->create(['name' => 'Test Institution']);
$formation = \App\Models\Formation::factory()->create([
    'name' => 'Formation Test',
    'code' => 'TEST001',
    'institution_id' => $institution->id
]);
$subject = \App\Models\Subject::factory()->create([
    'name' => 'Mathématiques',
    'formation_id' => $formation->id
]);

// Créer une classe directement
$class = \App\Models\Classes::create([
    'name' => 'Classe Test',
    'level' => 1,
    'academic_year' => '2024-2025',
    'formation_id' => $formation->id,
    'max_students' => 30,
    'is_active' => true
]);

$teacherUser = \App\Models\User::factory()->create(['name' => 'Prof Test', 'email' => 'teacher_' . time() . '@test.com', 'account_type' => 'teacher']);
$teacher = \App\Models\Teacher::factory()->create(['user_id' => $teacherUser->id, 'institution_id' => $institution->id]);
$studentUser = \App\Models\User::factory()->create(['name' => 'Étudiant Test', 'email' => 'student_' . time() . '@test.com', 'account_type' => 'student']);

// Créer un étudiant directement sans utiliser la factory défaillante
$student = \App\Models\Student::create([
    'student_number' => 'STU' . time(),
    'first_name' => 'Jean',
    'last_name' => 'Dupont',
    'email' => 'jean.dupont_' . time() . '@test.com',
    'birth_date' => now()->subYears(20),
    'phone' => '0123456789',
    'address' => '123 Rue Test',
    'emergency_contact' => 'Parent Test',
    'emergency_phone' => '0987654321',
    'class_id' => $class->id, // Maintenant on a une classe
    'institution_id' => $institution->id,
    'is_active' => true,
    'user_id' => $studentUser->id,
]);

// Créer un quiz
$quiz = \App\Models\Quiz::factory()->create([
    'title' => 'Quiz Test Math',
    'teacher_id' => $teacher->user_id,
    'subject_id' => $subject->id,
    'status' => 'published'
]);

// Créer des questions
$question1 = \App\Models\Question::factory()->create([
    'quiz_id' => $quiz->id,
    'question_text' => 'Quelle est la capitale de la France ?',
    'type' => 'multiple_choice',
    'options' => json_encode([
        ['text' => 'Paris', 'is_correct' => true],
        ['text' => 'Lyon', 'is_correct' => false],
        ['text' => 'Marseille', 'is_correct' => false]
    ]),
    'points' => 2
]);

$question2 = \App\Models\Question::factory()->create([
    'quiz_id' => $quiz->id,
    'question_text' => '2 + 2 = 4 ?',
    'type' => 'true_false',
    'correct_answer' => 'true',
    'points' => 1
]);

// Créer une session
$session = \App\Models\QuizSession::factory()->create([
    'quiz_id' => $quiz->id,
    'teacher_id' => $teacher->user_id,
    'title' => 'Session Test',
    'status' => 'active',
    'starts_at' => now()->subMinutes(10),
    'ends_at' => now()->addMinutes(50)
]);

// Créer un résultat pour l'étudiant
$result = \App\Models\Result::factory()->create([
    'quiz_session_id' => $session->id,
    'student_id' => $student->id,
    'status' => 'in_progress'
]);

echo "✅ Données de test créées :\n";
echo "- Institution: " . $institution->name . "\n";
echo "- Enseignant: " . $teacherUser->name . "\n";
echo "- Étudiant: " . $studentUser->name . "\n";
echo "- Quiz: " . $quiz->title . "\n";
echo "- Session: " . $session->title . "\n";
echo "- Résultat ID: " . $result->id . "\n";
echo "- Questions créées: 2\n\n";

// Simuler la soumission de réponses
echo "=== SIMULATION DE SOUMISSION ===\n";

// Authentifier l'étudiant
\Auth::login($studentUser);

$controller = new \App\Http\Controllers\Student\StudentResponseController();
$request = new \Illuminate\Http\Request();
$request->merge([
    'responses' => [
        [
            'question_id' => $question1->id,
            'answer' => 'Paris'  // Bonne réponse
        ],
        [
            'question_id' => $question2->id,
            'answer' => 'true'  // Bonne réponse
        ]
    ]
]);

try {
    $response = $controller->submitResponses($request, $result->id);
    $data = json_decode($response->getContent(), true);

    echo "✅ Soumission réussie !\n";
    echo "Points totaux: " . ($data['total_points'] ?? 'N/A') . "\n";
    echo "Points max: " . ($data['max_points'] ?? 'N/A') . "\n";
    echo "Pourcentage: " . ($data['percentage'] ?? 'N/A') . "%\n";
    echo "Réponses correctes: " . ($data['correct_answers'] ?? 'N/A') . "\n";

    // Vérifier le résultat en base
    $updatedResult = \App\Models\Result::find($result->id);
    echo "\n=== VÉRIFICATION EN BASE ===\n";
    echo "Statut du résultat: " . $updatedResult->status . "\n";
    echo "Points totaux en base: " . $updatedResult->total_points . "\n";
    echo "Pourcentage en base: " . $updatedResult->percentage . "%\n";

    // Vérifier les réponses
    $responses = \App\Models\StudentResponse::where('quiz_session_id', $session->id)
        ->where('student_id', $student->id)
        ->get();

    echo "Nombre de réponses enregistrées: " . $responses->count() . "\n";
    foreach ($responses as $resp) {
        echo "- Question " . $resp->question_id . ": " . ($resp->is_correct ? 'Correct' : 'Incorrect') . " (" . $resp->points_earned . "/" . $resp->points_possible . " points)\n";
    }

} catch (\Exception $e) {
    echo "❌ Erreur lors de la soumission: " . $e->getMessage() . "\n";
}

echo "\n=== TEST TERMINÉ ===\n";