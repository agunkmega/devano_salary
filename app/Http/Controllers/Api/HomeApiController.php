<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Notification;
use App\Models\Employee;
use Illuminate\Http\Request;

class HomeApiController extends Controller
{
    public function getAnnouncements()
    {
        $announcements = Announcement::orderBy('is_important', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $data = $announcements->map(function ($a) {
            return [
                'id' => (string) $a->id,
                'title' => $a->title,
                'snippet' => $a->snippet,
                'content' => $a->content,
                'category' => $a->category,
                'date' => $a->publish_date ? $a->publish_date->toIso8601String() : $a->created_at?->toIso8601String(),
                'is_important' => (bool) $a->is_important,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function getCelebrations()
    {
        $today = now();
        $employees = Employee::with('department')
            ->where('is_active', true)
            ->get();

        $celebrations = [];

        foreach ($employees as $e) {
            $isBirthday = $e->birth_date &&
                \Carbon\Carbon::parse($e->birth_date)->month === $today->month &&
                \Carbon\Carbon::parse($e->birth_date)->day === $today->day;

            $isAnniversary = $e->join_date &&
                \Carbon\Carbon::parse($e->join_date)->month === $today->month &&
                \Carbon\Carbon::parse($e->join_date)->day === $today->day;

            if ($isBirthday) {
                $celebrations[] = [
                    'id' => (string) $e->id,
                    'name' => $e->full_name,
                    'department' => $e->department?->name ?? 'Devano Team',
                    'type' => 'birthday',
                    'milestone_years' => 0,
                    'date' => $today->toIso8601String(),
                ];
            } elseif ($isAnniversary) {
                $years = $today->year - \Carbon\Carbon::parse($e->join_date)->year;
                if ($years > 0) {
                    $celebrations[] = [
                        'id' => (string) $e->id,
                        'name' => $e->full_name,
                        'department' => $e->department?->name ?? 'Devano Team',
                        'type' => 'anniversary',
                        'milestone_years' => $years,
                        'date' => $today->toIso8601String(),
                    ];
                }
            }
        }

        return response()->json(['data' => $celebrations]);
    }

    public function sendWish(Request $request)
    {
        $validated = $request->validate([
            'target_id' => 'required',
            'message' => 'required|string',
        ]);

        $targetEmployee = Employee::find($validated['target_id']);
        if (!$targetEmployee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $senderUser = $request->user();
        $senderEmployee = Employee::where('user_id', $senderUser->id)->first();
        $senderName = $senderEmployee ? $senderEmployee->full_name : $senderUser->name;

        if ($targetEmployee->user_id) {
            Notification::create([
                'user_id' => $targetEmployee->user_id,
                'title' => 'Ucapan Selamat dari ' . $senderName,
                'message' => $validated['message'],
                'type' => 'celebration',
                'icon' => 'cake',
                'is_read' => false,
            ]);
        }

        return response()->json([
            'message' => 'Ucapan selamat berhasil dikirim!',
        ]);
    }
}
