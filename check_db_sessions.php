<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sessions = DB::table('quiz_sessions')->orderBy('created_at', 'desc')->get();
echo 'Total sessions in DB: ' . $sessions->count() . PHP_EOL;
foreach($sessions as $s) {
    echo "ID: {$s->id}, Teacher: {$s->teacher_id}, Title: {$s->title}, Status: {$s->status}, Created: {$s->created_at}" . PHP_EOL;
}