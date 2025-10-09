<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = App\Models\Student::where('user_id', 23)->first();
echo $student ? $student->email : 'Not found';