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

            $mailSettings = Cache::remember('mail_settings', 3600, function () {
                return Setting::where('group', 'email')->get()->keyBy('key');
            });
            if ($mailSettings->isNotEmpty()) {
                $overrides = [];
                if ($mailSettings->has('mail_host')) $overrides['mailers.smtp.host'] = $mailSettings->get('mail_host')->value;
                if ($mailSettings->has('mail_port')) $overrides['mailers.smtp.port'] = (int) $mailSettings->get('mail_port')->value;
                if ($mailSettings->has('mail_username')) $overrides['mailers.smtp.username'] = $mailSettings->get('mail_username')->value;
                if ($mailSettings->has('mail_password')) $overrides['mailers.smtp.password'] = $mailSettings->get('mail_password')->value;
                if ($mailSettings->has('mail_encryption')) $overrides['mailers.smtp.encryption'] = $mailSettings->get('mail_encryption')->value ?: null;
                if ($mailSettings->has('mail_from_address')) $overrides['mail.from.address'] = $mailSettings->get('mail_from_address')->value;
                if ($mailSettings->has('mail_from_name')) $overrides['mail.from.name'] = $mailSettings->get('mail_from_name')->value;
                config($overrides);
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
