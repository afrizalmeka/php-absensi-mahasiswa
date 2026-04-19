# AbsenKu — Sistem Absensi Mahasiswa

Aplikasi web untuk mengelola absensi mahasiswa per mata kuliah, berbasis PHP dan SQLite.

## Fitur

- Login multi-role (admin / dosen)
- Dashboard statistik kehadiran hari ini
- Input absensi per pertemuan (hadir / izin / sakit / alpha)
- Rekap kehadiran per mata kuliah dengan persentase
- Export rekap ke format CSV
- Kelola data mahasiswa dan mata kuliah

## Prasyarat

- PHP 8.x (`php -v` untuk mengecek versi)
- Ekstensi `pdo_sqlite` (aktif secara default pada instalasi PHP standar)

## Cara Menjalankan

1. Clone atau unduh folder ini
2. Masuk ke folder project:
   ```bash
   cd php-absensi-mahasiswa
   ```
3. Jalankan server PHP bawaan:
   ```bash
   php -S localhost:8005
   ```
4. Buka browser: `http://localhost:8005`

Database SQLite dibuat otomatis saat pertama kali halaman diakses.

## Akun Demo

| Role  | Email                  | Password |
|-------|------------------------|----------|
| Admin | admin@absenku.test     | admin123 |
| Dosen | dosen@absenku.test     | dosen123 |

## Struktur Folder

```
php-absensi-mahasiswa/
├── index.php               # Dashboard
├── login.php
├── logout.php
├── input_absen.php         # Input absensi per pertemuan
├── rekap.php               # Rekap & export CSV
├── admin_mahasiswa.php     # Kelola data mahasiswa
├── admin_matakuliah.php    # Kelola mata kuliah
├── config.php
├── css/style.css
├── php/
│   ├── auth.php
│   └── header.php
└── database/
    └── init.php            # Inisialisasi & seed database otomatis
```

## Catatan untuk Mahasiswa

Aplikasi ini adalah **versi latihan** yang mengandung bug untuk keperluan praktikum pengujian perangkat lunak. Temukan dan perbaiki bug yang ada sebagai bagian dari tugas praktikum.
