<?php

use Illuminate\Support\Facades\Broadcast;

// ── Default User Channel ──
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum', 'web']]);

Broadcast::channel('users.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum', 'web']]);

// ── 1. Presence Channel untuk Status Online / Offline Realtime ──
$presenceCallback = function ($user) {
    if ($user) {
        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar ?? null,
            'role' => $user->role ?? null,
        ];
    }
    return false;
};

Broadcast::channel('chat', $presenceCallback, ['guards' => ['sanctum', 'web']]);
Broadcast::channel('presence-chat', $presenceCallback, ['guards' => ['sanctum', 'web']]);

// ── 2. Private Chat Channel ──
$chatCallback = function ($user, $userId) {
    return (string) $user->id === (string) $userId;
};

Broadcast::channel('chat.{userId}', $chatCallback, ['guards' => ['sanctum', 'web']]);
Broadcast::channel('private-chat.{userId}', $chatCallback, ['guards' => ['sanctum', 'web']]);

// ── 3. Private Notifications Channel ──
$notifCallback = function ($user, $userId) {
    return (string) $user->id === (string) $userId;
};

Broadcast::channel('notifications.{userId}', $notifCallback, ['guards' => ['sanctum', 'web']]);
Broadcast::channel('private-notifications.{userId}', $notifCallback, ['guards' => ['sanctum', 'web']]);
