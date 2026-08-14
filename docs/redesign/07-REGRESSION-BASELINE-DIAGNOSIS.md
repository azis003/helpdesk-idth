# Regression Baseline Diagnosis

Dokumen ini merekam hasil **W0.1 — Regression Baseline Recovery** pada 13 Agustus 2026, zona waktu Asia/Jakarta. Tujuan W0.1 hanya memulihkan baseline regression test yang dapat direproduksi sampai summary final. Tidak ada redesign, perubahan Blade/CSS/JavaScript, perubahan backend, bug fix, refactor, perubahan test, atau perubahan production behavior yang diterapkan.

## Initial W0 Result

W0 berakhir **BLOCKED** karena command literal `php artisan test` tidak mencapai summary, satu test Catalog gagal, dan satu test Organization tampak hang. W0.1 membatasi pekerjaan pada reproduksi, diagnosis, audit isolasi, dan dokumentasi proposed remediation.

### Environment baseline

| Item | Nilai |
|---|---|
| PHP | 8.4.19 |
| Laravel | 12.64.0 |
| PHPUnit | 11.5.56 |
| Collision | 8.9.5 |
| Test database | SQLite `:memory:` |
| Cache / session | `array` / `array` |
| Queue | `sync` |
| Xdebug CLI | 3.5.1; dinonaktifkan per proses diagnosis dengan `XDEBUG_MODE=off` |
| Test ditemukan | 136 |

### Repository state sebelum W0.1

- Branch: `redesign-frontend`.
- Perubahan yang sudah ada sebelum W0.1 hanya tujuh dokumen `docs/redesign/00` sampai `06`.
- Hash SHA-256 dokumen `00` sampai `06` sama dengan hash yang direkam pada W0.
- Tidak ada path implementation atau test yang berubah sebelum diagnosis.

### Kontrol diagnosis

- Test dijalankan pada SQLite `:memory:`; tidak ada database aplikasi lokal yang dimutasi.
- Xdebug hanya dinonaktifkan pada environment proses test; tidak ada konfigurasi PHP yang diedit.
- Dua test diagnostik temporer dipakai untuk mencatat state/request boundary, lalu dihapus.
- Marker temporer dipakai pada renderer Collision di `vendor/`, lalu dipulihkan.
- Semua JUnit XML dibuat di `C:\Windows\Temp` dan dihapus setelah dibaca.
- Proses test yang dihentikan karena timeout diidentifikasi dari command line lalu dihentikan; server Laravel lokal yang sudah ada tidak disentuh.

## Catalog Failure Reproduction

Test:

```text
Tests\Feature\Admin\CatalogManagementTest
test_super_admin_can_manage_location_hierarchy_and_attachment_policy
```

Command reproduksi utama:

```text
XDEBUG_MODE=off php artisan test tests/Feature/Admin/CatalogManagementTest.php --filter=test_super_admin_can_manage_location_hierarchy_and_attachment_policy --debug
```

Hasil tiga run terisolasi:

| Run | Exit | Wall duration | PHPUnit duration | Peak aggregate working set | Assertions | Result |
|---:|---:|---:|---:|---:|---:|---|
| 1 | 1 | 1,560 s | 0,77 s | 149,97 MiB | 19 | FAIL |
| 2 | 1 | 1,701 s | 0,75 s | 145,57 MiB | 19 | FAIL |
| 3 | 1 | 1,545 s | 0,75 s | 146,80 MiB | 19 | FAIL |

`Peak aggregate working set` menjumlahkan proses launcher/Artisan dan child PHPUnit yang masih hidup pada setiap sample.

Assertion terakhir:

```text
tests/Feature/Admin/CatalogManagementTest.php:420
->assertSessionHasNoErrors()
```

Error session:

```text
Tipe lampiran tersebut sudah digunakan pada cakupan yang sama.
```

### Database state tepat sebelum request yang gagal

Instrumentasi temporer membaca row global `supporting` sebelum dan sesudah pemanggilan eksplisit `ServiceCatalogSeeder`:

```json
{
  "before_explicit_seed": [
    {
      "id": 1,
      "service_type_id": null,
      "type_key": "supporting",
      "is_active": true,
      "deleted_at": null
    }
  ],
  "after_explicit_seed": [
    {
      "id": 1,
      "service_type_id": null,
      "type_key": "supporting",
      "is_active": true,
      "deleted_at": null
    }
  ]
}
```

