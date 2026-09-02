<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalSchedule;
use Illuminate\Http\Request;

class PersonalScheduleApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $schedules = PersonalSchedule::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();

        $data = $schedules->map(function ($s) {
            return [
                'id' => (string) ($s->client_id ?: $s->id),
                'server_id' => $s->id,
                'title' => $s->title,
                'subtitle' => $s->subtitle ?? '',
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'category' => $s->category,
                'date' => $s->date?->format('Y-m-d'),
                'is_completed' => (bool) $s->is_completed,
                'is_recurring' => (bool) $s->is_recurring,
                'repeat_type' => $s->repeat_type ?? 'none',
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|string',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'start_time' => 'required|string|max:20',
            'end_time' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:50',
            'date' => 'required',
            'is_completed' => 'nullable|boolean',
            'is_recurring' => 'nullable|boolean',
            'repeat_type' => 'nullable|string|max:50',
        ]);

        $user = $request->user();

        // Check if existing by client_id
        if (!empty($validated['client_id'])) {
            $existing = PersonalSchedule::where('user_id', $user->id)
                ->where('client_id', $validated['client_id'])
                ->first();

            if ($existing) {
                $existing->update([
                    'title' => $validated['title'],
                    'subtitle' => $validated['subtitle'] ?? null,
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'] ?? null,
                    'category' => $validated['category'] ?? 'task',
                    'date' => \Carbon\Carbon::parse($validated['date'])->toDateString(),
                    'is_completed' => $validated['is_completed'] ?? false,
                    'is_recurring' => $validated['is_recurring'] ?? false,
                    'repeat_type' => $validated['repeat_type'] ?? 'none',
                ]);

                return response()->json([
                    'message' => 'Jadwal berhasil diperbarui.',
                    'data' => [
                        'id' => (string) $existing->client_id,
                        'server_id' => $existing->id,
                        'title' => $existing->title,
                        'subtitle' => $existing->subtitle ?? '',
                        'start_time' => $existing->start_time,
                        'end_time' => $existing->end_time,
                        'category' => $existing->category,
                        'date' => $existing->date?->format('Y-m-d'),
                        'is_completed' => (bool) $existing->is_completed,
                        'is_recurring' => (bool) $existing->is_recurring,
                        'repeat_type' => $existing->repeat_type ?? 'none',
                    ],
                ]);
            }
        }

        $schedule = PersonalSchedule::create([
            'user_id' => $user->id,
            'client_id' => $validated['client_id'] ?? null,
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
            'category' => $validated['category'] ?? 'task',
            'date' => \Carbon\Carbon::parse($validated['date'])->toDateString(),
            'is_completed' => $validated['is_completed'] ?? false,
            'is_recurring' => $validated['is_recurring'] ?? false,
            'repeat_type' => $validated['repeat_type'] ?? 'none',
        ]);

        return response()->json([
            'message' => 'Jadwal berhasil disimpan.',
            'data' => [
                'id' => (string) ($schedule->client_id ?: $schedule->id),
                'server_id' => $schedule->id,
                'title' => $schedule->title,
                'subtitle' => $schedule->subtitle ?? '',
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'category' => $schedule->category,
                'date' => $schedule->date?->format('Y-m-d'),
                'is_completed' => (bool) $schedule->is_completed,
                'is_recurring' => (bool) $schedule->is_recurring,
                'repeat_type' => $schedule->repeat_type ?? 'none',
            ],
        ], 201);
    }

    public function update($id, Request $request)
    {
        $user = $request->user();
        $schedule = PersonalSchedule::where('user_id', $user->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('client_id', $id);
            })
            ->first();

        if (!$schedule) {
            return response()->json(['message' => 'Jadwal tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'start_time' => 'sometimes|string|max:20',
            'end_time' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:50',
            'date' => 'sometimes',
            'is_completed' => 'sometimes|boolean',
            'is_recurring' => 'sometimes|boolean',
            'repeat_type' => 'nullable|string|max:50',
        ]);

        if (isset($validated['date'])) {
            $validated['date'] = \Carbon\Carbon::parse($validated['date'])->toDateString();
        }

        $schedule->update($validated);

        return response()->json([
            'message' => 'Jadwal berhasil diperbarui.',
            'data' => [
                'id' => (string) ($schedule->client_id ?: $schedule->id),
                'server_id' => $schedule->id,
                'title' => $schedule->title,
                'subtitle' => $schedule->subtitle ?? '',
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'category' => $schedule->category,
                'date' => $schedule->date?->format('Y-m-d'),
                'is_completed' => (bool) $schedule->is_completed,
                'is_recurring' => (bool) $schedule->is_recurring,
                'repeat_type' => $schedule->repeat_type ?? 'none',
            ],
        ]);
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $schedule = PersonalSchedule::where('user_id', $user->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('client_id', $id);
            })
            ->first();

        if ($schedule) {
            $schedule->delete();
        }

        return response()->json([
            'message' => 'Jadwal berhasil dihapus.',
        ]);
    }

    public function sync(Request $request)
    {
        $validated = $request->validate([
            'schedules' => 'present|array',
        ]);

        $user = $request->user();
        $incoming = $validated['schedules'];

        foreach ($incoming as $item) {
            if (empty($item['title'])) continue;

            $clientId = $item['id'] ?? null;
            $date = !empty($item['date']) ? \Carbon\Carbon::parse($item['date'])->toDateString() : now()->toDateString();

            PersonalSchedule::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'client_id' => $clientId,
                ],
                [
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'] ?? null,
                    'start_time' => $item['start_time'] ?? '08:00',
                    'end_time' => $item['end_time'] ?? null,
                    'category' => $item['category'] ?? 'task',
                    'date' => $date,
                    'is_completed' => $item['is_completed'] ?? false,
                    'is_recurring' => $item['is_recurring'] ?? false,
                    'repeat_type' => $item['repeat_type'] ?? 'none',
                ]
            );
        }

        // Return all latest schedules from server
        return $this->index($request);
    }
}
