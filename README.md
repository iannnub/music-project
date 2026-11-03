# KakYo Lesson - Sistem Manajemen Kursus Musik Terpadu

KakYo Lesson adalah aplikasi manajemen kursus musik berbasis web dengan arsitektur MVC (Model-View-Controller) PHP Native untuk Studio 1 Balung dan Studio 2 Jember Kota. Sistem ini mengelola pendaftaran siswa mandiri, penjadwalan kelas privat dan band, presensi multi-lokasi, kurikulum raport berbasis Bab dan Subbab, serta administrasi keuangan dan gaji pengajar.

## Fitur Utama

- **Registrasi Siswa Mandiri Multi-Cabang**: Pendaftaran online dengan kalkulasi tagihan perdana otomatis sesuai cabang (`Studio 1 Balung`: SPP Rp 250.000 + Pendaftaran Rp 50.000; `Studio 2 Jember Kota`: SPP Rp 350.000 + Pendaftaran Rp 50.000) serta validasi wajib baca Tata Tertib Siswa & Wali Murid.
- **Presensi Live GPS Multi-Lokasi**: Absensi pengajar dan siswa berbasis foto kamera dan titik koordinat aktual pengguna tanpa batasan satu radius titik studio, mendukung operasional lintas cabang.
- **Manajemen Kelas Privat & Grup Band**: Pengelolaan kelas 1-on-1 serta kelas grup terstruktur dalam unit Band (`Band 1`, `Band 2`, dan seterusnya) lengkap dengan anggota siswa per band.
- **Kurikulum Bab & Subbab + Cetak Raport A4**: Pengajar menyusun materi per Bab dan Subbab untuk tiap siswa dengan kalkulasi persentase penguasaan otomatis dan fitur cetak Raport A4.
- **Rekap Gaji Pengajar & Pembersihan Foto Bulanan**: Perhitungan gaji pengajar otomatis, konfirmasi manual admin bagi pengajar yang terlambat atau lupa absen, pratinjau foto bukti absen, dan penghapusan otomatis file foto bulan sebelumnya setiap tanggal 1 tanpa menghapus riwayat gaji di database.
- **Portal Video Latihan TikTok**: Integrasi tautan video latihan resmi KakYo Lesson yang dikelola Admin untuk dipantau siswa dan wali murid.
- **Antarmuka Dark Liquid Glass**: Tema gelap solid (`#09090B`), aksen oranye (`#FF8C24`), dan putih bersih (`#FFFFFF`) tanpa gradasi, dilengkapi navigasi *Dynamic Island* dan transisi halaman modern.

## Tech Stack

- **Backend**: PHP 8.x (Native MVC, PDO Prepared Statements, CSRF Protection, Session Hardening)
- **Database**: MySQL / MariaDB (`db/migration_v2_client_updates.sql`)
- **Frontend**: HTML5, Custom CSS3 (`liquid-glass.css`), Bootstrap 4, DataTables, SweetAlert2, FontAwesome 5

## Panduan Pembaruan Database (v2)

Jalankan skrip migrasi berikut melalui phpMyAdmin atau MySQL CLI sebelum menggunakan fitur terbaru:

```sql
SOURCE db/migration_v2_client_updates.sql;
```

Untuk pembersihan otomatis foto absensi guru setiap awal bulan serta pemrosesan status Alpha harian via Cron:

```bash
php cron/process_alpha.php
```

## Kontak Studio

- **Studio 1 Balung**: Jln. Puger (Dekat Dira Balung), Kabupaten Jember
- **Studio 2 Jember Kota**: Kota Jember, Jawa Timur
- **WhatsApp**: +62 851-7986-1126
- **Instagram**: `@kakyo_lesson`

Created by Septian Putra (iannnub).
