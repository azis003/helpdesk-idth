# Regression Baseline Hardening

Dokumen ini adalah hasil authoritative fase **W0.1b — Baseline Test Hardening** pada branch `redesign-frontend`, 13 Agustus 2026 (Asia/Jakarta). Diagnosis awal tetap berada di `docs/redesign/07-REGRESSION-BASELINE-DIAGNOSIS.md`.

## Scope

Fase ini memperbaiki tiga test stale/colliding yang telah diklasifikasikan sebagai test atau test-infrastructure issue. Perubahan hanya menyelaraskan fixture dan assertion dengan kontrak aplikasi yang sudah aktif; fase ini bukan application bug fix dan bukan UI redesign.

Guardrail yang diterapkan:

- production behavior, backend, route, controller, request validation, policy, service/domain workflow, model, migration, database schema, authentication, authorization, session, resource frontend, konfigurasi produksi, dan dependency tidak diubah;
- test tidak dihapus, tidak di-skip, dan tidak diganti dengan assertion trivial;
- assertion yang sebelumnya bermakna dipertahankan atau diganti dengan assertion yang lebih presisi;
- tidak ada patch permanen pada `vendor/`;
- seluruh command test dijalankan dengan `XDEBUG_MODE=off`;
- runner authoritative tetap PHPUnit native, kemudian hasilnya dibandingkan dengan wrapper Laravel Artisan.

## Files Changed

Perubahan W0.1b hanya berada pada empat file berikut:

| File | Jenis perubahan |
|---|---|
| `tests/Feature/Admin/CatalogManagementTest.php` | Fixture, duplicate rejection, update-flow, dan persistence assertion attachment policy. |
| `tests/Feature/Admin/OrganizationManagementTest.php` | Assertion navigasi presentasional lama diganti kontrak DOM/XPath yang semantik. |
| `tests/Feature/DashboardTest.php` | Assertion Tier 2 dipersempit ke struktur, URL, dan data-scope aktual. |
| `docs/redesign/08-REGRESSION-BASELINE-HARDENING.md` | Evidence dan keputusan gate fase ini. |

Dokumen `00` sampai `07` merupakan hasil fase sebelumnya dan tidak diubah oleh W0.1b. Tidak ada file baru atau perubahan pada dependency.

## TST-01 Catalog Remediation

Target:

`CatalogManagementTest::test_super_admin_can_manage_location_hierarchy_and_attachment_policy`

Root cause sebelumnya adalah collision antara fixture test dan record canonical global yang sudah dibuat migration:

```text
service_type_id = null
type_key = supporting
```

Remediasi:

1. create flow menggunakan kombinasi valid dan unik `SVC-01 + supporting`;
2. record diambil kembali dengan exact scope `service_type_id` dan `type_key`, sehingga tidak mungkin salah memilih record global;
3. create kedua pada scope dan `type_key` yang sama wajib menghasilkan session error `type_key`;
4. jumlah record pada combination tersebut wajib tetap satu setelah duplicate rejection;
5. record yang berhasil dibuat diperbarui melalui named route `admin.catalog.attachment-policies.update` dengan HTTP PUT;
6. label, batas ukuran, batas jumlah, MIME, extension, visibility, dan active state diverifikasi setelah `refresh()`.

Kombinasi service-specific tersebut sesuai kontrak uniqueness aktual. Tidak dibuat `type_key` palsu atau unsupported. Assertion location hierarchy yang sudah ada tetap dipertahankan, dan successful Super Admin flow tetap melewati Form Request, policy/DomainAuthorization, controller, serta service yang sama dengan aplikasi produksi.

## TST-02 Organization Remediation

Target:

`OrganizationManagementTest::test_super_admin_can_render_organization_pages`

Assertion lama mencari class presentasional dormant:

```text
ui-sidebar-dropdown
ui-mobile-nav-dropdown
```

Class tersebut bukan lagi bagian markup authoritative. Test sekarang mem-parsing response melalui `DOMDocument` dan `DOMXPath`, tanpa dependency baru, lalu memverifikasi kontrak semantik berikut:

