# SIGAP TBC – Sistem Skrining Tuberkulosis

SIGAP TBC adalah aplikasi web berbasis Laravel 12 untuk mendukung proses skrining tuberkulosis di lapangan. Aplikasi ini menyediakan alur registrasi, login, dan manajemen akun untuk lima jenis pengguna:

- Pasien
- Kader TBC
- Puskesmas
- Kelurahan
- Pemerintah Daerah

Setiap akun memiliki detail profil (NIK, kontak, alamat, instansi) serta status aktif/tidak aktif sehingga tim admin dapat mengelola akses tanpa email verification.

## Fitur Utama

- **Multi-role auth** dengan Laravel Breeze (Blade + Tailwind).
- **Registrasi mandiri** untuk semua jenis pengguna dengan form yang panjang.
- **Dashboard personal** yang menampilkan status peran, aktivitas akun, dan detail kontak.
- **Tabel `user_details`** untuk menyimpan data tambahan pasien/kader/faskes.
- **Session & queue siap pakai** dengan konfigurasi database driver.

## Persyaratan

- PHP 8.2+
- Composer 2+
- Node.js 20+ & npm
- MySQL/MariaDB (atau database lain yang kompatibel dengan Laravel)

## Cara Menjalankan Proyek

1. **Clone & install dependency**
   ```bash
   composer install
   npm install
   ```

2. **Siapkan environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Lalu perbarui konfigurasi database di `.env` (misal `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

3. **Migrasi database**
   ```bash
   php artisan migrate
   ```
   Jika ingin memulai dengan data contoh (pemda, puskesmas, kader, pasien, treatment Faskes, dll) jalankan:
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Build asset**
   ```bash
   npm run dev   # atau npm run build untuk mode produksi
   ```

5. **Jalankan aplikasi**
   ```bash
   php artisan serve
   ```
   Akses aplikasi di `http://localhost:8000`.

## Struktur Autentikasi

- `app/Enums/UserRole.php` — daftar peran resmi dan label tampilan.
- `app/Models/User` — menyimpan role & status aktif.
- `app/Models/UserDetail` — detail tambahan setiap user.
- `resources/views/auth/register.blade.php` — form registrasi multi-role.
- `resources/views/dashboard.blade.php` — dashboard ringkas menampilkan info pengguna.

## Kontribusi & Lisensi

Proyek ini dibangun di atas Laravel dan menggunakan lisensi MIT. Silakan buka issue atau pull request jika ingin berkontribusi pada modul SIGAP TBC.
