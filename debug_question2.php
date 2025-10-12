<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$data = json_decode(file_get_contents('test_data.json'), true);
$question1 = \App\Models\Question::find($data['question1_id']);
echo 'Question ID: ' . $question1->id . "\n";
echo 'Type: ' . $question1->type . "\n";
echo 'Options: ' . $question1->options . "\n";
$options = json_decode($question1->options, true);
echo 'Options decoded: ' . print_r($options, true) . "\n";
echo 'Correct option: ' . print_r($question1->getCorrectOptionAttribute(), true) . "\n";
echo 'Check answer Paris: ' . ($question1->checkAnswer('Paris') ? 'true' : 'false') . "\n";