Row sudah ada setelah migration dan sebelum seeder eksplisit test. Seeder tetap idempotent dan tidak membuat row kedua.

## Catalog Failure Root Cause

Urutan penyebab yang terbukti:

1. `RefreshDatabase` menjalankan migration pada SQLite `:memory:`.
2. Migration `2026_08_09_000004_create_global_supporting_attachment_policy.php:32` membuat kebijakan global dengan `service_type_id = null` dan `type_key = supporting` melalui `updateOrInsert`.
3. Test memanggil `ServiceCatalogSeeder`; seeder pada line 231 memakai `firstOrCreate`, sehingga mempertahankan row global yang sama.
4. Test pada `CatalogManagementTest.php:407-418` mengirim create request dengan `type_key = supporting` tanpa `service_type_id`; scope request juga global (`null`).
5. `AttachmentPolicyService::ensureUnique()` pada line 99 memakai `withTrashed()`, mencocokkan scope dan key yang sama, lalu melempar validation error pada line 113.
6. Redirect dengan validation error adalah behavior aplikasi yang diharapkan; assertion “tidak ada error” pada line 420 yang tidak lagi sesuai fixture baseline.

### Klasifikasi penyebab

| Kandidat | Hasil | Bukti |
|---|---|---|
| Fixture collision | **YA — root cause** | Test mencoba membuat key/scope yang sudah menjadi baseline migration. |
| Seeder collision | Tidak | Seeder `firstOrCreate` mempertahankan row id 1; count tetap satu. |
| Factory state | Tidak | Attachment policy tidak dibuat oleh factory pada alur ini. |
| Test isolation | Tidak | Failure terjadi terisolasi 3/3 dan pada full suite. |
| Unique validation | Bekerja sesuai contract | Uniqueness berada di domain service dan mencakup soft-deleted row. |
| Transaction reset | Tidak | Row berasal dari migration baseline setiap test, bukan sisa test sebelumnya. |
| Test ordering dependency | Tidak | Failure identik ketika test dijalankan sendiri. |
| Application behavior aktual | Benar | Duplicate global key ditolak konsisten dengan service contract. |

**Classification: TEST INFRASTRUCTURE ISSUE — stale/colliding fixture.** Tidak ada application defect yang dibuktikan oleh failure ini.

## Organization Hang Reproduction

Test:

```text
Tests\Feature\Admin\OrganizationManagementTest
test_super_admin_can_render_organization_pages
```

### Reproduksi melalui Laravel test command

```text
XDEBUG_MODE=off php artisan test tests/Feature/Admin/OrganizationManagementTest.php --filter=test_super_admin_can_render_organization_pages --debug
```

| Item | Hasil |
|---|---|
| Timeout | 30 detik |
| Exit | 124 |
| Observed wall duration | 30,746 s |
| Peak aggregate working set | 199,15 MiB |
| Summary | Tidak terbit |

Tanpa instrumentasi renderer, output berhenti setelah `Test Prepared`. Ini belum cukup untuk menyatakan request hang, sehingga eksekusi dipisah menjadi request trace, PHPUnit native, dan renderer trace.

### Request trace

Test diagnostik temporer menyalin setup actor/fixture yang sama dan memberi marker sebelum/sesudah masing-masing request. Dengan PHPUnit native, seluruh request selesai `200`:

| Request | Status | Response bytes pada trace |
|---|---:|---:|
| `admin.users.index` | 200 | 104.748 |
| `admin.users.edit` | 200 | 35.022 |
| `admin.teams.index` | 200 | 30.762 |
| `admin.skills.index` | 200 | 40.378 |
| `admin.catalog.index?section=services` | 200 | 1.020.572 |
| `admin.catalog.index?section=locations` | 200 | 28.174 |

Ukuran HTML dapat berbeda sedikit karena token/session dan data factory, tetapi request boundary tidak hang.

### Reproduksi melalui PHPUnit native

```text
XDEBUG_MODE=off php vendor/bin/phpunit tests/Feature/Admin/OrganizationManagementTest.php --filter=test_super_admin_can_render_organization_pages
```

