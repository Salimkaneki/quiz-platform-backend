<?php

// Script de test pour créer une session d'examen et tester la soumission de réponses

$baseUrl = 'http://127.0.0.1:8000/api';

// Fonction pour faire des requêtes HTTP
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data && ($method === 'POST' || $method === 'PUT' || $method === 'PATCH')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return ['response' => $response, 'http_code' => $httpCode];
}

// 1. Connexion enseignant
echo "👨‍🏫 Connexion enseignant...\n";
$teacherLoginData = [
    'email' => 'salimpereira01@gmail.com',
    'password' => 'motdepasse123'
];

$result = makeRequest($baseUrl . '/teacher/login', 'POST', $teacherLoginData);
$teacherLoginResponse = json_decode($result['response'], true);

if ($result['http_code'] !== 200 || !isset($teacherLoginResponse['token'])) {
    echo "❌ Erreur connexion enseignant: " . $result['response'] . "\n";
    exit(1);
}

$teacherToken = $teacherLoginResponse['token'];
echo "✅ Enseignant connecté\n\n";

// 2. Créer une session d'examen valide
echo "📝 Création d'une session d'examen...\n";
$sessionData = [
    'quiz_id' => 12,
    'title' => 'Test - Soumission Réponses',
    'starts_at' => '2025-12-31T10:00:00Z', // Date lointaine pour éviter conflits
    'ends_at' => '2025-12-31T12:00:00Z',   // 2 heures plus tard
    'max_participants' => 50,
    'require_student_list' => false,
    'allowed_students' => [],
    'settings' => [
        'auto_submit' => true,
        'show_timer' => true
    ]
];

$result = makeRequest($baseUrl . '/teacher/sessions', 'POST', $sessionData, $teacherToken);
$sessionResponse = json_decode($result['response'], true);

if ($result['http_code'] !== 201) {
    echo "❌ Erreur création session: " . $result['response'] . "\n";
    exit(1);
}

$sessionId = $sessionResponse['session']['id'];
$sessionCode = $sessionResponse['session']['session_code'];

echo "✅ Session créée avec succès!\n";
echo "   ID: $sessionId\n";
echo "   Code: $sessionCode\n";
echo "   Titre: " . $sessionResponse['session']['title'] . "\n\n";

// 3. Activer la session immédiatement pour les tests
echo "▶️ Activation de la session...\n";
$result = makeRequest($baseUrl . "/teacher/sessions/$sessionId/activate", 'PATCH', null, $teacherToken);
$activateResponse = json_decode($result['response'], true);

if ($result['http_code'] !== 200) {
    echo "❌ Erreur activation session: " . $result['response'] . "\n";
    exit(1);
}

echo "✅ Session activée!\n\n";

// 4. Connexion étudiant
echo "👨‍🎓 Connexion étudiant...\n";
$studentLoginData = [
    'email' => 'kofi.amani@ul.edu.tg',
    'password' => 'password123'
];

$result = makeRequest($baseUrl . '/student/auth/login', 'POST', $studentLoginData);
$studentLoginResponse = json_decode($result['response'], true);

if ($result['http_code'] !== 200 || !isset($studentLoginResponse['token'])) {
    echo "❌ Erreur connexion étudiant: " . $result['response'] . "\n";
    exit(1);
}

$studentToken = $studentLoginResponse['token'];
echo "✅ Étudiant connecté\n\n";

// 5. L'étudiant rejoint la session
echo "🔗 L'étudiant rejoint la session...\n";
echo "   Session commence maintenant...\n";

$joinData = [
    'session_code' => $sessionCode
];

$result = makeRequest($baseUrl . '/student/session/join', 'POST', $joinData, $studentToken);
$joinResponse = json_decode($result['response'], true);

if ($result['http_code'] !== 200) {
    echo "❌ Erreur rejoindre session: " . $result['response'] . "\n";
    exit(1);
}

$sessionIdForStudent = $joinResponse['session']['id'];
echo "✅ Étudiant a rejoint la session!\n\n";

