# PRD — Aplikasi Helpdesk dan Troubleshooting Internal

| Atribut | Nilai |
|---|---|
| Status | Final untuk baseline pengembangan; keputusan inti disetujui |
| Versi | 1.0 |
| Tanggal | 5 Agustus 2026 |
| Produk | Helpdesk dan Troubleshooting Internal |
| Organisasi sasaran | Sekitar 150 pegawai, 6 tim kerja, 4 gedung |
| Keputusan teknis tetap | Aplikasi Laravel monolith |
| Target kapasitas | 40 tiket baru per hari, sekitar 5.000 tiket per tahun |
| Pemilik produk | Belum ditetapkan secara administratif |
| Batas dokumen | Versi 1.0 |

Versi 1.0 ini memasukkan rekomendasi keputusan terbuka yang telah disetujui pemilik produk. Beberapa input operasional yang belum tersedia—misalnya daftar kategori awal, nilai kebijakan lampiran, dan kolom tambahan spreadsheet lama—tetap harus diisi sebelum UAT, tetapi tidak lagi mengubah alur bisnis inti.

## Cara membaca dokumen

Prioritas kebutuhan:

- Must: wajib tersedia dan diterima untuk versi 1.0.
- Should: diusahakan tersedia bila kapasitas memungkinkan; tidak boleh mengubah aturan bisnis Must.
- Could: kandidat versi berikutnya dan bukan komitmen versi 1.0.

Penanda keputusan:

- [BRIEF] berasal langsung dari brief pemilik produk.
- [USULAN] rekomendasi teknis atau target pilot yang tidak mengubah aturan inti; ditetapkan pada technical discovery atau evaluasi pilot.
- [TBD] input operasional atau administratif yang masih harus tersedia sebelum tahap yang disebutkan.
- [ASUMSI] digunakan sementara agar desain dapat dilanjutkan; harus divalidasi sebelum memengaruhi produksi.

Dokumen ini sengaja mendefinisikan proses bisnis, hak akses, status, aturan transisi, data, dan acceptance criteria sebelum rancangan antarmuka rinci. Halaman dan komponen visual dibuat pada tahap desain setelah keputusan terbuka yang berdampak telah disepakati.

## 1. Executive Summary

### 1.1 Ringkasan produk

Aplikasi Helpdesk dan Troubleshooting Internal adalah aplikasi web internal untuk mencatat, menangani, memantau, dan melaporkan seluruh permintaan layanan TI pegawai. Aplikasi menjadi satu sumber kebenaran untuk nomor tiket, pemohon, pembuat tiket, status, prioritas, SLA, penanggung jawab, komunikasi, hasil pekerjaan, persetujuan, dan jejak audit.

Produk ini menggantikan pencatatan utama melalui WhatsApp, telepon, kedatangan langsung, dan spreadsheet dengan alur yang tercatat di aplikasi. Kanal komunikasi di luar aplikasi tidak diintegrasikan pada versi 1.0; jika permintaan diterima melalui kanal tersebut, Agen Tier 1 tetap dapat membuat tiket atas nama pegawai.

### 1.2 Pernyataan masalah

Saat ini permintaan TI masuk melalui satu pintu kepada PIC helpdesk, kemudian dicatat di spreadsheet berdasarkan ingatan dan pengetahuan PIC. Tidak ada nomor tiket, antrean bersama, status yang terlihat pemohon, prioritas dan SLA formal, penutupan resmi, atau audit trail yang memadai.

Akibatnya, organisasi sulit memastikan semua permintaan tercatat, memilih penanggung jawab secara konsisten, mengukur beban kerja tiga teknisi, mengevaluasi mutu layanan, dan menghasilkan laporan bulanan yang dapat diaudit.

### 1.3 Solusi yang diusulkan

Aplikasi menyediakan katalog tujuh layanan, formulir dinamis, pembuatan tiket mandiri atau atas nama pegawai, antrean Tier 1 atomik, triase dan penugasan Tier 2, status serta SLA berbasis jam layanan, persetujuan tunggal Manajer TI, komunikasi publik dan internal yang terpisah, kontrol khusus perubahan database produksi, notifikasi dalam aplikasi, dasbor, laporan Excel/PDF, serta audit log append-only.

### 1.4 Tujuan produk

1. Mencatat seluruh permintaan layanan TI yang diketahui unit TI.
2. Mengurangi ketergantungan pada ingatan satu orang.
3. Memberi pemohon kemampuan membuat, memantau, menjawab, mengonfirmasi, dan membuka kembali tiket sesuai aturan.
4. Memberi petugas antrean, penugasan, SLA, dan riwayat penanganan yang dapat dilihat bersama.
5. Mengukur beban kerja, kecepatan penyelesaian, kepatuhan SLA, dan kualitas hasil layanan.
6. Menggantikan spreadsheet manual sebagai sumber laporan bulanan.
7. Mengendalikan perubahan data produksi dan menjaga jejak audit untuk perubahan data operasional.

### 1.5 Target keberhasilan

Angka pada tabel berikut adalah [USULAN] target awal untuk pilot dan dapat disesuaikan setelah baseline selama 4 minggu pertama tersedia.

| KPI | Definisi pengukuran | Target awal yang diusulkan | Waktu evaluasi |
|---|---|---:|---|
| Cakupan pencatatan | Permintaan yang diketahui unit TI dan memiliki tiket | ≥ 95% setelah bulan ke-3 | Bulanan |
| Adopsi mandiri | Tiket yang dibuat langsung oleh pemohon dibagi seluruh tiket | ≥ 70% setelah bulan ke-3 | Bulanan |
| Kecepatan pembuatan | Waktu yang dibutuhkan pemohon untuk mengirim tiket sederhana | Median ≤ 2 menit | UAT dan bulanan |
| Kepatuhan SLA | Tiket dengan hasil selesai sesuai target SLA dibagi tiket yang memiliki SLA | ≥ 90%; SVC-07 tidak masuk penyebut | Bulanan |
| Ketepatan audit | Peristiwa wajib memiliki pelaku, waktu, objek, aksi, dan perubahan | 100% pada skenario uji wajib | UAT dan setiap rilis |
| Kesiapan laporan | Laporan bulanan dapat diekspor tanpa perhitungan spreadsheet manual | 100% kolom wajib tersedia | Sebelum laporan pertama |
| Keamanan akses | Temuan akses tanpa hak pada uji otorisasi | 0 temuan kritis/tinggi terbuka | Sebelum go-live |
| Ketersediaan | Ketersediaan aplikasi selama jam layanan | ≥ 99% | Bulanan |

Target adopsi dan KPI bisnis di atas bukan aturan operasional tiket. Pemilik produk harus menetapkan pemilik KPI, sumber data resmi, periode pilot, dan tindakan jika target tidak tercapai.

### 1.6 Konteks organisasi dan batas kapasitas

- Sekitar 150 pegawai tersebar pada 6 tim kerja.
- Pegawai berada di 4 gedung dengan beberapa lantai dan ruangan.
- Unit TI terdiri dari 1 PIC helpdesk, 3 teknisi dengan bidang keahlian berbeda, dan 1 Manajer TI.
- Membantu helpdesk bukan pekerjaan utama ketiga teknisi.
- Volume saat ini sekitar 15 permintaan pada setiap hari kerja.
- Kapasitas rancangan versi 1.0 adalah sekitar 40 tiket baru per hari.
- Sistem harus mendukung sekitar 30 pengguna aktif bersamaan dan sekitar 5.000 tiket per tahun.

### 1.7 Latar belakang dan proses bisnis eksisting

Proses berjalan saat ini:

1. Pegawai menyampaikan permintaan melalui WhatsApp, telepon, atau datang langsung kepada PIC helpdesk.
2. Semua permintaan diterima melalui satu pintu.
3. PIC memutuskan sendiri apakah permintaan dapat dikerjakan atau diteruskan kepada teknisi.
4. PIC memilih teknisi berdasarkan pengetahuan pribadi mengenai keahlian masing-masing teknisi.
5. Pencatatan dilakukan di spreadsheet oleh PIC.
6. Spreadsheet digunakan untuk laporan bulanan kepada pimpinan.

Kondisi yang belum tersedia:

- nomor tiket;
- antrean bersama;
- status yang dapat dilihat pemohon;
- prioritas dan target waktu formal;
- penutupan resmi;
- audit trail penanganan;
- pengukuran beban kerja dan kepatuhan SLA yang terstruktur.

### 1.8 Masalah yang diselesaikan

| Masalah | Dampak | Kemampuan produk yang menjawab |
|---|---|---|
| Pencatatan terpusat pada ingatan PIC | Permintaan dapat terlupa atau sulit ditelusuri | Tiket bernomor, pemohon, pembuat tiket, dan audit |
| Tidak ada antrean bersama | Teknisi tidak melihat beban dan prioritas secara konsisten | Antrean Tier 1, filter, prioritas, dan penanggung jawab |
| Penugasan berdasarkan pengetahuan pribadi | Saran dan histori penugasan tidak terdokumentasi | Pemetaan kategori-keahlian dan saran teknisi |
| Pemohon tidak melihat kemajuan | Pertanyaan status berulang dan pengalaman tidak pasti | Dasbor, status, komentar publik, dan notifikasi |
| Tidak ada SLA formal | Kinerja tidak dapat dibandingkan | Kalender jam layanan, timer SLA, dan laporan kepatuhan |
| Tidak ada penutupan dan konfirmasi | Hasil selesai tidak memiliki bukti | Solusi, Menunggu Konfirmasi, konfirmasi, dan auto-close |
| Tidak ada jejak perubahan | Sulit mengaudit perubahan data produksi | Audit append-only dan riwayat transaksi |
| Spreadsheet manual untuk laporan | Beban administratif dan risiko salah hitung | Laporan bulanan Excel/PDF dari transaksi aplikasi |

## 2. User Experience & Functionality

### 2.1 Prinsip pengalaman pengguna

1. Seluruh label, pesan validasi, status, notifikasi, dan laporan menggunakan bahasa Indonesia.
2. Pemohon dapat mengirim tiket sederhana dari ponsel dalam waktu kurang dari 2 menit.
3. Aplikasi memperlihatkan status dan penanggung jawab secara terpisah; peran tidak disisipkan ke dalam nilai status.
4. Alur aksi harus mencerminkan kewenangan server. Tombol yang disembunyikan di antarmuka bukan pengganti pemeriksaan otorisasi.
5. Komunikasi kepada pemohon dan catatan internal menggunakan dua kotak input serta dua tampilan yang berbeda; tidak ada sakelar publik/internal.
6. Pengguna mendapatkan umpan balik setelah aksi penting: berhasil, ditolak, menunggu, atau memerlukan data tambahan.
7. Rancangan antarmuka rinci, wireframe, dan sistem visual bukan bagian dari dokumen ini.

### 2.2 Pengguna dan peran

| Peran | Tujuan utama | Cakupan utama |
|---|---|---|
| Super Admin | Mengelola identitas, peran, organisasi, master data, konfigurasi, dan audit | Administrasi dan pengawasan; tidak otomatis boleh melakukan pekerjaan operasional |
| Pemohon | Mengajukan dan mengikuti permintaan layanan TI | Tiket milik sendiri, komentar publik, informasi tambahan, konfirmasi, pembatalan Baru, dan buka kembali sesuai syarat |
| Agen Tier 1 | Menjadi pintu masuk, mengambil antrean, melakukan triase, menangani, menolak, dan menugaskan | Antrean Baru dan tiket operasional yang menjadi kewenangannya |
| Agen Tier 2 | Menangani tiket sesuai bidang keahlian | Tiket yang ditugaskan kepadanya dan aksi pengembalian ke Tier 1 |
| Approver | Memberikan keputusan persetujuan | Permintaan persetujuan yang diarahkan kepada satu approver aktif |
| Ketua Tim Kerja | Memantau tiket anggota tim | Pemantauan tiket anggota; tidak memiliki hak menyetujui |

Satu pengguna boleh memiliki lebih dari satu peran. Hak yang diperoleh adalah gabungan hak dari peran yang diberikan, tetapi hak operasional tetap membutuhkan peran operasional secara eksplisit. Super Admin tidak otomatis menjadi Agen Tier 1 atau Agen Tier 2.

Manajer TI tidak ditambahkan sebagai peran ketujuh. Dalam versi 1.0, sistem memiliki satu konfigurasi Manajer TI aktif. Pengguna tersebut wajib memiliki peran Approver; seluruh permintaan persetujuan diarahkan kepadanya. Perubahan penetapan hanya dapat dilakukan Super Admin melalui aksi eksplisit yang mengalihkan persetujuan tertunda dan dicatat dalam audit.

### 2.3 Matriks hak akses

Keterangan: Milik berarti tiket yang pemohonnya adalah pengguna tersebut; Assigned berarti tiket yang sedang ditugaskan; Queue berarti antrean sesuai peran; Tim berarti anggota tim kerja; Semua berarti seluruh data yang memang termasuk cakupan fungsi; — berarti tidak berwenang.