| Item | Hasil |
|---|---|
| Exit | 1 |
| Wall duration | 1,086 s |
| PHPUnit time | 0,778 s |
| PHPUnit memory | 56,00 MiB |
| Peak aggregate working set | 86,10 MiB |
| Assertions | 37 sebelum failure |
| Native output size | 1.042.819 karakter |
| Result | FAIL; summary terbit |

Failure aktual:

```text
tests/Feature/Admin/OrganizationManagementTest.php:553
Failed asserting that response HTML contains "class=\"ui-sidebar-dropdown\"".
```

Response yang disisipkan ke exception assertion berukuran sekitar 1,02 juta karakter. Assertion berikutnya di line 554 juga mengharapkan `ui-mobile-nav-dropdown`, tetapi belum dijalankan karena failure pertama.

### Renderer trace

Marker temporer ditempatkan sebelum/sesudah setiap tahap `NunoMaduro\Collision\Writer::write()`. Pada `artisan test`, marker terakhir adalah:

```text
TRACE collision before renderTitleAndDescription
```

Tidak ada marker `after` dalam 12,82 detik sebelum timeout. Jalur terdekat dengan stall:

```text
Collision DefaultPrinter
→ Style::writeErrorsSummary()
→ Style::writeError()
→ Writer::write()
→ Writer::renderTitleAndDescription()  [vendor/nunomaduro/collision/src/Writer.php:94]
→ output->writeln(full assertion message) [Writer.php:217]
```

`artisan test` selalu menambahkan `--no-output` untuk PHPUnit dan mengaktifkan `COLLISION_PRINTER=DefaultPrinter`. Karena itu, failure detail dirender oleh Collision, bukan printer native PHPUnit.

## Organization Hang Root Cause

Ada dua lapis penyebab yang berbeda:

1. **Failure primer — stale presentation assertion.** Test mengharapkan class `ui-sidebar-dropdown` dan `ui-mobile-nav-dropdown`. Markup authoritative saat ini memakai beberapa `<nav aria-label="...">`, `ui-sidebar-label`, dan `ui-mobile-nav-group-label`; kedua class lama tidak ada. Request dan seluruh controller/view chain selesai normal.
2. **Hang semu sekunder — test runner rendering pathology.** Failure `assertSee()` membawa seluruh HTML halaman layanan sekitar 1,02 MB ke exception. Collision stall saat memformat/menulis message itu di `Writer::renderTitleAndDescription()`. PHPUnit native menulis failure yang sama dan selesai sekitar satu detik.

### Hipotesis yang dieliminasi

| Kemungkinan | Hasil |
|---|---|
| Infinite loop aplikasi | Tidak terbukti; keenam request kembali 200. |
| Recursive relationship / lazy-loading recursion | Tidak; response selesai dan ukuran stabil. |
| View recursion | Tidak; Blade menghasilkan HTML lengkap. |
| Route generation recursion | Tidak; seluruh route link dirender dan response selesai. |
| Model accessor recursion | Tidak ditemukan pada call path; request selesai. |
| Database deadlock | Tidak; SQLite request dan query chain selesai. |
| Expensive query yang tidak selesai | Tidak; page terbesar tetap selesai pada native runner. |
| Application boot issue | Tidak; setup dan request lain selesai. |
| Event/listener loop | Tidak ada evidence; request selesai. |
| HTTP request never returning | Ditolak oleh trace before/after request. |
| Collision error rendering | **YA — root cause hang pada wrapper.** Marker berhenti di renderer title/description. |

**Classification: TEST INFRASTRUCTURE ISSUE.** Assertion UI sudah stale, lalu formatter failure Collision membuatnya tampak sebagai application hang. Tidak ada application defect atau HTTP hang yang dibuktikan.

## Test Isolation Review

