<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;
use App\Models\Employee;

$emp = Employee::where('full_name', 'like', '%SWANDEWI%')->first();
$att = Attendance::where('employee_id', $emp->id)->whereDate('attendance_date', '2026-04-15')->first();
echo "late_minutes: " . var_export($att->late_minutes, true) . "\n";
echo "clock_in: " . ($att->clock_in ? $att->clock_in->format('Y-m-d H:i:s') : 'null') . "\n";
echo "clock_in raw: " . $att->getRawOriginal('clock_in') . "\n";
