<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST DE SOUMISSION DE RÉPONSES ===\n";

// Charger les données de test
if (!file_exists('test_data.json')) {
    echo "❌ Fichier test_data.json non trouvé. Veuillez exécuter create_test_data_simple.php d'abord.\n";
    exit(1);
}

$data = json_decode(file_get_contents('test_data.json'), true);
$student = \App\Models\Student::find($data['student_id']);
$result = \App\Models\Result::find($data['result_id']);
$question1 = \App\Models\Question::find($data['question1_id']);
$question2 = \App\Models\Question::find($data['question2_id']);
$session = \App\Models\QuizSession::find($data['session_id']);

if (!$student || !$result || !$question1 || !$question2 || !$session) {
    echo "❌ Certaines données de test sont manquantes.\n";
    exit(1);
}

echo "✅ Données de test chargées :\n";
echo "- Étudiant: " . $student->first_name . ' ' . $student->last_name . "\n";
echo "- Résultat ID: " . $result->id . "\n";
echo "- Session: " . $session->title . "\n";
echo "- Questions: 2\n\n";

// Simuler la soumission de réponses
echo "=== SIMULATION DE SOUMISSION ===\n";

// Authentifier l'étudiant
\Auth::login($student->user);

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
    $responseData = json_decode($response->getContent(), true);

    echo "✅ Soumission réussie !\n";
    echo "Points totaux: " . ($responseData['total_points'] ?? 'N/A') . "\n";
    echo "Points max: " . ($responseData['max_points'] ?? 'N/A') . "\n";
    echo "Pourcentage: " . ($responseData['percentage'] ?? 'N/A') . "%\n";
    echo "Réponses correctes: " . ($responseData['correct_answers'] ?? 'N/A') . "\n";

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
        $question = \App\Models\Question::find($resp->question_id);
        echo "- Question '" . substr($question->question_text, 0, 30) . "...': " . ($resp->is_correct ? 'Correct' : 'Incorrect') . " (" . $resp->points_earned . "/" . $resp->points_possible . " points)\n";
    }

} catch (\Exception $e) {
    echo "❌ Erreur lors de la soumission: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST TERMINÉ ===\n";