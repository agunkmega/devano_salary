# HRIS Absensi + Payroll

Aplikasi Web HRIS (Human Resource Information System) modern dengan fitur **Absensi** dan **Payroll** terintegrasi. Dibangun dengan **Laravel 11**, **MySQL**, **TailwindCSS**, dan **Alpine.js**.

## Fitur Utama

### Dashboard
- Statistik real-time (hadir, terlambat, cuti, tidak hadir)
- Grafik kehadiran bulanan
- Total gaji bulan berjalan
- Ringkasan kasbon
- Statistik per departemen

### Master Data Pegawai
- CRUD pegawai dengan NIK unik
- Import Excel
- Export Excel & PDF
- Pencarian dan filter multi-kriteria
- Foto pegawai
- Auto-create user account

### Mesin Absensi
- Import data dari file TXT/CSV (format mesin fingerprint)
- Mapping otomatis berdasarkan PIN/NIK
- Riwayat import
- Log aktivitas import
- Validasi data duplikat

### Pengaturan Shift
- Jam masuk & pulang
- Toleransi keterlambatan (menit)
- Hari kerja (array JSON)
- Multi shift (Pagi, Siang, Malam, Kantor)
- Flag shift malam

### Perhitungan Terlambat
- Otomatis hitung menit terlambat
- Potongan per menit (configurable via Settings)
- Toleransi keterlambatan
- Perhitungan pulang cepat
- Perhitungan lembur

### Cuti & Izin
- Pengajuan cuti online
- Approval multi-level
- 6 jenis cuti (Tahunan, Sakit, Melahirkan, Pernikahan, Izin, ITK)
- Sisa cuti per tahun
- Upload file pendukung

### Kasbon
- Input kasbon pegawai
- Cicilan otomatis terpotong gaji
- Riwayat pembayaran
- Sisa hutang
- Approval kasbon

### Payroll / Salary
- Perhitungan otomatis:
  - Gaji pokok + tunjangan
  - Bonus
  - Lembur (1.5x hourly rate)
  - Potongan terlambat (per menit)
  - Potongan kasbon (cicilan)
  - BPJS (2%)
  - Pajak (5% jika > 4.5jt)
- Generate payroll bulanan (single / massal)
- Slip gaji PDF
- Approval & payment workflow

### Reports
- Report absensi per pegawai
- Report keterlambatan
- Report lembur
- Report cuti
- Report payroll summary
- Export Excel & PDF

### Admin Panel
- Sidebar navigasi role-based
- Dark mode
- Settings perusahaan & payroll
- Database backup
- Activity log / audit trail
- Notifikasi

## Teknologi

| Teknologi | Versi |
|-----------|-------|
| Laravel | 11.x |
| PHP | 8.1+ |
| MySQL | 8.0+ |
| TailwindCSS | 3.x |
| Alpine.js | 3.x |
| Vite | 5.x |
| Laravel Breeze | Blade stack |
| Laravel Excel | 3.1 |
| DomPDF | 3.1 |

## Persyaratan Sistem

- PHP 8.1 atau lebih baru
- Composer 2.x
- Node.js 18+ & NPM
- MySQL 8.0+ / MariaDB 10.3+
- Extensi PHP: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD, Zip

## Instalasi di Localhost

### 1. Clone Project

```bash
git clone <repository-url> hris-absensi
cd hris-absensi
```

### 2. Install Dependencies PHP

```bash
composer install
```

### 3. Copy & Konfigurasi Environment

```bash
cp .env.example .env
```

Edit file `.env`:

```env
APP_NAME="HRIS Absensi"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hris_absensi
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Generate Key

```bash
php artisan key:generate
```

### 5. Setup Database MySQL

Buat database:

```bash
mysql -u root -p -e "CREATE DATABASE hris_absensi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 6. Migrasi & Seeder

```bash
php artisan migrate --seed
```

### 7. Install & Build Frontend

```bash
npm install
npm run build
```

### 8. Jalankan Development Server

```bash
php artisan serve
```

Akses di browser: `http://localhost:8000`

## Akun Default

| Role | Email | Password |
|------|-------|----------|
| **Super Admin** | superadmin@example.com | password |
| **HR** | hr@example.com | password |
| **Manager** | manager@example.com | password |
| **Staff** | staff@example.com | password |

