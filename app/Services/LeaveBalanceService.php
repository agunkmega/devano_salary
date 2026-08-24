<?php

namespace App\Services;

use App\Models\CompensatoryDay;
use App\Models\Leave;
use App\Models\LeaveType;
use Carbon\Carbon;

class LeaveBalanceService
{
    public static function typeConfig(): array
    {
        $ct = LeaveType::whereIn('code', ['CT', 'CUTI'])->first(['id', 'max_days_per_year']);
        $dp = LeaveType::where('code', 'DP')->first(['id']);

        return [$ct?->id, $dp?->id, $ct?->max_days_per_year ?? 12];
    }

    public static function leaveYear(): array
    {
        $now = now();

        if ($now->month === 12 && $now->day >= 26) {
            return [
                Carbon::create($now->year, 12, 26)->startOfDay(),
                Carbon::create($now->year + 1, 12, 25)->endOfDay(),
                $now->year . '/' . ($now->year + 1),
            ];
        }

        return [
            Carbon::create($now->year - 1, 12, 26)->startOfDay(),
            Carbon::create($now->year, 12, 25)->endOfDay(),
            ($now->year - 1) . '/' . $now->year,
        ];
    }

    public static function forEmployee($emp, $leaveYearStart, $leaveYearEnd, $ctId, $dpId, $ctQuota): array
    {
        $tenureDays = $emp->join_date ? $emp->join_date->diffInDays(now()) : 0;
        $eligible = $emp->cuti_eligible && $tenureDays >= 365;

        $effectiveCtQuota = 0;
        if ($eligible) {
            $anniversary = $emp->join_date->copy()->addYear();
            if ($anniversary->lte($leaveYearStart)) {
                $effectiveCtQuota = $ctQuota;
            } elseif ($anniversary->gt($leaveYearEnd)) {
                $effectiveCtQuota = 0;
            } else {
                $effectiveCtQuota = min($ctQuota, ($leaveYearEnd->year - $anniversary->year) * 12 + ($leaveYearEnd->month - $anniversary->month) + 1);
            }
        }

        $usedCt = $ctId ? Leave::where('employee_id', $emp->id)
            ->where('leave_type_id', $ctId)
            ->where('status', 'approved')
            ->whereBetween('start_date', [$leaveYearStart, $leaveYearEnd])
            ->sum('total_days') : 0;

        $usedDp = $dpId ? Leave::where('employee_id', $emp->id)
            ->where('leave_type_id', $dpId)
            ->where('status', 'approved')
            ->sum('total_days') : 0;

        $grantedDp = (int) CompensatoryDay::where('employee_id', $emp->id)->sum('days');

        return [
            'employee_id' => $emp->id,
            'nama' => $emp->full_name ?? '-',
            'jabatan' => $emp->position->name ?? $emp->department->name ?? '-',
            'cuti_eligible' => $eligible,
            'ct_quota' => $effectiveCtQuota,
            'ct_used' => (int) $usedCt,
            'ct_remaining' => max(0, $effectiveCtQuota - $usedCt),
            'dp_quota' => $grantedDp,
            'dp_used' => (int) $usedDp,
            'dp_remaining' => max(0, $grantedDp - $usedDp),
        ];
    }
}
