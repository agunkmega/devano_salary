<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileAppRelease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MobileReleaseApiController extends Controller
{
    /**
     * Get the latest mobile app release metadata.
     */
    public function getLatest()
    {
        $latest = MobileAppRelease::latest('id')->first();

        if ($latest) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $latest->id,
                    'version_name' => $latest->version_name,
                    'version_code' => $latest->version_code,
                    'release_date' => $latest->created_at->translatedFormat('d F Y, H:i'),
                    'file_size' => $latest->file_size,
                    'download_url' => $latest->download_url,
                    'release_notes' => $latest->release_notes ?? 'Rilis resmi aplikasi.',
                    'is_mandatory' => (bool) $latest->is_mandatory,
                    'platform' => $latest->platform,
                    'uploaded_by' => $latest->uploaded_by,
                    'download_count' => (int) $latest->download_count,
                ],
            ]);
        }

        $metaPath = storage_path('app/public/releases/meta.json');
        if (File::exists($metaPath)) {
            $data = json_decode(File::get($metaPath), true);
            if (is_array($data)) {
                return response()->json([
                    'success' => true,
                    'data' => $data,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'version_name' => '1.0.0 (Build 1)',
                'version_code' => 1,
                'release_date' => date('d F Y'),
                'file_size' => '69.6 MB',
                'download_url' => url('/download/app-release.apk'),
                'release_notes' => 'Rilis resmi aplikasi My Devano Silver Mobile.',
                'is_mandatory' => false,
                'platform' => 'android',
            ],
        ]);
    }

    /**
     * Get all release version upload history.
     */
    public function getHistory()
    {
        $releases = MobileAppRelease::latest('id')->get()->map(function ($rel) {
            return [
                'id' => $rel->id,
                'version_name' => $rel->version_name,
                'version_code' => $rel->version_code,
                'release_date' => $rel->created_at->translatedFormat('d F Y, H:i'),
                'file_size' => $rel->file_size,
                'download_url' => $rel->download_url,
                'release_notes' => $rel->release_notes ?? 'Pembaruan aplikasi.',
                'is_mandatory' => (bool) $rel->is_mandatory,
                'platform' => $rel->platform,
                'uploaded_by' => $rel->uploaded_by,
                'download_count' => (int) $rel->download_count,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $releases,
        ]);
    }

    /**
     * Upload a single APK file (Standard).
     */
    public function uploadApk(Request $request)
    {
        $file = $request->file('file') ?? $request->file('apk_file') ?? $request->file('apk');

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File APK tidak ditemukan atau gagal dikirim oleh browser.',
            ], 422);
        }

        if (!$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'File upload tidak valid: ' . $file->getErrorMessage(),
            ], 422);
        }

        $versionName = trim($request->input('version_name', '1.0.0'));
        $releaseNotes = trim($request->input('release_notes', 'Pembaruan aplikasi My Devano Silver Mobile.'));
        $isMandatory = $request->boolean('is_mandatory', false);

        return $this->finalizeApkFile($file->getRealPath(), $versionName, $releaseNotes, $isMandatory);
    }

    /**
     * Upload an APK in small batch chunks (Resumable & Chunked Upload).
     * Bypasses all PHP / Nginx upload limits by sending 2MB - 5MB chunks!
     */
    public function uploadChunk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'upload_id'    => 'required|string',
            'chunk_index'  => 'required|integer',
            'total_chunks' => 'required|integer',
            'version_name' => 'required|string',
            'file_chunk'   => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi paket chunk gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $uploadId = preg_replace('/[^a-zA-Z0-9_\-]/', '', $request->input('upload_id'));
        $chunkIndex = (int) $request->input('chunk_index');
        $totalChunks = (int) $request->input('total_chunks');
        $chunkFile = $request->file('file_chunk');

        $tempDir = storage_path('app/chunks/' . $uploadId);
        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true, true);
        }

        $chunkPath = $tempDir . '/chunk_' . str_pad($chunkIndex, 5, '0', STR_PAD_LEFT);
        $chunkFile->move($tempDir, 'chunk_' . str_pad($chunkIndex, 5, '0', STR_PAD_LEFT));

        // If not the final chunk, acknowledge receipt
        if ($chunkIndex < $totalChunks - 1) {
            return response()->json([
                'success' => true,
                'message' => 'Chunk ' . ($chunkIndex + 1) . '/' . $totalChunks . ' diterima.',
                'chunk_index' => $chunkIndex,
                'total_chunks' => $totalChunks,
                'completed' => false,
            ]);
        }

        // Final chunk received -> Assemble all chunks
        $assembledFilePath = $tempDir . '/assembled_app.apk';
        $out = fopen($assembledFilePath, 'wb');

        for ($i = 0; $i < $totalChunks; $i++) {
            $partPath = $tempDir . '/chunk_' . str_pad($i, 5, '0', STR_PAD_LEFT);
            if (!File::exists($partPath)) {
                fclose($out);
                return response()->json([
                    'success' => false,
                    'message' => "Potongan file batch index ke-$i hilang. Silakan ulangi proses upload.",
                ], 422);
            }
            $in = fopen($partPath, 'rb');
            while (!feof($in)) {
                fwrite($out, fread($in, 1048576)); // 1MB buffer
            }
            fclose($in);
        }
        fclose($out);

        $versionName = trim($request->input('version_name', '1.0.0'));
        $releaseNotes = trim($request->input('release_notes', 'Pembaruan aplikasi My Devano Silver Mobile.'));
        $isMandatory = $request->boolean('is_mandatory', false);

        $response = $this->finalizeApkFile($assembledFilePath, $versionName, $releaseNotes, $isMandatory);

        // Clean up temporary chunks directory
        File::deleteDirectory($tempDir);

        return $response;
    }

    /**
     * Finalize assembled or uploaded APK file, store to public storage, and save record.
     */
    protected function finalizeApkFile(string $sourceFilePath, string $versionName, string $releaseNotes, bool $isMandatory)
    {
        $cleanVer = preg_replace('/[^a-zA-Z0-9\.\_\-]/', '', $versionName);
        if (empty($cleanVer)) {
            $cleanVer = '1.0.0';
        }
        $versionFileName = 'app-release-v' . $cleanVer . '.apk';

        $apkStorageDir = storage_path('app/public/apk');
        $releasesStorageDir = storage_path('app/public/releases');

        if (!File::isDirectory($apkStorageDir)) {
            File::makeDirectory($apkStorageDir, 0755, true, true);
        }
        if (!File::isDirectory($releasesStorageDir)) {
            File::makeDirectory($releasesStorageDir, 0755, true, true);
        }

        $targetFile = $apkStorageDir . '/' . $versionFileName;
        File::copy($sourceFilePath, $targetFile);
        File::copy($targetFile, $apkStorageDir . '/app-release.apk');
        File::copy($targetFile, $releasesStorageDir . '/app-release.apk');

        $fileSizeBytes = filesize($targetFile);
        $fileSizeMb = round($fileSizeBytes / (1024 * 1024), 1) . ' MB';

        // Insert or update release record
        $release = MobileAppRelease::create([
            'version_name'  => $versionName,
            'version_code'  => time(),
            'file_name'     => $versionFileName,
            'file_path'     => 'apk/' . $versionFileName,
            'file_size'     => $fileSizeMb,
            'release_notes' => $releaseNotes,
            'is_mandatory'  => $isMandatory,
            'platform'      => 'android',
            'uploaded_by'   => auth()->user()?->name ?? 'Admin',
            'checksum'      => hash_file('sha256', $targetFile),
        ]);

        $meta = [
            'id' => $release->id,
            'version_name' => $versionName,
            'version_code' => $release->version_code,
            'release_date' => $release->created_at->translatedFormat('d F Y, H:i'),
            'file_size' => $fileSizeMb,
            'download_url' => url('/download/app-release.apk'),
            'release_notes' => $releaseNotes,
            'is_mandatory' => $isMandatory,
            'platform' => 'android',
        ];

        File::put($releasesStorageDir . '/meta.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json([
            'success' => true,
            'message' => 'Paket rilis aplikasi versi ' . $versionName . ' (' . $fileSizeMb . ') berhasil diunggah via Batch Chunks dan dicatat ke riwayat rilis!',
            'data' => $meta,
            'completed' => true,
        ]);
    }

    /**
     * Download the latest APK binary file.
     */
    public function downloadApk()
    {
        $latest = MobileAppRelease::latest('id')->first();
        if ($latest && Storage::exists('public/' . $latest->file_path)) {
            $latest->increment('download_count');
            return Storage::download('public/' . $latest->file_path, $latest->file_name, [
                'Content-Type' => 'application/vnd.android.package-archive',
            ]);
        }

        $paths = [
            storage_path('app/public/apk/app-release.apk'),
            storage_path('app/public/releases/app-release.apk'),
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                return Response::download($path, 'app-release.apk', [
                    'Content-Type' => 'application/vnd.android.package-archive',
                ]);
            }
        }

        return response()->json(['message' => 'File APK belum tersedia di server.'], 404);
    }
}