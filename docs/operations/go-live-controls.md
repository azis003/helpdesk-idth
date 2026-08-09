# Kontrol privacy, retention, password, training, dan support

Dokumen ini adalah lampiran operasional issue #20. Nilai owner, kontak, tanggal approval, dan support window harus diisi oleh organisasi sebelum keputusan `Go`.

## Privacy dan retention

| Kontrol | Baseline SIHATI | Evidence/owner | Status snapshot |
|---|---|---|---|
| Data pribadi | NIP, nama, tim, akun, komentar, dan lampiran hanya tampil sesuai role; audit menyamarkan password/token/email/NIP sensitif. | Review privacy owner + `AuditAndRetentionTest.php` | Lulus otomatis; approval privacy staging belum dilampirkan. |
| Tiket terminal | Retensi default 5 tahun setelah tiket terminal. | `RETENTION_TICKET_YEARS`, retention job, owner data | Deviasi sampai policy organisasi disahkan. |
| Hasil export SVC-02 | Lampiran `data_export_result` dihapus setelah 90 hari sesuai policy, metadata audit tetap dipertahankan. | `RETENTION_DATA_EXPORT_DAYS`, `AttachmentRetentionService` | Lulus otomatis; jadwal dan approval staging belum dilampirkan. |
| Log aplikasi | Diputar dan dibersihkan setelah minimal 30 hari; audit log tidak ikut dihapus oleh pembersihan log aplikasi. | `RETENTION_APPLICATION_LOG_DAYS`, runbook | Lulus baseline; owner operasi belum ditandatangani. |
| Private storage | Lampiran tidak disajikan dari URL publik; akses melalui controller authorization dan audit. | `ATTACHMENT_PRIVATE_DISK`, file access evidence | Lulus otomatis; direct URL staging belum dilampirkan. |

## SOP password awal dan reset

1. Super Admin membuat atau mereset akun melalui proses resmi; password sementara dibagikan melalui kanal out-of-band yang disetujui, bukan melalui Git, chat umum, atau log.
2. Akun password awal dapat langsung memakai fitur sesuai role; penggantian password tidak dipaksa saat login pertama.
3. Password baru memenuhi minimal 12 karakter dengan huruf besar, kecil, angka, dan simbol. Password disimpan hanya sebagai hash.
4. Reset tidak boleh dilakukan oleh pengguna untuk dirinya sendiri melalui prosedur admin. Setelah reset, pengguna dapat mengganti password secara mandiri bila diperlukan.
5. Insiden kredensial dicatat, akun dinonaktifkan bila perlu, sesi aktif diputus, dan audit/log yang mengandung secret tidak boleh dipertahankan.

Evidence: `tests/Feature/Auth/AuthenticationTest.php`, `tests/Feature/Auth/PasswordResetTest.php`, dan `tests/Feature/Authorization/RoleAuthorizationTest.php`.

## Training dan support window

| Persona | Materi minimum | Owner | Jadwal/status |
|---|---|---|---|
| Pemohon | Login pertama, membuat tiket, membalas permintaan informasi, konfirmasi, buka kembali | Pemilik operasional | Deviasi — isi tanggal dan daftar peserta |
| Agen Tier 1/2 | Claim, triase, SLA pause, komunikasi publik/internal, approval, kontrol SVC-02/SVC-03 | PIC helpdesk | Deviasi — isi tanggal dan daftar peserta |
| Approver/Manajer TI | Daftar approval, approve/reject, self-approval yang dibatasi | Pemilik proses | Deviasi — isi tanggal dan daftar peserta |
| Super Admin | Master data, branding, policy, user/role, audit, backup/readiness | Pemilik aplikasi | Deviasi — isi tanggal dan daftar peserta |
| Ketua Tim | Projection read-only, public update, batasan akses internal/private | Pemilik operasional | Deviasi — isi tanggal dan daftar peserta |

Support window wajib mencantumkan timezone Asia/Jakarta, jam aktif, kanal eskalasi, severity, target respons, dan kontak pengganti. Runbook teknis ada di [runbook.md](runbook.md); insiden dan restore harus memiliki owner serta nomor perubahan.

## Kontak yang harus diisi sebelum Go

| Fungsi | Nama | Kanal | Pengganti | Approved at |
|---|---|---|---|---|
| Pemilik produk | TBD | TBD | TBD | TBD |
| PIC operasional/helpdesk | TBD | TBD | TBD | TBD |
| Infrastruktur/backup | TBD | TBD | TBD | TBD |
| Keamanan/privacy | TBD | TBD | TBD | TBD |
| Support window | TBD | TBD | TBD | TBD |