| Kemampuan | Super Admin | Pemohon | Agen Tier 1 | Agen Tier 2 | Approver aktif | Ketua Tim Kerja |
|---|---|---|---|---|---|---|
| Login dan ganti password sendiri | Ya | Ya | Ya | Ya | Ya | Ya |
| Dipaksa mengganti password awal | Ya | Ya | Ya | Ya | Ya | Ya |
| Kelola pengguna dan status aktif | Semua | — | — | — | — | — |
| Kelola pemberian/pencabutan peran | Semua | — | — | — | — | — |
| Kelola tim, ketua, dan anggota | Semua | — | — | — | — | — |
| Kelola bidang keahlian dan pemetaan kategori | Semua | — | — | — | — | — |
| Kelola katalog, field dinamis, kategori, lokasi, SLA, jam layanan, hari libur, dan konfigurasi aplikasi | Semua | — | — | — | — | — |
| Buat tiket sendiri | Ya sebagai Pemohon bila memiliki akses tersebut | Milik | Ya bila juga memiliki peran Pemohon | Ya bila juga memiliki peran Pemohon | Ya bila juga memiliki peran Pemohon | Ya bila juga memiliki peran Pemohon |
| Buat tiket atas nama pegawai lain | — kecuali juga memiliki peran Tier 1 | — | Ya | — | — | — |
| Lihat tiket | Sesuai cakupan administrasi yang disetujui | Milik | Queue dan tiket operasional yang menjadi kewenangannya | Assigned | Permintaan persetujuan | Tim |
| Lihat catatan internal | Sesuai kewenangan yang disetujui | — | Tiket berwenang | Tiket assigned | Tiket untuk diputuskan | — |
| Lihat dan mengambil antrean Baru | — sebagai Super Admin saja | — | Queue bersama Tier 1 | — | — | — |
| Klaim tiket Baru secara atomik | — kecuali juga Tier 1 | — | Ya | — | — | — |
| Triase: kerjakan, tugaskan, atau tolak | — kecuali juga Tier 1 | — | Ya | — | — | — |
| Menangani tiket Tier 1 | — kecuali juga Tier 1 | — | Ya | — | — | — |
| Menangani tiket Tier 2 | — kecuali juga Tier 2 | — | Menugaskan | Assigned | — | — |
| Mengembalikan tiket ke Tier 1 terakhir | — kecuali juga Tier 2 | — | — | Ya untuk tiket assigned | — | — |
| Mengubah prioritas | — kecuali juga agen operasional | — | Ya, dengan alasan | — | — | — |
| Meminta persetujuan | — kecuali juga agen operasional | — | Ya | Ya | — | — |
| Memberikan keputusan persetujuan | — | — | — | — | Ya, hanya approver aktif | — |
| Menyetujui pengajuan sendiri | — | — | — | — | Hanya jika akun juga merupakan Manajer TI yang ditetapkan | — |
| Mengirim balasan ke pemohon | — kecuali juga berwenang | Milik | Tiket berwenang | Assigned | Tiket untuk diputuskan | — |
| Mengirim catatan internal | — kecuali juga berwenang | — | Tiket berwenang | Assigned | Tiket untuk diputuskan | — |
| Meminta informasi tambahan | — kecuali juga agen | — | Ya | Ya | — | — |
| Mengubah ke Menunggu Pihak Ketiga | — kecuali juga agen | — | Ya | Ya | — | — |
| Menetapkan solusi dan Menunggu Konfirmasi | — kecuali juga agen | — | Ya | Ya | — | — |
| Konfirmasi hasil dan buka kembali | — kecuali juga pemohon | Milik dan sesuai syarat | Memantau | Memantau | — | — |
| Membatalkan tiket Baru | — | Milik | — | — | — | — |
| Menolak tiket | — kecuali juga Tier 1 | — | Ya, dengan alasan | — | — | — |
| Membuat/menonaktifkan pengumuman global | — kecuali juga Tier 1 | — | Ya | — | — | — |
| Melihat dasbor menyeluruh | Ya | — | Ya | — | Ya sebagai Manajer TI/approver sesuai hak | — |
| Melihat laporan dan ekspor | Ya | — | Ya | — | Ya sesuai cakupan | — |
| Melihat dan menyaring audit log | Ya | — | — | — | — | — |

Implementasi wajib menggunakan middleware, policy, gate, dan pemeriksaan domain di sisi server untuk setiap aksi. Matriks ini adalah batas produk; penyederhanaan di antarmuka tidak boleh memperluas hak.

### 2.4 User stories

| ID | User story | Prioritas | Acceptance criteria utama |
|---|---|---|---|
| US-01 | Sebagai Pemohon, saya ingin membuat tiket dari katalog layanan agar permintaan saya tercatat tanpa bergantung pada PIC. | Must | AC-03, AC-04, AC-05 |
| US-02 | Sebagai Agen Tier 1, saya ingin membuat tiket atas nama pegawai lain agar permintaan dari telepon atau kedatangan langsung tetap terdokumentasi. | Must | AC-04 |
| US-03 | Sebagai Pemohon, saya ingin melihat status, perkembangan, dan permintaan jawaban tiket saya. | Must | AC-06, AC-12, AC-14 |
| US-04 | Sebagai Agen Tier 1, saya ingin mengambil tiket Baru secara atomik agar tidak terjadi pekerjaan ganda. | Must | AC-07 |
| US-05 | Sebagai Agen Tier 1, saya ingin melakukan triase dengan tiga hasil yang jelas. | Must | AC-08 |
| US-06 | Sebagai Agen, saya ingin mendapat saran teknisi berdasarkan keahlian tetapi tetap memilih secara manual. | Must | AC-09 |
| US-07 | Sebagai Agen, saya ingin berkomunikasi kepada pemohon dan mencatat catatan internal secara terpisah. | Must | AC-11 |
| US-08 | Sebagai Agen, saya ingin meminta persetujuan kepada Manajer TI ketika diperlukan. | Must | AC-10 |
| US-09 | Sebagai Agen, saya ingin menghentikan SLA saat menunggu pemohon atau pihak ketiga. | Must | AC-12, AC-13, AC-18 |
| US-10 | Sebagai Agen, saya ingin menyelesaikan tiket dengan solusi dan meminta konfirmasi pemohon. | Must | AC-14 |
| US-11 | Sebagai Pemohon, saya ingin menyatakan hasil belum sesuai atau membuka kembali tiket yang sudah ditutup sesuai batas waktu. | Must | AC-15, AC-16 |
| US-12 | Sebagai Agen Tier 1, saya ingin menolak permintaan dengan alasan yang terlihat pemohon. | Must | AC-17 |
| US-13 | Sebagai pelaksana perubahan database, saya ingin sistem memblokir eksekusi sebelum bukti kontrol lengkap. | Must | AC-19 |
| US-14 | Sebagai pelaksana permintaan tarik data, saya ingin hasil wajib tersedia bagi pemohon sebelum tiket menunggu konfirmasi. | Must | AC-20 |
| US-15 | Sebagai pemohon dan petugas, saya ingin menerima notifikasi penting di aplikasi. | Must | AC-21 |
| US-16 | Sebagai pimpinan dan PIC, saya ingin melihat dasbor dan laporan bulanan tanpa menghitung spreadsheet manual. | Must | AC-22 |
| US-17 | Sebagai Super Admin, saya ingin mengelola semua master data dan konfigurasi melalui aplikasi. | Must | AC-23 |
| US-18 | Sebagai auditor internal, saya ingin melihat riwayat perubahan dan percobaan aksi yang ditolak. | Must | AC-24 |

### 2.4.1 User journey utama

#### Journey A — Pemohon membuat dan memantau tiket sendiri

1. Pemohon login; bila masih menggunakan password awal, sistem memaksa ganti password.
2. Pemohon membuka katalog, melihat pengumuman aktif, lalu memilih jenis layanan.
3. Sistem menampilkan field dinamis; lokasi diwajibkan hanya untuk layanan yang mensyaratkannya.
4. Pemohon mengisi deskripsi, prioritas usulan, field layanan, dan lampiran bila ada.
5. Sistem memvalidasi, membuat nomor tiket, menyimpan status Baru, dan mengirim notifikasi in-app.
6. Pemohon membuka dasbor untuk melihat status, perkembangan, permintaan informasi, atau permintaan konfirmasi.
7. Pemohon membalas permintaan informasi, mengonfirmasi hasil, menyatakan hasil belum sesuai, atau membuka kembali sesuai aturan.

#### Journey B — Agen Tier 1 membuat tiket atas nama pegawai

1. Pegawai menyampaikan permintaan melalui kanal yang tersedia saat ini.
2. Agen Tier 1 login dan memilih pegawai sebagai pemohon; akun Agen disimpan sebagai pembuat.
3. Agen mengisi katalog, form dinamis, lokasi, prioritas usulan, dan lampiran.
4. Sistem membuat tiket Baru dan menyimpan pemohon, pembuat, serta flag apakah tiket dibuat mandiri.
5. Pemohon dapat melihat tiketnya melalui akun sendiri; Agen tetap melanjutkan proses dari antrean.

#### Journey C — Klaim dan triase Tier 1

1. Semua Agen Tier 1 melihat antrean Baru yang sama.
2. Agen memilih aksi ambil; transaksi atomik memastikan satu Agen menjadi pengambil.
3. Status berubah Baru → Diproses dan audit mencatat agen serta waktu.
4. Agen memeriksa kelengkapan, kategori, prioritas, dan saran teknisi.
5. Agen memilih satu hasil: kerjakan sendiri, tugaskan Tier 2, atau tolak dengan alasan.
6. Sistem mengubah status, penanggung jawab, tier, dan histori sesuai hasil.

#### Journey D — Penanganan Tier 1/Tier 2

1. Penanggung jawab mengerjakan tiket dan menambahkan Balasan ke Pemohon atau Catatan Internal.
2. Agen Tier 1 dapat menugaskan ke Tier 2 saat triase atau setelah pekerjaan dimulai; status tetap Dikerjakan ketika alih tier.
3. Agen Tier 2 mengerjakan tiket sesuai penugasan.
4. Jika perlu kembali, Tier 2 mengembalikan ke Agen Tier 1 terakhir yang melakukan triase; status menjadi Diproses.
5. Semua penugasan, pengembalian, eskalasi, komentar, dan perubahan status terlihat di histori sesuai hak.

#### Journey E — Persetujuan

1. Agen yang menangani menilai kebutuhan persetujuan dan menekan Butuh Persetujuan pada status Diproses/Dikerjakan.
2. Sistem menyimpan status, penanggung jawab, dan tier sebelumnya; status menjadi Menunggu Persetujuan.
3. Approver aktif melihat item di bagian Perlu Tindakan Saya.
4. Approver memilih Setuju, sehingga state sebelumnya dipulihkan, atau Tidak Setuju dengan catatan wajib, sehingga status final menjadi Tidak Disetujui.
5. Self-approval hanya berhasil untuk akun Manajer TI yang ditetapkan dan tidak berlaku otomatis untuk seluruh pemegang peran Approver.

#### Journey F — Menunggu pemohon atau pihak ketiga

1. Agen meminta informasi melalui Balasan ke Pemohon; status Menunggu Pemohon dan SLA berhenti.
2. Pemohon membalas; sistem mengembalikan tiket ke penanggung jawab sebelumnya dan memberi notifikasi.
3. Jika menunggu melewati batas konfigurasi, scheduler memberi penanda timeout dan mengembalikan tiket ke antrean sesuai keputusan final.
4. Bila bergantung pihak lain, Agen mengisi nama pihak ketiga dan opsional tanggal tindak lanjut; status Menunggu Pihak Ketiga dan SLA berhenti.
5. Setelah ketergantungan selesai, Agen melanjutkan ke status Dikerjakan dan SLA berjalan kembali.

#### Journey G — Penyelesaian dan penutupan

1. Agen mengisi solusi dan memenuhi kontrol layanan khusus.
2. Status menjadi Menunggu Konfirmasi; SLA berhenti dan timer konfirmasi dimulai.
3. Pemohon menyatakan hasil sesuai, atau scheduler menutup otomatis setelah batas waktu.
4. Jika hasil belum sesuai sebelum tutup, pemohon mengisi alasan; tiket kembali Dikerjakan kepada penanggung jawab terakhir tanpa triase ulang.

#### Journey H — Buka kembali

1. Pemohon membuka tiket Ditutup dalam maksimal 7 hari kerja dan selama jumlah buka kembali belum mencapai 3.
2. Sistem mempertahankan nomor serta prioritas, membuat SLA penuh baru, mengembalikan penanggung jawab terakhir, dan mengubah status menjadi Dikerjakan.
3. Permintaan buka kembali keempat ditolak dengan instruksi membuat tiket baru.

#### Journey I — Kontrol perubahan database

1. Pada SVC-03, pelaksana mengunggah change_script, rollback_script, dan backup_evidence sebagai tiga file berbeda.
2. Sistem menolak Mulai Eksekusi jika ada bukti yang kurang, bertipe ganda, atau tidak valid; percobaan dicatat.
3. Jika lengkap, sistem mencatat pelaku dan waktu eksekusi.
4. Pelaksana mengisi verifikasi hasil; tiket baru dapat masuk Menunggu Konfirmasi setelah kontrol dan verifikasi terpenuhi.

#### Journey J — Permintaan tarik data

1. Pelaksana menangani SVC-02 dan mengunggah minimal satu hasil bertipe data_export_result.
2. Sistem memastikan hasil dapat diakses pemohon dan menolak Menunggu Konfirmasi bila belum ada.
3. Setelah penutupan, job retensi menghapus lampiran hasil setelah 90 hari dan mempertahankan metadata audit.

#### Journey K — Laporan dan pengawasan

1. PIC atau pengguna berwenang memilih periode laporan bulanan.
2. Sistem menyusun data transaksi dan snapshot historis, lalu menyediakan Excel/PDF.
3. Sebelum UAT, keluaran dibandingkan dengan kolom spreadsheet lama yang masih digunakan pimpinan.
4. Super Admin memeriksa audit log dan filter; Manajer TI/PIC memantau dasbor, SLA, beban, dan persetujuan.

### 2.5 Ruang lingkup versi 1.0

| Area | Isi ruang lingkup | Prioritas |
|---|---|---|
| Identitas dan akses | Provisioning password awal untuk seluruh pegawai, login username/password, ganti/reset password, nonaktifkan pengguna, multi-peran, otorisasi server | Must |
| Organisasi | Pengguna, 6 tim kerja, ketua, anggota, bidang keahlian, pemetaan kategori-keahlian | Must |
| Katalog | Tujuh jenis layanan, kategori, field dinamis, lokasi, pengumuman sebelum formulir | Must |
| Tiket | Nomor otomatis INC/REQ/CHG, pemohon dan pembuat terpisah, lampiran, prioritas, lokasi kondisional | Must |
| Operasional | Antrean Tier 1, klaim atomik, triase, saran teknisi, penugasan, pengembalian Tier 2 | Must |
| Workflow | Semua status dan transisi yang didefinisikan dalam PRD | Must |
| Komunikasi | Balasan ke Pemohon, Catatan Internal, lampiran, solusi, riwayat kronologis | Must |
| Persetujuan | Satu approver aktif, status Menunggu Persetujuan, keputusan, self-approval terbatas | Must |
| SLA | Kalender jam layanan, target per layanan, pause/resume, peringatan, overdue, metrik | Must |
| Kontrol khusus | Change script, rollback script, backup evidence, Mulai Eksekusi, verifikasi | Must |
| Data export | Lampiran data_export_result dan akses pemohon | Must |
| Notifikasi | Notifikasi dalam aplikasi dan pengumuman global | Must |
| Dasbor | Dasbor Pemohon, Agen, Approver, serta dasbor menyeluruh sesuai matriks | Must |
| Laporan | Laporan bulanan dengan kolom wajib dan ekspor Excel/PDF | Must |
| Audit | Audit log append-only termasuk aksi yang ditolak | Must |
| Retensi dan operasi | Retensi tiket, penghapusan hasil tarik data, backup, pemantauan storage, log aplikasi | Must |

