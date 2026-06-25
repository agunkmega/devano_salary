<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    use LogsActivity;
    private function getBackupDir(): string
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        $backupDir = $this->getBackupDir();
        $backups = [];
        $files = glob($backupDir . '/*.sql');
        if ($files) {
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => filesize($file),
                    'date' => filemtime($file),
                ];
            }
        }
        rsort($backups);

        return view('settings.index', compact('settings', 'backups'));
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

        Cache::forget('app_name');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $path = $request->file('logo')->store('logos', 'public');

        Setting::updateOrCreate(
            ['key' => 'app_logo'],
            ['value' => $path, 'group' => 'company']
        );

        Cache::forget('app_logo');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Logo berhasil diupload.');
    }

    public function deleteLogo()
    {
        $logo = Setting::where('key', 'app_logo')->first();
        if ($logo && $logo->value) {
            Storage::disk('public')->delete($logo->value);
            $logo->delete();
        }

        Cache::forget('app_logo');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Logo berhasil dihapus.');
    }

    private function inferGroup(string $key): string
    {
        if (str_starts_with($key, 'fingerspot_')) {
            return 'fingerspot';
        }
        if (str_starts_with($key, 'mail_')) {
            return 'email';
        }
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
            $backupDir = $this->getBackupDir();
            $filename = 'backup-' . date('Y-m-d-His') . '.sql';
            $filepath = $backupDir . '/' . $filename;

            $db = config('database.connections.mysql');
            $host = $db['host'];
            $port = $db['port'];
            $database = $db['database'];
            $username = $db['username'];
            $password = $db['password'];

            $cmd = sprintf(
                '"%s" --host=%s --port=%s --user=%s --password=%s --routines --single-transaction %s > "%s" 2>&1',
                'D:\FlyEnv-Data\env\mysql\bin\mysqldump.exe',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                $filepath
            );

            $output = null;
            $returnCode = null;
            exec($cmd, $output, $returnCode);

            if ($returnCode !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
                throw new \Exception('mysqldump gagal: ' . implode("\n", $output));
            }

            $this->logActivity('system', 'Backup', 'Backup database: ' . $filename);

            return redirect()->route('admin.settings.index', ['tab' => 'database'])
                ->with('success', 'Backup database berhasil: ' . $filename);
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index', ['tab' => 'database'])
                ->with('error', 'Backup gagal: ' . $e->getMessage());
        }
    }

    public function downloadBackup($filename)
    {
        $backupDir = $this->getBackupDir();
        $filepath = $backupDir . '/' . basename($filename);

        if (!file_exists($filepath)) {
            return redirect()->route('admin.settings.index', ['tab' => 'database'])
                ->with('error', 'File backup tidak ditemukan.');
        }

        return response()->download($filepath);
    }

    public function deleteBackup($filename)
    {
        $backupDir = $this->getBackupDir();
        $filepath = $backupDir . '/' . basename($filename);

        if (file_exists($filepath)) {
            unlink($filepath);
        }

        return redirect()->route('admin.settings.index', ['tab' => 'database'])
            ->with('success', 'Backup berhasil dihapus.');
    }
}
