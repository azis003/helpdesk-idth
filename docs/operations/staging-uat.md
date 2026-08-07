# Staging, UAT, pilot, dan go-live

## 1. Urutan gate

1. **Staging readiness** — environment, HTTPS, PostgreSQL, Redis, queue worker, scheduler, private storage, backup, health probe, dan monitoring tersedia.
2. **Technical verification** — test otomatis, security/access/file/audit/export, performance, backup, dan restore drill selesai.
3. **UAT** — pemilik proses menjalankan AC dengan kombinasi role yang relevan dan memeriksa audit.
4. **Pilot** — kelompok kecil pengguna memakai data yang disetujui dalam support window.
5. **Go/no-go** — pemilik produk, operasional, infra, dan keamanan menandatangani hasil atau deviasi.

## 2. Perintah verifikasi teknis

```bash
composer validate --strict
vendor/bin/pint --test
php artisan test
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan sihati:ops:health --json
php artisan sihati:ops:check-storage --json
```

Perintah dijalankan pada staging dengan environment PostgreSQL/Redis target. CI menjalankan subset yang sama pada `.github/workflows/ci.yml`.

## 3. Matriks AC-01 sampai AC-28

Isi kolom status dengan `Lulus`, `Gagal`, atau `Deviasi`; sertakan link bukti dan owner. Semua skenario wajib menggunakan data master staging yang telah disahkan.

| ID | Bukti otomatis/aktivitas | Status | Bukti, deviasi, owner |
|---|---|---|---|
| AC-01 | `tests/Feature/Auth/AuthenticationTest.php`; password awal hanya menuju ganti password | TBD | |
| AC-02 | `tests/Feature/Authorization/RoleAuthorizationTest.php`; Super Admin tanpa role operasional | TBD | |
| AC-03 | `tests/Feature/Admin/CatalogManagementTest.php`, `TicketManagementTest.php`; field dinamis dan lokasi | TBD | |
| AC-04 | `TicketManagementTest.php`; requester/creator/snapshot/self-created | TBD | |
| AC-05 | `TicketManagementTest.php` dan concurrency test nomor tiket | TBD | |
| AC-06 | `TicketManagementTest.php`, `DashboardTest.php`; cakupan pemohon | TBD | |
| AC-07 | `TicketTriageTest.php`; dua klaim bersamaan dan audit denied | TBD | |
| AC-08 | `TicketTriageTest.php`; hasil triase dan alasan penolakan | TBD | |
| AC-09 | `TicketTriageTest.php`; saran skill dan keputusan manual | TBD | |
| AC-10 | `TicketApprovalTest.php`; approval, restore state, self-approval | TBD | |
| AC-11 | `TicketCommunicationTest.php`; balasan publik vs catatan internal | TBD | |
| AC-12 | `TicketCommunicationTest.php`, scheduled command test; requester wait dan timeout | TBD | |
| AC-13 | `TicketCommunicationTest.php`, scheduled command test; third-party wait | TBD | |
| AC-14 | `TicketResolutionTest.php`; solusi, konfirmasi, auto-close | TBD | |
| AC-15 | `TicketResolutionTest.php`; reopen karena hasil belum sesuai | TBD | |
| AC-16 | `TicketResolutionTest.php`; reopen 7 hari kerja dan batas count | TBD | |
| AC-17 | `TicketManagementTest.php`, `TicketTriageTest.php`; cancel/reject | TBD | |
| AC-18 | `TicketResolutionTest.php` dan SLA service tests; jam kerja/pause/cycle | TBD | |
| AC-19 | `DatabaseChangeControlTest.php`; tiga bukti SVC-03 wajib berbeda | TBD | |
| AC-20 | `DatabaseChangeControlTest.php`, `TicketResolutionTest.php`; gate service control | TBD | |
| AC-21 | `TicketApprovalTest.php`, `TicketCommunicationTest.php`; notifikasi in-app | TBD | |
| AC-22 | `DashboardTest.php`, `MonthlyReportTest.php`; role scope dan Excel/PDF | TBD | |
| AC-23 | `Admin/*ManagementTest.php`, `OperationalPolicyManagementTest.php`; before/after audit | TBD | |
| AC-24 | `RoleAuthorizationTest.php`, `AuditAndRetentionTest.php`; negative access/file test | TBD | |
| AC-25 | `AuditAndRetentionTest.php`; append-only model dan role database | TBD | |
| AC-26 | `tests/performance/k6.js`; 300 tiket aktif, 30 VU, P95 sesuai target | TBD | |
| AC-27 | `deploy/backup/restore-drill.sh`; physical base + WAL, RPO <= 1 jam, RTO <= 4 jam | TBD | |
| AC-28 | access matrix dan `TicketCommunicationTest.php`; Ketua read-only tanpa internal/private | TBD | |

## 4. Security, access, concurrency, file, audit, export

Catat hasil terpisah untuk:

- security test: HTTPS, `APP_DEBUG=false`, CSRF, rate limit login, password policy, dependency audit, header/cookie, dan least-privilege database;
- access matrix: setiap role diuji untuk dashboard, ticket, comment, attachment, approval, report, dan admin action;
- concurrency: dua claim dan allocator nomor tiket bersamaan; tidak boleh ada double claim atau nomor ulang;
- file access: requester, agent, approver, ketua, dan akun tanpa hak mencoba URL download; private disk tidak boleh punya URL publik;
- audit immutability: update/delete UI dan SQL role aplikasi ditolak, denied action tetap ada setelah rollback transaksi;
- export: kolom minimum, filter periode, Excel, PDF, retensi hasil export, dan audit download.

## 5. Data master dan sign-off

Sebelum UAT, lampirkan versi dan pemilik untuk daftar user/NIP/peran, enam tim dan ketua, lokasi, service catalog, kategori/skill, SLA/calendar/holiday, kebijakan lampiran, notification matrix, contoh spreadsheet laporan lama, privacy/retention policy, serta infrastruktur backup.

Sebelum pilot, lampirkan daftar peserta, materi pelatihan, support window, escalation contact, baseline performance, backup/restore evidence, dan rencana pembersihan data uji.

Keputusan akhir:

| Peran | Nama | Keputusan | Timestamp | Tanda tangan/link |
|---|---|---|---|---|
| Pemilik produk | TBD | Go/No-go | TBD | |
| Pemilik operasional | TBD | Go/No-go | TBD | |
| Infrastruktur | TBD | Go/No-go | TBD | |
| Keamanan/privacy | TBD | Go/No-go | TBD | |
