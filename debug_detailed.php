<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$data = json_decode(file_get_contents('test_data.json'), true);
$question1 = \App\Models\Question::find($data['question1_id']);
echo 'isMultipleChoice: ' . ($question1->isMultipleChoice() ? 'true' : 'false') . "\n";
echo 'options exists: ' . (!empty($question1->options) ? 'true' : 'false') . "\n";
$options = is_string($question1->options) ? json_decode($question1->options, true) : $question1->options;
echo 'options count: ' . count($options) . "\n";
foreach ($options as $i => $opt) {
    echo 'Option ' . $i . ': text=' . $opt['text'] . ', is_correct=' . var_export($opt['is_correct'], true) . ', type=' . gettype($opt['is_correct']) . "\n";
}
$filtered = collect($options)->where('is_correct', '!=', false)->where('is_correct', '!==', null);
echo 'Filtered count: ' . $filtered->count() . "\n";
echo 'First filtered: ' . print_r($filtered->first(), true) . "\n";