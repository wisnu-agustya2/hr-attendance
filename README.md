# hr-attendance

Aplikasi absensi dan cuti karyawan, dibangun dengan **CodeIgniter 4**. Karyawan mencatat kehadiran dan mengajukan cuti. HR menyetujui cuti dan memantau rekap bulanan.

## Fitur
**Karyawan**
- Check-in dan check-out sekali sehari. Status "terlambat" ditentukan otomatis dari jam masuk dan toleransi
- Ringkasan bulan berjalan: hadir, terlambat, cuti, alpa, total jam kerja, dan sisa cuti tahunan
- Riwayat absensi per bulan
- Pengajuan cuti tahunan, sakit, atau izin. Hanya hari kerja yang dihitung, dan pengajuan yang masih menunggu bisa dibatalkan

**HR / Admin**
- Kehadiran hari ini: siapa yang tepat waktu, terlambat, cuti, atau belum hadir
- Persetujuan cuti, dengan pengecekan ulang kuota saat disetujui
- Rekap bulanan per karyawan (hadir, terlambat, cuti, sakit, izin, alpa, jam kerja) dan export CSV yang bisa dibuka di Excel
- Kelola karyawan: tambah, ubah, reset password, dan nonaktifkan akun

**Aturan bisnis**
- Kuota cuti tahunan (default 12 hari) hanya terpotong oleh cuti tahunan yang disetujui
- Pengajuan yang tanggalnya bertabrakan dengan pengajuan lain ditolak
- Alpa = hari kerja yang sudah lewat tanpa absensi dan tanpa cuti/izin yang disetujui
- Jam masuk, toleransi, kuota, hari kerja, dan hari libur bisa diatur di `app/Config/Attendance.php` atau `.env`

**Keamanan**
- Proteksi CSRF di semua form, password di-hash dengan bcrypt, dan session diregenerasi saat login
- Login dibatasi 5 percobaan per menit
- Halaman HR dilindungi filter peran
- Unique key `(employee_id, work_date)` mencegah double check-in walau tombol diklik bersamaan
- Export CSV aman dari formula injection

## Menjalankan
Butuh PHP 8.2 atau lebih baru (dengan extension `intl`, `mbstring`, `sqlite3`) dan Composer.
```bash
git clone https://github.com/wisnu-agustya2/hr-attendance.git
cd hr-attendance
composer install
cp env .env
php spark migrate
php spark db:seed DatabaseSeeder
php spark serve
```
Seeder membuat 1 akun HR (`hr@example.com`), 4 karyawan (misalnya `andi@example.com`), absensi 15 hari kerja terakhir, dan contoh pengajuan cuti. Password ditampilkan di terminal. Buka http://localhost:8080.

Untuk membuat admin sendiri atau mengganti password admin:
```bash
php spark hr:admin hr@perusahaan.com "Nama Admin"
```

## Tes
27 test: unit test untuk logika hari kerja, keterlambatan, dan rekap; feature test untuk login, absensi, cuti, persetujuan, rekap, dan hak akses.
```bash
composer test
```

## Struktur
```
app/Config/Attendance.php        aturan absensi
app/Libraries/WorkCalendar.php   hari kerja dan hari libur
app/Libraries/AttendanceRules.php status terlambat dan durasi kerja
app/Libraries/MonthlyRecap.php   rekap bulanan (tanpa database, mudah diuji)
app/Libraries/LeavePolicy.php    validasi kuota dan tanggal cuti
app/Controllers/                 karyawan: absensi, cuti
app/Controllers/Admin/           HR: dashboard, karyawan, persetujuan, rekap
app/Filters/                     filter login dan peran admin
app/Database/                    migrasi dan seeder demo
tests/Unit, tests/Feature        PHPUnit
```

## Lisensi
MIT
