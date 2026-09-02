<?php

use Illuminate\Support\Facades\Broadcast;

// ── Default User Channel ──
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum', 'web']]);

// ── 1. Presence Channel untuk Status Online / Offline Realtime ──
Broadcast::channel('presence-chat', function ($user) {
    if ($user) {
        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar ?? null,
            'role' => $user->role ?? null,
        ];
    }
    return false;
}, ['guards' => ['sanctum', 'web']]);

// ── 2. Private Chat Channel ──
Broadcast::channel('private-chat.{userId}', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
}, ['guards' => ['sanctum', 'web']]);

// ── 3. Private Notifications Channel ──
Broadcast::channel('private-notifications.{userId}', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
}, ['guards' => ['sanctum', 'web']]);
