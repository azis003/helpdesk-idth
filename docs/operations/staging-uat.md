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

Untuk menghasilkan bundle evidence dengan status, timestamp, owner, log, dan exit code yang konsisten, jalankan `bash deploy/scripts/verify-go-live.sh` sesuai [go-live-evidence.md](go-live-evidence.md). Perintah dijalankan pada staging dengan environment PostgreSQL/Redis target. CI menjalankan subset yang sama pada `.github/workflows/ci.yml`.

## 3. Matriks AC-01 sampai AC-28

Isi kolom status dengan `Lulus`, `Gagal`, atau `Deviasi`; sertakan link bukti dan owner. Semua skenario wajib menggunakan data master staging yang telah disahkan.

Snapshot matrix berikut dicatat pada `2026-08-07T20:51:19+07:00`. `Lulus` berarti automated baseline lulus; `Deviasi` berarti evidence staging/operasional masih wajib dilampirkan. Timestamp akan diperbarui pada saat UAT staging.

| ID | Bukti otomatis/aktivitas | Status | Bukti, owner, timestamp, keputusan tindak lanjut |
|---|---|---|---|
| AC-01 | `tests/Feature/Auth/AuthenticationTest.php`; password awal dapat langsung memakai aplikasi sesuai role | Lulus | Automated baseline; owner QA; `2026-08-07T20:51:19+07:00`; ulangi pada PostgreSQL/Redis staging. |
| AC-02 | `tests/Feature/Authorization/RoleAuthorizationTest.php`; Super Admin tanpa role operasional | Lulus | Automated baseline; owner QA/Keamanan; `2026-08-07T20:51:19+07:00`; lampirkan negative authorization staging. |
| AC-03 | `tests/Feature/Admin/CatalogManagementTest.php`, `TicketManagementTest.php`; field dinamis dan lokasi | Lulus | Automated baseline; owner QA; `2026-08-07T20:51:19+07:00`; cocokkan dengan master data disahkan. |
| AC-04 | `TicketManagementTest.php`; requester/creator/snapshot/self-created | Lulus | Automated baseline; owner QA; `2026-08-07T20:51:19+07:00`; lakukan satu UAT atas nama pegawai. |
| AC-05 | `TicketManagementTest.php` dan worker allocator PostgreSQL | Deviasi | Test no-reuse lulus, worker concurrency PostgreSQL terskip pada SQLite; owner QA/Infra; `2026-08-07T20:51:19+07:00`; jalankan worker bersamaan di staging dan lampirkan output. |
| AC-06 | `TicketManagementTest.php`, `DashboardTest.php`; cakupan pemohon | Lulus | Automated baseline; owner QA; `2026-08-07T20:51:19+07:00`; verifikasi dengan akun pemohon staging. |
| AC-07 | `TicketTriageTest.php`; dua klaim dan audit denied | Deviasi | Skenario sequential lulus, dua proses PostgreSQL bersamaan belum dijalankan sebagai UAT; owner QA/Infra; `2026-08-07T20:51:19+07:00`; lampirkan dua worker dan audit denied. |
| AC-08 | `TicketTriageTest.php`; hasil triase dan alasan penolakan | Lulus | Automated baseline; owner PIC helpdesk; `2026-08-07T20:51:19+07:00`; sampling UAT tiga hasil. |
| AC-09 | `TicketTriageTest.php`; saran skill dan keputusan manual | Lulus | Automated baseline; owner PIC helpdesk; `2026-08-07T20:51:19+07:00`; cocokkan mapping skill master. |
| AC-10 | `TicketApprovalTest.php`; approval, restore state, self-approval | Lulus | Automated baseline; owner Pemilik proses; `2026-08-07T20:51:19+07:00`; validasi approver aktif staging. |
| AC-11 | `TicketCommunicationTest.php`; balasan publik vs catatan internal | Lulus | Automated baseline; owner QA/Keamanan; `2026-08-07T20:51:19+07:00`; lampirkan uji kebocoran requester. |
| AC-12 | `TicketCommunicationTest.php`, scheduler tests; requester wait dan timeout | Lulus | Automated baseline; owner PIC helpdesk; `2026-08-07T20:51:19+07:00`; simulasi kalender staging. |
| AC-13 | `TicketCommunicationTest.php`; third-party wait dan resume | Lulus | Automated baseline; owner PIC helpdesk; `2026-08-07T20:51:19+07:00`; simulasi pihak ketiga pada UAT. |
| AC-14 | `TicketResolutionTest.php`; solusi, konfirmasi, auto-close | Lulus | Automated baseline; owner PIC helpdesk; `2026-08-07T20:51:19+07:00`; verifikasi timeout dari scheduler staging. |
| AC-15 | `TicketResolutionTest.php`; reopen karena hasil belum sesuai | Lulus | Automated baseline; owner QA; `2026-08-07T20:51:19+07:00`; UAT dengan alasan wajib. |
| AC-16 | `TicketResolutionTest.php`; reopen 7 hari kerja dan batas count | Lulus | Automated baseline; owner PIC helpdesk; `2026-08-07T20:51:19+07:00`; verifikasi tanggal kalender staging. |
| AC-17 | `TicketManagementTest.php`, `TicketTriageTest.php`; cancel/reject | Lulus | Automated baseline; owner QA; `2026-08-07T20:51:19+07:00`; sampling cancel/reject dengan alasan. |
| AC-18 | `TicketResolutionTest.php` dan SLA service tests; jam kerja/pause/cycle | Lulus | Automated baseline; owner PIC helpdesk; `2026-08-07T20:51:19+07:00`; verifikasi holiday dan pause pada calendar staging. |
| AC-19 | `DatabaseChangeControlTest.php`; tiga bukti SVC-03 berbeda | Lulus | Automated baseline; owner Keamanan/DBA; `2026-08-07T20:51:19+07:00`; lampirkan evidence tipe berbeda pada staging. |
| AC-20 | `DatabaseChangeControlTest.php`, `TicketResolutionTest.php`; gate service control | Lulus | Automated baseline; owner PIC helpdesk; `2026-08-07T20:51:19+07:00`; rekonsiliasi result SVC-02/SVC-03. |
| AC-21 | `TicketApprovalTest.php`, `TicketCommunicationTest.php`; notifikasi in-app | Lulus | Automated baseline; owner PIC helpdesk; `2026-08-07T20:51:19+07:00`; cek penerima notification matrix staging. |
| AC-22 | `DashboardTest.php`, `MonthlyReportTest.php`; role scope dan Excel/PDF | Lulus | Automated baseline; owner Pemilik operasional; `2026-08-07T20:51:19+07:00`; lampirkan export reconciliation. |
| AC-23 | `Admin/*ManagementTest.php`, `OperationalPolicyManagementTest.php`; before/after audit | Lulus | Automated baseline; owner Super Admin/QA; `2026-08-07T20:51:19+07:00`; sampling perubahan master staging. |
| AC-24 | `RoleAuthorizationTest.php`, `AuditAndRetentionTest.php`; negative access/file test | Lulus | Automated baseline; owner Keamanan; `2026-08-07T20:51:19+07:00`; jalankan seluruh role matrix di staging. |
| AC-25 | `AuditAndRetentionTest.php`; append-only model dan role database | Lulus | Model/UI baseline lulus; owner DBA/Keamanan; `2026-08-07T20:51:19+07:00`; wajib uji SQL role aplikasi PostgreSQL. |
| AC-26 | `tests/performance/k6.js`; 300 tiket aktif, 30 VU, P95 sesuai target | Deviasi | Output k6 staging dan metadata volume belum tersedia; owner Infra/QA; `2026-08-07T20:51:19+07:00`; no-go sampai P95 list <2s, create <1s, error <1%. |
| AC-27 | `deploy/backup/restore-drill.sh`; physical base + WAL, RPO <= 1 jam, RTO <= 4 jam | Deviasi | Arsip backup/restore staging belum tersedia; owner Infra/Backup; `2026-08-07T20:51:19+07:00`; no-go sampai RPO/RTO dan private storage evidence lulus. |
| AC-28 | `TeamChairAccessTest.php`, access matrix; Ketua read-only tanpa internal/private | Lulus | Automated baseline; owner QA/Keamanan; `2026-08-07T20:51:19+07:00`; ulangi direct URL/private file test di staging. |

