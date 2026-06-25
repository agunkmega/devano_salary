<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\StationController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\LeaveController;
use App\Http\Controllers\Admin\CashAdvanceController;
use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\NationalHolidayController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FingerSpotController;
use App\Http\Controllers\Portal\AuthController as PortalAuthController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Portal\LeaveController as PortalLeaveController;
use App\Http\Controllers\Portal\LeaveApprovalController as PortalLeaveApprovalController;
use App\Http\Controllers\Portal\CashAdvanceController as PortalCashAdvanceController;

Route::redirect('/', '/admin/dashboard');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:super_admin,hr,manager,staff'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('employees/import', [EmployeeController::class, 'importExcel'])->name('employees.import');
    Route::get('employees/export-excel', [EmployeeController::class, 'exportExcel'])->name('employees.export-excel');
    Route::get('employees/export-pdf', [EmployeeController::class, 'exportPdf'])->name('employees.export-pdf');
    Route::resource('employees', EmployeeController::class);
    Route::resource('users', UserController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('positions', PositionController::class);
    Route::resource('stations', StationController::class);
    Route::resource('shifts', ShiftController::class);

    Route::resource('attendances', AttendanceController::class)->except(['destroy']);
    Route::post('attendances/import-txt', [AttendanceController::class, 'importTxt'])->name('attendances.import-txt');
    Route::post('attendances/import-csv', [AttendanceController::class, 'importCsv'])->name('attendances.import-csv');
    Route::post('attendances/import-checkpoint', [AttendanceController::class, 'importCheckpoint'])->name('attendances.import-checkpoint');
    Route::get('attendances/history', [AttendanceController::class, 'history'])->name('attendances.history');

    Route::resource('leave-types', LeaveTypeController::class)->except(['show']);
    Route::resource('leaves', LeaveController::class)->parameters(['leaves' => 'leave']);
    Route::patch('leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
    Route::patch('leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
    Route::patch('leaves/{leave}/cancel', [LeaveController::class, 'cancel'])->name('leaves.cancel');
    Route::get('my-leaves', [LeaveController::class, 'myLeaves'])->name('leaves.my-leaves');

    Route::resource('cash-advances', CashAdvanceController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('cash-advances/{cashAdvance}/approve', [CashAdvanceController::class, 'approve'])->name('cash-advances.approve');
    Route::patch('cash-advances/{cashAdvance}/reject', [CashAdvanceController::class, 'reject'])->name('cash-advances.reject');
    Route::post('cash-advances/{cashAdvance}/pay', [CashAdvanceController::class, 'pay'])->name('cash-advances.pay');

    Route::resource('payrolls', PayrollController::class);
    Route::post('payrolls/generate-all', [PayrollController::class, 'generateAll'])->name('payrolls.generate-all');
    Route::get('payrolls/generation-progress', [PayrollController::class, 'generationProgress'])->name('payrolls.generation-progress');
    Route::post('payrolls/generate/{employee}', [PayrollController::class, 'generate'])->name('payrolls.generate');
    Route::patch('payrolls/{payroll}/approve', [PayrollController::class, 'approve'])->name('payrolls.approve');
    Route::patch('payrolls/{payroll}/regenerate', [PayrollController::class, 'regenerate'])->name('payrolls.regenerate');
    Route::patch('payrolls/{payroll}/pay', [PayrollController::class, 'pay'])->name('payrolls.pay');
    Route::get('payrolls/{payroll}/slip-pdf', [PayrollController::class, 'slipPdf'])->name('payrolls.slip-pdf');
    Route::post('payrolls/{payroll}/send-whatsapp', [PayrollController::class, 'sendWhatsApp'])->name('payrolls.send-whatsapp');
    Route::post('payrolls/{payroll}/send-email', [PayrollController::class, 'sendEmail'])->name('payrolls.send-email');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('attendance', [ReportController::class, 'attendance'])->name('attendance');
        Route::get('attendance-excel', [ReportController::class, 'attendanceExcel'])->name('attendance-excel');
        Route::get('attendance-pdf', [ReportController::class, 'attendancePdf'])->name('attendance-pdf');
        Route::get('lateness', [ReportController::class, 'lateness'])->name('lateness');
        Route::get('lateness-excel', [ReportController::class, 'latenessExcel'])->name('lateness-excel');
        Route::get('overtime', [ReportController::class, 'overtime'])->name('overtime');
        Route::get('leave', [ReportController::class, 'leave'])->name('leave');
        Route::get('attendance-print', [ReportController::class, 'attendancePrint'])->name('attendance-print');
        Route::get('payroll', [ReportController::class, 'payroll'])->name('payroll');
        Route::get('payroll-print', [ReportController::class, 'payrollPrint'])->name('payroll-print');
        Route::get('payroll-print-detail', [ReportController::class, 'payrollPrintDetail'])->name('payroll-print-detail');
        Route::get('payroll-excel-detail', [ReportController::class, 'payrollExcelDetail'])->name('payroll-excel-detail');
        Route::get('leave-balance', [ReportController::class, 'leaveBalance'])->name('leave-balance');
        Route::get('bpjs', [ReportController::class, 'bpjs'])->name('bpjs');
        Route::get('bpjs-print', [ReportController::class, 'bpjsPrint'])->name('bpjs-print');
        Route::get('bpjs-pdf', [ReportController::class, 'bpjsPdf'])->name('bpjs-pdf');
    });

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/logo', [SettingController::class, 'uploadLogo'])->name('settings.logo.upload');
    Route::delete('settings/logo', [SettingController::class, 'deleteLogo'])->name('settings.logo.delete');
    Route::post('settings/backup', [SettingController::class, 'backup'])->name('settings.backup');
    Route::get('settings/backup/{filename}/download', [SettingController::class, 'downloadBackup'])->name('settings.backup.download');
    Route::delete('settings/backup/{filename}', [SettingController::class, 'deleteBackup'])->name('settings.backup.delete');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::patch('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::resource('national-holidays', NationalHolidayController::class);

    Route::get('fingerspot', [FingerSpotController::class, 'index'])->name('fingerspot.index');
    Route::get('fingerspot/machines', [FingerSpotController::class, 'machines'])->name('fingerspot.machines');
    Route::post('fingerspot/fetch', [FingerSpotController::class, 'fetch'])->name('fingerspot.fetch');
    Route::post('fingerspot/import', [FingerSpotController::class, 'import'])->name('fingerspot.import');
    Route::get('fingerspot/export', [FingerSpotController::class, 'exportExcel'])->name('fingerspot.export');
});

Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('login', [PortalAuthController::class, 'login'])->name('login');
    Route::post('login', [PortalAuthController::class, 'authenticate'])->name('login');
    Route::post('logout', [PortalAuthController::class, 'logout'])->name('logout');

    Route::middleware('portal')->group(function () {
        Route::get('dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');
        Route::get('leave/create', [PortalLeaveController::class, 'create'])->name('leave.create');
        Route::post('leave', [PortalLeaveController::class, 'store'])->name('leave.store');
        Route::get('leave-approvals', [PortalLeaveApprovalController::class, 'index'])->name('leave-approval.index');
        Route::patch('leave-approvals/{leave}/approve', [PortalLeaveApprovalController::class, 'approve'])->name('leave-approval.approve');
        Route::patch('leave-approvals/{leave}/reject', [PortalLeaveApprovalController::class, 'reject'])->name('leave-approval.reject');
        Route::get('cash-advance/create', [PortalCashAdvanceController::class, 'create'])->name('cash-advance.create');
        Route::post('cash-advance', [PortalCashAdvanceController::class, 'store'])->name('cash-advance.store');
        Route::post('photo', [PortalDashboardController::class, 'updatePhoto'])->name('photo.update');
        Route::get('password', [PortalDashboardController::class, 'changePassword'])->name('password');
        Route::post('password', [PortalDashboardController::class, 'updatePassword'])->name('password.update');
    });
});

require __DIR__.'/auth.php';