- desktop navigation berada pada application sidebar `#app-sidebar[data-sidebar]`;
- mobile navigation berada pada region berlabel `Navigasi mobile`;
- group desktop dan mobile tetap terpisah dan berurutan: Menu Utama, Master Data, Konfigurasi, Laporan;
- masing-masing region memiliki satu link untuk named-route Dashboard, Pengguna, Tim Kerja, Keahlian, Layanan, Lokasi, Pengumuman, Branding, Laporan, dan Audit Trail;
- href, visible label, base class desktop/mobile, serta `is-active` diperiksa per link;
- link service aktif pada section services dan link location aktif pada section locations;
- entry lama Manajemen Formulir, Manajemen SLA, Parameter Batas Waktu, Parameter Jam Layanan, dan Kebijakan Lampiran tidak muncul di navigation region;
- Super Admin tidak memperoleh link operasional ticket index, ticket queue, atau approval hanya karena memiliki role administratif.

Assertion content utama `Manajemen Layanan`, `Syarat keahlian`, `Identitas Aplikasi`, dan `Manajemen Lokasi` tetap ada. Assertion baru memakai boolean/count dengan pesan ringkas; jika selector gagal, response HTML katalog sekitar 1 MB tidak dimasukkan ke exception message.

## TST-03 Dashboard Remediation

Target:

`DashboardTest::test_technician_dashboard_shows_personal_ticket_summary`

Assertion lama `assertDontSee('Tiket Saya')` bersifat global dan menolak link work-area yang valid. Assertion lama juga menolak subject tiket aktif milik teknisi sendiri, padahal service dashboard memang memproyeksikan `assigned_tickets` milik actor.

Remediasi:

- absence metric Tier 1 diperiksa sebagai elemen `<dt>` spesifik untuk Dikerjakan Sendiri, Dikerjakan Teknisi, dan Antrian Tiket;
- absence queue section Tier 1 diperiksa melalui id `helpdesk-queue-heading`;
- link sah menuju `tickets.queue?tab=mine` dengan CTA `Buka Tiket Saya` wajib tersedia;
- tiga subject tiket yang benar-benar di-assign kepada Tier 2 wajib terlihat;
- subject `Tiket milik teknisi lain` wajib tidak terlihat;
- counter `assigned_count`, `awaiting_confirmation_count`, dan `closed_count` tetap diverifikasi melalui view data;
- heading, description, dan pengumuman internal yang sudah diuji sebelumnya tetap dipertahankan.

Perubahan ini memperjelas perbedaan antara UI legacy yang tidak boleh muncul, navigation/action yang sah, data authorized milik actor, dan data private milik actor lain.

## Assertion Intent Before vs After

| ID | Sebelum | Setelah | Intent yang dipertahankan/diperkuat |
|---|---|---|---|
| TST-01 | Mencoba membuat global `supporting` yang sudah ada, lalu mengharapkan tanpa error. | Membuat `SVC-01 + supporting`, menguji duplicate rejection, melakukan PUT update, lalu memverifikasi persistence exact scope. | Valid create/update flow, domain uniqueness, parsing MIME/extension, dan persistence tetap teruji. |
| TST-02 | Mencari dua class dropdown lama serta exact raw class string. | DOM/XPath memeriksa region desktop/mobile, order, named-route href, label, class, active state, dan absence link terlarang. | Navigation behavior dan role boundary diuji tanpa mengikat test ke wrapper markup dormant. |
| TST-03 | Menolak semua kemunculan `Tiket Saya` dan menolak subject tiket actor sendiri. | Menolak struktur Tier 1 secara exact, mewajibkan link `tab=mine`, mewajibkan data actor, dan menolak data teknisi lain. | Role variant, work-area link, dan privacy/data scope sekarang dibedakan secara eksplisit. |

Jumlah test tetap **136**. Full-suite assertion count berubah dari baseline diagnosis **1.384** menjadi **1.690** karena kontrak navigasi desktop/mobile dan data-scope diperiksa lebih presisi; tidak ada meaningful assertion yang dihapus tanpa pengganti.

## Isolated Results

Semua command isolated menggunakan native PHPUnit dan `XDEBUG_MODE=off`.

