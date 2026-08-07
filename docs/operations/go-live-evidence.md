# Paket evidence technical verification dan go-live

Dokumen ini adalah indeks evidence untuk issue #20. Evidence yang dihasilkan pada satu environment tidak boleh dipakai untuk menyimpulkan environment lain; setiap artefak harus menyebutkan environment, commit/image tag, timestamp, konfigurasi tanpa secret, owner, dan keputusan.

## Snapshot baseline repository

Snapshot berikut diambil pada `2026-08-07T20:51:19+07:00` dari branch `development` sebelum verifikasi staging tersedia.

| Pemeriksaan | Hasil | Evidence | Catatan |
|---|---|---|---|
| Composer manifest | Lulus | `composer validate --strict` | `composer.json` valid. |
| Format PHP | Lulus | `vendor/bin/pint --test` | Tidak ada perubahan format. |
| Feature/unit test | Lulus | `php artisan test --testdox` | 97 passed, 871 assertions, 1 skipped dari 98 test; worker concurrency PostgreSQL terskip pada runner lokal yang memakai SQLite. |
| Security response headers | Lulus | `tests/Feature/SecurityHeadersTest.php` | `nosniff`, frame/referrer/permissions policy, CSP, dan HSTS untuk staging/production HTTPS diverifikasi. |
| Frontend build | Lulus | `npm run build` | Vite production build berhasil. |
| Config/route/view cache | Lulus | Perintah cache Laravel | Cache berhasil dibuat. |
| Health lokal | Deviasi | `php artisan sihati:ops:health --json` | Database/cache/queue/storage sehat, tetapi heartbeat scheduler dan status backup belum dikonfigurasi. Ini bukan evidence staging. |
| Performance 300 tiket/30 VU | Deviasi | `tests/performance/k6.js` | Endpoint staging, data master, output k6, dan metadata environment belum dilampirkan. |
| Restore drill | Deviasi | `deploy/backup/restore-drill.sh` | Arsip physical base, WAL, private storage, dan evidence restore belum tersedia pada workspace. |
| Sign-off | Deviasi | `go-live-signoff.md` | Menunggu pemilik produk, operasional, infrastruktur, dan keamanan. |

Snapshot ini sengaja tidak menyatakan aplikasi siap go-live. Status `Deviasi` adalah kontrol agar evidence lokal tidak disalahartikan sebagai hasil UAT/staging.

## Cara mengumpulkan evidence yang dapat diaudit

Jalankan pada host staging yang sudah memakai data master yang disahkan. Jangan menaruh secret di output atau commit.

```bash
set -a
source .env.staging
set +a

GO_LIVE_TEST_DATABASE=sihati_verification \
GO_LIVE_EVIDENCE_DIR="storage/app/private/evidence/go-live/$(date -u +%Y%m%dT%H%M%SZ)" \
  bash deploy/scripts/verify-go-live.sh
```

`GO_LIVE_TEST_DATABASE` harus merupakan database uji terisolasi yang berbeda dari `DB_DATABASE`; runner tidak pernah menjalankan `RefreshDatabase` terhadap database aplikasi staging/production. Runner menjalankan validasi manifest, format, test, dependency frontend, build, cache, migrasi, health/readiness, storage, dan scheduler. Runner juga fail-closed terhadap bukti yang belum ada:

- `GO_LIVE_MASTER_DATA_EVIDENCE` — JSON approval data master minimal 150 user, 6 tim, dan 7 layanan;
- `GO_LIVE_PERFORMANCE_SUMMARY` dan `GO_LIVE_PERFORMANCE_METADATA` — output k6 serta metadata staging minimal 300 tiket aktif dan 30 VU;
- `GO_LIVE_RESTORE_EVIDENCE` — output `restore-drill.sh` dengan `rpo_passed=true` dan `rto_passed=true`;
- `GO_LIVE_SIGNOFF_FILE` — JSON sign-off yang menyatakan `Go` dan memiliki approval `product`, `operations`, `infrastructure`, serta `security` bernilai `true`.

Contoh metadata performance dan sign-off (isi dengan nilai nyata, jangan memakai contoh ini sebagai evidence):

```json
{
  "environment": "staging",
  "active_ticket_count": 300,
  "vus": 30,
  "error_rate": 0.0,
  "base_url": "https://staging-helpdesk.example.org",
  "commit": "<image-or-commit-tag>",
  "tested_at": "2026-08-07T00:00:00Z"
}
```

```json
{
  "environment": "staging",
  "decision": "Go",
  "approvals": {
    "product": true,
    "operations": true,
    "infrastructure": true,
    "security": true
  }
}
```

Exit code runner:

| Kode | Arti |
|---:|---|
| `0` | Semua pemeriksaan dan evidence memenuhi gate. |
| `1` | Ada pemeriksaan teknis yang gagal. |
| `2` | Tidak ada kegagalan teknis, tetapi ada evidence wajib yang belum tersedia atau menyimpang; keputusan tetap no-go. |

Output minimal terdiri dari `manifest.tsv`, `summary.md`, log per command, `phpunit.xml`, health JSON, performance evidence, restore evidence, dan sign-off. Arsipkan bundle tersebut di lokasi evidence organisasi dengan akses terbatas.

## Urutan evidence staging

1. Lampirkan approval data master dan konfigurasi environment tanpa secret.
2. Jalankan access matrix, negative authorization, file access, audit immutability, export reconciliation, serta concurrency test dengan PostgreSQL/Redis target.
3. Jalankan `k6` memakai [performance.md](performance.md), lalu simpan summary JSON dan metadata volume/VU.
4. Jalankan backup penuh, WAL/incremental, private storage backup, dan [restore drill](runbook.md#5-backup-dan-restore) pada direktori/database restore terisolasi.
5. Pastikan heartbeat scheduler, queue, cache, storage, backup, dan `/health/ready` sehat.
6. Lengkapi [matriks AC-01 sampai AC-28](staging-uat.md), [kontrol privacy/retention/support](go-live-controls.md), dan [sign-off](go-live-signoff.md).
7. Pemilik produk dan operasional menetapkan `Go` atau `No-go`; deviasi tanpa owner, expiry date, dan keputusan tertulis tidak boleh dianggap lulus.

## Keputusan saat snapshot ini

**No-go sementara.** Implementasi aplikasi dan automated baseline telah diverifikasi, tetapi environment staging, performance evidence, restore evidence, dan sign-off operasional belum tersedia. Keputusan dapat diubah hanya setelah bundle evidence lengkap dan seluruh owner menyetujui.
