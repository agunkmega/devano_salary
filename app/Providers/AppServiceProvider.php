<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
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
    }
}