| Area | Implementasi / evidence | Kesimpulan |
|---|---|---|
| Refresh strategy | `tests/TestCase.php` memakai `RefreshDatabase`; `RoleSeeder` dijalankan pada setiap `setUp()`. | Transaction/database state di-reset per test. |
| SQLite lifecycle | `phpunit.xml` menetapkan `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`. | Migration baseline dibuat per proses PHPUnit; tidak memakai database aplikasi. |
| Seeder | `RoleSeeder` memakai `updateOrCreate`; katalog memakai `updateOrCreate`/`firstOrCreate`. | Idempotent. Catalog failure berasal dari asumsi fixture test, bukan leak. |
| Factory | `UserFactory` membuat identifier unik dan menyimpan hash password pada static cache. | Static password cache tidak menyimpan row/role/session dan tidak menyebabkan failure. |
| Ordering | Catalog, Organization, dan Dashboard gagal saat isolated serta pada full suite. | Tiga failure tidak bergantung urutan. |
| Cache | Test store `array`; health test memakai prefix khusus. | Tidak ada cache eksternal/persisten. |
| Session | Driver `array`; application test context dibangun ulang. | Tidak ada session lintas proses; setiap HTTP test mengelola session sendiri. |
| Queue | Connection `sync`. | Tidak ada worker/background queue yang menahan runner. |
| Event fake/reset | Tidak ditemukan `Event::fake`, `Queue::fake`, `Bus::fake`, atau `Mail::fake`. | Tidak ada fake global yang bocor. |
| Model listener test | `TicketManagementTest` memasang listener `Ticket::creating` lalu memanggil `flushEventListeners()` dalam `finally`. Model `Ticket` tidak mendefinisikan listener boot sendiri. | Tidak ada leakage yang teramati; penggunaan static global tetap perlu dijaga bila model kelak memiliki listener produksi. |
| Carbon/test time | Class yang menetapkan `Carbon::setTestNow()` mengosongkannya pada `tearDown()`; case lokal memakai `finally`. | Test clock dipulihkan. |
| Config | `OperationalReadinessTest`/`SecurityHeadersTest` mengubah config di container test. | Container aplikasi test di-refresh; full run tidak menunjukkan cross-test leak. |
| Storage | `Storage::fake()` dipakai pada test upload/retention; temp report, heartbeat, log-retention, dan worker barrier memiliki cleanup. | Tidak ada artifact repo; satu concurrency test PostgreSQL di-skip pada SQLite. |
| Notifications | Database notification dan queue sync berada di transaction test. | Tidak ada delivery eksternal atau state lintas test. |
| Diagnostic artifacts | Temporary test, vendor marker, JUnit XML, dan orphan diagnostic process telah dibersihkan. | Clean. |

Full suite diulang tiga kali melalui PHPUnit native: dua run JUnit-only dan satu run console. Ketiganya menghasilkan test count, assertion count, failure names, dan skipped count yang identik.

## Targeted Suite Matrix

Command template per class:

```text
XDEBUG_MODE=off php vendor/bin/phpunit <test-class-path> --no-output --log-junit <temporary-path>
```

Setiap class dijalankan dalam proses terpisah dengan timeout internal 120 detik. Duration di bawah adalah duration PHPUnit dari JUnit; tidak ada class yang hang.

| Suite | Status | Tests | Passed | Failed | Skipped | Assertions | Duration |
|---|---|---:|---:|---:|---:|---:|---:|
| AuthenticationTest | PASS | 7 | 7 | 0 | 0 | 39 | 1,159 s |
| RoleAuthorizationTest | PASS | 8 | 8 | 0 | 0 | 23 | 0,737 s |
| DashboardTest | **FAIL** | 5 | 4 | 1 | 0 | 62 | 1,138 s |
| TicketManagementTest | PASS | 14 | 14 | 0 | 0 | 168 | 2,673 s |
| TicketTriageTest | PASS | 17 | 17 | 0 | 0 | 196 | 1,393 s |
| TicketCommunicationTest | PASS | 6 | 6 | 0 | 0 | 136 | 1,613 s |
| TicketApprovalTest | PASS | 3 | 3 | 0 | 0 | 51 | 0,921 s |
| TicketResolutionTest | PASS | 3 | 3 | 0 | 0 | 49 | 0,842 s |
| TicketInternalFieldTest | PASS | 3 | 3 | 0 | 0 | 44 | 1,001 s |
| TeamChairAccessTest | PASS | 2 | 2 | 0 | 0 | 46 | 0,865 s |
| MonthlyReportTest | PASS | 5 | 5 | 0 | 0 | 55 | 1,435 s |
| CatalogManagementTest | **FAIL** | 8 | 7 | 1 | 0 | 110 | 1,623 s |
| OrganizationManagementTest | **FAIL** | 20 | 19 | 1 | 0 | 140 | 2,107 s |
| TicketNotificationTest | PASS | 3 | 3 | 0 | 0 | 12 | 0,928 s |
| AuditAndRetentionTest | PASS | 7 | 7 | 0 | 0 | 30 | 0,765 s |
| **Total targeted** | **12 PASS / 3 FAIL** | **111** | **108** | **3** | **0** | **1.161** | **19,200 s** |

