<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            $appName = Cache::remember('app_name', 3600, function () {
                return Setting::where('key', 'app_name')?->value('value');
            });
            if ($appName) {
                config(['app.name' => $appName]);
            }
        } catch (\Exception $e) {
            //
        }

        Event::listen(Login::class, function ($event) {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'log_type' => 'auth',
                'action' => 'Login',
                'description' => 'User login: ' . $event->user->name . ' (' . $event->user->email . ')',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        Event::listen(Logout::class, function ($event) {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'log_type' => 'auth',
                'action' => 'Logout',
                'description' => 'User logout: ' . $event->user->name,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}
