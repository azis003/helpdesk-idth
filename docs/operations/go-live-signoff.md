# Sign-off technical verification dan go-live

Form ini adalah evidence yang harus ditandatangani setelah [matriks UAT](staging-uat.md), [kontrol operasional](go-live-controls.md), performance, dan restore drill selesai. Tanda tangan tidak boleh diisi oleh agent yang menjalankan verifikasi seorang diri.

## Lampiran wajib

| Lampiran | Owner | Status snapshot | Link evidence |
|---|---|---|---|
| Access matrix dan negative authorization | Keamanan / QA | Deviasi — staging belum dijalankan | `access-matrix.md` |
| Export reconciliation dengan spreadsheet acuan | Pemilik operasional | Deviasi — spreadsheet acuan belum dilampirkan | `staging-uat.md#4-security-access-concurrency-file-audit-export` |
| Performance 300 tiket aktif / 30 VU | Infrastruktur / QA | Deviasi — output k6 belum dilampirkan | `performance.md` |
| Backup penuh, WAL, private storage | Infrastruktur / backup | Deviasi — evidence host belum dilampirkan | `runbook.md#5-backup-dan-restore` |
| Restore drill RPO/RTO | Infrastruktur / backup | Deviasi — evidence restore belum dilampirkan | `restore-drill.sh` |
| Privacy/retention policy | Keamanan/privacy | Deviasi — approval organisasi belum dilampirkan | `go-live-controls.md` |
| Training, support window, escalation contact | Pemilik operasional | Deviasi — daftar peserta/kontak belum diisi | `go-live-controls.md` |
| Health/readiness, queue, scheduler, storage | Infrastruktur | Deviasi — staging marker belum tersedia | `runbook.md#6-monitoring-dan-retensi` |

## Keputusan

| Peran | Nama | Keputusan (`Go`/`No-go`) | Timestamp Asia/Jakarta | Tanda tangan/link |
|---|---|---|---|---|
| Pemilik produk | TBD | No-go sementara | 2026-08-07T20:51:19+07:00 | Menunggu bundle evidence staging |
| Pemilik operasional | TBD | No-go sementara | 2026-08-07T20:51:19+07:00 | Menunggu UAT dan training |
| Infrastruktur | TBD | No-go sementara | 2026-08-07T20:51:19+07:00 | Menunggu backup/restore/performance |
| Keamanan/privacy | TBD | No-go sementara | 2026-08-07T20:51:19+07:00 | Menunggu access/privacy review |

Keputusan snapshot: **No-go sementara**. Form ini hanya berubah menjadi `Go` jika semua lampiran wajib tersedia, deviasi memiliki owner dan tanggal kedaluwarsa, serta tidak ada defect kritis/tinggi terbuka.