### 2.6 Hal yang tidak termasuk versi 1.0

Item berikut tidak boleh masuk backlog Must versi 1.0:

- Integrasi WhatsApp.
- Integrasi email.
- Integrasi Telegram.
- Web Push.
- SSO, LDAP, dan Active Directory.
- Integrasi API dengan aplikasi internal lain.
- Integrasi langsung dengan SIMPEL.
- Manajemen aset atau inventaris TI.
- Proses pengadaan.
- Proses keuangan.
- Aplikasi mobile native.
- Basis pengetahuan.
- Survei kepuasan.
- Tiket induk untuk gangguan massal.
- Autentikasi dua faktor.
- Delegasi approver otomatis di dalam aplikasi.

Item tersebut merupakan kandidat [Could] untuk versi berikutnya, bukan komitmen roadmap. Tidak ada fitur baru di luar brief yang boleh ditambahkan tanpa keputusan pemilik produk.

### 2.7 Katalog layanan dan formulir

| Kode | Jenis layanan | Target SLA awal | Kelas nomor tiket |
|---|---|---:|---|
| SVC-01 | Kendala jaringan atau konektivitas | 1 hari kerja | INC |
| SVC-02 | Permintaan tarik data | 3 hari kerja | REQ |
| SVC-03 | Perubahan data yang tidak dapat dilakukan melalui aplikasi | 5 hari kerja | CHG |
| SVC-04 | Perubahan pada aplikasi | 7 hari kerja | CHG |
| SVC-05 | Permintaan atau perbaikan hardware | 3 hari kerja, tidak termasuk waktu pengadaan | INC untuk perbaikan; REQ untuk permintaan |
| SVC-06 | Permintaan software | 2 hari kerja, tidak termasuk waktu pengadaan | REQ |
| SVC-07 | Usulan sistem atau aplikasi baru | Tidak menggunakan SLA | CHG |

Pemetaan kelas tiket pada tabel telah disetujui. Untuk SVC-05, form wajib memiliki subjenis Perbaikan atau Permintaan; subjenis tersebut menentukan kelas nomor tiket tanpa mengubah target SLA layanan.

Format nomor tiket final adalah {KELAS}-{TAHUN}-{NOMOR5DIGIT}, contoh INC-2026-00001. Urutan dipisah per kelas dan tahun kalender Asia/Jakarta, bersifat atomik, dan nomor yang pernah digunakan tidak boleh digunakan ulang.

Aturan formulir:

- Pemohon memilih jenis layanan dari katalog.
- Form menampilkan field dinamis yang dikonfigurasi untuk jenis layanan tersebut.
- Pemohon boleh mengunggah tangkapan layar atau dokumen pendukung sesuai batas ukuran dan tipe berkas yang dikonfigurasi.
- Lokasi wajib untuk SVC-01 dan SVC-05; lokasi opsional untuk layanan lain.
- Pemohon mengusulkan prioritas Kritis, Tinggi, Sedang, atau Rendah.
- Sistem membuat nomor tiket unik secara otomatis dari kelas layanan yang telah dipetakan.
- Pengumuman aktif tampil di atas formulir sebelum pengiriman.
- Formulir harus menampilkan validasi yang dapat dipahami dalam bahasa Indonesia.

Formulir SVC-07 menggantikan formulir Word dan memiliki dua bagian:

Bagian Pemohon:

1. Nama aplikasi atau sistem.
2. Identitas pemohon otomatis dari profil.
3. Latar belakang atau permasalahan.
4. Tujuan pengembangan.
5. Pengguna yang akan dilayani.
6. Fitur atau kebutuhan utama.
7. Aplikasi yang perlu diintegrasikan, bila ada.
8. Jenis data yang akan dikelola.
9. Target waktu.
10. Alasan target waktu.

Bagian Tim TI:

1. Verifikasi dan penilaian.
2. Tingkat kompleksitas.
3. Risiko keamanan atau data.
4. Prioritas.
5. Rencana mulai.
6. PIC atau tim pengembang.
7. Catatan dan rencana tindak lanjut.

Field dinamis harus dapat dikelola Super Admin tanpa perubahan kode. Definisi field yang sudah dipakai tiket tidak diedit; perubahan dilakukan dengan membuat versi baru atau menonaktifkan definisi lama. Nilai field disimpan bersama snapshot label dan definisi yang digunakan saat pengajuan agar laporan lama tidak berubah ketika form dikonfigurasi ulang. Jenis field minimum dan aturan validasi tiap field merupakan input katalog yang harus tersedia sebelum UAT.

### 2.8 Status tiket

Status tidak memuat nama atau peran penanggung jawab. Penanggung jawab aktif dan tier penanganan ditampilkan dari data tiket.

| Status | Arti bisnis | Penanggung jawab/tier | Dampak SLA |
|---|---|---|---|
| Baru | Tiket telah dibuat dan belum diambil | Belum ada | Berjalan sejak waktu mulai SLA |
| Diproses | Tiket telah diambil Agen Tier 1 dan sedang ditriase | Agen Tier 1 | Berjalan |
| Dikerjakan | Tiket sedang dikerjakan oleh penanggung jawab aktif | Agen Tier 1 atau Tier 2 | Berjalan |
| Menunggu Persetujuan | Menunggu keputusan Manajer TI | Penanggung jawab sebelumnya disimpan | Berhenti |
| Menunggu Pemohon | Petugas meminta informasi tambahan | Penanggung jawab sebelumnya disimpan | Berhenti |
| Menunggu Pihak Ketiga | Pekerjaan bergantung vendor, pengadaan, atau pihak lain | Penanggung jawab sebelumnya disimpan | Berhenti |
| Menunggu Konfirmasi | Petugas menyatakan pekerjaan selesai dan menunggu tanggapan pemohon | Penanggung jawab terakhir disimpan | Berhenti |
| Ditutup | Pekerjaan selesai dan dikonfirmasi atau ditutup otomatis | Penanggung jawab terakhir tetap sebagai histori | Tidak berjalan |
| Ditolak | Permintaan diakhiri Agen Tier 1 dengan alasan wajib | Penanggung jawab terakhir tersimpan | Tidak berjalan |
| Tidak Disetujui | Permintaan tidak disetujui Manajer TI dan bersifat final | Penanggung jawab terakhir tersimpan | Tidak berjalan |
| Dibatalkan | Pemohon membatalkan tiket saat masih Baru | Belum ada atau pencatat tiket | Tidak berjalan |

Status Menunggu Konfirmasi menghentikan SLA, tetapi tetap memiliki timer terpisah untuk batas auto-close. Timer konfirmasi bukan target SLA.

### 2.9 Aturan transisi status

| Aksi | Dari → ke | Pelaku | Validasi wajib dan catatan |
|---|---|---|---|
| Membuat tiket | — → Baru | Pemohon atau Agen Tier 1 | Data form valid; pemohon dan pembuat disimpan terpisah; nomor unik dibuat |
| Mengambil antrean | Baru → Diproses | Agen Tier 1 aktif | Harus atomik; hanya satu agen berhasil; simpan waktu dan pelaku |
| Kerjakan sendiri | Diproses → Dikerjakan | Agen Tier 1 yang melakukan triase | Penanggung jawab Tier 1 diisi; kategori dan prioritas ditetapkan |
| Tugaskan teknisi | Diproses → Dikerjakan | Agen Tier 1 yang melakukan triase | Penanggung jawab Tier 2 diisi; saran teknisi opsional dan keputusan manual dicatat |
| Mulai/lanjutkan pekerjaan | Diproses → Dikerjakan | Agen berwenang | Tidak boleh ada status baru atau status peran seperti Dikerjakan oleh Teknisi |
| Alihkan Tier 1 ke Tier 2 setelah mulai dikerjakan | Dikerjakan → Dikerjakan | Agen yang berwenang | Status tetap; hanya penanggung jawab dan tier berubah; histori penugasan bertambah |
| Kembalikan ke Tier 1 | Dikerjakan → Diproses | Agen Tier 2 yang ditugaskan | Kembali kepada Agen Tier 1 terakhir yang melakukan triase; histori alasan dan waktu wajib |
| Minta persetujuan | Diproses/Dikerjakan → Menunggu Persetujuan | Agen yang menangani | Simpan status, penanggung jawab, dan tier sebelumnya; permintaan diarahkan ke approver aktif |
| Setujui | Menunggu Persetujuan → status sebelumnya | Approver aktif | Kembalikan status, penanggung jawab, dan tier yang disimpan; keputusan dan waktu dicatat |
| Tidak setuju | Menunggu Persetujuan → Tidak Disetujui | Approver aktif | Alasan wajib; final; tidak dapat dibuka kembali |
| Minta informasi | Status aktif → Menunggu Pemohon | Agen yang menangani | Balasan ke Pemohon wajib berisi pertanyaan/penjelasan; SLA berhenti |
| Pemohon membalas | Menunggu Pemohon → Dikerjakan | Pemohon | Balasan masuk ke riwayat publik; kembali ke penanggung jawab sebelumnya; notifikasi dikirim |
| Waktu tunggu habis | Menunggu Pemohon → Dikerjakan | Scheduler | Setelah default 3 hari kerja, penanggung jawab sebelumnya dipertahankan, flag waktu tunggu berakhir disimpan, tiket masuk antrean pribadi, SLA kembali berjalan dari sisa waktu, dan notifikasi diberikan kepada penanggung jawab |
| Tunggu pihak ketiga | Status aktif → Menunggu Pihak Ketiga | Agen yang menangani | Nama pihak ketiga wajib; perkiraan tindak lanjut opsional; SLA berhenti |
| Lanjut setelah pihak ketiga | Menunggu Pihak Ketiga → Dikerjakan | Penanggung jawab sebelumnya atau agen yang diberi kewenangan | Nama pihak ketiga dan waktu tunggu tetap dalam histori; SLA kembali berjalan |
| Nyatakan selesai | Dikerjakan → Menunggu Konfirmasi | Agen yang menangani | Solusi wajib; kontrol khusus layanan harus lulus; hasil data export wajib bila SVC-02 |
| Konfirmasi sesuai | Menunggu Konfirmasi → Ditutup | Pemohon | Catat ditutup berdasarkan konfirmasi pemohon |
| Auto-close | Menunggu Konfirmasi → Ditutup | Scheduler | Default 3 hari kerja tanpa tanggapan; catat ditutup otomatis |
| Hasil belum sesuai sebelum tutup | Menunggu Konfirmasi → Dikerjakan | Pemohon | Alasan wajib; kembali ke penanggung jawab terakhir; tidak menambah jumlah buka kembali; SLA melanjutkan sisa target |
| Buka kembali | Ditutup → Dikerjakan | Pemohon | Maksimal 7 hari kerja; maksimal 3 kali; prioritas tetap; target SLA penuh baru; tanpa triase ulang |
| Tolak | Diproses/Dikerjakan → Ditolak | Agen Tier 1 | Alasan wajib dan terlihat pemohon; final; tidak dapat dibuka kembali |
| Batalkan | Baru → Dibatalkan | Pemohon | Hanya pemohon; setelah klaim Tier 1 tidak boleh membatalkan sendiri |

Setiap transisi yang berhasil atau ditolak oleh sistem menghasilkan audit log. Aksi status harus berupa perintah domain yang memvalidasi status asal, pelaku, data wajib, dan hak akses; pengguna tidak boleh mengubah nilai status secara langsung.

### 2.10 Antrean, triase, dan penugasan

#### Antrean bersama

- Semua tiket Baru masuk ke antrean bersama Tier 1.
- Semua Agen Tier 1 aktif dapat melihat antrean tersebut.
- Tiket Baru tidak memiliki penanggung jawab.
- Urutan antrean menggunakan prioritas Kritis, Tinggi, Sedang, Rendah, lalu waktu pembuatan paling lama terlebih dahulu.
- Klaim dilakukan dalam transaksi database dengan penguncian baris dan pemeriksaan ulang status. Jika dua agen mengklaim tiket yang sama, hanya satu yang berhasil; percobaan lainnya menerima pesan gagal yang dapat dipahami dan tetap dicatat.

#### Triase

Agen Tier 1 memeriksa kelengkapan, menetapkan atau mengubah kategori, menetapkan atau mengubah prioritas, lalu memilih tepat satu hasil:

1. Kerjakan sendiri: status Dikerjakan, tier Tier 1, penanggung jawab agen tersebut.
2. Tugaskan kepada teknisi: status Dikerjakan, tier Tier 2, penanggung jawab teknisi yang dipilih.
3. Tolak: status Ditolak, alasan wajib, final.

Sistem menyarankan teknisi berdasarkan pemetaan kategori masalah dengan bidang keahlian teknisi. Saran tidak mengunci pilihan; Agen Tier 1 boleh memilih teknisi lain. Keputusan aktual, saran yang ditampilkan [USULAN], dan alasan bila tersedia disimpan untuk analisis penugasan.

#### Penugasan dan pengembalian

- Penugasan ke Tier 2 dapat dilakukan saat triase atau setelah tiket mulai dikerjakan.
- Perpindahan Tier 1 ke Tier 2 tidak mengubah status Dikerjakan.
- Agen Tier 2 dapat mengembalikan tiket kepada Agen Tier 1 terakhir yang melakukan triase.
- Saat dikembalikan, status menjadi Diproses dan penerima adalah Agen Tier 1 terakhir tersebut.
- Riwayat penugasan, pengembalian, eskalasi, tier, pelaku, waktu, dan alasan dicatat kronologis.
- Tier 2 tidak menugaskan tiket ke Tier 2 lain pada versi 1.0; penugasan dan perubahan tier dilakukan Agen Tier 1 sesuai hak akses.

