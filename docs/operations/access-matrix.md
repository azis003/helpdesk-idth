# Access matrix dan bukti negative authorization

Matriks hak normatif tetap berada di [PRD bagian 2.3](../PRD-helpdesk-internal.md#23-matriks-hak-akses). Dokumen ini menetapkan cara eksekusi dan evidence untuk issue #20; perubahan hak harus mengubah PRD dan test yang relevan.

## Cakupan uji per peran

| Area | Super Admin | Pemohon | Agen Tier 1 | Agen Tier 2 | Approver aktif | Ketua Tim Kerja |
|---|---|---|---|---|---|---|
| Login, logout, ganti password, akun nonaktif | Ya | Ya | Ya | Ya | Ya | Ya |
| Kelola user, role, tim, skill, katalog, lokasi, policy, branding, audit | Ya | Tolak | Tolak | Tolak | Tolak | Tolak |
| Buat tiket sendiri | Sesuai role operasional tambahan | Milik sendiri | Hanya bila memiliki role Pemohon | Hanya bila memiliki role Pemohon | Hanya bila memiliki role Pemohon | Hanya bila memiliki role Pemohon |
| Buat tiket atas nama pegawai | Hanya bila juga Tier 1 | Tolak | Ya | Tolak | Tolak | Tolak |
| Lihat tiket | Cakupan administrasi | Milik sendiri | Queue dan assigned | Assigned | Permintaan persetujuan | Anggota tim |
| Catatan internal dan lampiran internal | Sesuai authorization | Tolak | Tiket berwenang | Assigned | Tiket yang diputuskan | Tolak |
| Klaim/triase/tangani | Hanya dengan role operasional tambahan | Tolak | Ya | Assigned sesuai alur | Tolak | Tolak |
| Approval | Tolak sebagai Super Admin saja | Tolak | Tolak | Tolak | Approver aktif | Tolak |
| Laporan dan export | Ya | Tolak | Sesuai hak report | Tolak | Ya bila aktif | Sesuai policy |

## Skenario negative yang wajib direkam

| Skenario | Expected result | Bukti otomatis |
|---|---|---|
| Super Admin tanpa role operasional mencoba claim/handle | HTTP 403, tidak ada perubahan tiket, audit `denied` | `tests/Feature/Authorization/RoleAuthorizationTest.php` |
| Pemohon membuka tiket orang lain | HTTP 403/404 tanpa kebocoran data | `tests/Feature/TicketManagementTest.php` |
| Ketua Tim mencoba write, catatan internal, atau download lampiran | HTTP 403/404; projection hanya public/read-only | `tests/Feature/TeamChairAccessTest.php` |
| Akun tanpa hak membuka URL lampiran atau disk public | Ditolak dan audit penolakan | `tests/Feature/AuditAndRetentionTest.php` |
| Pengguna non-admin membuka endpoint administrasi | HTTP 403 | `tests/Feature/Authorization/RoleAuthorizationTest.php`, `tests/Feature/Admin/*ManagementTest.php` |
| Approver tidak aktif atau akun password awal mencoba keputusan | Ditolak dan tidak mengubah status | `tests/Feature/TicketApprovalTest.php` |
| Audit log di-update/delete oleh aplikasi | Exception append-only; SQL role aplikasi juga harus ditolak di staging | `tests/Feature/AuditAndRetentionTest.php`, `deploy/postgres/grant-app-privileges.sql` |

## Status baseline

Automated baseline pada `2026-08-07T20:51:19+07:00` lulus untuk skenario yang dapat dijalankan pada SQLite test runner. Uji server staging dengan PostgreSQL, Redis, data master yang disahkan, dan direct URL storage tetap merupakan gate UAT; hasilnya harus dilampirkan ke bundle evidence dan tidak boleh diganti dengan hasil lokal.