## Struktur Database

### Tabel Utama

| Tabel | Deskripsi |
|-------|-----------|
| `users` | User login & role (super_admin, hr, manager, staff) |
| `employees` | Data master pegawai |
| `departments` | Departemen perusahaan |
| `positions` | Jabatan/posisi |
| `shifts` | Pengaturan shift kerja |
| `attendances` | Data absensi harian |
| `attendance_logs` | Log import dari mesin absensi |
| `leave_types` | Jenis cuti/izin |
| `leaves` | Pengajuan cuti & izin |
| `cash_advances` | Data kasbon pegawai |
| `cash_advance_payments` | Riwayat pembayaran kasbon |
| `payrolls` | Data payroll/gaji bulanan |
| `payroll_details` | Detail komponen gaji |
| `settings` | Pengaturan sistem |
| `activity_logs` | Log aktivitas user |
| `import_logs` | Riwayat import data |
| `notifications` | Notifikasi sistem |

## Role & Hak Akses

| Menu | Super Admin | HR | Manager | Staff |
|------|:-----------:|:--:|:-------:|:-----:|
| Dashboard | ✓ | ✓ | ✓ | ✓ |
| Pegawai | ✓ | ✓ | ✓ | - |
| Departemen | ✓ | ✓ | - | - |
| Jabatan | ✓ | ✓ | - | - |
| Absensi | ✓ | ✓ | ✓ | - |
| Shift | ✓ | ✓ | - | - |
| Cuti / Izin | ✓ | ✓ | ✓ | ✓ |
| Kasbon | ✓ | ✓ | ✓ | ✓ |
| Payroll | ✓ | ✓ | ✓ | - |
| Laporan | ✓ | ✓ | ✓ | - |
| Settings | ✓ | - | - | - |
| Activity Log | ✓ | - | - | - |

## Format File Import Absensi

### Format TXT

File teks dengan delimiter tab atau koma. Setiap baris berisi **PIN** dan **Timestamp**.

```
EMP-001	2024-01-15 07:45:00
EMP-002	2024-01-15 08:15:00
EMP-003	2024-01-15 07:50:00
```

Atau dengan koma:

```
EMP-001,2024-01-15 07:45:00
EMP-002,2024-01-15 08:15:00
EMP-003,2024-01-15 07:50:00
```

**Keterangan:**
- Kolom 1: PIN/NIK pegawai (harus sesuai dengan field `nik` di tabel employees)
- Kolom 2: Timestamp format `YYYY-MM-DD HH:MM:SS`

### Format CSV

```csv
pin,date,time
EMP-001,2024-01-15,07:45:00
EMP-002,2024-01-15,08:15:00
EMP-003,2024-01-15,07:50:00
```

## API Mesin Absensi

### Endpoint REST API

| Method | URL | Deskripsi |
|--------|-----|-----------|
| POST | `/api/attendance/push` | Push data dari mesin absensi |
| GET | `/api/attendance/logs` | Get logs absensi |
| POST | `/api/attendance/import` | Import via API |

### Format Push Data

```json
{
    "records": [
        {"pin": "EMP-001", "timestamp": "2024-01-15 07:45:00"},
        {"pin": "EMP-002", "timestamp": "2024-01-15 08:15:00"}
    ]
}
```

## Build Production

```bash
# Optimasi Laravel
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build asset production
npm run build

# Set APP_ENV=production di .env
# Set APP_DEBUG=false
```

## Backup Database

```bash
# Manual via command
php artisan db:backup

# Atau via panel admin (Settings > Backup Database)
```

Atau via MySQL:

```bash
mysqldump -u root -p hris_absensi > backup-hris-$(date +%Y%m%d).sql
```

## Troubleshooting

### 1. Error "Target class [x] does not exist"
```bash
composer dump-autoload
php artisan optimize:clear
```

### 2. Error "No application encryption key"
```bash
php artisan key:generate
```

### 3. Error 403 saat akses halaman
Pastikan user sudah login dengan role yang sesuai.

### 4. Error koneksi database
Periksa konfigurasi di `.env` dan pastikan MySQL service berjalan.

### 5. Error upload file (max size)
Edit `php.ini`: `upload_max_filesize = 10M` dan `post_max_size = 12M`

## Lisensi

MIT License
