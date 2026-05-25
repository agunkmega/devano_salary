<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Console\Command;

class RecalculateAttendance extends Command
{
    protected $signature = 'attendance:recalculate
        {--from= : Start date (Y-m-d)}
        {--to= : End date (Y-m-d)}
        {--employee= : Employee ID}
        {--dry-run : Preview changes without saving}';

    protected $description = 'Recalculate late, early leave, and overtime minutes for attendance records';

    public function handle(AttendanceService $service): int
    {
        $query = Attendance::with('employee.shift');

        if ($from = $this->option('from')) {
            $query->whereDate('attendance_date', '>=', $from);
        }
        if ($to = $this->option('to')) {
            $query->whereDate('attendance_date', '<=', $to);
        }
        if ($employeeId = $this->option('employee')) {
            $query->where('employee_id', $employeeId);
        }

        $total = $query->count();
        if ($total === 0) {
            $this->warn('No attendance records found.');
            return 0;
        }

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->info("[DRY RUN] Would recalculate {$total} records.");
        } else {
            $this->info("Recalculating {$total} records...");
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $query->chunk(100, function ($attendances) use ($service, $dryRun, &$updated, $bar) {
            foreach ($attendances as $att) {
                $oldLate = $att->late_minutes;
                $service->recalculateAttendance($att);

                if ($dryRun) {
                    if ($oldLate !== $att->late_minutes) {
                        $this->line("  [{$att->id}] {$att->attendance_date} emp={$att->employee_id}: late {$oldLate}->{$att->late_minutes}");
                    }
                } else {
                    if ($oldLate !== $att->late_minutes) {
                        $updated++;
                    }
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info("Dry run complete.");
        } else {
            $this->info("Done. {$updated} records updated out of {$total}.");
        }

        return 0;
    }
}