### 2.11 Prioritas

Nilai prioritas adalah Kritis, Tinggi, Sedang, dan Rendah. Pemohon memilih prioritas usulan ketika membuat tiket. Agen Tier 1 dapat menyesuaikannya saat triase atau selama penanganan sesuai kewenangan.

Setiap perubahan prioritas wajib:

- memiliki alasan;
- menyimpan nilai sebelum dan sesudah;
- menyimpan pelaku dan waktu;
- masuk ke histori tiket dan audit log;
- dapat dihitung sebagai metrik.

Prioritas digunakan untuk pengurutan antrean tetapi tidak mengubah target SLA. Hanya Agen Tier 1 yang dapat mengubah prioritas pada versi 1.0.

### 2.12 Persetujuan

- Sistem tidak menentukan otomatis apakah tiket membutuhkan persetujuan.
- Tidak ada aturan otomatis berdasarkan jenis layanan atau ambang nilai rupiah.
- Agen Tier 1 atau Tier 2 yang menangani tiket menilai kebutuhan dan menekan aksi Butuh Persetujuan.
- Aksi hanya tersedia ketika status Diproses atau Dikerjakan.
- Semua permintaan diarahkan kepada satu approver aktif, yaitu Manajer TI yang ditetapkan.
- Ketua Tim Kerja tidak memiliki hak menyetujui.
- Saat permintaan dibuat, status, penanggung jawab, dan tier sebelumnya disimpan.
- Setuju mengembalikan ketiganya ke nilai sebelumnya.
- Tidak Setuju mengubah status menjadi Tidak Disetujui, final, dengan catatan wajib yang terlihat sesuai kewenangan pemohon.
- Akun yang masih menggunakan password awal tidak boleh memberikan persetujuan.
- Manajer TI boleh menyetujui pengajuannya sendiri. Pengecualian ini hanya berlaku bila akun tersebut adalah Manajer TI yang ditetapkan, bukan otomatis karena memiliki peran Approver.

Super Admin tidak dapat menonaktifkan atau mencabut peran Approver dari Manajer TI aktif sebelum menetapkan pengganti. Perubahan approver aktif harus memindahkan permintaan persetujuan tertunda secara eksplisit dan menghasilkan audit log.

### 2.13 Komunikasi, informasi tambahan, dan pihak ketiga

Halaman tiket memiliki dua kotak terpisah:

- Balasan ke Pemohon: terlihat oleh pemohon dan petugas berwenang, untuk pertanyaan, perkembangan, dan hasil pekerjaan.
- Catatan Internal: hanya untuk petugas berwenang dan tidak terlihat oleh pemohon.

Masing-masing memiliki tombol kirim sendiri, gaya visual berbeda, dan tidak menggunakan sakelar publik/internal. Setiap kiriman memiliki pelaku, waktu, isi, dan lampiran bila ada.

#### Menunggu Pemohon

- Agen meminta informasi tambahan melalui Balasan ke Pemohon.
- Pertanyaan atau penjelasan wajib diisi.
- Status menjadi Menunggu Pemohon dan SLA berhenti.
- Pemohon menerima notifikasi dalam aplikasi.
- Saat pemohon membalas, status kembali Dikerjakan, penanggung jawab kembali ke nilai sebelumnya, dan penanggung jawab menerima notifikasi.
- Default waktu tunggu adalah 3 hari kerja dan dapat diubah Super Admin.
- Jika waktu habis, tiket menjadi Dikerjakan, tetap ditugaskan kepada penanggung jawab sebelumnya, masuk antrean pribadi dengan flag waktu tunggu berakhir, dan notifikasi dikirim kepada penanggung jawab.
- Kekurangan informasi tidak boleh digunakan sebagai alasan menolak tiket.

#### Menunggu Pihak Ketiga

- Agen dapat memilih status Menunggu Pihak Ketiga.
- Nama pihak ketiga wajib diisi.
- Perkiraan tanggal tindak lanjut boleh diisi.
- SLA berhenti.
- Tiket dipisahkan dari tiket aktif dalam dasbor dan laporan.
- Setelah ketergantungan selesai, penanggung jawab sebelumnya atau agen yang diberi kewenangan mengembalikan status menjadi Dikerjakan dan SLA berjalan kembali.

### 2.14 Penyelesaian, konfirmasi, dan buka kembali

Penyelesaian:

- Agen wajib mengisi solusi sebelum memindahkan tiket ke Menunggu Konfirmasi.
- SLA berhenti ketika status Menunggu Konfirmasi.
- SVC-02 juga harus memiliki minimal satu lampiran bertipe data_export_result yang dapat diakses pemohon.
- SVC-03 harus lulus kontrol perubahan database dan verifikasi hasil.

Konfirmasi:

- Pemohon dapat menyatakan hasil sudah sesuai; tiket menjadi Ditutup.
- Pemohon dapat menyatakan hasil belum sesuai sebelum tiket tertutup; alasan wajib, tiket kembali Dikerjakan, kembali ke penanggung jawab terakhir, tidak melalui triase ulang, tidak menambah jumlah buka kembali, dan SLA melanjutkan sisa target.
- Jika pemohon tidak memberi tanggapan selama 3 hari kerja, tiket ditutup otomatis.
- Histori membedakan penutupan berdasarkan konfirmasi pemohon dan auto-close.

Buka kembali setelah tutup:

- Hanya status Ditutup yang dapat dibuka kembali.
- Maksimal 7 hari kerja setelah penutupan.
- Maksimal 3 kali untuk nomor tiket yang sama.
- Permintaan keempat harus menjadi tiket baru.
- Nomor tiket tetap sama.
- Status menjadi Dikerjakan dan kembali kepada penanggung jawab terakhir.
- Mendapat target SLA penuh yang baru; bila dibuka di luar jam layanan, perhitungan target baru dimulai pada jam layanan berikutnya.
- Prioritas tetap.
- Tidak melalui triase ulang dan tidak menyebabkan eskalasi otomatis ke Tier 2.
- Setiap buka kembali dicatat dalam histori dan audit.

### 2.15 Penolakan dan pembatalan

Pembatalan:

- Hanya pemohon yang dapat membatalkan tiket ketika status Baru.
- Setelah tiket diambil Agen Tier 1, pemohon tidak dapat membatalkan sendiri.
- Agen Tier 1 tidak membatalkan tiket atas nama pemohon. Jika permintaan tidak lagi dibutuhkan setelah diambil, Agen Tier 1 menggunakan Ditolak dengan alasan yang terlihat pemohon.

Penolakan:

- Hanya Agen Tier 1 yang boleh menolak.
- Hanya saat status Diproses atau Dikerjakan.
- Alasan wajib dan terlihat oleh pemohon.
- Bersifat final dan tidak dapat dibuka kembali.
- Bila pemohon tidak lagi membutuhkan permintaan setelah tiket diambil, Agen Tier 1 menggunakan penolakan dengan alasan tersebut.
- Pemohon yang tidak setuju harus membuat tiket baru.

### 2.16 SLA dan kalender layanan

Kebijakan awal:

- Satu target penyelesaian per jenis layanan.
- Tidak ada target respons terpisah.
- SLA tidak dibedakan berdasarkan prioritas.
- Zona waktu Asia/Jakarta atau WIB.
- Jam layanan default Senin–Jumat, 08.00–16.00 WIB; jam Jumat sama.
- Hari libur tidak dihitung.
- Tiket di luar jam layanan mulai dihitung pada jam layanan berikutnya.
- SLA berhenti pada Menunggu Persetujuan, Menunggu Pemohon, Menunggu Pihak Ketiga, dan Menunggu Konfirmasi.
- SVC-07 tidak menggunakan SLA.
- Waktu pengadaan tidak dihitung dalam target SVC-05 dan SVC-06 ketika tiket menunggu pihak ketiga/pengadaan.

Sistem menampilkan:

- sisa waktu SLA;
- tiket yang mendekati batas;
- tiket yang melewati batas;
- kepatuhan SLA di dasbor dan laporan.

Tiket dianggap mendekati batas ketika sisa SLA aktif kurang dari atau sama dengan 20% target layanan. Nilai 20% dapat dikonfigurasi Super Admin. Tiket pada status pause tidak mengurangi sisa SLA dan tidak ditandai overdue sampai SLA kembali berjalan. Kepatuhan SLA diukur pada saat pertama kali masuk Menunggu Konfirmasi; setiap buka kembali memiliki siklus SLA penuh yang terpisah. Target SLA dan kalender yang dipakai tiket disimpan sebagai snapshot atau segmen histori agar perubahan konfigurasi tidak mengubah rekam masa lalu secara diam-diam.

### 2.17 Kontrol perubahan database produksi

Kontrol ini hanya berlaku untuk SVC-03:

1. Sebelum eksekusi, tersedia lampiran bertipe change_script.
2. Tersedia lampiran bertipe rollback_script.
3. Tersedia lampiran bertipe backup_evidence.
4. Ketiganya wajib ada dan harus berasal dari tiga berkas berbeda.
5. Satu berkas tidak boleh memenuhi lebih dari satu tipe.
6. Aksi Mulai Eksekusi ditolak bila persyaratan belum lengkap.
7. Saat berhasil dimulai, sistem mencatat pelaku dan waktu.
8. Percobaan yang ditolak juga dicatat dalam audit log.
9. Pelaksana wajib melakukan verifikasi hasil.
10. Tiket tidak dapat masuk Menunggu Konfirmasi sebelum kontrol dan verifikasi selesai.
11. Semua aktivitas kontrol dicatat dalam audit yang tidak dapat diubah.

Pelaksana yang sama boleh melakukan verifikasi pada versi 1.0, tetapi identitas, waktu, hasil, dan catatan verifikasi wajib dicatat. Kriteria bukti backup, format skrip, dan boleh tidaknya eksekusi diulang menjadi input kebijakan operasional sebelum UAT. Sistem menyimpan tipe lampiran sebagai atribut terpisah, bukan menebak dari nama file.

### 2.18 Permintaan tarik data

Untuk SVC-02:

- hasil pengambilan data wajib menjadi lampiran di tiket;
- lampiran hasil minimal satu;
- tipe lampiran wajib data_export_result;
- lampiran dapat diakses oleh pemohon;
- tiket tidak dapat dipindahkan ke Menunggu Konfirmasi sebelum persyaratan terpenuhi;
- lampiran hasil dihapus 90 hari setelah tiket ditutup sesuai kebijakan retensi;
- histori metadata lampiran, akses, dan penghapusan tetap dicatat sesuai kebijakan audit.

Jika tiket dibuka kembali sebelum 90 hari, penghapusan lampiran hasil ditunda dan dihitung ulang 90 hari setelah penutupan terakhir. Setelah lampiran dihapus, pemohon tidak dapat mengakses file tersebut; metadata penghapusan tetap tersedia dalam histori/audit.

### 2.19 Notifikasi dan pengumuman

Notifikasi versi 1.0 hanya tersedia di dalam aplikasi melalui ikon lonceng dan daftar notifikasi. Tidak ada WhatsApp, email, Telegram, atau Web Push.

Peristiwa yang wajib menghasilkan notifikasi sesuai penerima yang ditetapkan dalam matriks notifikasi:

- tiket dibuat;
- tiket diambil Agen Tier 1;
- tiket ditugaskan;
- tiket dieskalasi;
- komentar baru;
- permintaan persetujuan;
- keputusan persetujuan;
- permintaan informasi kepada pemohon;
- balasan pemohon;
- pekerjaan selesai;
- hasil dinyatakan belum sesuai;
- tiket ditutup;
- tiket dibuka kembali;
- tiket ditolak;
- tiket dibatalkan;
- tiket menunggu pihak ketiga.

Penerima tepat untuk setiap peristiwa, apakah notifikasi dibuat untuk komentar internal, dan kapan notifikasi dianggap dibaca harus diisi pada matriks notifikasi sebelum UAT. Notifikasi menyimpan jenis peristiwa, penerima, objek tiket, waktu dibuat, waktu dibaca, dan tautan internal ke objek.

Pengumuman:

- Agen Tier 1 dapat membuat pengumuman global.
- Pengumuman dapat menyampaikan gangguan jaringan atau informasi layanan TI.
- Pengumuman tampil di dasbor dan di atas formulir pembuatan tiket.
- Pengumuman memiliki masa aktif.
- Pengumuman dapat dinonaktifkan.
- Pengumuman dapat dinonaktifkan oleh pembuatnya atau Agen Tier 1 aktif lainnya.

### 2.20 Dasbor

#### Dasbor Pemohon

- Tiket milik saya.
- Status tiket.
- Perkembangan terbaru.
- Tiket yang membutuhkan jawaban.
- Tiket yang membutuhkan konfirmasi.
- Notifikasi terbaru.

#### Dasbor Agen

- Antrean tiket Baru.
- Tiket yang menjadi tanggung jawabnya.
- Tiket mendekati SLA.
- Tiket melewati SLA.
- Tiket Menunggu Pemohon.
- Tiket Menunggu Pihak Ketiga.
- Persetujuan yang sedang ditunggu.

#### Dasbor Approver

Bagian Perlu Tindakan Saya harus berada paling atas dan menampilkan:

- daftar persetujuan tertunda;
- lama waktu menunggu;
- tautan langsung ke tiket dan aksi keputusan.

#### Dasbor menyeluruh

Untuk Super Admin, Agen Tier 1, dan Manajer TI:

- jumlah tiket;
- sebaran jenis layanan;
- sebaran kategori;
- sebaran status;
- kepatuhan SLA;
- beban kerja per agen;
- jumlah tiket dibuka kembali;
- jumlah perubahan prioritas;
- jumlah persetujuan mandiri;
- tiket yang dibuat mandiri oleh pemohon;
- tiket yang dibuat Agen Tier 1 atas nama pemohon;
- sebaran gangguan per gedung dan lantai.

