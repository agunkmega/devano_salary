<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Http\Request;

class ChatApiController extends Controller
{
    public function getConfig(Request $request)
    {
        $settings = Setting::where('group', 'mobile_api')->get()->keyBy('key');
        $scope = $settings->get('mobile_chat_permission_scope')?->value ?? 'all';
        $customTitle = $settings->get('mobile_chat_title')?->value;
        $isEnabled = ($scope !== 'disabled') && ((string) ($settings->get('mobile_chat_enabled')?->value ?? '1') === '1');

        if (!empty($customTitle)) {
            $pageTitle = $customTitle;
        } else {
            switch ($scope) {
                case 'disabled':
                    $pageTitle = 'Chat Dinonaktifkan';
                    break;
                case 'hr_admin_only':
                    $pageTitle = 'Pusat Bantuan & HRD';
                    break;
                case 'hr_and_it_only':
                    $pageTitle = 'Helpdesk HRD & IT';
                    break;
                case 'same_department':
                    $pageTitle = 'Obrolan Tim Divisi';
                    break;
                case 'department_and_hr':
                    $pageTitle = 'Pesan Tim & HRD';
                    break;
                case 'department_and_hr_it':
                    $pageTitle = 'Pesan Tim, HRD & IT';
                    break;
                case 'all':
                default:
                    $pageTitle = 'Pesan & Diskusi Tim';
                    break;
            }
        }

        return response()->json([
            'data' => [
                'is_enabled' => $isEnabled,
                'chat_mode' => $settings->get('mobile_chat_mode')?->value ?? 'hybrid',
                'permission_scope' => $scope,
                'page_title' => $pageTitle,
                'allow_images' => (bool) ($settings->get('mobile_chat_allow_images')?->value ?? '1'),
                'max_length' => (int) ($settings->get('mobile_chat_max_length')?->value ?? 1000),
                'welcome_message' => $settings->get('mobile_chat_welcome_message')?->value ?? 'Halo! Silakan mulai percakapan atau diskusi pekerjaan di sini.',
            ],
        ]);
    }

    public function getRooms(Request $request)
    {
        $user = $request->user();

        // Distinct room IDs where user participated or room messages exist
        $recentMessages = ChatMessage::with('sender')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('room_id');

        $rooms = [];
        foreach ($recentMessages as $roomId => $msgs) {
            $latest = $msgs->first();
            $unreadCount = $user ? $msgs->where('sender_user_id', '!=', $user->id)->where('is_read', false)->count() : 0;

            // Try to resolve room name from employee
            $contactEmployee = Employee::find($roomId);
            $roomName = $contactEmployee ? ($contactEmployee->full_name . ' (' . ($contactEmployee->position?->name ?? 'Staff') . ')') : ('Ruang #' . $roomId);

            $rooms[] = [
                'id' => (string) $roomId,
                'name' => $roomName,
                'last_message' => $latest?->content ?? '',
                'last_message_at' => $latest?->created_at?->toIso8601String() ?? now()->toIso8601String(),
                'unread_count' => $unreadCount,
                'avatar' => $contactEmployee?->photo,
                'phone' => $contactEmployee?->phone,
                'is_online' => true,
            ];
        }

        return response()->json(['data' => $rooms]);
    }

