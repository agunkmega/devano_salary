<?php
require __DIR__ . '/vendor/autoload.php';
use Carbon\CarbonPeriod;
$p = CarbonPeriod::create('2026-04-01', '2026-04-30');
$count = 0;
foreach ($p as $d) {
    $count++;
    if ($d->dayOfWeek == 0) echo $d->format('Y-m-d l') . "\n";
}
echo "Total dates: $count\n";
