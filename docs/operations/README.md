# Operasi SIHATI

Dokumen operasional untuk issue #14:

- [Runbook deployment, monitoring, incident, dan go-live](runbook.md)
- [Staging, UAT, pilot, dan matriks AC-01 sampai AC-28](staging-uat.md)
- [Performance test dan bukti NFR](performance.md)

Artefak teknis yang dirujuk dokumen ini berada di:

- `deploy/docker-compose.production.yml` untuk topologi PostgreSQL, Redis, queue worker, scheduler, private storage, dan HTTPS melalui Caddy.
- `deploy/backup/` untuk backup PostgreSQL, private storage, pemeriksaan WAL, dan restore drill.
- `app/Services/OperationalHealthService.php` dan `app/Services/StorageCapacityService.php` untuk readiness serta alert storage.
- `tests/performance/k6.js` untuk uji beban terukur.

Dokumen ini adalah baseline yang dapat dijalankan. Nilai secret, domain, data master, pemilik sign-off, dan bukti hasil tetap harus diisi oleh lingkungan staging/produksi yang disetujui.
