<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $value = str_replace(',', '.', $value);

            $existing = Setting::where('key', $key)->first();

            $group = $existing ? $existing->group : $this->inferGroup($key);

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    private function inferGroup(string $key): string
    {
        $payrollKeys = ['bpjs_', 'tax_', 'iuran_wajib', 'overtime_', 'late_penalty'];
        foreach ($payrollKeys as $prefix) {
            if (str_starts_with($key, $prefix)) {
                return 'payroll';
            }
        }
        return 'general';
    }

    public function backup()
    {
        try {
            Artisan::call('backup:run');

            return redirect()->route('admin.settings.index')
                ->with('success', 'Database backup completed successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
}
