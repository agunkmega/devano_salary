<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$depts = App\Models\Department::all();
foreach ($depts as $d) {
    echo "id: {$d->id}, name: {$d->name}, code: {$d->code}\n";
}
