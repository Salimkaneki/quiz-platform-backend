<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CRÉATION DE DONNÉES DE TEST SIMPLIFIÉES ===\n";

// Créer des données de test de base
$institution = \App\Models\Institution::factory()->create(['name' => 'Test Institution']);
$formation = \App\Models\Formation::factory()->create(['name' => 'Formation Test', 'code' => 'TEST001', 'institution_id' => $institution->id]);
$subject = \App\Models\Subject::factory()->create(['name' => 'Mathématiques', 'formation_id' => $formation->id]);

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

// Créer un teacher directement
$teacher = new \App\Models\Teacher();
$teacher->user_id = $teacherUser->id;
$teacher->institution_id = $institution->id;
$teacher->specialization = 'Mathématiques';
$teacher->grade = 'professeur';
$teacher->save();

$studentUser = \App\Models\User::factory()->create(['name' => 'Étudiant Test', 'email' => 'student_' . time() . '@test.com', 'account_type' => 'student']);

// Créer un étudiant directement
$student = new \App\Models\Student();
$student->student_number = 'STU' . time();
$student->first_name = 'Jean';
$student->last_name = 'Dupont';
$student->email = 'jean.dupont_' . time() . '@test.com';
$student->birth_date = now()->subYears(20);
$student->phone = '0123456789';
$student->address = '123 Rue Test';
$student->emergency_contact = 'Parent Test';
$student->emergency_phone = '0987654321';
$student->class_id = $class->id;
$student->institution_id = $institution->id;
$student->is_active = true;
$student->user_id = $studentUser->id;
$student->save();

echo "✅ Données créées avec succès\n";
echo "Institution: " . $institution->name . "\n";
echo "Classe: " . $class->name . "\n";
echo "Étudiant: " . $student->first_name . " " . $student->last_name . "\n";
echo "Enseignant: " . $teacherUser->name . "\n";

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

echo "Quiz: " . $quiz->title . "\n";
echo "Session: " . $session->title . "\n";
echo "Résultat ID: " . $result->id . "\n";
echo "Questions créées: 2\n\n";

// Sauvegarder les IDs pour le test suivant
file_put_contents('test_data.json', json_encode([
    'student_id' => $student->id,
    'result_id' => $result->id,
    'question1_id' => $question1->id,
    'question2_id' => $question2->id,
    'session_id' => $session->id
]));

echo "=== DONNÉES SAUVEGARDÉES ===\n";