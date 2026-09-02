<?php

namespace App\Services;

use App\Events\NewNotificationEvent;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\CashAdvance;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to a specific user (DB + Reverb WebSocket broadcast)
     */
    public static function sendToUser(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        ?string $url = null,
        ?string $icon = null
    ): ?Notification {
        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'icon' => $icon ?? self::getDefaultIcon($type),
                'url' => $url,
                'is_read' => false,
            ]);

            // Broadcast via Laravel Reverb
            try {
                broadcast(new NewNotificationEvent($notification))->toOthers();
            } catch (\Throwable $e) {
                Log::warning('[NotificationService] Broadcast error: ' . $e->getMessage());
            }

            return $notification;
        } catch (\Throwable $e) {
            Log::error('[NotificationService] Failed to send notification to user ' . $userId . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send notification to all HR & Admin users
     */
    public static function sendToHr(
        string $title,
        string $message,
        string $type = 'info',
        ?string $url = null
    ): array {
        $hrUsers = User::whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_HR])
            ->where('is_active', true)
            ->get();

        $notifications = [];
        foreach ($hrUsers as $user) {
            $notifications[] = self::sendToUser($user->id, $title, $message, $type, $url);
        }
        return $notifications;
    }

    /**
     * Send notification to the Department Head of an employee
     */
    public static function sendToDepartmentHead(
        Employee $employee,
        string $title,
        string $message,
        string $type = 'info',
        ?string $url = null
    ): ?Notification {
        if (!$employee->department_id) {
            return null;
        }

        $department = $employee->department;
        if (!$department || !$department->department_head_id) {
            return null;
        }

        $headEmployee = $department->departmentHead;
        if (!$headEmployee || !$headEmployee->user_id || $headEmployee->id === $employee->id) {
            return null;
        }

        return self::sendToUser($headEmployee->user_id, $title, $message, $type, $url);
    }

    /**
     * Broadcast notification to all active employees
     */
    public static function broadcastToAll(
        string $title,
        string $message,
        string $type = 'announcement',
        ?string $url = null
    ): int {
        $users = User::where('is_active', true)->get();
        $count = 0;
        foreach ($users as $user) {
            if (self::sendToUser($user->id, $title, $message, $type, $url)) {
                $count++;
            }
        }
        return $count;
    }

    // ── Helper Process Specific Triggers ──

    /**
     * Trigger when employee submits leave / DP
     */
    public static function notifyLeaveSubmission(Leave $leave): void
    {
        $employee = $leave->employee;
        if (!$employee) return;

        $typeName = $leave->leaveType?->name ?? 'Cuti';
        $start = $leave->start_date?->format('d/m/Y') ?? '';
        $end = $leave->end_date?->format('d/m/Y') ?? '';
        $dateRange = $start === $end ? $start : "$start s/d $end";
        $days = $leave->total_days ?? 1;

        $title = "Pengajuan $typeName Baru";
        $message = "{$employee->full_name} mengajukan $typeName ($days hari: $dateRange). Alasan: {$leave->reason}";

        // 1. Notify Department Head (if exists)
        self::sendToDepartmentHead($employee, $title, $message, 'leave', '/portal/leave-approval');

        // 2. Notify HR / Admin
        self::sendToHr($title, $message, 'leave', '/admin/leaves');
    }

    /**
     * Trigger when leave status changes (Approved / Rejected)
     */
    public static function notifyLeaveStatus(Leave $leave, ?string $reason = null): void
    {
        $employee = $leave->employee;
        if (!$employee || !$employee->user_id) return;

        $typeName = $leave->leaveType?->name ?? 'Cuti';
        $dateRange = $leave->start_date?->format('d/m/Y') ?? '';

        if ($leave->status === 'approved') {
            $title = "Pengajuan $typeName Disetujui";
            $message = "Pengajuan $typeName Anda untuk tanggal $dateRange telah disetujui.";
            $type = 'success';
        } elseif ($leave->status === 'rejected') {
            $title = "Pengajuan $typeName Ditolak";
            $message = "Pengajuan $typeName Anda untuk tanggal $dateRange ditolak." . ($reason ? " Alasan: $reason" : "");
            $type = 'danger';
        } else {
            return;
        }

        self::sendToUser($employee->user_id, $title, $message, $type, '/leaves');
    }

    /**
     * Trigger when employee submits cash advance / kasbon
     */
    public static function notifyCashAdvanceSubmission(CashAdvance $advance): void
    {
        $employee = $advance->employee;
        if (!$employee) return;

        $amount = number_format($advance->amount, 0, ',', '.');
        $title = "Pengajuan Kasbon Baru";
        $message = "{$employee->full_name} mengajukan kasbon sebesar Rp $amount. Alasan: {$advance->reason}";

        self::sendToHr($title, $message, 'cash_advance', '/admin/cash-advances');
    }

    /**
     * Trigger when cash advance status changes
     */
    public static function notifyCashAdvanceStatus(CashAdvance $advance, ?string $reason = null): void
    {
        $employee = $advance->employee;
        if (!$employee || !$employee->user_id) return;

        $amount = number_format($advance->amount, 0, ',', '.');

        if ($advance->status === 'approved') {
            $title = "Kasbon Disetujui";
            $message = "Pengajuan kasbon Anda sebesar Rp $amount telah disetujui.";
            $type = 'success';
        } elseif ($advance->status === 'rejected') {
            $title = "Kasbon Ditolak";
            $message = "Pengajuan kasbon Anda sebesar Rp $amount ditolak." . ($reason ? " Alasan: $reason" : "");
            $type = 'danger';
        } elseif ($advance->status === 'paid') {
            $title = "Kasbon Telah Dicairkan";
            $message = "Dana kasbon Anda sebesar Rp $amount telah dicairkan.";
            $type = 'success';
        } else {
            return;
        }

        self::sendToUser($employee->user_id, $title, $message, $type, '/wallet');
    }

    private static function getDefaultIcon(string $type): string
    {
        return match ($type) {
            'leave' => 'calendar-check',
            'cash_advance', 'wallet' => 'wallet',
            'payroll' => 'receipt',
            'attendance' => 'clock',
            'chat' => 'message-square',
            'success' => 'check-circle',
            'danger', 'error' => 'alert-circle',
            'warning' => 'alert-triangle',
            'announcement' => 'volume-2',
            default => 'bell',
        };
    }
}
