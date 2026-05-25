<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\ValidationException;

class EmployeesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use Importable;

    protected $rowCount = 0;
    protected $errors = [];

    public function model(array $row)
    {
        $row['nik'] = (string) ($row['nik'] ?? '');
        if (empty($row['nik'])) return null;

        $this->rowCount++;

        $deptName = $row['department'] ?? $row['departemen'] ?? '';
        $dept = Department::where('name', $deptName)
            ->orWhere('code', $deptName)
            ->first();
        if (!$dept) {
            $dept = Department::first();
        }

        $posName = $row['position'] ?? $row['jabatan'] ?? '';
        $pos = Position::where('name', $posName)
            ->orWhere('code', $posName)
            ->first();
        if (!$pos) {
            $pos = Position::first();
        }

        $existing = Employee::where('nik', $row['nik'])->first();

        $data = [
            'full_name' => $row['name'] ?? $row['full_name'],
            'birth_date' => $row['birth_date'] ?? $row['tanggal_lahir'] ?? now()->subYears(25)->format('Y-m-d'),
            'gender' => $row['gender'] ?? $row['jenis_kelamin'] ?? 'L',
            'department_id' => $dept->id,
            'position_id' => $pos->id,
            'phone' => $row['phone'] ?? $row['telepon'] ?? null,
            'email' => $row['email'] ?? null,
            'address' => $row['address'] ?? $row['alamat'] ?? null,
            'join_date' => $row['join_date'] ?? $row['tanggal_masuk'] ?? now()->format('Y-m-d'),
            'base_salary' => (float) ($row['base_salary'] ?? $row['gaji_pokok'] ?? 0),
            'allowance' => (float) ($row['allowance'] ?? $row['tunjangan'] ?? 0),
            'allowance_type' => $row['allowance_type'] ?? $row['jenis_tunjangan'] ?? null,
            'allowance_absensi' => (float) ($row['allowance_absensi'] ?? $row['tunjangan_absensi'] ?? 0),
            'allowance_transport' => (float) ($row['allowance_transport'] ?? $row['tunjangan_transport'] ?? 0),
            'allowance_jabatan' => (float) ($row['allowance_jabatan'] ?? $row['tunjangan_jabatan'] ?? 0),
            'allowance_insentif' => (float) ($row['allowance_insentif'] ?? $row['tunjangan_insentif'] ?? 0),
            'is_active' => true,
        ];

        if ($existing) {
            $existing->update($data);
            return $existing;
        }

        return new Employee(array_merge($data, [
            'nik' => $row['nik'],
        ]));
    }

    public function rules(): array
    {
        return [];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}