### Failure tambahan dari targeted matrix

`DashboardTest::test_technician_dashboard_shows_personal_ticket_summary` gagal deterministik:

```text
tests/Feature/DashboardTest.php:240
Failed asserting that response HTML does not contain "Tiket Saya".
```

Isolated native result: 1 failure, 11 assertions, PHPUnit time 0,696 s, memory 52,00 MiB. Active dashboard pada `resources/views/dashboard.blade.php:274` secara sah merender link menuju `tickets.queue?tab=mine` dengan label **Tiket Saya**. Assertion negatif yang global terlalu luas: ia tidak membedakan link navigasi yang sah dari card/list lama yang hendak dilarang test. Ini adalah stale/overbroad test assertion, bukan data-scope failure; test tetap secara terpisah melarang subject tiket aktif tampil.

## Test Infrastructure Issues

1. **Catalog fixture collision** — test membuat canonical global `supporting` yang sudah dibuat migration.
2. **Organization stale selector assertions** — test mengharapkan dua class navigation lama yang tidak ada pada markup authoritative.
3. **Dashboard overbroad text assertion** — `assertDontSee('Tiket Saya')` bertabrakan dengan link work-area yang sah.
4. **Collision failure-rendering stall** — `artisan test` tidak dapat merender exception Organization yang membawa HTML sekitar 1,02 MB.
5. **Xdebug startup noise** — Xdebug CLI default mencoba debugger port 9003, tetapi bukan root cause; seluruh diagnosis mematikannya dan pathology tetap dapat direproduksi.
6. **Intentional environment skip** — test concurrency PostgreSQL pada `TicketNumberAllocatorTest` di-skip saat database test adalah SQLite.

## Application Existing Defects

Tidak ada application implementation defect baru yang terbukti dalam W0.1:

- duplicate attachment-policy ditolak sesuai service/domain uniqueness;
- seluruh organization page request mengembalikan 200 dan selesai;
- link dashboard Tier 2 menuju tab tiket miliknya merupakan behavior UI aktif;
- tidak ditemukan deadlock, request recursion, listener loop, atau query yang tidak selesai.

Existing UI/behavior asymmetries yang sudah dicatat pada `02-UI-CONTRACT-MATRIX.md` dan `06-BASELINE-EVIDENCE.md` tetap berada di scope terpisah. W0.1 tidak mengubah klasifikasi atau implementation-nya.

## Proposed Remediation

Tidak ada remediation berikut yang diterapkan pada W0.1.

| ID | Root cause | Proposed minimal fix | Affected source | Behavior impact | Production behavior | Scope |
|---|---|---|---|---|---|---|
| TST-01 | Catalog fixture collision | Untuk menguji create path, gunakan `type_key` unik khusus test dan query row itu secara exact. Alternatif bila intent-nya canonical policy: ubah test menjadi update existing global `supporting` melalui PUT. | `tests/Feature/Admin/CatalogManagementTest.php` | Mempertahankan assertion create/update dan duplicate rejection; tidak melemahkan test. | Tidak berubah | Test-only |
| TST-02 | Organization stale selectors | Ganti assertion class lama dengan assertion semantic terhadap struktur navigation aktif, active named-route link, desktop/mobile parity, dan authorization. Gunakan DOM/Crawler selector agar failure tidak menyertakan seluruh HTML 1 MB. | `tests/Feature/Admin/OrganizationManagementTest.php` | Contract navigation diuji lebih presisi. | Tidak berubah | Test-only |
| TST-03 | Dashboard assertion terlalu luas | Pertahankan assertion data-scope dan subject privacy, tetapi targetkan ketidakadaan legacy metric/card dengan selector/struktur spesifik; jangan melarang label link `Tiket Saya` yang sah. | `tests/Feature/DashboardTest.php` | Menghilangkan false positive tanpa mengurangi coverage privacy. | Tidak berubah | Test-only |
| RUN-01 | Collision renderer stall | Jadikan `XDEBUG_MODE=off php vendor/bin/phpunit` sebagai command baseline/CI sementara. | Test command/documentation only | Full summary reproducible. | Tidak berubah | Test infrastructure |
| RUN-02 | Collision renderer stall | Evaluasi upgrade/pin/fix upstream Collision atau konfigurasi resmi untuk melewati custom failure printer; jangan patch `vendor/` permanen. | Dev dependency / test tooling | Mengembalikan reliabilitas `php artisan test`. | Tidak berubah | Separate tooling scope |