Rentang waktu default adalah bulan berjalan pada zona waktu Asia/Jakarta. Filter minimum mengikuti dimensi yang telah ditentukan pada dasbor; hak ekspor mengikuti hak laporan dan tidak diberikan kepada Pemohon atau Tier 2 pada versi 1.0.

### 2.21 Laporan bulanan

PIC helpdesk menarik laporan dari aplikasi dan menyampaikannya kepada pimpinan di luar aplikasi. Laporan tersedia untuk diekspor ke Excel dan PDF.

Kolom minimal:

- nomor tiket;
- nama pemohon;
- NIP pemohon;
- tim kerja pemohon saat tiket dibuat;
- deskripsi;
- jenis layanan;
- kategori;
- prioritas;
- waktu melapor;
- waktu dinyatakan selesai;
- waktu ditutup;
- status akhir;
- penanggung jawab;
- solusi;
- pengguna yang membuat tiket;
- penanda tiket dibuat mandiri oleh pemohon.

Nilai historis seperti nama, NIP, tim kerja, jenis layanan, dan kategori yang dipakai dalam laporan harus memiliki snapshot saat tiket dibuat atau perubahan yang dapat ditelusuri. Sebelum UAT, seluruh kolom spreadsheet lama yang masih digunakan pimpinan dibandingkan dengan keluaran laporan aplikasi. Kolom tambahan pada spreadsheet lama yang tidak tercantum di brief menjadi [TBD], bukan otomatis ditambahkan.

### 2.22 Kebutuhan fungsional tingkat produk

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-AUTH-01 | Sistem menyediakan login username dan password serta sesi pengguna. | Must |
| FR-AUTH-02 | Password awal memaksa pengguna menuju halaman ganti password sebelum memakai fungsi lain. | Must |
| FR-AUTH-03 | Pengguna dapat mengganti password sendiri. Super Admin dapat mereset password. | Must |
| FR-AUTH-04 | Pengguna dapat dinonaktifkan tanpa dihapus; akun nonaktif tidak boleh login. | Must |
| FR-AUTH-05 | Sistem mendukung banyak peran per pengguna dan otorisasi server untuk setiap aksi. | Must |
| FR-AUTH-06 | Super Admin dapat melakukan inisialisasi atau reset password awal untuk seluruh pegawai sesuai prosedur distribusi yang disetujui. | Must |
| FR-ORG-01 | Super Admin mengelola pengguna, tim, ketua, anggota, keahlian, dan pemetaan kategori-keahlian melalui aplikasi. | Must |
| FR-MASTER-01 | Super Admin mengelola katalog layanan, field dinamis, kategori, lokasi, SLA, jam layanan, hari libur, timeout, batas lampiran, tipe lampiran, nama instansi, logo, dan konfigurasi umum yang disepakati. | Must |
| FR-CAT-01 | Pemohon memilih satu dari tujuh layanan dan mengisi field dinamisnya. | Must |
| FR-CAT-02 | SVC-01 dan SVC-05 mewajibkan lokasi; layanan lain memperbolehkan lokasi kosong. | Must |
| FR-TICKET-01 | Sistem membuat nomor tiket unik dengan format {KELAS}-{TAHUN}-{NOMOR5DIGIT}, berdasarkan mapping kelas yang disahkan dan subjenis SVC-05. | Must |
| FR-TICKET-02 | Sistem memisahkan pemohon dari pengguna yang mengetik/membuat tiket. | Must |
| FR-TICKET-03 | Sistem mendukung prioritas usulan, lampiran, dan pengumuman aktif sebelum pengiriman. | Must |
| FR-QUEUE-01 | Tiket Baru masuk antrean bersama Tier 1 dan belum memiliki penanggung jawab. | Must |
| FR-QUEUE-02 | Klaim tiket menggunakan transaksi atomik dan mencatat agen pengambil. | Must |
| FR-TRIAGE-01 | Triase memiliki tepat tiga hasil: kerjakan sendiri, tugaskan Tier 2, atau tolak. | Must |
| FR-ASSIGN-01 | Sistem memberi saran teknisi berdasarkan keahlian, tetapi keputusan penugasan tetap manual. | Must |
| FR-ASSIGN-02 | Sistem mencatat seluruh penugasan, perubahan tier, eskalasi, dan pengembalian. | Must |
| FR-PRIORITY-01 | Perubahan prioritas memerlukan alasan dan histori before/after. | Must |
| FR-APPROVAL-01 | Agen dapat meminta persetujuan hanya dari Diproses/Dikerjakan dan sistem menyimpan konteks sebelumnya. | Must |
| FR-APPROVAL-02 | Approver aktif dapat menyetujui atau menolak; penolakan memerlukan catatan dan final. | Must |
| FR-COMM-01 | Balasan ke Pemohon dan Catatan Internal memiliki kotak, tombol, dan visibilitas terpisah. | Must |
| FR-WAIT-01 | Menunggu Pemohon menyimpan pertanyaan, menghentikan SLA, memberi notifikasi, dan melanjutkan ke penanggung jawab sebelumnya saat dibalas. | Must |
| FR-WAIT-02 | Menunggu Pihak Ketiga memerlukan nama pihak ketiga, menghentikan SLA, dan dipisahkan di laporan/dasbor. | Must |
| FR-CLOSE-01 | Solusi wajib sebelum Menunggu Konfirmasi; konfirmasi, auto-close, dan hasil belum sesuai memiliki jejak berbeda. | Must |
| FR-REOPEN-01 | Buka kembali mengikuti jendela 7 hari kerja, maksimal 3 kali, SLA penuh baru, tanpa triase ulang. | Must |
| FR-END-01 | Pemohon hanya membatalkan saat Baru; Tier 1 dapat menolak dengan alasan pada status yang diizinkan. | Must |
| FR-SLA-01 | Sistem menghitung SLA hanya pada kalender jam layanan dan menghentikannya pada status pause. | Must |
| FR-SLA-02 | Dasbor dan laporan menampilkan sisa, mendekati batas pada sisa aktif ≤ 20%, overdue, dan kepatuhan SLA. | Must |
| FR-DBCHANGE-01 | SVC-03 memblokir eksekusi sampai tiga tipe lampiran berbeda dan verifikasi terpenuhi. | Must |
| FR-DBCHANGE-02 | Aksi Mulai Eksekusi dan percobaan ditolak dicatat dengan pelaku dan waktu. | Must |
| FR-EXPORT-01 | SVC-02 memerlukan lampiran data_export_result yang dapat diakses pemohon sebelum Menunggu Konfirmasi. | Must |
| FR-NOTIF-01 | Peristiwa penting menghasilkan notifikasi in-app sesuai penerima. | Must |
| FR-ANNOUNCE-01 | Tier 1 dapat membuat pengumuman global dengan masa aktif dan status aktif/nonaktif. | Must |
| FR-DASH-01 | Sistem menyediakan dasbor per persona dan dasbor menyeluruh sesuai cakupan hak. | Must |
| FR-REPORT-01 | Sistem menghasilkan laporan bulanan dengan seluruh kolom minimal dan ekspor Excel/PDF. | Must |
| FR-AUDIT-01 | Audit log append-only mencatat peristiwa wajib, termasuk aksi yang ditolak. | Must |
| FR-RETENTION-01 | Tiket disimpan 5 tahun; lampiran hasil tarik data dihapus 90 hari setelah penutupan; log aplikasi disimpan minimal 30 hari. | Must |
| FR-OPS-01 | Sistem memberi peringatan kapasitas storage pada 80%, menyediakan backup penuh harian serta incremental/WAL maksimal satu jam, dan mendukung uji pemulihan. | Must |

## 3. AI System Requirements (Jika Berlaku)

Tidak ada fitur AI yang direncanakan atau diwajibkan dalam versi 1.0. Sistem rekomendasi teknisi berbasis bidang keahlian adalah pencarian/pemetaan data master deterministik, bukan model AI.

Karena tidak ada AI:

- tidak ada tool atau API model yang menjadi dependensi;
- tidak ada evaluasi akurasi model;
- tidak ada data prompt, embedding, atau proses inferensi;
- saran teknisi harus dapat dijelaskan dari pemetaan kategori masalah dan bidang keahlian;
- penugasan tetap merupakan keputusan manual Agen Tier 1.

Jika kelak ingin menambahkan AI, fitur tersebut memerlukan PRD atau addendum terpisah yang mencakup tujuan, sumber data, privasi, evaluasi, fallback manual, dan persetujuan pemilik produk.

## 4. Technical Specifications

### 4.1 Rekomendasi stack

Laravel monolith adalah keputusan tetap dari pemilik produk. Komponen berikut adalah [USULAN] yang dipilih agar aplikasi tetap sederhana untuk tim internal, mendukung alur server-side, dan tidak memerlukan SPA terpisah.

| Lapisan | Rekomendasi | Alasan dan batas |
|---|---|---|
| Backend | Laravel versi stabil yang masih didukung pada saat implementasi, berjalan pada PHP versi yang didukung | Menyatukan routing, validasi, policy, ORM, queue, scheduler, storage, dan testing dalam satu aplikasi |
| Presentasi | Blade + Livewire versi kompatibel + Tailwind CSS; Alpine.js hanya untuk interaksi ringan | Form dinamis, antrean, dialog, upload, dan pembaruan server-side tanpa membuat frontend SPA |
| Database | PostgreSQL versi stabil yang disetujui infrastruktur | Transaksi relasional, constraint, JSONB untuk nilai form dinamis, dan penguncian baris untuk klaim antrean |
| Queue/cache/rate limit | Redis | Menjalankan pekerjaan latar belakang, notifikasi, auto-close, pembersihan, dan rate limiting tanpa membebani request |
| Penyimpanan lampiran | Object storage privat yang kompatibel S3 atau private filesystem di luar direktori publik | Lampiran tidak boleh dapat diakses melalui URL publik; pilihan final mengikuti infrastruktur |
| Autentikasi dan otorisasi | Auth Laravel + model roles/user_roles + Policy/Gate domain | Enam peran, multi-peran, aturan khusus self-approval, dan pemeriksaan server-side yang eksplisit |
| Excel | Laravel-compatible package berbasis PhpSpreadsheet | Mendukung ekspor laporan tabular; package dan versi harus melewati review dependency |
| PDF | Renderer PDF server-side yang kompatibel dengan kebutuhan layout, mulai dari Dompdf | Laporan bulanan tidak memerlukan aplikasi desktop; headless browser hanya bila UAT membuktikan diperlukan |
| Testing | PHPUnit/Laravel feature tests, Livewire tests, database tests, dan browser regression untuk alur kritis | Menjamin aturan status, otorisasi, concurrency, upload, SLA, serta ekspor |
| Web/runtime | Nginx atau web server setara, PHP-FPM, HTTPS, Linux server atau platform internal yang disetujui | Sesuai karakter aplikasi web internal; detail sizing dan topologi adalah keputusan infrastruktur |
| Operasi | Laravel scheduler, queue worker, dan monitoring queue; Laravel Horizon [Should] bila Redis dipakai | Auto-close, SLA, notifikasi, retensi, peringatan storage, dan ekspor dapat berjalan di luar request pengguna |

Tidak ada React, Vue, Next.js, Inertia, aplikasi mobile native, atau backend API publik pada versi 1.0. API internal hanya boleh ditambahkan jika dibutuhkan oleh implementasi yang disetujui dan tidak mengubah ruang lingkup.

Rujukan teknis resmi untuk keputusan ini:

