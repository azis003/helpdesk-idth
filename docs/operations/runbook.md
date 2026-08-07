# Runbook deployment dan operasional SIHATI

## 1. Batasan dan topologi

Deployment referensi menggunakan satu atau lebih container aplikasi Laravel dengan komponen berikut:

| Komponen | Tanggung jawab | Kriteria sehat |
|---|---|---|
| Caddy | Terminasi HTTPS dan reverse proxy | Sertifikat valid, domain mengarah ke host, port 80/443 terbuka |
| Nginx | Static asset dan FastCGI ke PHP-FPM | `GET /up` mengembalikan HTTP 200 |
| PHP-FPM | Request web Laravel | `php artisan migrate:status` berhasil |
| Queue worker | Notifikasi dan pekerjaan asinkron | Proses dikelola supervisor/container restart policy |
| Scheduler | `schedule:work`, retensi, heartbeat, storage check | cache heartbeat berumur di bawah 180 detik |
| PostgreSQL | Data bisnis, audit, queue/session bila dipilih | `pg_isready`, koneksi aplikasi, backup penuh dan WAL |
| Redis | Queue, cache lock, session, rate limit | `redis-cli ping`, cache probe berhasil |
| Private storage | Lampiran tiket dan bukti operasional | tidak dipetakan ke public web, backup file berhasil |

Topologi referensi dapat dijalankan dengan `deploy/docker-compose.production.yml`. Caddy hanya dipakai jika host menerima HTTPS langsung; bila organisasi sudah memiliki load balancer/ingress, gunakan service `web` di belakang ingress dan pastikan `APP_URL` tetap HTTPS.

## 2. Preflight sebelum staging

1. Sediakan domain, DNS, sertifikat/ingress, PostgreSQL 16 atau layanan PostgreSQL yang kompatibel, Redis 7 atau layanan Redis yang kompatibel, dan private storage persisten.
2. Salin `.env.production.example` menjadi `.env.production`. Isi `APP_KEY`, domain, kredensial database/Redis, lokasi status backup, dan secret lain melalui secret manager.
3. Pastikan `APP_DEBUG=false`, `APP_URL=https://...`, `SESSION_SECURE_COOKIE=true`, `QUEUE_CONNECTION=redis`, dan `CACHE_STORE=redis`.
4. Batasi akses PostgreSQL, Redis, backup, dan private storage hanya dari jaringan aplikasi/operasional. Jangan masukkan secret ke Git.
5. Buat role database aplikasi terpisah dari owner migrasi. Migrasi terakhir mencabut hak `UPDATE` dan `DELETE` pada `audit_logs` bila `DB_APP_ROLE` diisi.
6. Tetapkan owner untuk infra, aplikasi, backup, keamanan, data master, UAT, support window, dan keputusan go/no-go.

## 3. Deploy staging atau produksi

Dari root repository:

```bash
cp .env.production.example .env.production
# Isi secret dan domain melalui secret manager/editor yang disetujui
mkdir -p /opt/sihati/backups
docker compose --env-file .env.production -f deploy/docker-compose.production.yml build
docker compose --env-file .env.production -f deploy/docker-compose.production.yml up -d db redis
# Migrasi dijalankan dengan owner DB; role aplikasi tetap least-privilege.
docker compose --env-file .env.production -f deploy/docker-compose.production.yml run --rm \
  -e DB_USERNAME="$POSTGRES_SUPERUSER" -e DB_PASSWORD="$POSTGRES_SUPERPASSWORD" \
  app php artisan migrate --force
docker compose --env-file .env.production -f deploy/docker-compose.production.yml exec -T db \
  psql --username "$POSTGRES_SUPERUSER" --dbname "$POSTGRES_DB" --set=app_role="$DB_USERNAME" \
  < deploy/postgres/grant-app-privileges.sql
docker compose --env-file .env.production -f deploy/docker-compose.production.yml up -d app web worker scheduler https
```