## 4. Security, access, concurrency, file, audit, export

Catat hasil terpisah untuk hal berikut. Format eksekusi dan negative case ada di [access-matrix.md](access-matrix.md); hasil snapshot saat ini dirangkum di [go-live-evidence.md](go-live-evidence.md).

- security test: HTTPS, `APP_DEBUG=false`, CSRF, rate limit login, password policy, dependency audit, header/cookie, dan least-privilege database;
- access matrix: setiap role diuji untuk dashboard, ticket, comment, attachment, approval, report, dan admin action;
- concurrency: dua claim dan allocator nomor tiket bersamaan; tidak boleh ada double claim atau nomor ulang;
- file access: requester, agent, approver, ketua, dan akun tanpa hak mencoba URL download; private disk tidak boleh punya URL publik;
- audit immutability: update/delete UI dan SQL role aplikasi ditolak, denied action tetap ada setelah rollback transaksi;
- export: kolom minimum, filter periode, Excel, PDF, retensi hasil export, dan audit download.

| Kontrol | Status snapshot | Owner | Timestamp | Keputusan tindak lanjut |
|---|---|---|---|---|
| HTTPS, `APP_DEBUG=false`, CSRF, rate limit, password, header/cookie | Deviasi | Infrastruktur/Keamanan | `2026-08-07T20:51:19+07:00` | Security headers automated lulus; jalankan domain staging, cookie, dan dependency review. |
| Access matrix dan negative authorization | Deviasi | QA/Keamanan | `2026-08-07T20:51:19+07:00` | Automated baseline ada; ulangi dengan data master staging. |
| File access/private storage | Deviasi | Keamanan/Infra | `2026-08-07T20:51:19+07:00` | Uji direct URL dan seluruh role pada storage staging. |
| Audit immutability | Deviasi | DBA/Keamanan | `2026-08-07T20:51:19+07:00` | Uji UI/policy dan SQL role aplikasi PostgreSQL. |
| Export reconciliation | Deviasi | Pemilik operasional | `2026-08-07T20:51:19+07:00` | Bandingkan dengan spreadsheet acuan dan simpan checksum/approval. |
| Concurrency claim dan nomor tiket | Deviasi | QA/Infra | `2026-08-07T20:51:19+07:00` | Jalankan worker PostgreSQL bersamaan; tidak boleh double claim/number reuse. |

