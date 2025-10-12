<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CRÉATION DE DONNÉES DE TEST MINIMALES ===\n";

// Créer des données de base qui fonctionnent
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

// Créer les utilisateurs directement
$teacherUser = new \App\Models\User();
$teacherUser->name = 'Prof Test';
$teacherUser->email = 'teacher_' . time() . '@test.com';
$teacherUser->password = bcrypt('password');
$teacherUser->account_type = 'teacher';
$teacherUser->is_active = true;
$teacherUser->save();

$studentUser = new \App\Models\User();
$studentUser->name = 'Étudiant Test';
$studentUser->email = 'student_' . time() . '@test.com';
$studentUser->password = bcrypt('password');
$studentUser->account_type = 'student';
$studentUser->is_active = true;
$studentUser->save();

// Créer le teacher
$teacher = new \App\Models\Teacher();
$teacher->user_id = $teacherUser->id;
$teacher->institution_id = $institution->id;
$teacher->specialization = 'Mathématiques';
$teacher->grade = 'professeur';
$teacher->save();

// Créer l'étudiant
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

// Créer le quiz directement
$quiz = new \App\Models\Quiz();
$quiz->title = 'Quiz Test Math';
$quiz->teacher_id = $teacher->user_id;
$quiz->subject_id = $subject->id;
$quiz->status = 'published';
$quiz->save();

// Créer les questions directement
$question1 = new \App\Models\Question();
$question1->quiz_id = $quiz->id;
$question1->question_text = 'Quelle est la capitale de la France ?';
$question1->type = 'multiple_choice';
$question1->options = json_encode([
    ['text' => 'Paris', 'is_correct' => true],
    ['text' => 'Lyon', 'is_correct' => false],
    ['text' => 'Marseille', 'is_correct' => false]
]);
$question1->points = 2;
$question1->save();

$question2 = new \App\Models\Question();
$question2->quiz_id = $quiz->id;
$question2->question_text = '2 + 2 = 4 ?';
$question2->type = 'true_false';
$question2->correct_answer = 'true';
$question2->points = 1;
$question2->save();

// Créer la session directement
$session = new \App\Models\QuizSession();
$session->quiz_id = $quiz->id;
$session->teacher_id = $teacher->user_id;
$session->title = 'Session Test';
$session->session_code = 'TST' . rand(100, 999);
$session->status = 'active';
$session->starts_at = now()->subMinutes(10);
$session->ends_at = now()->addMinutes(50);
$session->save();

// Créer le résultat directement
$result = new \App\Models\Result();
$result->quiz_session_id = $session->id;
$result->student_id = $student->id;
$result->status = 'in_progress';
$result->total_points = 0;
$result->max_points = 3; // 2 + 1 points des questions
$result->percentage = 0;
$result->total_questions = 2;
$result->correct_answers = 0;
$result->started_at = now();
$result->save();

echo "✅ Toutes les données créées avec succès !\n";
echo "Institution: " . $institution->name . "\n";
echo "Classe: " . $class->name . "\n";
echo "Étudiant: " . $student->first_name . ' ' . $student->last_name . "\n";
echo "Enseignant: " . $teacherUser->name . "\n";
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