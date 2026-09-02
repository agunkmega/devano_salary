<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

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

        $users = \App\Models\User::where('is_active', true)->orderBy('name')->get();
        $reverbConfig = [
            'host' => config('reverb.servers.reverb.hostname') ?: env('REVERB_HOST', '127.0.0.1'),
            'port' => config('reverb.servers.reverb.port') ?: env('REVERB_PORT', 8080),
            'scheme' => config('reverb.apps.apps.0.options.scheme') ?: env('REVERB_SCHEME', 'http'),
            'app_key' => config('reverb.apps.apps.0.key') ?: env('REVERB_APP_KEY', 'employee-key'),
        ];

        return view('settings.index', compact('settings', 'backups', 'users', 'reverbConfig'));
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
        if (str_starts_with($key, 'mobile_') || str_starts_with($key, 'auth_')) {
            return 'mobile_api';
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

            $mysqldump = $this->findMysqldump();
            if ($mysqldump && function_exists('proc_open')) {
                try {
                    $cmd = [
                        $mysqldump,
                        "--host=$host",
                        "--port=$port",
                        "--user=$username",
                        "--password=$password",
                        '--routines',
                        '--single-transaction',
                        $database,
                    ];

                    $process = new Process($cmd);
                    $process->setTimeout(120);
                    $process->run();

                    if ($process->isSuccessful()) {
                        file_put_contents($filepath, $process->getOutput());

                        $this->logActivity('system', 'Backup', 'Backup database: ' . $filename);

                        return redirect()->route('admin.settings.index', ['tab' => 'database'])
                            ->with('success', 'Backup database berhasil: ' . $filename);
                    }
                } catch (\Exception $e) {
                    // fallback to PHP backup
                }
            }

            $this->phpBackup($filepath);

            $this->logActivity('system', 'Backup', 'Backup database (PHP): ' . $filename);

            return redirect()->route('admin.settings.index', ['tab' => 'database'])
                ->with('success', 'Backup database berhasil (PHP method): ' . $filename);
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index', ['tab' => 'database'])
                ->with('error', 'Backup gagal: ' . $e->getMessage());
        }
    }

    private function phpBackup(string $filepath): void
    {
        $pdo = DB::connection()->getPdo();
        $fh = fopen($filepath, 'w');

        $write = function (string $line) use ($fh) {
            fwrite($fh, $line);
        };

        $write("-- Backup generated by HRIS\n-- Date: " . date('Y-m-d H:i:s') . "\n\n");
        $write("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
        $write("SET FOREIGN_KEY_CHECKS = 0;\n");
        $write("SET NAMES utf8mb4;\n\n");

        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $createSql = $createStmt['Create Table'];
            if (!empty($createStmt['Create Trigger'] ?? null)) {
                $createSql = $createStmt['Create Trigger'];
            }

            $write("--\n-- Table structure for `{$table}`\n--\n\n");
            $write("DROP TABLE IF EXISTS `{$table}`;\n");
            $write("{$createSql};\n\n");

            $rowCount = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            if ($rowCount == 0) continue;

            $write("--\n-- Dumping data for `{$table}`\n--\n\n");

            $columns = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(\PDO::FETCH_COLUMN);
            $colNames = implode('`, `', $columns);

            $chunkSize = 500;
            $offset = 0;

            while ($offset < $rowCount) {
                $rows = $pdo->query("SELECT * FROM `{$table}` LIMIT {$chunkSize} OFFSET {$offset}")->fetchAll(\PDO::FETCH_ASSOC);
                if (empty($rows)) break;

                $write("INSERT INTO `{$table}` (`{$colNames}`) VALUES\n");
                $values = [];
                foreach ($rows as $row) {
                    $escaped = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $escaped[] = 'NULL';
                        } else {
                            $escaped[] = $pdo->quote($value);
                        }
                    }
                    $values[] = '(' . implode(', ', $escaped) . ')';
                }
                $write(implode(",\n", $values) . ";\n\n");

                $offset += $chunkSize;
            }
        }

        $write("SET FOREIGN_KEY_CHECKS = 1;\n");

        fclose($fh);
    }

    private function findMysqldump(): ?string
    {
        $candidates = ['mysqldump', 'mariadb-dump', '/www/server/mysql/bin/mysqldump', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump'];
        foreach ($candidates as $cmd) {
            if (str_contains($cmd, '/')) {
                if (file_exists($cmd)) return $cmd;
            } elseif (function_exists('proc_open')) {
                try {
                    $test = new Process([$cmd, '--version']);
                    $test->run();
                    if ($test->isSuccessful()) return $cmd;
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        return null;
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

    public function testBroadcast(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|in:all,user',
            'user_id' => 'nullable|required_if:target,user|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string|in:info,success,warning,danger,leave,cash_advance,announcement,attendance',
        ]);

        $title = $validated['title'];
        $message = $validated['message'];
        $type = $validated['type'] ?? 'info';

        if ($validated['target'] === 'all') {
            event(new \App\Events\BroadcastTestEvent($title, $message, $type));

            return response()->json([
                'success' => true,
                'message' => "Siaran uji coba (Snackbar + Suara) berhasil dikirim ke semua klien tanpa menyimpan ke database.",
                'details' => [
                    'target' => 'all',
                    'save_to_db' => false,
                    'title' => $title,
                    'type' => $type,
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                ],
            ]);
        } else {
            $user = \App\Models\User::findOrFail($validated['user_id']);
            event(new \App\Events\BroadcastTestEvent($title, $message, $type, (int) $user->id));

            return response()->json([
                'success' => true,
                'message' => "Pesan uji coba (Snackbar + Suara) berhasil dikirim ke {$user->name} tanpa menyimpan ke database.",
                'details' => [
                    'target' => 'user',
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'save_to_db' => false,
                    'title' => $title,
                    'type' => $type,
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                ],
            ]);
        }
    }
}
