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

echo "=== TEST LOGIQUE SESSIONS ===\n\n";

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

// 5. Simuler l'authentification et les logs
echo "\n=== SIMULATION AUTH + LOGS ===\n";

// Simuler ce que fait le contrôleur
$mockTeacher = (object) [
    'id' => 1, // ID du modèle Teacher
    'user_id' => $teacherId, // ID de l'utilisateur
];

\Log::info('QuizSessionController@index - Teacher info SIMULATION', [
    'teacher_id' => $mockTeacher->id,
    'user_id' => $mockTeacher->user_id,
    'teacher_model' => $mockTeacher
]);

$query = QuizSession::where('teacher_id', $mockTeacher->user_id)
    ->with(['quiz.subject']);

$sessions = $query->latest()->paginate(15);

\Log::info('QuizSessionController@index - Query results SIMULATION', [
    'total_sessions' => $sessions->total(),
    'sessions_count' => $sessions->count(),
    'sessions_data' => $sessions->items()
]);

echo "Logs simulés écrits. Vérifiez storage/logs/laravel.log\n";

echo "\n=== FIN DU TEST ===\n";