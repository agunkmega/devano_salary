<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (App\Models\Shift::all() as $s) {
    echo $s->id . ' | ' . $s->name . PHP_EOL;
}
unlink(__FILE__);