| Target | Tests | Passed | Failed | Errors | Skipped | Assertions | PHPUnit duration | Memory | Result |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---|
| `CatalogManagementTest.php` | 8 | 8 | 0 | 0 | 0 | 126 | 1,684 s | 58,00 MB | PASS |
| `OrganizationManagementTest.php` | 20 | 20 | 0 | 0 | 0 | 423 | 1,356 s | 58,00 MB | PASS |
| `DashboardTest.php` | 5 | 5 | 0 | 0 | 0 | 69 | 1,062 s | 56,00 MB | PASS |

Tidak ada isolated test yang hang.

## Targeted Regression Results

Command targeted mencakup 15 suite wajib:

```text
AuthenticationTest
RoleAuthorizationTest
DashboardTest
TicketManagementTest
TicketTriageTest
TicketCommunicationTest
TicketApprovalTest
TicketResolutionTest
TicketInternalFieldTest
TeamChairAccessTest
MonthlyReportTest
CatalogManagementTest
OrganizationManagementTest
TicketNotificationTest
AuditAndRetentionTest
```

Hasil aggregate:

| Tests | Passed | Failed | Errors | Skipped | Assertions | Duration | Memory | Result |
|---:|---:|---:|---:|---:|---:|---:|---:|---|
| 111 | 111 | 0 | 0 | 0 | 1.467 | 19,503 s | 96,00 MB | PASS |

## Full Native PHPUnit Result

Command authoritative:

```powershell
$env:XDEBUG_MODE='off'; php vendor/bin/phpunit
```

Hasil final:

| Tests | Passed | Failed | Errors | Skipped | Assertions | Duration | Memory | Exit |
|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| 136 | 135 | 0 | 0 | 1 | 1.690 | 21,672 s | 94,00 MB | 0 |

Satu skip adalah test worker concurrency PostgreSQL pada `TicketNumberAllocatorTest`; test tersebut secara intentional di-skip karena baseline test memakai SQLite. Ini bukan regression dan bukan failure.

## Artisan Test Result

Command:

```powershell
$env:XDEBUG_MODE='off'; php artisan test
```

Wrapper Laravel selesai normal dengan exit code 0:

| Tests | Passed | Failed | Errors | Skipped | Assertions | Duration | Result |
|---:|---:|---:|---:|---:|---:|---:|---|
| 136 | 135 | 0 | 0 | 1 | 1.690 | 20,89 s | PASS |

Collision writer tidak stall ketika suite hijau. Karena wrapper mencapai summary final, W0.1b tidak memerlukan tooling caveat dan tidak ada alasan untuk memodifikasi `vendor/`.

## Frontend Build Result

Command:

```text
npm run build
```

Hasil:

- Vite `6.4.3`;
- 59 modules transformed;
- production bundle berhasil dibuat dalam 2,10 s;
- exit code 0;
- build tidak menghasilkan perubahan tracked di luar scope W0.1b.

## Production Behavior Impact

**Production behavior impact: NONE.**

Tidak ada perubahan pada:

- `app/`;
- `resources/`;
- `routes/`;
- `database/`;
- `config/`;
- `vendor/`;
- dependency manifest atau lock file;
- generated production artifact yang tracked.

Seluruh request test tetap menggunakan route, HTTP method, Form Request, policy/DomainAuthorization, controller, service, model, dan persistence contract yang sudah ada. Hanya fixture dan assertion test yang berubah.

## Remaining Baseline Failures

**0 failure dan 0 error.**

Satu intentional environment skip tetap ada untuk concurrency PostgreSQL ketika driver test adalah SQLite. Tidak ada application defect baru, unexplained failure, hang, atau test yang dilemahkan untuk mencapai hasil hijau.

## W0.1b Gate Result

**W0.1b PASS**

Alasan gate:

1. ketiga stale/colliding test telah diperbaiki secara semantik;
2. create/update, duplicate rejection, navigation role boundary, dan Tier 2 data privacy tetap atau lebih kuat teruji;
3. full native PHPUnit mencapai 136 tests dengan 0 failure, 0 error, dan 1 intentional skip;
4. `php artisan test` juga selesai normal tanpa Collision stall;
5. frontend production build berhasil;
6. production behavior dan production source tidak berubah.
