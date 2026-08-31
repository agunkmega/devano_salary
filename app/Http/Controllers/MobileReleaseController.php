<?php

namespace App\Http\Controllers;

use App\Models\MobileAppRelease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MobileReleaseController extends Controller
{
    public function getLatest()
    {
        $latest = MobileAppRelease::latest('id')->first();

        if (!$latest) {
            return response()->json([
                'success' => true,
                'data' => [
                    'version_name' => '1.0.0',
                    'version_code' => 1,
                    'release_date' => date('d F Y'),
                    'file_size' => '28.6 MB',
                    'download_url' => url('/download/app-release.apk'),
                    'release_notes' => 'Rilis resmi aplikasi My Devano Silver Mobile.',
                    'is_mandatory' => false,
                    'platform' => 'android',
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $latest->id,
                'version_name' => $latest->version_name,
                'version_code' => $latest->version_code,
                'release_date' => $latest->created_at->translatedFormat('d F Y, H:i'),
                'file_size' => $latest->file_size,
                'download_url' => $latest->download_url,
                'release_notes' => $latest->release_notes ?? 'Pembaruan aplikasi.',
                'is_mandatory' => $latest->is_mandatory,
                'platform' => $latest->platform,
                'uploaded_by' => $latest->uploaded_by,
                'download_count' => $latest->download_count,
            ],
        ]);
    }

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
                'is_mandatory' => $rel->is_mandatory,
                'platform' => $rel->platform,
                'uploaded_by' => $rel->uploaded_by,
                'download_count' => $rel->download_count,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $releases,
        ]);
    }

    public function uploadApk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'apk_file'      => 'required|file|max:153600',
            'version_name'  => 'required|string|max:50',
            'version_code'  => 'nullable|integer',
            'release_notes' => 'nullable|string',
            'is_mandatory'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi file gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('apk_file');
        $cleanVer = preg_replace('/[^a-zA-Z0-9\.\_\-]/', '', $request->input('version_name'));
        $fileName = 'app-release-v' . $cleanVer . '.apk';

        $storedPath = $file->storeAs('public/apk', $fileName);
        $cleanPath = str_replace('public/', '', $storedPath);

        Storage::copy($storedPath, 'public/apk/app-release.apk');

        $bytes = $file->getSize();
        $formattedSize = round($bytes / (1024 * 1024), 1) . ' MB';

        $release = MobileAppRelease::create([
            'version_name'  => $request->input('version_name'),
            'version_code'  => $request->input('version_code', (MobileAppRelease::count() + 1)),
            'file_name'     => $fileName,
            'file_path'     => $cleanPath,
            'file_size'     => $formattedSize,
            'release_notes' => $request->input('release_notes', 'Pembaruan aplikasi'),
            'is_mandatory'  => $request->boolean('is_mandatory'),
            'platform'      => 'android',
            'uploaded_by'   => auth()->user()?->name ?? 'Admin Devano',
            'checksum'      => hash_file('sha256', $file->getRealPath()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'APK versi ' . $release->version_name . ' berhasil diunggah!',
            'data' => $release,
        ]);
    }

    public function downloadLatestApk()
    {
        $latest = MobileAppRelease::latest('id')->first();

        if ($latest && Storage::exists('public/' . $latest->file_path)) {
            $latest->increment('download_count');
            return Storage::download('public/' . $latest->file_path, $latest->file_name);
        }

        if (Storage::exists('public/apk/app-release.apk')) {
            return Storage::download('public/apk/app-release.apk', 'app-release.apk');
        }

        return abort(404, 'File APK belum diunggah ke server.');
    }
}