- [Laravel database transactions](https://laravel.com/docs/13.x/database) dan [query locking](https://laravel.com/docs/13.x/queries).
- [Laravel queues](https://laravel.com/docs/13.x/queues).
- [Livewire forms](https://livewire.laravel.com/docs/4.x/forms) dan [file uploads](https://livewire.laravel.com/docs/4.x/uploads).
- [PostgreSQL explicit row-level locking](https://www.postgresql.org/docs/current/explicit-locking.html).
- [Redis job queues](https://redis.io/docs/latest/develop/use-cases/job-queue/).

Versi package, strategi deployment, ukuran server, dan apakah object storage tersedia adalah [TBD] pada fase technical discovery.

### 4.2 Arsitektur dan aliran data

Arsitektur logis:

1. Browser pengguna terhubung melalui HTTPS.
2. Web server meneruskan request ke Laravel monolith.
3. Laravel memvalidasi sesi, peran, policy, aturan domain, dan input.
4. Data transaksi disimpan di PostgreSQL melalui service/action domain dan transaksi database.
5. Lampiran disimpan di storage privat; metadata, tipe, hash, pemilik, dan hubungan tiket disimpan di database.
6. Redis digunakan untuk queue, cache yang aman, rate limit, dan pekerjaan terjadwal.
7. Queue worker memproses notifikasi in-app, auto-close, peringatan SLA, ekspor, cleanup, dan pekerjaan lain yang tidak boleh memperlambat request.
8. Scheduler menjalankan pemeriksaan waktu tunggu, kalender SLA, retensi, dan kapasitas storage.

Komponen tidak boleh melewati Laravel untuk memeriksa otorisasi lampiran atau mengubah status tiket. Pengguna tidak boleh mengakses database atau storage secara langsung.

### 4.3 Batas integrasi

Versi 1.0 tidak terintegrasi dengan WhatsApp, email, Telegram, Web Push, SSO, LDAP, Active Directory, SIMPEL, atau API aplikasi internal lain. Integrasi yang ada hanya komponen internal deployment:

- database PostgreSQL;
- Redis;
- private object storage atau private filesystem;
- scheduler dan queue worker;
- web server dan HTTPS;
- library ekspor Excel/PDF.

Pengiriman password awal dan reset password harus mengikuti SOP aman yang ditetapkan Super Admin karena email dan kanal eksternal tidak tersedia dalam versi 1.0. Password sementara hanya dapat dipakai untuk satu kali inisialisasi dan wajib diganti saat login pertama.

### 4.4 Konsistensi, concurrency, dan atomisitas

Klaim tiket Baru wajib memenuhi pola berikut:

1. Mulai transaksi database.
2. Pilih satu tiket dengan status Baru dan penanggung jawab kosong menggunakan row-level lock.
3. Verifikasi ulang status dan penanggung jawab di dalam transaksi.
4. Isi penanggung jawab, tier Tier 1, waktu klaim, dan status Diproses.
5. Tulis histori status, histori penugasan, dan audit log.
6. Commit.
7. Kirim notifikasi setelah commit.

Jika baris telah diklaim oleh transaksi lain, operasi kedua tidak boleh mengubah tiket. Pesan kegagalan dan percobaan aksi yang ditolak dicatat. Constraint database harus mencegah nomor tiket ganda, referensi relasi tidak valid, dan satu lampiran memenuhi dua tipe kontrol pada tiket SVC-03.

Operasi status, approval, eksekusi database, dan penutupan juga memakai transaksi. Pekerjaan queue yang bergantung pada data baru dikirim setelah commit agar tidak membaca keadaan transaksi yang belum selesai.

### 4.5 Pemisahan jenis data

#### Master data

Master data adalah data dasar yang digunakan berulang kali:

- pengguna dan status aktif;
- peran dan pemberian peran;
- tim kerja, ketua, dan anggota;
- bidang keahlian teknisi;
- kategori masalah;
- pemetaan kategori masalah dengan bidang keahlian;
- jenis layanan;
- definisi field dinamis dan pilihan field;
- gedung, lantai, dan ruangan;
- tipe lampiran yang diizinkan.

Master data memiliki identitas, status aktif/nonaktif bila relevan, waktu perubahan, dan pelaku perubahan. Data yang sudah dipakai tiket tidak boleh dihapus secara fisik bila penghapusan merusak histori.

#### Data kebijakan dan konfigurasi sistem

Data ini mengendalikan perilaku aplikasi dan dikelola melalui antarmuka Super Admin:

- target SLA per jenis layanan;
- jam layanan dan zona waktu;
- hari libur;
- lama tunggu konfirmasi;
- lama tunggu informasi tambahan;
- batas ukuran lampiran;
- jenis file yang diperbolehkan;
- nama instansi dan logo;
- pengaturan umum aplikasi yang disepakati;
- identitas approver aktif dan Manajer TI bila keputusan model tersebut disetujui.

Konfigurasi infrastruktur seperti secret database, sertifikat HTTPS, kredensial object storage, dan proses deployment tetap merupakan tanggung jawab lingkungan operasi, bukan data bisnis yang diedit pengguna. Batas ini harus dikonfirmasi agar tuntutan “semua konfigurasi melalui antarmuka” tidak disalahartikan.

#### Data transaksi

Data transaksi muncul dari aktivitas operasional:

- tiket dan nomor tiket;
- snapshot pemohon, pembuat, tim, NIP, layanan, kategori, dan lokasi;
- nilai field dinamis;
- komentar publik dan internal;
- lampiran dan metadata akses;
- penugasan aktif;
- permintaan dan keputusan persetujuan;
- notifikasi;
- pengumuman;
- solusi, verifikasi, dan penutupan;
- permintaan buka kembali;
- kontrol eksekusi SVC-03;
- hasil data export SVC-02.

#### Riwayat dan audit

Riwayat menjelaskan evolusi transaksi:

- histori status;
- histori penugasan, tier, eskalasi, dan pengembalian;
- histori perubahan prioritas;
- histori pause/resume dan perhitungan SLA;
- histori approval;
- histori komentar dan akses lampiran;
- histori buka kembali dan alasan penutupan;
- histori kontrol eksekusi dan verifikasi;
- audit log lintas objek, termasuk aksi yang ditolak.

Riwayat transaksi boleh dibaca sesuai hak akses, tetapi tidak boleh diedit melalui antarmuka operasional. Audit log memiliki kontrol append-only yang lebih ketat.

### 4.6 Model data konseptual

Model berikut adalah model konseptual, bukan skema migration final. Nama tabel dan tipe kolom dapat berubah selama technical design selama aturan dan jejak histori tetap terjaga.

#### Entitas identitas dan organisasi

| Entitas | Relasi dan atribut kunci |
|---|---|
| User | username, nama, NIP, status aktif, password awal, password changed at, profil organisasi |
| Role | enam peran yang ditentukan brief |
| UserRole | relasi banyak-ke-banyak user dan role, pelaku pemberian, waktu mulai/akhir bila diperlukan |
| WorkTeam | nama tim kerja dan status |
| TeamMembership | user, satu tim utama aktif, histori perpindahan, dan status |
| TeamChair | user yang menjadi ketua tim, periode/status |
| Skill | bidang keahlian teknisi |
| UserSkill | relasi teknisi dan skill |
| CategorySkill | pemetaan kategori masalah dengan skill |

#### Entitas master dan konfigurasi

| Entitas | Relasi dan atribut kunci |
|---|---|
| ServiceType | kode SVC, nama, deskripsi, kelas nomor, status aktif, kebijakan SLA |
| ServiceFieldDefinition | service type, key, label, tipe field, required, aturan validasi, urutan, versi |
| ServiceFieldOption | pilihan untuk field tertentu |
| ProblemCategory | kode/nama kategori dan status |
| Building | gedung |
| Floor | lantai dalam gedung |
| Room | ruangan dalam lantai |
| SlaPolicy | target, satuan hari kerja, berlaku mulai, status |
| ServiceCalendar | zona waktu, hari layanan, jam buka/tutup |
| Holiday | tanggal, nama, status |
| SystemSetting | kunci konfigurasi, nilai, tipe, versi, pelaku perubahan |
| AttachmentPolicy | ukuran, ekstensi/MIME, tipe lampiran, status |

#### Entitas transaksi

| Entitas | Relasi dan atribut kunci |
|---|---|
| Ticket | nomor, kelas, pemohon, pembuat, layanan, kategori, prioritas, status, lokasi, deskripsi, solusi, penanggung jawab, tier, timestamp, closure reason |
| TicketFieldValue | tiket, definisi field/snapshot label, nilai terstruktur |
| TicketAssignment | tiket, dari/to user, dari/to tier, aksi, alasan, pelaku, waktu |
| TicketStatusHistory | tiket, status sebelum/sesudah, aksi, pelaku, waktu, metadata |
| TicketPriorityHistory | tiket, prioritas sebelum/sesudah, alasan, pelaku, waktu |
| TicketSlaSegment | tiket, mulai/berakhir, aktif/pause, alasan pause, target snapshot |
| TicketComment | tiket, penulis, visibility publik/internal, isi, waktu |
| Attachment | tiket/komentar, tipe, nama asli, storage key privat, ukuran, MIME, hash, uploader, visibility, deleted at |
| ApprovalRequest | tiket, status sebelumnya, penanggung jawab sebelumnya, tier sebelumnya, approver, waktu meminta, waktu putus, catatan |
| ThirdPartyWait | tiket, nama pihak ketiga, perkiraan tindak lanjut, mulai/selesai, catatan |
| DatabaseChangeControl | tiket, status tiga bukti, execution started, verifier, verified at, hasil |
| ReopenRecord | tiket, nomor urut, requester, alasan, waktu tutup sebelumnya, waktu buka kembali |
| Notification | user penerima, jenis, tiket/objek, dibuat, dibaca |
| Announcement | judul, isi, pembuat, masa aktif, aktif/nonaktif |

#### Entitas audit dan operasi

| Entitas | Relasi dan atribut kunci |
|---|---|
| AuditLog | pelaku atau anonymous context, aksi, objek, waktu, before/after, alasan, IP, user agent, request/correlation ID, hasil |
| LoginAttempt | user/username, berhasil/gagal, waktu, IP, alasan teknis yang aman |
| ReportExport | jenis laporan, periode, peminta, format, status, file, waktu |
| StorageMetric | waktu ukur, kapasitas terpakai, ambang, status peringatan |
| ApplicationLog | log teknis dengan retensi minimal 30 hari dan tanpa password/secrets |

Relasi penting:

- User memiliki banyak Role melalui UserRole.
- User dapat memiliki TeamMembership dan UserSkill.
- ServiceType memiliki banyak ServiceFieldDefinition dan satu kebijakan SLA aktif.
- Building memiliki banyak Floor; Floor memiliki banyak Room.
- Ticket memiliki satu pemohon, satu pembuat, satu layanan, satu status aktif, nol atau satu penanggung jawab aktif, banyak komentar, lampiran, assignment, histori, notifikasi terkait, dan audit.
- ApprovalRequest menyimpan konteks sebelum menunggu persetujuan sehingga persetujuan tidak kehilangan state sebelumnya.
- TicketSlaSegment memisahkan waktu aktif dan pause agar sisa SLA dapat dihitung ulang.

Snapshot yang disarankan untuk laporan:

- nama dan NIP pemohon;
- tim kerja pemohon saat tiket dibuat;
- nama/kode layanan;
- nama/kode kategori;
- lokasi;
- nama penanggung jawab saat peristiwa laporan;
- target SLA dan kalender yang digunakan.

### 4.7 Retensi dan siklus hidup data

| Data | Kebijakan |
|---|---|
| Tiket dan histori bisnis | Disimpan 5 tahun |
| Audit log | Retensi minimum mengikuti kebutuhan audit dan kebijakan organisasi; periode final [TBD], tidak boleh lebih pendek dari periode yang diwajibkan |
| Lampiran hasil SVC-02 | Dihapus 90 hari setelah tiket ditutup, dengan metadata penghapusan tetap dicatat |
| Lampiran lainnya | Mengikuti retensi tiket dan kebijakan storage yang disahkan |
| Notifikasi | Periode retensi [TBD]; harus cukup untuk kebutuhan operasional |
| Log aplikasi | Minimal 30 hari |
| Backup | Backup penuh harian, incremental/WAL maksimal setiap satu jam, RPO maksimal satu jam, RTO maksimal empat jam layanan; retensi dan lokasi mengikuti SOP infrastruktur |

Job penghapusan harus idempotent, menghasilkan audit/operational log, tidak menghapus ticket record, dan tidak menghapus lampiran yang bukan tipe data_export_result hanya karena job tersebut. Jadwal pembersihan harus diuji pada lingkungan non-produksi.

### 4.8 Kebutuhan non-fungsional

| ID | Kebutuhan | Cara verifikasi | Prioritas |
|---|---|---|---|
| NFR-01 | Antarmuka sepenuhnya berbahasa Indonesia. | Review string UI, validasi, notifikasi, dan laporan | Must |
| NFR-02 | Seluruh alur pemohon responsif di ponsel. | UAT pada ukuran layar ponsel yang disepakati | Must |
| NFR-03 | Pemohon dapat membuat tiket sederhana kurang dari 2 menit. | Uji tugas dengan skenario sederhana dan stopwatch | Must |
| NFR-04 | Halaman daftar tiket dimuat kurang dari 2 detik pada sekitar 300 tiket aktif. | Uji performa pada data representatif dengan P95 | Must |
| NFR-05 | Penyimpanan tiket baru kurang dari 1 detik. | Uji request end-to-end pada lingkungan target dengan P95 | Must |
| NFR-06 | Mendukung sekitar 30 pengguna aktif bersamaan. | Load test dengan skenario baca, buat, klaim, dan komentar | Must |
| NFR-07 | Mendukung minimal 40 tiket baru per hari dan sekitar 5.000 tiket per tahun. | Uji kapasitas dan review desain indeks/retensi | Must |
| NFR-08 | Ketersediaan minimal 99% selama jam layanan. | Monitoring bulanan dan laporan downtime | Must |
| NFR-09 | Database dicadangkan setiap hari. | Bukti job backup berhasil dan alarm kegagalan | Must |
| NFR-10 | Pemulihan backup diuji sebelum go-live. | Restore drill dengan bukti hasil dan waktu pemulihan | Must |
| NFR-11 | Sistem memberi peringatan saat storage mencapai 80%. | Simulasi ambang storage dan verifikasi notifikasi in-app/admin | Must |
| NFR-12 | Log aplikasi disimpan minimal 30 hari. | Review kebijakan retensi dan penghapusan | Must |
| NFR-13 | Konfigurasi produk yang disebutkan dalam PRD dapat diubah melalui antarmuka. | Uji perubahan oleh Super Admin tanpa kode/database | Must |
| NFR-14 | Password di-hash aman, CSRF aktif, login dibatasi, HTTPS digunakan, dan otorisasi server diterapkan. | Security test dan review konfigurasi | Must |
| NFR-15 | Lampiran berada di luar direktori publik dan diakses melalui rute terotorisasi. | Uji direct URL, role matrix, dan access log | Must |
| NFR-16 | Tiket disimpan 5 tahun dan hasil tarik data dihapus setelah 90 hari dari penutupan. | Uji scheduled jobs dan data lifecycle | Must |
| NFR-17 | Sistem mencatat audit append-only tanpa edit/hapus melalui aplikasi. | Negative test dan inspeksi hak database | Must |
| NFR-18 | Seluruh konfigurasi penting yang termasuk ruang lingkup bisnis memiliki histori perubahan. | Uji perubahan master/config dan audit | Must |

Pengukuran NFR-04 dan NFR-05 menggunakan P95 pada lingkungan target. Browser minimum, maintenance window, dan definisi availability tetap menjadi input technical discovery; RPO/RTO ditetapkan maksimal satu jam kehilangan data dan maksimal empat jam layanan untuk pemulihan.

### 4.9 Keamanan dan privasi

#### Identitas dan sesi

- Password disimpan dengan hashing aman, bukan enkripsi reversibel.
- Pengguna yang masih menggunakan password awal selalu diarahkan ke ganti password dan tidak dapat memberikan persetujuan.
- Percobaan login dibatasi dengan rate limiting; pesan gagal tidak membocorkan apakah username ada.
- Session cookie aman, HTTPS wajib, dan CSRF protection tidak boleh dinonaktifkan pada formulir state-changing.
- Akun nonaktif tidak dapat login atau menerima hak operasional baru.

#### Otorisasi

- Setiap route, action, download, dan perubahan domain memeriksa server-side authorization.
- Pemeriksaan mencakup peran, status aktif, cakupan tiket, status saat ini, dan precondition bisnis.
- Super Admin tidak otomatis dapat melakukan aksi Tier 1/Tier 2.
- Role Approver saja tidak memberi pengecualian self-approval.
- Akses Ketua Tim dibatasi pada tiket anggota sesuai aturan yang disahkan.

#### Lampiran

- Lampiran disimpan di luar direktori publik.
- Download melalui controller/route yang memeriksa hak akses pada setiap request.
- Nama file asli tidak digunakan sebagai storage path.
- Jenis, MIME, ekstensi, ukuran, dan tipe lampiran divalidasi berdasarkan konfigurasi aktif.
- Lampiran publik untuk pemohon hanya terlihat jika tiket dan tipe lampirannya memang berwenang.
- Akses lampiran sukses maupun ditolak masuk audit log sesuai kebijakan.
- Virus/malware scanning adalah [Should] teknis yang perlu diputuskan berdasarkan infrastruktur dan risiko; tidak menggantikan validasi tipe/ukuran.

#### Data pribadi dan kepatuhan

- NIP, nama, lokasi, komentar, dan lampiran diperlakukan sebagai data yang perlu dilindungi.
- Pemrosesan, hak akses, retensi, penghapusan lampiran, dan backup harus ditinjau terhadap peraturan perlindungan data pribadi yang berlaku serta kebijakan instansi.
- Data sensitif tidak boleh masuk application log, queue payload yang tidak perlu, atau pesan error kepada pengguna.
- Lingkungan development/test menggunakan data sintetis atau data yang telah disamarkan.

### 4.10 Audit dan kontrol perubahan

Audit log wajib mencatat:

- login;
- perubahan status;
- pengambilan tiket;
- penugasan;
- eskalasi;
- pengembalian ke Tier 1;
- perubahan prioritas;
- permintaan dan keputusan persetujuan;
- komentar;
- perubahan peran;
- perubahan master data;
- perubahan konfigurasi;
- akses lampiran;
- percobaan melakukan aksi yang ditolak sistem.

Setiap entri memuat:

- pelaku atau konteks anonymous untuk percobaan login;
- waktu dengan timezone standar;
- jenis aksi;
- objek dan identifier;
- hasil berhasil/ditolak;
- perubahan before/after jika ada;
- alasan atau pesan business rule yang aman;
- IP, user agent, dan request/correlation ID bila tersedia.

Audit harus append-only:

- UI tidak menyediakan edit/hapus;
- role aplikasi tidak memiliki operasi update/delete pada tabel audit;
- [USULAN] gunakan database role terpisah atau trigger/constraint untuk mengurangi risiko perubahan langsung;
- maintenance dan migrasi harus memiliki prosedur terkontrol;
- Super Admin hanya dapat melihat dan menyaring, bukan mengubah isi audit.

Percobaan aksi yang ditolak harus ditulis walaupun aksi bisnis tidak menghasilkan perubahan tiket. Mekanisme penulisan audit denied action tidak boleh hilang hanya karena transaksi bisnis dibatalkan.

### 4.11 Acceptance criteria

Kriteria berikut menjadi definisi selesai minimum untuk UAT versi 1.0. Semua skenario dijalankan dengan kombinasi peran yang relevan dan hasil audit diperiksa.

| ID | Given/When/Then | Prioritas |
|---|---|---|
| AC-01 | Given pengguna memakai password awal, when membuka aplikasi, then pengguna hanya dapat menuju ganti password dan tidak dapat menyetujui tiket. | Must |
| AC-02 | Given pengguna memiliki Super Admin tanpa peran operasional, when mencoba klaim atau menangani tiket, then server menolak dan mencatat percobaan. | Must |
| AC-03 | Given pemohon memilih layanan, when form dimuat, then field dinamis dan pengumuman aktif yang sesuai tampil; SVC-01/SVC-05 menolak tanpa lokasi. | Must |
| AC-04 | Given Agen Tier 1 membuat tiket atas nama pegawai, when tiket tersimpan, then pemohon, pembuat, NIP/tim snapshot, dan flag self-created tersimpan terpisah. | Must |
| AC-05 | Given mapping kelas dan subjenis SVC-05 telah disahkan, when tiket dibuat, then nomor memakai format {KELAS}-{TAHUN}-{NOMOR5DIGIT}, kelas yang benar, tidak bertabrakan pada request bersamaan, dan nomor tidak digunakan ulang. | Must |
| AC-06 | Given tiket dibuat, when pemohon membuka dasbor, then pemohon hanya melihat tiket dan komentar publik yang berwenang serta dapat melihat status/perkembangan. | Must |
| AC-07 | Given dua Agen Tier 1 mengklaim tiket Baru bersamaan, when transaksi selesai, then tepat satu klaim berhasil, satu tiket menjadi Diproses, dan percobaan lainnya gagal serta diaudit. | Must |
| AC-08 | Given Agen Tier 1 melakukan triase, when memilih hasil, then hanya Kerjakan sendiri, Tugaskan Tier 2, atau Tolak yang tersedia; penolakan memerlukan alasan. | Must |
| AC-09 | Given kategori memiliki mapping skill, when triase dibuka, then sistem menampilkan saran teknisi; when agen memilih teknisi lain, then keputusan manual tetap diizinkan dan tercatat. | Must |
| AC-10 | Given tiket Diproses/Dikerjakan, when agen meminta persetujuan, then status menjadi Menunggu Persetujuan dan state sebelumnya tersimpan; approver aktif dapat memulihkan state atau menolak final dengan catatan wajib; self-approval hanya berhasil untuk Manajer TI aktif yang sudah mengganti password awal. | Must |
| AC-11 | Given petugas mengirim Balasan ke Pemohon dan Catatan Internal, when pemohon melihat tiket, then hanya balasan publik terlihat dan kedua aksi memiliki tombol/input terpisah. | Must |
| AC-12 | Given agen meminta informasi, when status Menunggu Pemohon, then SLA berhenti, pertanyaan wajib, notifikasi pemohon dibuat, dan balasan pemohon mengembalikan tiket ke penanggung jawab sebelumnya; setelah 3 hari kerja tanpa balasan, tiket menjadi Dikerjakan dengan flag timeout dan notifikasi penanggung jawab. | Must |
| AC-13 | Given tiket Menunggu Pihak Ketiga, when agen menyimpan status, then nama pihak ketiga wajib, SLA berhenti, tiket muncul pada bucket tunggu pihak ketiga, dan saat dilanjutkan kembali menjadi Dikerjakan dengan SLA berjalan. | Must |
| AC-14 | Given agen mengisi solusi dan syarat layanan terpenuhi, when menandai selesai, then status Menunggu Konfirmasi; konfirmasi menutup dengan alasan konfirmasi; timeout menutup dengan alasan auto-close. | Must |
| AC-15 | Given Menunggu Konfirmasi, when pemohon menyatakan belum sesuai, then alasan wajib, tiket kembali Dikerjakan kepada penanggung jawab terakhir, tidak melalui triase, dan reopen count tidak berubah. | Must |
| AC-16 | Given tiket Ditutup, when pemohon membuka kembali dalam 7 hari kerja dan count kurang dari 3, then nomor tetap, status Dikerjakan, penanggung jawab terakhir kembali, dan SLA penuh baru dibuat. | Must |
| AC-17 | Given tiket Baru, when pemohon membatalkan, then status Dibatalkan; given Diproses/Dikerjakan, when Tier 1 menolak tanpa alasan, then server menolak. | Must |
| AC-18 | Given tiket lintas jam layanan, hari libur, dan status pause, when SLA dihitung, then hanya jam layanan aktif yang mengurangi sisa target; indikator mendekati batas muncul pada sisa aktif ≤ 20%; buka kembali memulai siklus SLA penuh baru. | Must |
| AC-19 | Given SVC-03 kurang satu dari tiga tipe bukti atau memakai satu file untuk dua tipe, when Mulai Eksekusi diklik, then aksi ditolak dan percobaan dicatat. | Must |
| AC-20 | Given SVC-03 memiliki tiga bukti dan verifikasi selesai atau SVC-02 memiliki data_export_result, when agen mencoba Menunggu Konfirmasi, then aksi hanya berhasil bila kontrol layanan terkait terpenuhi. | Must |
| AC-21 | Given peristiwa penting terjadi, when transaksi commit, then penerima yang ditetapkan mendapatkan notifikasi in-app tanpa mengirim kanal eksternal. | Must |
| AC-22 | Given user membuka dasbor atau laporan, when data dirender, then cakupan data mengikuti role dan laporan memuat semua kolom minimal serta dapat diekspor ke Excel/PDF. | Must |
| AC-23 | Given Super Admin mengubah master/config dari UI, when perubahan disimpan, then tidak perlu kode/database langsung dan before/after serta pelaku masuk histori/audit. | Must |
| AC-24 | Given pengguna mencoba akses tiket, komentar, lampiran, atau aksi tanpa hak, when request masuk, then server menolak, tidak membocorkan data, dan audit mencatat percobaan. | Must |
| AC-25 | Given audit log telah dibuat, when Super Admin mencoba mengubah/menghapus melalui UI, then tidak ada aksi tersebut; role database aplikasi juga tidak dapat melakukan update/delete. | Must |
| AC-26 | Given data 300 tiket aktif dan 30 pengguna bersamaan, when skenario performa dijalankan, then target NFR-04 sampai NFR-07 terpenuhi atau deviation disetujui tertulis. | Must |
| AC-27 | Given backup harian dan incremental/WAL maksimal satu jam berjalan, when restore drill dijalankan sebelum go-live, then database dan lampiran yang diperlukan dapat dipulihkan dengan RPO maksimal satu jam dan RTO maksimal empat jam layanan. | Must |
| AC-28 | Given Ketua Tim membuka tiket anggota timnya, when data ditampilkan, then hanya metadata read-only, status, SLA, penanggung jawab, balasan publik, dan solusi terlihat; Catatan Internal serta lampiran privat tidak dapat diakses. | Must |

## 5. Risks & Roadmap

### 5.1 Keputusan yang sudah dikonfirmasi

Keputusan berikut berasal dari jawaban pemilik produk:

- Aplikasi wajib menggunakan Laravel monolith.
- Stack pendukung dapat dipilih berdasarkan kebutuhan aplikasi.
- Belum ada deadline dan anggaran yang ditetapkan.
- Target keberhasilan boleh diusulkan dan harus divalidasi.
- Keputusan inti pada PRD ini telah disetujui; input operasional yang belum tersedia dipisahkan dari aturan bisnis dan harus dipenuhi sebelum UAT/go-live.

### 5.2 Keputusan final dan asumsi operasional

Keputusan berikut telah disetujui pemilik produk dan menjadi aturan versi 1.0:

1. Zona waktu operasional adalah Asia/Jakarta.
2. Format nomor adalah {KELAS}-{TAHUN}-{NOMOR5DIGIT}; urutan dipisah per kelas/tahun dan tidak pernah digunakan ulang.
3. Mapping kelas adalah SVC-01 INC, SVC-02 REQ, SVC-03 CHG, SVC-04 CHG, SVC-05 INC untuk perbaikan atau REQ untuk permintaan, SVC-06 REQ, dan SVC-07 CHG.
4. SVC-05 memiliki subjenis Perbaikan atau Permintaan.
5. Manajer TI aktif disimpan sebagai satu konfigurasi, wajib memiliki peran Approver, dan menjadi satu-satunya approver aktif.
6. Self-approval hanya berlaku bagi akun Manajer TI aktif yang sudah mengganti password awal.
7. Timeout Menunggu Pemohon setelah 3 hari kerja mengubah tiket menjadi Dikerjakan, mempertahankan penanggung jawab, memberi flag timeout, mengembalikan SLA dari sisa waktu, dan mengirim notifikasi.
8. Setelah Menunggu Pihak Ketiga selesai, tiket kembali Dikerjakan dan SLA berjalan kembali.
9. Urutan antrean adalah prioritas Kritis, Tinggi, Sedang, Rendah, kemudian waktu pembuatan paling lama.
10. Hanya Agen Tier 1 yang mengubah prioritas, menugaskan, dan mengubah tier pada versi 1.0. Tier 2 tidak menugaskan ke Tier 2 lain.
11. Tiket mendekati batas bila sisa SLA aktif kurang dari atau sama dengan 20% target layanan. Kepatuhan dihitung saat pertama kali masuk Menunggu Konfirmasi; buka kembali memiliki siklus SLA baru.
12. Ketua Tim hanya melihat metadata tiket, status, SLA, penanggung jawab, balasan publik, dan solusi secara read-only; Catatan Internal dan lampiran privat tidak terlihat.
13. Struktur keanggotaan memiliki satu tim utama aktif per pengguna dengan histori perpindahan.
14. Definisi field dinamis yang sudah digunakan tidak diedit; perubahan dibuat sebagai versi baru atau dinonaktifkan.
15. Verifikasi SVC-03 boleh dilakukan pelaksana yang sama pada versi 1.0 dengan bukti pelaku, waktu, hasil, dan catatan.
16. RPO maksimal satu jam dan RTO maksimal empat jam layanan; backup penuh harian ditambah incremental/WAL maksimal satu jam.
17. Password awal dan reset password menggunakan SOP aman Super Admin; password sementara wajib diganti saat login pertama.
18. Pengumuman dapat dinonaktifkan oleh pembuat atau Agen Tier 1 aktif lainnya.
19. Laporan dan ekspor tidak tersedia untuk Pemohon atau Agen Tier 2 pada versi 1.0 dan menggunakan bulan kalender Asia/Jakarta sebagai periode default.

Lampiran SVC-02 yang belum dihapus akan memiliki ulang hitungan retensi 90 hari setelah penutupan terakhir bila tiket dibuka kembali. Jika penanggung jawab terakhir tidak aktif, fallback operasionalnya harus ditetapkan pada technical discovery sebelum UAT.

### 5.3 Input operasional yang harus tersedia sebelum UAT

Item berikut bukan lagi keputusan alur bisnis, tetapi data atau kebijakan input yang diperlukan untuk pengujian:

- Pemilik produk dan pejabat yang menandatangani UAT.
- Daftar kategori masalah, bidang keahlian, dan mapping awal.
- Tipe field dinamis, aturan validasi, serta tahap pengisian bagian Tim TI pada SVC-07.
- Struktur gedung, lantai, ruangan, dan aturan detail lokasi.
- Batas ukuran, jumlah, MIME/ekstensi, dan tipe lampiran.
- Kriteria backup_evidence, format script, dan kebijakan percobaan ulang Mulai Eksekusi.
- Matriks penerima notifikasi, perlakuan komentar internal, dan definisi notifikasi dibaca.
- Kolom tambahan dari spreadsheet lama yang masih diwajibkan pimpinan.
- Retensi audit, notifikasi, dan backup; lokasi backup; browser minimum; maintenance window; serta detail infrastruktur.
- Persetujuan perlindungan data pribadi, keputusan malware scanning, dan validasi layout PDF.
- Fallback bila penanggung jawab tidak aktif saat pemohon membalas atau tiket dibuka kembali.

### 5.4 Dependensi

| Dependensi | Pemilik/kontributor | Dampak bila belum tersedia | Kapan diperlukan |
|---|---|---|---|
| Daftar pengguna, NIP, status aktif, dan mapping peran | Pemilik produk/HR/TI | Seed user dan uji akses tidak dapat dilakukan | Sebelum development dan go-live |
| Daftar 6 tim, ketua, anggota | Pemilik produk | Dasbor tim dan hak akses Ketua tidak dapat divalidasi | Sebelum UAT |
| Daftar gedung, lantai, ruangan | TI/General Affairs | Form lokasi dan laporan geografis tidak lengkap | Sebelum UAT |
| Daftar kategori, keahlian, mapping | Manajer TI | Triase dan saran teknisi tidak dapat diuji | Sebelum Sprint workflow |
| Definisi tujuh katalog dan field dinamis | Pemilik produk/TI | Form dan validasi tidak final | Sebelum Sprint katalog |
| Mapping kelas tiket dan format nomor | Pemilik produk | Nomor tiket tidak dapat dibangun | Sebelum Sprint tiket |
| Kalender jam layanan dan hari libur | Pemilik produk/HR | Perhitungan SLA tidak dapat dipercaya | Sebelum Sprint SLA |
| Nilai batas lampiran dan tipe file | Super Admin/TI | Upload dan kontrol layanan tidak dapat diuji | Sebelum UAT |
| Penetapan Manajer TI/approver aktif | Manajer TI/pemilik produk | Alur approval dan self-approval tidak dapat diuji | Sebelum Sprint approval |
| Spreadsheet lama dan contoh laporan pimpinan | PIC helpdesk/pimpinan | Kesesuaian laporan tidak dapat diverifikasi | Sebelum UAT |
| Infrastruktur PostgreSQL, Redis, storage, backup, HTTPS | Tim infrastruktur | Deployment dan non-functional test terblokir | Sebelum staging |
| Persetujuan kebijakan data pribadi dan retensi | Pimpinan/Legal/TI | Go-live memiliki risiko kepatuhan | Sebelum produksi |

### 5.5 Risiko dan mitigasi

| Risiko | Dampak | Kemungkinan | Mitigasi | Pemilik risiko |
|---|---|---|---|---|
| Pengguna tetap mengirim semua permintaan melalui kanal lama | Cakupan pencatatan rendah | Tinggi | Sosialisasi, tautan katalog yang mudah, prosedur Agen membuat tiket atas nama, ukur adopsi | Pemilik produk |
| Mapping layanan, kategori, dan keahlian tidak lengkap | Triase dan saran teknisi tidak berguna | Tinggi | Workshop master data, seed awal, review sebelum UAT | Manajer TI |
| Perhitungan SLA salah pada jam/hari libur/pause | Laporan kinerja tidak dipercaya | Tinggi | Time-freezing tests, kalender snapshot, simulasi lintas zona, review hasil bisnis | Tech lead + Manajer TI |
| Race condition pada klaim tiket | Pekerjaan ganda atau histori salah | Sedang | Row locking, transaksi, constraint, concurrency test dua agen | Tech lead |
| Catatan internal atau lampiran bocor ke pemohon | Pelanggaran kerahasiaan | Sedang | Policy server-side, private storage, negative access test, audit download | Tech lead + Security |
| Audit dapat diubah oleh aplikasi atau admin | Bukti pengendalian tidak sah | Sedang | Append-only role/trigger, pembatasan DB, negative test, review migration | Tech lead + DBA |
| Approver tunggal menjadi bottleneck | Tiket tertahan | Sedang | Dasbor Perlu Tindakan Saya, metrik waktu tunggu, proses operasional eskalasi di luar aplikasi; delegasi otomatis tetap out of scope | Manajer TI |
| Ketiga teknisi memiliki pekerjaan utama lain | Penumpukan antrean | Tinggi | Dasbor beban, penugasan manual berbasis skill, laporan overdue, review kapasitas | Manajer TI |
| Storage lampiran tumbuh lebih cepat dari rencana | Upload gagal atau biaya meningkat | Sedang | Private object storage, metrik 80%, retensi hasil export, kuota dan review bulanan | Infrastruktur |
| Penghapusan hasil export menghapus bukti yang dibutuhkan | Kehilangan data atau sengketa | Rendah/Sedang | Konfirmasi kebijakan retensi, job idempotent, audit metadata, backup sesuai kebijakan | Pemilik produk + Legal |
| Perubahan konfigurasi mengubah histori lama | Laporan periode lalu berubah | Sedang | Snapshot layanan/SLA/form, audit before/after, larangan edit definisi yang sudah dipakai | Tech lead |
| Laporan baru tidak cocok dengan spreadsheet pimpinan | UAT gagal atau laporan tetap manual | Tinggi | Bandingkan kolom sebelum UAT, sample reconciliation, sign-off pimpinan | PIC helpdesk |
| Password awal didistribusikan tidak aman | Pengambilalihan akun | Sedang | Prosedur distribusi terpisah, forced change, rate limit, larangan approval dengan password awal | Super Admin |
| Retensi data pribadi tidak sesuai kebijakan | Risiko hukum dan reputasi | Sedang | Review perlindungan data pribadi, minimisasi log, retensi configurable yang disahkan | Pimpinan/Legal |

### 5.6 Rencana fase pengembangan

Urutan fase berikut adalah [USULAN]. Durasi dan tanggal tidak ditetapkan karena deadline belum tersedia.

#### Fase 0 — Discovery dan penguncian keputusan

Hasil:

- pemilik produk dan reviewer ditetapkan;
- pertanyaan terbuka berdampak tinggi diputuskan;
- katalog, mapping nomor, kategori, skill, lokasi, user, peran, kalender, SLA, timeout, lampiran, dan approver ditetapkan;
- spreadsheet lama dan definisi laporan pimpinan diterima sebagai bahan perbandingan;
- klasifikasi data dan kebijakan retensi disetujui;
- arsitektur deployment, backup, RPO/RTO, dan storage disepakati.

Gate: tidak ada keputusan inti yang belum disetujui pada nomor tiket, hak akses, status, SLA, approval, kontrol SVC-03, dan retensi; data master dan konfigurasi yang tercantum pada bagian 5.3 tersedia untuk staging.

#### Fase 1 — Fondasi aplikasi

Ruang lingkup:

- Laravel monolith, database, auth, session, password awal, reset/ganti password;
- user, role, status aktif, policy server-side;
- struktur master data dan audit dasar;
- layout responsif bahasa Indonesia;
- pipeline test dan deployment staging.

Output ini adalah fondasi teknis, bukan kandidat go-live.

#### Fase 2 — Katalog, tiket, dan antrean

Ruang lingkup:

- tujuh layanan dan field dinamis;
- lokasi, prioritas, lampiran, announcement;
- pembuatan tiket mandiri dan atas nama;
- nomor INC/REQ/CHG sesuai keputusan;
- antrean Tier 1 dan klaim atomik;
- triase tiga hasil, kategori, saran skill, penugasan, histori.

Gate: AC-03 sampai AC-09 lulus.

#### Fase 3 — Workflow operasional, SLA, approval, dan kontrol

Ruang lingkup:

- seluruh status dan transisi;
- komunikasi publik/internal;
- Menunggu Pemohon dan Menunggu Pihak Ketiga;
- solusi, konfirmasi, auto-close, belum sesuai, buka kembali;
- approval tunggal dan self-approval terbatas;
- kalender SLA, pause/resume, overdue;
- kontrol SVC-03 dan SVC-02;
- notifikasi in-app dan audit lengkap.

Gate: AC-10 sampai AC-21 lulus, termasuk simulasi waktu.

#### Fase 4 — Dasbor, laporan, keamanan, dan UAT

Ruang lingkup:

- seluruh dasbor persona;
- laporan bulanan Excel/PDF;
- rekonsiliasi dengan spreadsheet lama;
- security test, access matrix test, file access test, audit immutability test;
- performance/concurrency test;
- backup restore drill;
- pelatihan Super Admin, PIC, Agen, Approver, dan Ketua Tim.

Gate: AC-22 sampai AC-27 lulus dan semua Must yang gagal memiliki keputusan go/no-go tertulis.

#### Fase 5 — Pilot dan go-live versi 1.0

Ruang lingkup:

- pilot terbatas dengan data dan pengguna yang disetujui;
- pantau adopsi, error, SLA, audit, storage, dan feedback;
- perbaiki defect prioritas tinggi;
- seed data produksi final;
- aktifkan backup dan monitoring;
- go-live dengan runbook dan support window.

#### V1.1 dan V2.0

- V1.1 [Should]: penguatan operasional yang tidak mengubah alur, misalnya monitoring queue yang lebih lengkap, malware scanning lampiran bila diwajibkan, penyempurnaan filter/report, dan perbaikan hasil UAT.
- V2.0 [Could]: evaluasi item yang dikecualikan seperti integrasi kanal, SSO/LDAP/AD, API internal, asset, knowledge base, survei, tiket gangguan massal, 2FA, atau delegasi approver. Masing-masing memerlukan discovery dan keputusan baru.

### 5.7 Kriteria kesiapan go-live

Aplikasi siap go-live hanya jika seluruh kondisi berikut terpenuhi:

1. Pemilik produk dan pemilik operasional telah menandatangani PRD versi 1.0, acceptance criteria, serta input operasional yang dipersyaratkan sebelum UAT.
2. Seluruh kebutuhan Must terimplementasi atau memiliki deviasi tertulis yang disetujui; tidak ada defect kritis/tinggi terbuka.
3. Enam peran dan matriks akses diuji pada server, termasuk pengguna multi-peran, Super Admin tanpa peran operasional, approver aktif, Ketua Tim, dan akun nonaktif.
4. Data awal sekitar 150 pegawai, 6 tim, ketua/anggota, 4 gedung, lantai/ruangan, tujuh layanan, kategori, skill, mapping, jam layanan, hari libur, SLA, timeout, attachment policy, instansi, dan logo telah diverifikasi.
5. Mapping kelas INC/REQ/CHG dan format nomor tiket telah disahkan.
6. Test concurrency membuktikan klaim tiket atomik; hanya satu Agen yang berhasil.
7. Seluruh acceptance criteria AC-01 sampai AC-27 lulus.
8. Perhitungan SLA lulus skenario jam layanan, luar jam, hari libur, semua status pause, timeout, overdue, dan buka kembali.
9. Kontrol SVC-03 menolak eksekusi yang tidak lengkap, menerima tiga tipe bukti berbeda, mencatat verifikasi, dan mengaudit percobaan ditolak.
10. SVC-02 tidak dapat Menunggu Konfirmasi tanpa data_export_result yang dapat diakses pemohon.
11. Catatan internal dan lampiran private lulus uji kebocoran dan negative authorization.
12. Audit log append-only lulus uji UI, policy, database role, serta pencatatan aksi ditolak.
13. Laporan bulanan memuat kolom minimal; hasilnya telah dibandingkan dengan spreadsheet lama dan disetujui pimpinan.
14. Ekspor Excel dan PDF lulus uji isi, periode, encoding bahasa Indonesia, serta hak akses.
15. Load/performance test memenuhi NFR-03 sampai NFR-07 atau deviasinya disetujui.
16. Backup penuh harian, incremental/WAL maksimal satu jam, monitoring storage 80%, log 30 hari, dan restore drill telah berhasil.
17. HTTPS, password hashing, CSRF, login throttling, private storage, dan server-side authorization diverifikasi.
18. Prosedur distribusi password awal, reset, support, incident response, restore, retensi, dan penghapusan lampiran tersedia.
19. Pengguna inti telah dilatih dan tersedia runbook untuk PIC, agen, approver, Super Admin, serta Ketua Tim.
20. Owner KPI dan periode baseline untuk evaluasi bulan pertama sampai ketiga telah ditetapkan.

### 5.8 Checklist persetujuan PRD

| Keputusan | Status |
|---|---|
| Laravel monolith | Disepakati |
| Stack pendukung | Disetujui sebagai rekomendasi; versi package dan sizing ditetapkan pada technical discovery |
| Target deadline dan anggaran | Belum ditetapkan secara administratif; tidak mengubah kebutuhan versi 1.0 |
| Pemilik produk | Belum ditetapkan secara administratif |
| Mapping kelas tiket dan format nomor | Disetujui |
| Identitas approver aktif/Manajer TI | Disetujui |
| Dynamic field dan versioning form | Disetujui; katalog field dan validasi diisi sebelum UAT |
| Timeout menunggu pemohon dan pihak ketiga | Disetujui |
| Threshold mendekati SLA dan definisi kepatuhan | Disetujui |
| Hak visibilitas Ketua Tim | Disetujui |
| Retensi audit, notifikasi, dan backup | Aturan inti disetujui; nilai retensi dan SOP infrastruktur diisi sebelum UAT |
| RPO/RTO | Disetujui: RPO maksimal 1 jam, RTO maksimal 4 jam layanan |
| Infrastruktur detail | Diisi pada technical discovery sebelum staging |
| Kolom spreadsheet lama tambahan | Diisi dan direkonsiliasi sebelum UAT |
| Kebijakan perlindungan data pribadi | Review dan sign-off sebelum go-live |

PRD ini berstatus final untuk baseline pengembangan versi 1.0. Input operasional pada bagian 5.3 tetap menjadi prasyarat staging, UAT, atau go-live, tetapi tidak mengubah keputusan inti yang telah disetujui. Perubahan baru yang mengubah status, hak akses, SLA, retensi, atau ruang lingkup harus dicatat sebagai perubahan versi PRD dan memiliki dampak UAT yang jelas.
