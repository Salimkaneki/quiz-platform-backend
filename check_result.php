<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$data = json_decode(file_get_contents('test_data.json'), true);
echo 'Test data: ' . print_r($data, true) . "\n";
$result = \App\Models\Result::find($data['result_id']);
echo 'Result status: ' . $result->status . "\n";
echo 'Result points: ' . $result->total_points . '/' . $result->max_points . ' (' . $result->percentage . "%)\n";