Tidak ada proposed fix yang membutuhkan route, controller, request validation, policy, service/domain workflow, model, migration, database schema, authentication, atau authorization change.

## Changes Allowed Before Re-run

Jika remediation diotorisasi pada scope berikutnya, perubahan sebelum re-run harus dibatasi pada:

1. tiga test stale/colliding yang disebut pada TST-01 sampai TST-03;
2. test-runner/dev tooling untuk Collision, bila diperlukan;
3. dokumentasi command regression baseline.

Syaratnya:

- assertion diganti dengan contract yang lebih presisi, bukan dihapus atau dilonggarkan agar hijau;
- tidak ada production source yang berubah;
- Catalog duplicate behavior tetap diuji;
- Organization desktop/mobile navigation dan active route tetap diuji;
- Dashboard data scope dan tidak tampilnya subject yang dilarang tetap diuji;
- targeted suites dan full PHPUnit suite dijalankan ulang;
- `php artisan test` diuji ulang terpisah bila Collision/tooling diremediasi.

Pada W0.1 saat ini, **tidak ada perubahan test/tooling tersebut yang diterapkan**.

## W0.1 Gate Result

### Full-suite evidence

Command console final:

```text
XDEBUG_MODE=off php vendor/bin/phpunit
```

Summary PHPUnit native:

```text
Time: 00:29.231, Memory: 96.00 MB
There were 3 failures:
1) Tests\Feature\Admin\CatalogManagementTest::test_super_admin_can_manage_location_hierarchy_and_attachment_policy
2) Tests\Feature\Admin\OrganizationManagementTest::test_super_admin_can_render_organization_pages
3) Tests\Feature\DashboardTest::test_technician_dashboard_shows_personal_ticket_summary
FAILURES!
Tests: 136, Assertions: 1384, Failures: 3, Skipped: 1.
```

| Full run | Command mode | Tests | Passed | Failed | Errors | Skipped | PHPUnit duration | Wall duration | Peak aggregate working set |
|---:|---|---:|---:|---:|---:|---:|---:|---:|---:|
| 1 | Native + JUnit, output suppressed | 136 | 132 | 3 | 0 | 1 | 25,528 s | 26,025 s | 142,34 MiB |
| 2 | Native + JUnit, output suppressed | 136 | 132 | 3 | 0 | 1 | 25,085 s | 25,580 s | Tidak diukur |
| 3 | Native console summary | 136 | 132 | 3 | 0 | 1 | 29,231 s | 29,551 s | 141,34 MiB |

Semua run menghasilkan **1.384 assertions**, failure names yang sama, dan satu skip yang sama. Tidak ada hang pada PHPUnit native.

### Caveat command Laravel

`php artisan test` tetap tidak dapat menjadi command baseline yang reliabel selama failure HTML besar masuk ke Collision. Isolated Organization run masih stall di renderer dan tidak mencapai summary. Ini adalah masalah wrapper/printer, bukan test engine atau request aplikasi.

Baseline regression yang dapat direproduksi untuk sementara adalah:

```text
XDEBUG_MODE=off php vendor/bin/phpunit
```

## **W0.1 PASS WITH EXISTING FAILURES**

Alasan gate:

1. full suite mencapai summary PHPUnit final secara konsisten;
2. total final dapat direproduksi: **132 passed, 3 failed, 1 skipped, 0 errors**;
3. ketiga failure deterministic dan diklasifikasikan sebagai test/test-runner infrastructure issue;
4. tidak ada application fix atau production behavior change yang diperlukan untuk mendapatkan summary;
5. default `artisan test` tetap dicatat sebagai runner caveat dan proposed tooling remediation, bukan disamarkan sebagai PASS.

Status ini menerima ketiga failure sebagai **existing regression baseline evidence**, bukan persetujuan untuk mengabaikannya atau melemahkan test. W1 tetap harus mengikuti gate W0 keseluruhan pada `06-BASELINE-EVIDENCE.md`, termasuk fixture dan visual-runtime prerequisite yang berada di luar W0.1.
