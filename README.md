# SIHATI

SIHATI adalah aplikasi helpdesk internal berbasis Laravel monolith. Issue #1 membangun fondasi identitas, sesi, role, otorisasi server-side, audit, dan shell responsif berbahasa Indonesia.

## Stack

- Laravel 12
- PHP 8.2+
- Blade dan Tailwind CSS
- PostgreSQL untuk development lokal dan deployment
- PHPUnit untuk feature test

## Menjalankan lokal

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Buka `http://localhost:8000/login`. Seeder hanya membuat enam role canonical; tidak ada akun default dengan password yang diketahui.

Konfigurasi lokal menggunakan PostgreSQL pada `127.0.0.1:5432`, database `sihati`, user `postgres`, dan password kosong sesuai setup development DBngin. Sesuaikan nilai `DB_*` di `.env` jika environment Anda berbeda.

Untuk membuat akun Super Admin pertama secara interaktif, gunakan command berikut dan ikuti prompt password:

```bash
php artisan sihati:provision-super-admin admin "Super Admin"
```

Buat akun lain melalui provisioning yang disetujui lingkungan deployment atau factory untuk test.

Untuk development dengan Vite, jalankan `npm run dev` pada terminal terpisah.

## Fondasi yang tersedia

- Login dengan username, rate limiting, session regeneration, logout, CSRF, dan audit login.
- Forced password change untuk password awal atau password hasil reset melalui modal wajib di dashboard; fitur lain tetap diblokir di server sampai selesai.
- Password kuat minimal 12 karakter dengan huruf besar, huruf kecil, angka, dan simbol.
- Akun nonaktif tidak dapat login; sesi aktif akun yang dinonaktifkan diputus sebelum akses berikutnya.
- Enam role: Super Admin, Pemohon, Agen Tier 1, Agen Tier 2, Approver, dan Ketua Tim Kerja.
- Multi-role melalui tabel pivot `user_roles`.
- Policy dan pemeriksaan domain untuk reset password, klaim tiket, dan penanganan tiket.
- Super Admin tanpa role operasional tidak dapat klaim atau menangani tiket.
- Audit log append-only dari aplikasi untuk aksi berhasil dan percobaan yang ditolak.
- Layout login, ganti password, dasbor, administrasi pengguna, dan audit log yang responsif serta berbahasa Indonesia.

## Verifikasi

```bash
php artisan test
vendor/bin/pint --test
php artisan view:cache
npm run build
```

Konfigurasi production wajib menggunakan HTTPS dan mengaktifkan `SESSION_SECURE_COOKIE=true`.
