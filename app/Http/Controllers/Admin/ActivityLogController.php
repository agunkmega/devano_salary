<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')
            ->when(request('user_id'), function ($q, $userId) {
                $q->where('user_id', $userId);
            })
            ->when(request('action'), function ($q, $action) {
                $q->where('action', $action);
            })
            ->when(request('log_type'), function ($q, $type) {
                $q->where('log_type', $type);
            })
            ->when(request('date_from'), function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when(request('date_to'), function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            })
            ->when(request('search'), function ($q, $search) {
                $q->where('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20);

        $users = User::where('is_active', true)->get();
        $actions = ActivityLog::select('action')->distinct()->pluck('action');
        $logTypes = ActivityLog::select('log_type')->distinct()->pluck('log_type');

        return view('activity-logs.index', compact('logs', 'users', 'actions', 'logTypes'));
    }
}
