<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\QuizSession;
use App\Models\User;

echo "=== TOUTES LES SESSIONS ===\n";
$sessions = QuizSession::with('quiz')->latest()->get();

foreach($sessions as $s) {
    $teacher = User::find($s->teacher_id);
    echo "Session {$s->id}: Teacher: " . ($teacher ? $teacher->email : 'NULL') . ", Title: {$s->title}, Status: {$s->status}, Created: {$s->created_at}\n";
}

echo "\n=== UTILISATEURS ENSEIGNANTS ===\n";
$teachers = User::where('account_type', 'teacher')->get();
foreach($teachers as $t) {
    echo "ID: {$t->id}, Email: {$t->email}\n";
}