<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$question = \App\Models\Question::find(39);
echo 'Question ID: ' . $question->id . "\n";
echo 'Type: ' . $question->type . "\n";
echo 'Options: ' . $question->options . "\n";
echo 'Options decoded: ' . print_r(json_decode($question->options, true), true) . "\n";
echo 'Correct option: ' . print_r($question->getCorrectOptionAttribute(), true) . "\n";
echo 'Check answer Paris: ' . ($question->checkAnswer('Paris') ? 'true' : 'false') . "\n";
echo 'Check answer paris: ' . ($question->checkAnswer('paris') ? 'true' : 'false') . "\n";