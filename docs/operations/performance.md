# Performance test SIHATI

## Target

Uji dilakukan pada environment staging yang mendekati produksi dengan minimal 300 tiket aktif dan 30 pengguna aktif bersamaan. Target issue #14 dan PRD:

| Operasi | Target P95 |
|---|---:|
| Daftar tiket | < 2.000 ms |
| Penyimpanan tiket baru | < 1.000 ms |
| Concurrent users | 30 VU aktif |
| Throughput acuan | 40 tiket baru/hari, 5.000 tiket/tahun |

Jika environment, data, atau infrastruktur berbeda dari target, lampirkan deviasi tertulis dan keputusan go/no-go.

## Menjalankan k6

`tests/performance/k6.js` melakukan login per VU, membaca daftar tiket, dan—bila `K6_CREATE_FORM` diisi—mengukur POST pembuatan tiket. Form create harus berupa URL-encoded field yang valid untuk katalog pada environment uji; gunakan akun non-produksi yang sudah mengganti password awal.

```bash
k6 run \
  --env K6_BASE_URL=https://staging-helpdesk.example.org \
  --env K6_USERNAME=perf-agent \
  --env K6_PASSWORD='<secret dari secret manager>' \
  --env K6_READ_VUS=30 \
  --env K6_WRITE_VUS=5 \
  --env K6_DURATION=2m \
  --env K6_CREATE_FORM='subject=Uji+performa&description=Data+uji&service_type_id=1&service_type_variant_id=1' \
  tests/performance/k6.js
```

Jangan menjalankan write scenario terhadap produksi. Bersihkan data uji menggunakan prosedur yang disetujui; jangan menghapus audit evidence secara langsung.

## Bukti dan interpretasi

Simpan output k6, commit/image tag, ukuran database, jumlah tiket, jumlah VU, konfigurasi instance, timestamp, dan hasil `sihati:ops:health --json`. Kriteria lulus adalah seluruh threshold k6 lulus, error rate di bawah 1%, tidak ada queue backlog permanen, dan tidak ada error kritis pada log. Kegagalan harus menghasilkan analisis bottleneck atau deviasi tertulis.
