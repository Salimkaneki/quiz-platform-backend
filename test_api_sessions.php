<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger la configuration Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\QuizSession;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Http;

echo "=== TEST API SESSIONS ===\n\n";

// 1. Vérifier la connexion à la base de données
try {
    $pdo = DB::connection()->getPdo();
    echo "✅ Connexion à la base de données réussie\n";
} catch (\Exception $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Compter le nombre total de sessions
$totalSessions = QuizSession::count();
echo "📊 Nombre total de sessions dans la base: $totalSessions\n";

// 3. Lister les dernières sessions
echo "\n=== DERNIÈRES SESSIONS ===\n";
$recentSessions = QuizSession::orderBy('created_at', 'desc')->limit(5)->get();

if ($recentSessions->isEmpty()) {
    echo "❌ Aucune session trouvée\n";
} else {
    foreach ($recentSessions as $session) {
        echo "ID: {$session->id}, Quiz: {$session->quiz_id}, Teacher: {$session->teacher_id}, Status: {$session->status}, Created: {$session->created_at}\n";
    }
}

// 4. Tester la logique du contrôleur
echo "\n=== TEST LOGIQUE CONTRÔLEUR ===\n";

// Simuler un enseignant (celui qui a des sessions)
$teacherWithSessions = QuizSession::select('teacher_id')->distinct()->first();
if (!$teacherWithSessions) {
    echo "❌ Aucun enseignant avec des sessions trouvé\n";
    exit(1);
}

$teacherId = $teacherWithSessions->teacher_id;
$teacher = User::find($teacherId);

if (!$teacher) {
    echo "❌ Utilisateur enseignant non trouvé (ID: $teacherId)\n";
    exit(1);
}

echo "Test avec l'enseignant: {$teacher->email} (ID: $teacherId)\n";

// Simuler la requête du contrôleur
$query = QuizSession::where('teacher_id', $teacherId)
    ->with(['quiz.subject']);

$sessions = $query->latest()->paginate(15);

echo "Sessions trouvées: " . $sessions->total() . "\n";

if ($sessions->total() > 0) {
    foreach ($sessions->items() as $session) {
        echo "- ID: {$session->id}, Title: {$session->title}, Status: {$session->status}\n";
    }
}

// 5. Tester l'API directement
echo "\n=== TEST API DIRECT ===\n";

// D'abord, obtenir un token (simulation)
$loginResponse = Http::post('http://127.0.0.1:8000/api/teacher/login', [
    'email' => $teacher->email,
    'password' => 'password' // Mot de passe par défaut
]);

if ($loginResponse->successful()) {
    $token = $loginResponse->json()['token'];
    echo "✅ Token obtenu: " . substr($token, 0, 20) . "...\n";

    // Tester la récupération des sessions
    $sessionsResponse = Http::withToken($token)
        ->get('http://127.0.0.1:8000/api/teacher/sessions');

    if ($sessionsResponse->successful()) {
        $data = $sessionsResponse->json();
        echo "✅ API Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Erreur API: " . $sessionsResponse->status() . " - " . $sessionsResponse->body() . "\n";
    }
} else {
    echo "❌ Impossible d'obtenir un token: " . $loginResponse->status() . " - " . $loginResponse->body() . "\n";
}

echo "\n=== FIN DU TEST ===\n";