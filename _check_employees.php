<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$emps = App\Models\Employee::take(10)->get()->map(fn($e) => [
    'id' => $e->id,
    'nik' => $e->nik,
    'name' => $e->full_name
]);
echo json_encode($emps, JSON_PRETTY_PRINT) . "\n";
