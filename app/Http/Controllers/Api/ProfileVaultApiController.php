<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmergencyContact;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileVaultApiController extends Controller
{
    public function getDocuments(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['data' => []]);
        }

        $nik = $employee->nik ?? '-';
        $ktp = $employee->identity_number ?? '-';
        $npwp = $employee->npwp_number ?? '-';
        $bpjsTk = $employee->bpjs_ketenagakerjaan_number ?? '-';
        $bpjsKes = $employee->bpjs_kesehatan_number ?? '-';

        $documents = [
            [
                'id' => '1',
                'title' => 'Kartu Tanda Penduduk (KTP)',
                'category' => 'identitas',
                'document_number' => $ktp,
                'status' => 'Terverifikasi HR',
            ],
            [
                'id' => '2',
                'title' => 'Nomor Pokok Wajib Pajak (NPWP)',
                'category' => 'identitas',
                'document_number' => $npwp,
                'status' => 'Terverifikasi HR',
            ],
            [
                'id' => '3',
                'title' => 'BPJS Ketenagakerjaan (TK)',
                'category' => 'asuransi',
                'document_number' => $bpjsTk,
                'status' => 'Aktif',
            ],
            [
                'id' => '4',
                'title' => 'BPJS Kesehatan',
                'category' => 'asuransi',
                'document_number' => $bpjsKes,
                'status' => 'Aktif',
            ],
            [
                'id' => '5',
                'title' => 'Surat Perjanjian Kerja (PKWT)',
                'category' => 'kontrak',
                'document_number' => 'PKWT/DEVANO/2026/' . $nik,
                'status' => 'Berlaku s/d Dec 2026',
            ],
        ];

        return response()->json(['data' => $documents]);
    }

    public function getEmergencyContacts(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['data' => []]);
        }

        $contacts = EmergencyContact::where('employee_id', $employee->id)
            ->orderBy('is_primary', 'desc')
            ->get();

        return response()->json([
            'data' => $contacts->map(fn($c) => [
                'id' => (string) $c->id,
                'name' => $c->name,
                'relationship' => $c->relationship,
                'phone' => $c->phone,
                'is_primary' => (bool) $c->is_primary,
                'address' => $c->address,
            ]),
        ]);
    }

    public function addEmergencyContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'relationship' => 'required|string',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'is_primary' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $contact = EmergencyContact::create([
            'employee_id' => $employee->id,
            'name' => $validated['name'],
            'relationship' => $validated['relationship'],
            'phone' => $validated['phone'],
            'is_primary' => $validated['is_primary'] ?? false,
            'address' => $validated['address'] ?? null,
        ]);

        return response()->json([
            'message' => 'Kontak darurat berhasil ditambahkan.',
            'data' => [
                'id' => (string) $contact->id,
                'name' => $contact->name,
                'relationship' => $contact->relationship,
                'phone' => $contact->phone,
                'is_primary' => (bool) $contact->is_primary,
            ],
        ], 201);
    }

    public function deleteEmergencyContact($id, Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        EmergencyContact::where('employee_id', $employee->id)
            ->where('id', $id)
            ->delete();

        return response()->json([
            'message' => 'Kontak darurat berhasil dihapus.',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'photo' => 'nullable',
        ]);

        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            if ($employee && $employee->photo && Storage::disk('public')->exists($employee->photo)) {
                Storage::disk('public')->delete($employee->photo);
            }
            $photoPath = $request->file('photo')->store('photos', 'public');
        } elseif ($request->filled('photo') && is_string($request->photo) && str_starts_with($request->photo, 'data:image')) {
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $request->photo);
            $decoded = base64_decode($imageData);
            if ($decoded !== false) {
                if ($employee && $employee->photo && Storage::disk('public')->exists($employee->photo)) {
                    Storage::disk('public')->delete($employee->photo);
                }
                $filename = 'photos/' . uniqid('photo_') . '.jpg';
                Storage::disk('public')->put($filename, $decoded);
                $photoPath = $filename;
            }
        }

        $userUpdate = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];
        if ($photoPath) {
            $userUpdate['photo'] = $photoPath;
        }
        $user->update($userUpdate);

        if ($employee) {
            $employeeUpdate = [
                'full_name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];
            if ($photoPath) {
                $employeeUpdate['photo'] = $photoPath;
            }
            $employee->update($employeeUpdate);
        }

        $updatedEmployee = Employee::with(['department', 'position', 'shift', 'station'])
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'photo_url' => $updatedEmployee?->photo_url,
            'employee' => $updatedEmployee,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        $currentValid = Hash::check($request->current_password, $user->password);
        if (!$currentValid && $employee && $employee->birth_date) {
            $currentValid = $request->current_password === \Carbon\Carbon::parse($employee->birth_date)->format('Y-m-d');
        }

        if (!$currentValid) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini salah.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        if ($employee) {
            $employee->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}
