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

try {
    // Vérifier s'il y a des sessions dans la base
    $sessions = QuizSession::all();
    echo 'Total sessions: ' . $sessions->count() . PHP_EOL;

    // Vérifier s'il y a des enseignants
    $teachers = Teacher::all();
    echo 'Total teachers: ' . $teachers->count() . PHP_EOL;

    // Vérifier les utilisateurs enseignants
    $teacherUsers = User::where('account_type', 'teacher')->get();
    echo 'Total teacher users: ' . $teacherUsers->count() . PHP_EOL;

    foreach ($teacherUsers as $user) {
        $teacher = $user->teacher;
        if ($teacher) {
            $sessionCount = QuizSession::where('teacher_id', $teacher->id)->count();
            echo 'User ' . $user->email . ' (Teacher ID: ' . $teacher->id . ') has ' . $sessionCount . ' sessions' . PHP_EOL;
        } else {
            echo 'User ' . $user->email . ' has no teacher profile' . PHP_EOL;
        }
    }

    // Afficher les 5 dernières sessions
    echo PHP_EOL . 'Last 5 sessions:' . PHP_EOL;
    $lastSessions = QuizSession::with('teacher.user')->latest()->limit(5)->get();
    foreach ($lastSessions as $session) {
        echo '- Session ID: ' . $session->id . ', Teacher: ' . ($session->teacher ? $session->teacher->user->email : 'NULL') . ', Status: ' . $session->status . PHP_EOL;
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}