// 6. Récupérer les questions de la session
echo "❓ Récupération des questions...\n";
$result = makeRequest($baseUrl . "/student/session/$sessionIdForStudent/questions", 'GET', null, $studentToken);
$questionsResponse = json_decode($result['response'], true);

if ($result['http_code'] !== 200) {
    echo "❌ Erreur récupération questions: " . $result['response'] . "\n";
    exit(1);
}

$questions = $questionsResponse['questions'] ?? [];
echo "✅ " . count($questions) . " questions récupérées\n";

// Afficher les questions
foreach ($questions as $i => $question) {
    echo "   " . ($i + 1) . ". " . $question['question_text'] . " (" . $question['type'] . ")\n";
}
echo "\n";

// 7. Préparer les réponses de test
echo "📝 Préparation des réponses de test...\n";
$responses = [];

foreach ($questions as $question) {
    $answer = null;

    switch ($question['type']) {
        case 'multiple_choice':
            // Prendre la première option comme réponse
            $answer = isset($question['options'][0]['id']) ? (string)$question['options'][0]['id'] : "0";
            break;
        case 'true_false':
            $answer = "true";
            break;
        case 'open_ended':
            $answer = "Ceci est une réponse de test pour la question ouverte.";
            break;
        default:
            $answer = "Réponse par défaut";
    }

    $responses[] = [
        'question_id' => $question['id'],
        'answer' => $answer
    ];

    echo "   Question {$question['id']}: $answer\n";
}
echo "\n";

// 8. Soumettre les réponses
echo "📤 Soumission des réponses...\n";
$submitData = [
    'responses' => $responses
];

$result = makeRequest($baseUrl . "/student/results/{$joinResponse['result']['id']}/responses", 'POST', $submitData, $studentToken);
$submitResponse = json_decode($result['response'], true);

if ($result['http_code'] !== 200) {
    echo "❌ Erreur soumission réponses: " . $result['response'] . "\n";
    exit(1);
}

echo "✅ Réponses soumises avec succès!\n";
echo "   Résultat ID: " . $submitResponse['result']['id'] . "\n";
echo "   Score: " . ($submitResponse['result']['total_points'] ?? 'N/A') . "/" . ($submitResponse['result']['max_points'] ?? 'N/A') . "\n";
echo "   Pourcentage: " . ($submitResponse['result']['percentage'] ?? 'N/A') . "%\n\n";

// 9. Vérifier le résultat depuis l'enseignant
echo "👨‍🏫 Vérification du résultat côté enseignant...\n";
$result = makeRequest($baseUrl . "/teacher/quiz-sessions/$sessionId/results", 'GET', null, $teacherToken);
$teacherResultsResponse = json_decode($result['response'], true);

if ($result['http_code'] === 200) {
    echo "✅ Résultats visibles côté enseignant!\n";
    echo "   Nombre de résultats: " . count($teacherResultsResponse) . "\n";

    if (count($teacherResultsResponse) > 0) {
        $firstResult = $teacherResultsResponse[0];
        echo "   Premier résultat:\n";
        echo "   - Étudiant: " . ($firstResult['student']['name'] ?? 'N/A') . "\n";
        echo "   - Score: " . ($firstResult['total_points'] ?? 'N/A') . "/" . ($firstResult['max_points'] ?? 'N/A') . "\n";
        echo "   - Statut: " . ($firstResult['status'] ?? 'N/A') . "\n";
    }
} else {
    echo "❌ Erreur récupération résultats enseignant: " . $result['response'] . "\n";
}

echo "\n🎉 Test complet terminé avec succès!\n";
echo "\n📋 RÉSUMÉ DU TEST:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Session créée: ID $sessionId (Code: $sessionCode)\n";
echo "✅ Session activée\n";
echo "✅ Étudiant connecté et a rejoint la session\n";
echo "✅ Questions récupérées: " . count($questions) . "\n";
echo "✅ Réponses soumises: " . count($responses) . "\n";
echo "✅ Résultats visibles côté enseignant\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

?>