    public function getDirectory(Request $request)
    {
        $user = $request->user();
        $currentEmployee = $user ? Employee::where('user_id', $user->id)->first() : null;
        $chatScope = Setting::where('key', 'mobile_chat_permission_scope')->value('value') ?? 'all';

        if ($chatScope === 'disabled') {
            return response()->json(['data' => []]);
        }

        // Precise HR definition (avoiding general 'Admin' matching other departments like Produksi/Casting)
        $isHrClosure = function ($q) {
            $q->where(function ($sub) {
                $sub->whereHas('department', function ($dq) {
                    $dq->where('name', 'like', '%HR%')
                       ->orWhere('name', 'like', '%Human%')
                       ->orWhere('name', 'like', '%Personalia%')
                       ->orWhere('name', 'like', '%General Manager%');
                })->orWhereHas('position', function ($pq) {
                    $pq->where('name', 'like', '%HR%')
                       ->orWhere('name', 'like', '%Personalia%')
                       ->orWhere('name', 'like', '%General Manager%');
                });
            });
        };

        // Precise IT definition (word matching for IT to avoid substring match in Secur-IT-y or Hosp-IT-ality)
        $isItClosure = function ($q) {
            $q->where(function ($sub) {
                $sub->whereHas('department', function ($dq) {
                    $dq->where('name', '=', 'IT')
                       ->orWhere('name', 'like', 'IT %')
                       ->orWhere('name', 'like', '% IT')
                       ->orWhere('name', 'like', '% IT %')
                       ->orWhere('name', 'like', '%Programmer%')
                       ->orWhere('name', 'like', '%Developer%')
                       ->orWhere('name', 'like', '%Software%')
                       ->orWhere('name', 'like', '%Teknologi%')
                       ->orWhere('name', 'like', '%Informasi%');
                })->orWhereHas('position', function ($pq) {
                    $pq->where('name', '=', 'IT')
                       ->orWhere('name', 'like', 'IT %')
                       ->orWhere('name', 'like', '% IT')
                       ->orWhere('name', 'like', '% IT %')
                       ->orWhere('name', 'like', '%Programmer%')
                       ->orWhere('name', 'like', '%Developer%')
                       ->orWhere('name', 'like', '%Engineer%')
                       ->orWhere('name', 'like', '%Teknisi IT%');
                });
            });
        };

        $query = Employee::with(['department', 'position'])
            ->where('is_active', true);

        if ($user && $user->id) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', '!=', $user->id);
            });
        }

        // Apply permission scope filter
        if ($chatScope === 'same_department' && $currentEmployee && $currentEmployee->department_id) {
            $query->where('department_id', $currentEmployee->department_id);
        } elseif ($chatScope === 'hr_admin_only') {
            $query->where($isHrClosure);
        } elseif ($chatScope === 'hr_and_it_only') {
            $query->where(function ($sq) use ($isHrClosure, $isItClosure) {
                $sq->where($isHrClosure)->orWhere($isItClosure);
            });
        } elseif ($chatScope === 'department_and_hr' && $currentEmployee && $currentEmployee->department_id) {
            $query->where(function ($sq) use ($currentEmployee, $isHrClosure) {
                $sq->where('department_id', $currentEmployee->department_id)
                   ->orWhere($isHrClosure);
            });
        } elseif ($chatScope === 'department_and_hr_it' && $currentEmployee && $currentEmployee->department_id) {
            $query->where(function ($sq) use ($currentEmployee, $isHrClosure, $isItClosure) {
                $sq->where('department_id', $currentEmployee->department_id)
                   ->orWhere($isHrClosure)
                   ->orWhere($isItClosure);
            });
        }

        $employees = $query->get();

        $directory = $employees->map(function ($e) {
            return [
                'id' => (string) $e->id,
                'name' => $e->full_name,
                'department' => $e->department?->name ?? 'General',
                'role' => $e->position?->name ?? 'Karyawan',
                'avatar' => $e->photo,
                'is_online' => true,
                'phone' => $e->phone,
            ];
        });

        return response()->json(['data' => $directory]);
    }

    public function getMessages($roomId, Request $request)
    {
        $messages = ChatMessage::with('sender')
            ->where('room_id', $roomId)
            ->orderBy('id', 'asc')
            ->get();

        $user = $request->user();

        if ($user) {
            // Mark incoming messages as read
            ChatMessage::where('room_id', $roomId)
                ->where('sender_user_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $data = $messages->map(function ($m) use ($user) {
            $isMe = $user ? ($m->sender_user_id === $user->id) : false;
            return [
                'id' => (string) $m->id,
                'sender_id' => (string) $m->sender_user_id,
                'sender_name' => $isMe ? 'Saya' : ($m->sender?->name ?? 'Pengirim'),
                'content' => $m->content,
                'timestamp' => $m->created_at?->toIso8601String(),
                'is_me' => $isMe,
                'image_url' => $m->image_url,
                'is_read' => (bool) $m->is_read,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function sendMessage($roomId, Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'image_url' => 'nullable|string',
        ]);

        $user = $request->user();

        $msg = ChatMessage::create([
            'room_id' => $roomId,
            'sender_user_id' => $user ? $user->id : 1,
            'content' => $validated['content'],
            'image_url' => $validated['image_url'] ?? null,
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'Pesan terkirim.',
            'data' => [
                'id' => (string) $msg->id,
                'sender_id' => (string) ($user ? $user->id : 1),
                'sender_name' => $user->name ?? 'Saya',
                'content' => $msg->content,
                'timestamp' => $msg->created_at?->toIso8601String(),
                'is_me' => true,
                'image_url' => $msg->image_url,
                'is_read' => false,
            ],
        ], 201);
    }
}