## 5. Data master dan sign-off

Sebelum UAT, lampirkan versi dan pemilik untuk daftar user/NIP/peran, enam tim dan ketua, lokasi, service catalog, kategori/skill, SLA/calendar/holiday, kebijakan lampiran, notification matrix, contoh spreadsheet laporan lama, privacy/retention policy, serta infrastruktur backup. Format minimum approval data master dan kontrol operasional ada di [go-live-controls.md](go-live-controls.md).

Sebelum pilot, lampirkan daftar peserta, materi pelatihan, support window, escalation contact, baseline performance, backup/restore evidence, dan rencana pembersihan data uji. Gunakan [go-live-signoff.md](go-live-signoff.md) sebagai lembar keputusan.

Keputusan akhir:

| Peran | Nama | Keputusan | Timestamp | Tanda tangan/link |
|---|---|---|---|---|
| Pemilik produk | TBD | No-go sementara | `2026-08-07T20:51:19+07:00` | `go-live-signoff.md` |
| Pemilik operasional | TBD | No-go sementara | `2026-08-07T20:51:19+07:00` | `go-live-signoff.md` |
| Infrastruktur | TBD | No-go sementara | `2026-08-07T20:51:19+07:00` | `go-live-signoff.md` |
| Keamanan/privacy | TBD | No-go sementara | `2026-08-07T20:51:19+07:00` | `go-live-signoff.md` |