Pada PostgreSQL terkelola, jalankan migrasi menggunakan owner database, lalu berikan `USAGE` schema, DML pada tabel, dan `USAGE/SELECT` pada sequence kepada `DB_USERNAME` sesuai kebijakan. Script `deploy/postgres/grant-app-privileges.sh` mempertahankan revoke `UPDATE/DELETE` pada `audit_logs`. Pada Docker Compose, nilai `POSTGRES_SUPERUSER` adalah owner bootstrap; script init membuat role aplikasi, tetapi grant tabel/sequence dijalankan setelah migrasi sesuai prosedur database organisasi. `BACKUP_STATUS_HOST_PATH` harus menunjuk direktori host yang ditulis backup runner dan dibaca container secara read-only.

Validasi sesudah deploy:

```bash
docker compose --env-file .env.production -f deploy/docker-compose.production.yml ps
docker compose --env-file .env.production -f deploy/docker-compose.production.yml exec app php artisan sihati:ops:health --json
curl --fail --silent https://helpdesk.example.org/up
curl --fail --silent https://helpdesk.example.org/health/ready
docker compose --env-file .env.production -f deploy/docker-compose.production.yml exec app php artisan schedule:list
docker compose --env-file .env.production -f deploy/docker-compose.production.yml exec app php artisan queue:failed
```

`/up` adalah liveness probe. `/health/ready` memeriksa database, cache, queue, private storage, scheduler heartbeat, dan status backup. Pada produksi, readiness baru boleh hijau setelah backup status file dan heartbeat scheduler tersedia.

## 4. Queue dan scheduler

- Jalankan tepat satu scheduler per environment aktif. `schedule:work` menjalankan heartbeat setiap menit dan pemeriksaan storage setiap jam.
- Jalankan worker dengan process manager/container restart policy. Worker referensi memakai `--tries=3`, `--timeout=120`, `--max-time=3600`, dan `--max-jobs=1000` agar proses dirotasi.
- `queue:monitor` terjadwal menulis peringatan ke log `ops` ketika kedalaman queue melewati `OPS_QUEUE_MAX_DEPTH`.
- Periksa kegagalan dengan `php artisan queue:failed`; setelah penyebab diperbaiki, gunakan `queue:retry` sesuai prosedur perubahan.
- Jangan mengaktifkan lebih dari satu scheduler tanpa cache lock terdistribusi. Redis adalah pilihan produksi untuk lock tersebut.

## 5. Backup dan restore

Backup harus berjalan di luar request aplikasi dan menyimpan salinan off-host/object storage.

Contoh jadwal host backup:

```cron
0 1 * * * bash /opt/sihati/deploy/backup/backup-full.sh >> /var/log/sihati-backup.log 2>&1
10 1 * * * bash /opt/sihati/deploy/backup/backup-files.sh >> /var/log/sihati-backup.log 2>&1
*/15 * * * * bash /opt/sihati/deploy/backup/check-wal-age.sh >> /var/log/sihati-backup.log 2>&1
```

Set minimal `PGHOST`, `PGDATABASE`, `PGUSER`, `PGPASSWORD` melalui secret manager, `PRIVATE_STORAGE_ROOT`, `BACKUP_ROOT`, `WAL_ARCHIVE_DIR`, dan `BACKUP_STATUS_FILE` (contoh host: `/opt/sihati/backups/backup-status.json`). Jika memakai object storage, isi `BACKUP_S3_URI` dan kredensial AWS melalui environment. Script menulis checksum dan status JSON secara atomic; aplikasi membaca status tersebut melalui `OPS_BACKUP_STATUS_FILE` (contoh container: `/var/lib/sihati/backups/backup-status.json`).

Host backup perlu menyediakan `pg_basebackup`, `pg_ctl`, `psql`, `tar`, `sha256sum`, dan `jq`; `aws` CLI diperlukan bila tujuan off-host memakai S3. Role backup PostgreSQL harus memiliki hak replication/base-backup sesuai kebijakan, dan WAL archive harus berada pada storage durable yang ikut disalin off-host.

Target operasional:

- backup PostgreSQL penuh minimal sekali setiap 24 jam;
- WAL terarsip dan terverifikasi maksimal berumur 3.600 detik;
- private storage ikut dicadangkan, dengan checksum dan salinan off-host;
- restore drill ke database bernama `sihati_restore_*` minimal sebelum go-live dan berkala setelahnya;
- bukti restore memuat timestamp, durasi, dump, arsip lampiran, dan hasil verifikasi.

Jalankan restore drill hanya pada database dan direktori restore terisolasi:

```bash
RESTORE_BASE_ARCHIVE=/var/backups/sihati/full/sihati-base-<timestamp>.tar.gz \
RESTORE_FILES_ARCHIVE=/var/backups/sihati/files/sihati-private-<timestamp>.tar.gz \
RESTORE_PGDATA=/var/lib/sihati/restore/sihati-pgdata-20260807 \
RESTORE_STORAGE_ROOT=/var/lib/sihati/restore/20260807 \
RESTORE_WAL_ARCHIVE_DIR=/var/backups/sihati/wal \
RESTORE_DATABASE=sihati \
RESTORE_EVIDENCE_FILE=/var/backups/sihati/evidence/restore-20260807.json \
bash deploy/backup/restore-drill.sh
```

Backup penuh memakai `pg_basebackup` physical base backup, bukan hanya logical dump, sehingga WAL archive dapat dipakai untuk PITR. Restore drill menyalakan cluster pada port terisolasi, menjalankan smoke query, memulihkan private storage, lalu menulis RPO/RTO dan jumlah tiket/lampiran ke evidence JSON. Kegagalan backup atau restore adalah keputusan go/no-go, bukan warning yang boleh diabaikan.

## 6. Monitoring dan retensi

Periksa minimal:

```bash
php artisan sihati:ops:health --json
php artisan sihati:ops:check-storage --json
php artisan queue:failed
tail -f storage/logs/ops-$(date +%F).log
```

Ambang storage privat:

- `>= 80%`: status warning dan notifikasi in-app ke Super Admin;
- `>= 90%`: status critical dan readiness gagal bila `OPS_REQUIRE_STORAGE_CHECK=true`;
- object storage yang tidak menyediakan kapasitas portable harus memakai alarm provider storage, sementara command aplikasi melaporkan status unknown.

Log aplikasi dan log operasional diputar harian dan dibersihkan oleh scheduler setelah minimal 30 hari. Audit log tidak dihapus oleh pembersihan log aplikasi; kontrol retensi audit mengikuti kebijakan data dan database.

## 7. Incident response dan rollback

### Aplikasi gagal sehat

1. Cek `/up`, `/health/ready`, `sihati:ops:health`, log `ops`, status container, dan koneksi PostgreSQL/Redis.
2. Jika hanya worker bermasalah, hentikan retry berulang, perbaiki penyebab, lalu restart worker dan periksa `queue:failed`.
3. Jika migrasi atau release bermasalah, aktifkan maintenance mode pada load balancer/aplikasi, pertahankan bukti log, dan kembalikan image ke release sebelumnya. Jangan menjalankan `migrate:rollback` pada produksi tanpa rencana perubahan dan backup yang disetujui.
4. Jika data rusak atau kehilangan storage, eskalasi ke owner backup dan jalankan restore drill/restore produksi sesuai persetujuan insiden. Catat RPO/RTO aktual.

### Deployment gagal

1. Tahan traffic baru dan pertahankan release sebelumnya bila health check gagal.
2. Simpan output `docker compose ps`, `sihati:ops:health --json`, migrasi, dan log untuk post-incident review.
3. Untuk perubahan skema yang irreversible, gunakan prosedur restore dan change control; tidak ada rollback otomatis yang menghapus data.

## 8. Pilot dan go-live

Sebelum pilot, lengkapi data master aktual, konfigurasi operasional, privacy/retention sign-off, matriks akses, pelatihan pengguna inti, support window, serta daftar kontak eskalasi. Pilot harus memakai kelompok pengguna dan data yang disetujui, dengan monitoring error, SLA, audit, queue, backup, storage, dan feedback.

Go-live hanya boleh diputuskan jika:

- seluruh AC-01 sampai AC-28 lulus atau memiliki deviasi tertulis dengan owner dan tanggal kedaluwarsa;
- security test, access matrix test, concurrency test, file access test, audit immutability test, dan export test memiliki bukti;
- target performance terpenuhi pada environment target atau ada persetujuan deviasi;
- full backup, WAL, private storage backup, dan restore drill memenuhi RPO maksimal satu jam serta RTO maksimal empat jam;
- support window, runbook, rollback/restore contact, dan komunikasi pengguna sudah diuji.

Template bukti dan matriks UAT tersedia di [staging-uat.md](staging-uat.md).
