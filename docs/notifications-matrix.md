# Matriks notifikasi tiket

Matriks ini adalah keputusan implementasi notifikasi in-app SIHATI versi 1.0. Semua penerima dideduplikasi, akun nonaktif tidak dikirimi notifikasi, dan pengiriman menggunakan kanal `database` internal setelah transaksi bisnis berhasil commit.

| Peristiwa | Penerima |
|---|---|
| Tiket dibuat | Pemohon dan Pembuat tiket |
| Tiket diklaim | Pemohon dan Pembuat tiket |
| Tiket ditugaskan | Pemohon, Pembuat tiket, dan penanggung jawab baru |
| Tiket dieskalasi ke Tier 2 | Pemohon, Pembuat tiket, dan Agen Tier 2 tujuan |
| Komentar publik | Pemohon, kecuali penulis komentar |
| Catatan internal | Penanggung jawab tiket, kecuali penulis catatan |
| Permintaan persetujuan | Approver aktif yang dituju |
| Keputusan persetujuan | Pemohon, pengaju persetujuan, dan penanggung jawab, kecuali pengambil keputusan |
| Permintaan informasi | Pemohon, kecuali bila aktor adalah Pemohon |
| Balasan Pemohon | Penanggung jawab sebelumnya, kecuali bila aktor adalah penanggung jawab |
| Pekerjaan selesai | Pemohon |
| Hasil belum sesuai | Penanggung jawab terakhir |
| Tiket ditutup | Pemohon, penanggung jawab, dan Pembuat tiket, kecuali aktor |
| Tiket dibuka kembali | Penanggung jawab terakhir |
| Tiket ditolak saat triase | Pemohon dan Pembuat tiket |
| Tiket dibatalkan | Pemohon dan Pembuat tiket |
| Menunggu pihak ketiga | Pemohon, kecuali bila aktor adalah Pemohon |
| Menunggu Pemohon berakhir | Penanggung jawab sebelumnya |

Pengembalian tiket Tier 2 ke Tier 1 mengikuti penerima event penugasan. Saat pihak ketiga selesai, Pemohon menerima pembaruan bahwa tiket kembali dikerjakan.

Notifikasi menyimpan event, judul, pesan, nomor/id tiket, waktu pembuatan, dan URL internal tiket. ID notifikasi bersifat deterministik berdasarkan event, kejadian, dan penerima agar retry aman tidak membuat baris duplikat.
