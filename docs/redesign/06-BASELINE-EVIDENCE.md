# Baseline Evidence

Dokumen ini merekam hasil **W0 — Baseline Capture and Regression Gate** pada 13 Agustus 2026, zona waktu Asia/Jakarta. Seluruh pemeriksaan bersifat read-only terhadap source dan data lokal, kecuali pembuatan dokumen ini. Tidak ada redesign, bug fix, refactor, perubahan test, perubahan Blade/CSS/JavaScript, atau perubahan backend.

## Repository State

### Guardrail eksekusi

- Satu-satunya file baru W0 adalah `docs/redesign/06-BASELINE-EVIDENCE.md`.
- Enam dokumen Fase 2 tidak diedit.
- Tidak ada file di `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, atau `tests/` yang diubah.
- `npm run build` menulis ulang artefak build yang di-ignore; tidak ada perubahan tracked dari command tersebut.
- Tidak ada seeder, migration, factory, provisioning command, atau mutation database lokal yang dijalankan.
- Query database lokal hanya membaca hitungan agregat; nama pengguna, username, NIP, email, dan kredensial tidak direkam.

### Snapshot awal Git

Command yang dijalankan sebelum W0:

```text
git status
git diff --stat
git diff --name-only
```

Hasil awal:

- branch: `redesign-frontend`, sama dengan `origin/redesign-frontend`;
- hanya enam file dokumentasi Fase 2 yang tampil sebagai file baru dan belum di-stage;
- diff awal: 6 file, 1.527 insertions;
- seluruh path awal berada di `docs/redesign/`.

### Bukti enam dokumen Fase 2 tidak berubah

| File | SHA-256 selama W0 |
|---|---|
| `00-REDESIGN-GUARDRAILS.md` | `D86488E7AD4123B2306173BB4DF55259D6AEB6ED0E196E1AB05DA17C51E8BEA8` |
| `01-UI-INVENTORY.md` | `DCDB2D91A896C9011AAEC62C5139B21FA41564BE5D58A43669FF7678E24CA624` |
| `02-UI-CONTRACT-MATRIX.md` | `47B500B0A6852AA56DFD7F7EAA006AA06C446C8DECEA3CB0F4F332F1FE502048` |
| `03-COMPONENT-INVENTORY.md` | `9BF43E2FB95506DAB075E241DFEE5E3E7D330DF5418F79BC3AFC1777983ACE52` |
| `04-ROLE-VARIANT-MATRIX.md` | `121937951DB91551DFA343DFC9958B484C2EE3536AD3EC012997A7496579990F` |
| `05-IMPLEMENTATION-WAVES.md` | `C3F0644C6DC0E6820C80DC8A9A2CDBECD094F3A78159F646FA886AE196169F08` |

## Route Baseline

Command wajib:

```text
php artisan route:list --except-vendor
```

Hasil: **exit 0**, `Showing [112] routes`.

Verifikasi tambahan menggunakan output JSON menghasilkan:

| Metrik | Hasil | Gate |
|---|---:|---|
| Total non-vendor HTTP contracts | **112** | PASS — sama dengan contract freeze |
| Read/response (`GET|HEAD`) | **31** | PASS |
| Mutation/form action | **81** | PASS |
| Named route | **111** | PASS |
| Unnamed route | **1** (`GET /`) | PASS |

Compatibility aliases yang tetap terlihat pada route baseline:

- `tickets.complete` dan `tickets.resolve`;
- `tickets.confirm` dan `tickets.requester-confirm`;
- `tickets.not-satisfied` dan `tickets.confirmation.not-satisfied`;
- `reports.index` dan `reports.monthly`;
- `admin.catalog.index`, `admin.services.index`, `admin.locations.index`, dan redirect `admin.forms.index`.

Warning lingkungan yang tidak mengubah hasil route:

```text
Xdebug: [Step Debug] Time-out connecting to debugging client ... localhost:9003
```

PHP CLI mengaktifkan Xdebug 3.5.1 dengan `xdebug.mode=debug` dan `xdebug.start_with_request=yes`. Route command tetap selesai sukses.

## PHP Test Baseline

### Lingkungan test

| Item | Nilai |
|---|---|
| PHP | 8.4.19 |
| Laravel | 12.64.0 |
| PHPUnit | 11.5.56 |
| Test database | SQLite `:memory:` dari `phpunit.xml` |
| Queue/session/cache test | `sync` / `array` / `array` |
| Test ditemukan | **136** |

### Command wajib literal

```text
php artisan test
```

Hasil literal:

- command tidak selesai dalam **604,1 detik**;
- runner dihentikan oleh command timeout dengan exit **124**;
- PHPUnit tidak sempat mengeluarkan summary total passed/failed/skipped;
- proses orphan yang spesifik berasal dari run tersebut dihentikan setelah PID dan waktu mulai diverifikasi;
- tidak ada test atau implementation yang diubah.

Karena summary final tidak pernah terbit, angka full-suite yang sah adalah:

| Metrik full suite | Hasil |
|---|---|
| Passed | **Tidak tersedia — suite tidak selesai** |
| Failed | **Tidak tersedia sebagai total final — suite tidak selesai** |
| Skipped | **Tidak tersedia — suite tidak selesai** |
| Duration | **> 604,1 detik; timeout** |

### Run diagnostik read-only

Untuk mengetahui titik berhenti tanpa mengubah config, test, atau source, suite yang sama dijalankan dengan Xdebug dimatikan hanya pada environment proses dan output debug ditempatkan di direktori temporer di luar repository.

Hasil sebelum hang:

| Status | Jumlah |
|---|---:|
| Passed sebelum hang | **36** |
| Failed sebelum hang | **1** |
| Skipped sebelum hang | **0** |
| Tidak selesai | **99** — satu test hang dan 98 test berikutnya belum dijalankan |

Failure yang dapat direproduksi secara terisolasi:

```text
Tests\Feature\Admin\CatalogManagementTest
test_super_admin_can_manage_location_hierarchy_and_attachment_policy
```

- isolated result: **1 failed**, 19 assertions, **0,99 detik**;
- assertion: `tests/Feature/Admin/CatalogManagementTest.php:420`;
- session error existing: `Tipe lampiran tersebut sudah digunakan pada cakupan yang sama.`

Hang yang dapat direproduksi secara terisolasi:

```text
Tests\Feature\Admin\OrganizationManagementTest
test_super_admin_can_render_organization_pages
```

- tidak selesai dalam **64 detik**;
- isolated command berakhir timeout exit 124;
- hang terjadi setelah `setUp` dan `Test Prepared`;
- tidak ada diagnosis/fix implementation pada W0.

Kesimpulan test baseline: **FAIL/INCOMPLETE EXISTING BASELINE**. Existing failure tunggal tidak otomatis memblokir redesign, tetapi full suite yang tidak dapat mencapai summary membuat regression baseline belum reproducible.

## Frontend Build Baseline

Command wajib:

```text
npm run build
```

Hasil:

| Item | Hasil |
|---|---|
| Exit | **0** |
| Vite | 6.4.3 |
| Module transformed | 59 |
| Vite build time | 10,94 detik |
| Total command time | 13,6 detik |
| Warning | Tidak ada |
| Error | Tidak ada |

Artefak yang dilaporkan Vite:

- `public/build/manifest.json`;
- `assets/theme-modern-DzXWvx2C.css`;
- `assets/app-BSxcChcp.css`;
- `assets/app-DAHXo_BV.css`;
- `assets/app-CkTBMi6P.js`.

Build gate: **PASS**.

## Actor Matrix

### Mekanisme fixture yang tersedia

- `tests/TestCase.php::createUser()` dapat membuat actor ephemeral dengan kombinasi role apa pun di SQLite `:memory:`.
- `UserFactory` hanya membuat user aktif generik; role disinkronkan oleh helper test.
- `RoleSeeder` membuat enam role kanonis.
- `DatabaseSeeder` hanya memanggil `RoleSeeder`, `ServiceCatalogSeeder`, dan `OperationalPolicySeeder`; tidak ada deterministic actor seeder.
- README menyatakan seeder tidak membuat akun default dengan password yang diketahui.
- Database lokal memiliki enam user aktif, tetapi tidak ada registry kredensial W0 yang aman dan deterministic. Record tersebut tidak dipakai sebagai actor visual.

### Pure role dan current/stale Approver

| Actor fixture | Record lokal saat audit | Bukti test source | Deterministic login fixture | Status W0 |
|---|---:|---|---|---|
| Pure Super Admin | 1 | Admin/Auth/Authorization tests | Tidak ada | MISSING FOR VISUAL |
| Pure Pemohon | 3 | Dashboard, TicketManagement, Communication, Resolution | Tidak ada | MISSING FOR VISUAL |
| Pure Agen Tier 1 | 1 | Dashboard, Triage, Communication, Approval | Tidak ada | MISSING FOR VISUAL |
| Pure Agen Tier 2 | 1 | Dashboard, Triage, InternalField | Tidak ada | MISSING FOR VISUAL |
| Pure Approver — current active | 0; active assignment 0 | `TicketApprovalTest`, `MonthlyReportTest`, `OperationalPolicyManagementTest` membuat assignment ephemeral | Tidak ada | **CRITICAL GAP** |
| Pure Approver — stale/non-current | 0; stale assignment 0 | `TicketApprovalTest::test_non_active_approver_is_denied...` | Tidak ada | **CRITICAL GAP** |
| Pure Ketua Tim Kerja | 0; active chair assignment 0 | `DashboardTest`, `TeamChairAccessTest` | Tidak ada | **CRITICAL GAP** |

### Critical multi-role

| Kombinasi | Record lokal | Explicit deterministic test fixture | Behavior yang harus dibuktikan | Status W0 |
|---|---:|---|---|---|
| Super Admin + Agen Tier 1 | 0 | Tidak ditemukan | Admin shell precedence vs operational policy/direct URL | **CRITICAL GAP** |
| Pemohon + Approver | 0 | Tidak ditemukan | Requester-only list plus current-approver capability | **CRITICAL GAP** |
| Ketua Tim + Agen Tier 1 | 0 | Tidak ditemukan | Ticket read-only safe projection override; announcement caveat | **CRITICAL GAP** |
| Ketua Tim + Super Admin | 0 | Tidak ditemukan | Admin rights tetap ada, ticket safe/read-only, report denied | **CRITICAL GAP** |

Test source memiliki kombinasi lain, yaitu Super Admin+Pemohon dan Pemohon+Agen Tier 1, tetapi keduanya tidak menggantikan empat kombinasi wajib W0.

Kesimpulan actor fixture: helper test generik tersedia, tetapi fixture bernama dan login-ready untuk actor W0 tidak tersedia. Tidak ada data produksi/lokal yang diubah untuk menutup gap ini.

## Ticket Fixture Matrix

### Status tiket

Seluruh 11 enum status ditemukan dalam test source. Ini adalah bukti coverage intent, bukan bukti bahwa full suite lulus, karena suite berhenti sebelum selesai.

| Status | Local count | Test source utama | Visual fixture W0 |
|---|---:|---|---|
| Baru | 1 | Authorization, TicketManagement, TicketTriage | PARTIAL — actor login tidak deterministic |
| Diproses | 0 | TicketManagement, Communication, Approval, Triage | MISSING |
| Dikerjakan | 1 | Dashboard, Communication, Approval, Resolution, Triage | PARTIAL — actor/state pairing tidak deterministic |
| Menunggu Persetujuan | 0 | TicketApproval, TicketCommunication | MISSING |
| Menunggu Pemohon | 1 | Dashboard, TicketCommunication | PARTIAL — actor login tidak deterministic |
| Menunggu Pihak Ketiga | 0 | TicketCommunication | MISSING |
| Menunggu Konfirmasi | 0 | Dashboard, Resolution, DatabaseChangeControl | MISSING |
| Ditutup | 3 | Dashboard, Resolution, Triage | PARTIAL — reopen actor/state pairing tidak deterministic |
| Ditolak | 0 | TicketTriage | MISSING |
| Tidak Disetujui | 0 | TicketApproval, TicketTriage | MISSING |
| Dibatalkan | 1 | TicketManagement, TicketTriage | PARTIAL — actor login tidak deterministic |

### Prioritas

| Prioritas | Local count | Test source | Visual fixture W0 |
|---|---:|---|---|
| Kritis | 3 | TicketTriage | PARTIAL |
| Tinggi | 3 | TicketTriage, TeamChairAccess, MonthlyReport | PARTIAL |
| Sedang | 0 | TicketManagement, TicketTriage, Notification | MISSING |
| Rendah | 1 | TicketManagement, TicketTriage | PARTIAL |

### Service fixture

| Service scenario | Seed/catalog | Local ticket | Automated test source | Visual fixture W0 |
|---|---|---:|---|---|
| Normal/no special-control representative (SVC-04 atau SVC-06) | Ada | 0 | Catalog configuration ada; tidak ada named end-to-end normal fixture | **MISSING** |
| SVC-01 location | Ada | 5 | TicketManagement, Resolution, Communication | PARTIAL |
| SVC-02 data export | Ada | 1 | DatabaseChangeControl, TicketTriage | PARTIAL; result attachment ada, actor/state tidak siap |
| SVC-03 database change | Ada | 1 | DatabaseChangeControl | PARTIAL; tiga evidence file ada, control record 0 dan actor/state tidak siap |
| SVC-05 hardware/location | Ada | 0 | TicketManagement | **MISSING** |
| SVC-07 internal versioned fields | Ada | 0 | TicketInternalField, TeamChairAccess | **MISSING** |

Semua tujuh service code ada pada `ServiceCatalogSeeder` dan database lokal. Coverage katalog tidak sama dengan fixture workflow/rendering per service.

### Workflow dan visibility scenarios

| Scenario | Local evidence | Test source | Status W0 |
|---|---|---|---|
| Menunggu Pemohon aktif | 1 active requester wait | TicketCommunication | PARTIAL |
| Menunggu Pihak Ketiga start/resume | 0 active; 0 history | TicketCommunication | MISSING VISUAL |
| Approval pending/approve/reject | 0 approval request; 0 approver assignment | TicketApproval | **CRITICAL GAP** |
| Completion/solution | 3 ticket memiliki solution | TicketResolution | PARTIAL |
| Requester confirmation | Tidak ada Menunggu Konfirmasi | TicketResolution | MISSING VISUAL |
| Reopen | 0 ticket dengan `reopen_count > 0` | TicketResolution | MISSING VISUAL |
| Public comment | 11 local comments | TicketCommunication, TeamChairAccess | PARTIAL |
| Internal comment | 0 local comments | TicketCommunication | MISSING VISUAL |
| Public/requester-accessible attachment | 5 visibility `both`; 1 `data_export_result` | Communication, DatabaseChangeControl | PARTIAL |
| Internal attachment | 3; SVC-03 evidence types lengkap | Communication, TeamChairAccess, AuditAndRetention | PARTIAL; authorized actor/state tidak siap |
| Attachment denied | Tidak ada deterministic denied actor | TeamChairAccess, AuditAndRetention | MISSING VISUAL |
| Deleted/retained attachment | Tidak disiapkan sebagai visual fixture | AuditAndRetention, DatabaseChangeControl | MISSING VISUAL |
| Ketua Tim inside team scope | 6 active memberships, tetapi 0 active chair | Dashboard, TeamChairAccess | **CRITICAL GAP** |
| Ketua Tim outside team scope | 0 active chair | Dashboard, TeamChairAccess | **CRITICAL GAP** |

Kesimpulan ticket fixture: automated test source mencakup domain kritis secara luas, tetapi persistent deterministic fixture untuk visual/manual baseline tidak mencakup matrix wajib.

## Screen Coverage

Seluruh **23 UI aktif** dari `01-UI-INVENTORY.md` diperiksa ulang pada route, controller/view mapping, Blade, policy/data boundary, JavaScript selector, dan test source yang relevan. Dormant view tidak diperlakukan sebagai screen aktif.

### Review per screen

| UI ID | Screen | Contract/source review | Test evidence source | Visual baseline | Risk |
|---|---|---|---|---|---|
| UI-001 | Login | VERIFIED | AuthenticationTest | MANUAL CAPTURE REQUIRED | LOW |
| UI-002 | Dashboard | VERIFIED — 6 primary variants | DashboardTest, role/policy source | MANUAL CAPTURE REQUIRED | HIGH |
| UI-003 | Ganti kata sandi | VERIFIED | AuthenticationTest | MANUAL CAPTURE REQUIRED | MEDIUM |
| UI-004 | Daftar tiket | VERIFIED — 3 primary variants | TicketManagement, Triage, TeamChair | MANUAL CAPTURE REQUIRED | HIGH |
| UI-005 | Monitoring/antrean | VERIFIED | TicketTriageTest | MANUAL CAPTURE REQUIRED | HIGH |
| UI-006 | Semua tiket | VERIFIED | TicketTriageTest | MANUAL CAPTURE REQUIRED | HIGH |
| UI-007 | Buat tiket/katalog | VERIFIED — catalog dan selected-service form state | TicketManagementTest | MANUAL CAPTURE REQUIRED | HIGH |
| UI-008 | Detail tiket | VERIFIED — 6 primary variants, 14 modal IDs | Ticket workflow test classes | MANUAL CAPTURE REQUIRED | CRITICAL |
| UI-009 | Notifikasi | VERIFIED | TicketNotificationTest | MANUAL CAPTURE REQUIRED | MEDIUM |
| UI-010 | Persetujuan tertunda | VERIFIED | TicketApprovalTest | MANUAL CAPTURE REQUIRED | HIGH |
| UI-011 | Laporan bulanan | VERIFIED | MonthlyReportTest | MANUAL CAPTURE REQUIRED | HIGH |
| UI-012 | Manajemen pengguna | VERIFIED | OrganizationManagementTest | MANUAL CAPTURE REQUIRED | HIGH |
| UI-013 | Tambah pengguna | VERIFIED | OrganizationManagementTest | MANUAL CAPTURE REQUIRED | MEDIUM |
| UI-014 | Edit pengguna | VERIFIED | OrganizationManagementTest | MANUAL CAPTURE REQUIRED | HIGH |
| UI-015 | Reset password pengguna | VERIFIED | PasswordResetTest | MANUAL CAPTURE REQUIRED | MEDIUM |
| UI-016 | Manajemen lokasi | VERIFIED | Catalog/Organization tests | MANUAL CAPTURE REQUIRED | MEDIUM |
| UI-017 | Manajemen tim | VERIFIED | OrganizationManagementTest | MANUAL CAPTURE REQUIRED | MEDIUM |
| UI-018 | Manajemen keahlian | VERIFIED | OrganizationManagementTest | MANUAL CAPTURE REQUIRED | MEDIUM |
| UI-019 | Audit trail | VERIFIED | AuditLogPageTest, AuditAndRetentionTest | MANUAL CAPTURE REQUIRED | HIGH |
| UI-020 | Branding | VERIFIED | BrandingManagementTest | MANUAL CAPTURE REQUIRED | MEDIUM |
| UI-021 | Layanan/form dinamis | VERIFIED | CatalogManagementTest | MANUAL CAPTURE REQUIRED | HIGH |
| UI-022 | Pengumuman | VERIFIED | CatalogManagementTest | MANUAL CAPTURE REQUIRED | MEDIUM |
| UI-023 | 403 | VERIFIED | authorization denial tests | MANUAL CAPTURE REQUIRED | LOW |

### Primary variant coverage — 15

| Variant ID | Render/data variant | Source contract | Fixture state |
|---|---|---|---|
| D-01 | Dashboard pure Super Admin | VERIFIED | Local role record ada; no deterministic login |
| D-02 | Dashboard Pemohon-only | VERIFIED | Local role record ada; no deterministic login |
| D-03 | Dashboard Agen Tier 1 | VERIFIED | Local role record ada; no deterministic login |
| D-04 | Dashboard Agen Tier 2 | VERIFIED | Local role record ada; no deterministic login |
| D-05 | Dashboard current active Approver | VERIFIED | **MISSING** |
| D-06 | Dashboard Ketua Tim safe projection | VERIFIED | **MISSING** |
| L-01 | Ticket list requester-only | VERIFIED | Partial local; no deterministic login |
| L-02 | Ticket list operational/scoped | VERIFIED | Partial local; no deterministic login |
| L-03 | Ticket list Ketua Tim projection | VERIFIED | **MISSING** |
| T-01 | Ticket detail Super Admin read-only/internal visibility | VERIFIED | No deterministic ticket/login pairing |
| T-02 | Ticket detail Pemohon owner | VERIFIED | No deterministic ticket/login pairing |
| T-03 | Ticket detail Agen Tier 1 | VERIFIED | No deterministic state/action pairing |
| T-04 | Ticket detail assigned Agen Tier 2 | VERIFIED | No deterministic state/action pairing |
| T-05 | Ticket detail current pending Approver | VERIFIED | **MISSING** |
| T-06 | Ticket detail Ketua Tim safe projection | VERIFIED | **MISSING** |

Coverage source-level: **23/23 screens** dan **15/15 primary variants**. Coverage visual/runtime: **0/23 screens** dan **0/15 primary variants**, karena browser dan deterministic authenticated fixtures tidak tersedia.

### Dormant views

Status tetap **DO NOT REDESIGN / DO NOT ACTIVATE**:

- `tickets/requester-show.blade.php`;
- `admin/catalog/index.blade.php`;
- `admin/catalog/_attachments-section.blade.php`;
- `admin/catalog/_locations-section.blade.php`;
- `admin/catalog/_service-edit-modal.blade.php`;
- `admin/forms/index.blade.php`.

Tidak ada dormant view yang diaktifkan selama W0.

## Visual Baseline

### Capability result

- Local app merespons `200 OK` pada `http://127.0.0.1:8000/login`.
- Browser runtime diperiksa sesuai browser skill.
- Browser discovery menghasilkan daftar kosong: `[]`.
- Tidak ada browser yang dapat dikontrol pada environment ini.
- Tidak ada screenshot dibuat dan tidak ada file `baseline/*.png` dibuat.
- Repository juga tidak menyediakan akun default dengan password diketahui; authenticated variant tidak boleh dipalsukan.

Visual gate: **MANUAL CAPTURE REQUIRED**.

### Deterministic naming plan

Minimum primary render set adalah 35 render variants: 20 screen non-variant + 15 primary variants. Desktop dan mobile menghasilkan minimum **70 screenshot**.

| Scope | Filename stem yang wajib | Pair desktop/mobile |
|---|---|---:|
| UI-001 | `baseline/UI-001-login-{viewport}.png` | 1 |
| UI-002 | `baseline/UI-002-dashboard-{super-admin|pemohon|tier-1|tier-2|approver|team-chair}-{viewport}.png` | 6 |
| UI-003 | `baseline/UI-003-password-change-{viewport}.png` | 1 |
| UI-004 | `baseline/UI-004-ticket-list-{requester|operational|team-chair}-{viewport}.png` | 3 |
| UI-005 | `baseline/UI-005-ticket-queue-{viewport}.png` | 1 |
| UI-006 | `baseline/UI-006-all-tickets-{viewport}.png` | 1 |
| UI-007 | `baseline/UI-007-ticket-create-{viewport}.png` | 1 |
| UI-008 | `baseline/UI-008-ticket-detail-{super-admin|pemohon|tier-1|tier-2|approver|team-chair}-{viewport}.png` | 6 |
| UI-009 | `baseline/UI-009-notifications-{viewport}.png` | 1 |
| UI-010 | `baseline/UI-010-approvals-{viewport}.png` | 1 |
| UI-011 | `baseline/UI-011-reports-{viewport}.png` | 1 |
| UI-012 | `baseline/UI-012-users-{viewport}.png` | 1 |
| UI-013 | `baseline/UI-013-user-create-{viewport}.png` | 1 |
| UI-014 | `baseline/UI-014-user-edit-{viewport}.png` | 1 |
| UI-015 | `baseline/UI-015-user-reset-password-{viewport}.png` | 1 |
| UI-016 | `baseline/UI-016-locations-{viewport}.png` | 1 |
| UI-017 | `baseline/UI-017-teams-{viewport}.png` | 1 |
| UI-018 | `baseline/UI-018-skills-{viewport}.png` | 1 |
| UI-019 | `baseline/UI-019-audit-log-{viewport}.png` | 1 |
| UI-020 | `baseline/UI-020-branding-{viewport}.png` | 1 |
| UI-021 | `baseline/UI-021-services-{viewport}.png` | 1 |
| UI-022 | `baseline/UI-022-announcements-{viewport}.png` | 1 |
| UI-023 | `baseline/UI-023-forbidden-{viewport}.png` | 1 |

`{viewport}` wajib memakai `desktop` atau `mobile`.

### Supplemental visual states wajib

- UI-007: catalog state dan selected-service form state, termasuk SVC-01/02/03/05/07 serta normal service.
- UI-005: queue, mine, assigned, completed tabs untuk actor yang berwenang.
- UI-011: initial/no-query, result, empty, filter error, dan export-loading/error state.
- UI-012: create, detail, edit, dan password-reset modal.
- UI-016: building create/edit dan floor create/edit modal.
- UI-017: team create/edit modal.
- UI-018: skill create/edit modal.
- UI-021: service create/view/edit/preview modal, field builder, tab context, dan nested validation error.
- UI-008: seluruh 14 ticket modal; naming `baseline/UI-008-modal-{modal-id}-{viewport}.png`, minimum 28 screenshot.
- Error, empty, loading, long-content, overflow, keyboard-focus, and validation-auto-open states.

Semua item pada daftar ini berstatus **MANUAL CAPTURE REQUIRED**.

## Modal Baseline

### Shared dialog behavior dari source

`x-ui.modal-panel` dan generic modal manager memberi baseline berikut pada seluruh modal ticket yang dapat dibuka:

- backdrop dan tombol close memakai `data-ui-modal-close`;
- `Escape` menutup dialog;
- `Tab`/`Shift+Tab` dijaga di antara elemen focusable pertama dan terakhir;
- fokus awal menuju `data-ui-modal-focus` atau focusable pertama;
- fokus kembali ke opener terakhir bila modal dibuka melalui trigger;
- `data-auto-open=true` membuka modal setelah validation redirect;
- body memperoleh `overflow-hidden` selama modal terbuka;
- runtime behavior belum dapat diuji tanpa browser.

Baseline penting:

- ticket modal tidak memakai `data-ui-modal-form`, `data-reset-on-close`, atau `data-clear-on-close`;
- generic reset/clear branch tidak aktif untuk 14 modal ticket;
- bila auto-open terjadi tanpa opener, `lastTrigger` adalah `null`, sehingga tidak ada target focus-return;
- 10 dari 14 modal memiliki opener aktif;
- 4 dari 14 modal dirender kondisional tetapi tidak memiliki opener aktif;
- 13 dari 14 modal memiliki hidden `_action_modal`; approval decision forms tidak memilikinya.

### Matrix 14 modal ticket

| # | Modal ID | Opener aktif | Actor/state authoritative | Field/error contract | Marker dan JS | Error reopen/runtime |
|---:|---|---|---|---|---|---|
| 1 | `ticket-priority-modal` | Ya — Ubah Prioritas | T1; Baru, unassigned queue; `changePriority` | `priority`, `reason` | Marker ada; `data-ticket-priority-form/submit`; tidak ada dedicated consumer, global overlay tetap aktif | Source auto-open OK; manual close/Escape/focus required |
| 2 | `ticket-reject-modal` | Ya — Tolak | T1; Baru, unassigned queue; `reject` | `reason` | Marker ada; `data-ticket-reject-form/submit`; tidak ada dedicated consumer | Source auto-open OK; manual runtime required |
| 3 | `ticket-internal-comment-modal` | **Tidak ada**; aksi internal tersedia sebagai inline form terpisah | Assigned T1/T2 pada status policy atau pending Approver; `commentInternal` | `body`, `attachments[policy][]` | Marker ada; `data-ticket-communication-form/submit` | Modal normal tidak reachable; inline form tidak mengirim marker; manual/fix scope terpisah |
| 4 | `ticket-request-information-modal` | Ya — Kembalikan ke Pelapor | Assigned agent; Diproses/Dikerjakan | `body` | Marker ada; communication handler | Source auto-open OK; manual runtime required |
| 5 | `ticket-database-change-modal` | **Tidak ada** | Assigned agent; SVC-03, Dikerjakan; evidence/execution sequence | execute: none plus `database_change`; verify: `verification_result`, `verification_notes` | Marker ada pada kedua form; database-change handler; execute memakai Swal | Modal/actions tidak reachable dari active opener; critical manual/product defect scope terpisah |
| 6 | `ticket-complete-modal` | Ya — Selesai | Assigned agent; Dikerjakan; special readiness | `solution`; SVC-02 `data_export_result` | Marker ada; `data-ticket-resolution-form/submit`; tidak ada dedicated consumer, global overlay aktif | Source auto-open OK; manual SVC-02/03 runtime required |
| 7 | `ticket-confirmation-modal` | Ya — Konfirmasi | Pemohon owner; Menunggu Konfirmasi | confirm: none; not-satisfied: `reason` | Marker ada; confirm memakai Swal; not-satisfied memakai resolution selector tanpa dedicated consumer | Source auto-open OK untuk marker; manual both branches required |
| 8 | `ticket-reopen-modal` | Ya — Buka Kembali | Pemohon owner; Ditutup; domain window/count | optional `reason` | Marker ada; resolution selector tanpa dedicated consumer | Source auto-open OK; manual eligibility/error required |
| 9 | `ticket-approval-decision-modal` | Ya — Putuskan | Current active Approver + pending request assigned | approve: none; reject: `decision_note` | **Marker tidak ada pada kedua form**; approval-decision handler | Validation reject tidak dapat memenuhi auto-open expression; textarea tidak memakai `old()`; known baseline gap |
| 10 | `ticket-request-approval-modal` | Ya — Minta Approval | Assigned T1/T2; Diproses/Dikerjakan | optional `reason`; backend alias `note` | Marker ada; approval-request handler | Source auto-open OK; manual runtime required |
| 11 | `ticket-third-party-modal` | Ya — Pending/Lanjutkan | Assigned agent; start Diproses/Dikerjakan, resume Menunggu Pihak Ketiga | start: `third_party_name`, `follow_up_date`, `note`; resume: `reason` | Marker ada; communication handler | Source auto-open OK; manual both branches required |
| 12 | `ticket-triage-modal` | Ya — Triase | T1; active Blade narrows ke Baru unassigned queue | `outcome`, conditional `assigned_to_id`; backend accepts extra fields not rendered | Marker ada; triage dynamic-panel/submit handler | Source auto-open OK; manual suggestions/focus/error required |
| 13 | `ticket-assign-tier-two-modal` | **Tidak ada** | Assigned T1; Dikerjakan | `assigned_to_id`, optional `reason` | Marker ada; assignment handler | Modal/action tidak reachable from active opener; manual/fix scope terpisah |
| 14 | `ticket-return-tier-one-modal` | **Tidak ada** | Assigned T2; Dikerjakan; last triager required | `reason` | Marker ada; return handler | Modal/action tidak reachable from active opener; manual/fix scope terpisah |

Source coverage modal: **14/14 IDs**. Active opener coverage: **10/14**. Browser runtime coverage untuk close/Escape/focus trap/focus return/error reopen: **0/14 — MANUAL VERIFICATION REQUIRED**.

## Global Interaction Baseline

| Behavior | Source baseline | Automated/source evidence | Runtime gate |
|---|---|---|---|
| Sidebar collapse | `sihati.sidebar.collapsed` di `localStorage`; fallback bila storage gagal; ARIA label/expanded diperbarui | Source verified | MANUAL REQUIRED |
| Desktop navigation | Role/current-approver/policy conditions berada di `layouts.app`; Super Admin top-level branch tetap dominan | Role/policy tests dan matrix source | MANUAL REQUIRED untuk 6 pure + 4 multi-role |
| Mobile navigation | Native `<details>` menu dengan duplicated server-side role branches | Source verified | MANUAL REQUIRED untuk open/close, focus, overflow, parity |
| Navigation pending | Active link diganti di client, duplicate click ditahan, global loading aktif | Source verified | MANUAL REQUIRED |
| Notification dropdown | Native `<details>`; five latest; unread count; read-one POST milik current user | TicketNotification test source | MANUAL REQUIRED untuk keyboard/dismissal/focus |
| Logout | POST `logout`, CSRF, desktop dan mobile form | Authentication test source | MANUAL REQUIRED |
| Ganti password | GET/PUT contract, password tidak direpopulasi | Authentication test source | MANUAL REQUIRED |
| Flash SweetAlert | JSON `data-swal-flash`; success/warning/error; error HTML di-escape | Source verified | MANUAL REQUIRED |
| Destructive confirmation | `form[data-swal-confirm]`; duplicate confirm ditahan; focus cancel default | Source verified | MANUAL REQUIRED |
| Global loading overlay | Membuat sibling `inert`, fokus overlay, mencegah duplicate submit | Source verified | MANUAL REQUIRED |
| `pageshow` reset | `setGlobalLoadingState(false)` | Source verified | MANUAL REQUIRED, termasuk back-forward cache |
| Livewire navigation re-init | `livewire:navigate` reset marker/loading; `livewire:navigated` initialize ulang; attachment links dikecualikan | Source verified | MANUAL REQUIRED |
| Pagination query preservation | `withQueryString()` pada ticket, requester, Team Chair, user, location, skill, service, audit controllers/services | Source verified | MANUAL REQUIRED pada filter/page links |
| Form lock | Global overlay plus ticket/admin-specific disabled/`aria-busy` handlers | Source verified | MANUAL REQUIRED; pastikan tidak deadlock pada validation/back |
| Modal focus/close/reset | Close/Escape/trap/return ada; reset/clear hanya aktif jika data attributes dipasang | Source verified | MANUAL REQUIRED; ticket modal tidak mengaktifkan reset/clear |

Tidak ada klaim PASS runtime untuk interaksi global karena browser tidak tersedia.

## Known Existing Failures

1. Full `php artisan test` tidak selesai dalam 604,1 detik dan tidak menghasilkan summary.
2. `CatalogManagementTest::test_super_admin_can_manage_location_hierarchy_and_attachment_policy` gagal konsisten pada line 420 dengan duplicate attachment-policy error.
3. `OrganizationManagementTest::test_super_admin_can_render_organization_pages` hang konsisten, termasuk isolated run >64 detik.
4. Xdebug CLI mencoba terhubung ke `localhost:9003` pada setiap request; warning ini bukan penyebab tunggal karena run dengan `XDEBUG_MODE=off` tetap hang.
5. Browser visual runtime tidak tersedia, sehingga screenshot dan runtime interaction baseline tidak dapat dieksekusi.

Tidak ada failure di atas yang diperbaiki pada W0.

## Known Existing UI/Behavior Asymmetries

Asymmetry yang sudah dibekukan dalam `02-UI-CONTRACT-MATRIX.md` tetap berlaku:

- approval reject ticket modal tidak mengirim `_action_modal` dan tidak mengisi ulang `old('decision_note')`;
- attachment policy pending Approver tidak mengulang current active assignment check yang dipakai ticket view/report/decision;
- request-information backend menerima attachment, tetapi active UI hanya merender `body`;
- triage Form Request menerima outcome/field tambahan, tetapi active modal hanya merender `self`, `tier_2`, dan assignee;
- SVC-03 HTML requiredness dan server nullable input berbeda;
- login backend menerima `remember`, tetapi active login UI tidak merender control;
- `tickets.claim` dan `tickets.handle` tidak memiliki active trigger;
- room, attachment-policy, operational-policy, user split, dan service-field-status endpoints tertentu tidak memiliki active trigger.

Temuan W0 tambahan yang dicatat tanpa mengubah enam contract docs:

1. Empat dari 14 ticket modal tidak memiliki active opener: internal comment, database change, assign Tier 2, return Tier 1.
2. Internal comment action tetap tersedia sebagai inline form, tetapi inline form tidak membawa `_action_modal`; modal variant tidak menjadi error target normal.
3. Database-change, assign-Tier-2, dan return-Tier-1 form dirender ketika policy flag true, tetapi tidak ada `data-ui-modal-open` aktif yang ditemukan.
4. Ticket modal tidak memasang `data-ui-modal-form`, `data-reset-on-close`, atau `data-clear-on-close`; generic reset/error-clear behavior tidak aktif.
5. Selector `data-ticket-priority-form`, `data-ticket-reject-form`, dan `data-ticket-resolution-form` tidak memiliki dedicated consumer di `resources/js/app.js`; form tetap ditangani global loading overlay.

Temuan ini adalah evidence existing behavior. Ia bukan izin melakukan bug fix dalam scope redesign.

## Missing Fixtures / Manual Verification Required

### Critical fixture gaps

- current active Approver dan stale/non-current Approver;
- pure Ketua Tim dengan active chair assignment;
- Super Admin+Tier 1;
- Pemohon+Approver;
- Ketua Tim+Tier 1;
- Ketua Tim+Super Admin;
- status Diproses, Menunggu Persetujuan, Menunggu Pihak Ketiga, Menunggu Konfirmasi, Ditolak, Tidak Disetujui;
- priority Sedang;
- normal service representative, SVC-05, dan SVC-07;
- approval pending dan decision variants;
- active third-party wait;
- requester confirmation dan reopen cycle;
- internal comment/attachment authorized actor pairing;
- SVC-03 control execute/verify sequence;
- Team Chair inside/outside scope pairing.

### Manual visual/runtime checklist

- seluruh 70 minimum desktop/mobile primary screen/variant screenshot;
- 14 ticket modal desktop/mobile;
- major admin list/form/modal states;
- six pure-role navigation variants;
- four critical multi-role navigation/authorization variants;
- current vs stale Approver;
- keyboard-only open/close/Escape/Tab/focus return;
- modal validation auto-open and old/error routing;
- notification dropdown and ownership;
- sidebar persistence and `localStorage` failure fallback;
- mobile navigation parity and overflow;
- global overlay, duplicate submit, `pageshow`, and Livewire re-init;
- query preservation across search/filter/sort/pagination;
- report download success/failure/loading;
- empty, error, loading, long-label, long-file-name, and narrow viewport states.

Semua item tetap **MANUAL VERIFICATION REQUIRED** sampai browser, actor, dan ticket fixture tersedia.

## W0 Gate Result

### Gate summary

| Gate | Result | Evidence |
|---|---|---|
| Repository scope | PASS | Hanya dokumentasi `docs/redesign/` |
| Route baseline | PASS | 112 = 31 read/response + 81 mutation |
| Frontend build | PASS | Vite exit 0, no warning/error |
| PHP regression baseline | **BLOCKED** | Full suite timeout; one reproducible failure; one reproducible hang |
| Actor fixture matrix | **BLOCKED** | Approver, Team Chair, dan empat critical multi-role fixture tidak tersedia |
| Ticket fixture matrix | **BLOCKED** | Status/service/workflow visual fixture kritis tidak lengkap |
| 23 active screen source review | PASS | 23/23 |
| 15 primary variant source review | PASS | 15/15 |
| Visual baseline | **BLOCKED** | Browser list kosong; 0 screenshot |
| 14 modal source contract | PASS WITH ISSUES | 14 IDs verified; hanya 10 active opener; runtime 0/14 |
| Global interaction source review | PASS WITH MANUAL GAPS | Source verified; browser runtime unavailable |

## **BLOCKED**

Alasan authoritative mengikuti aturan gate W0:

1. regression baseline PHP tidak dapat direproduksi sampai summary final;
2. fixture kritis actor dan ticket belum tersedia;
3. visual/runtime baseline tidak dapat diambil karena browser unavailable dan authenticated fixture tidak deterministic.

Existing test failure sendiri tidak otomatis memblokir. Status **BLOCKED** berasal dari kombinasi suite hang, critical fixture gap, dan visual baseline yang tidak dapat direproduksi.

### Prasyarat membuka gate

Tanpa melakukan redesign:

1. sediakan fixture testing/local deterministic untuk enam pure role, current/stale Approver, dan empat multi-role wajib;
2. sediakan ticket fixture matrix lengkap untuk status, prioritas, service, workflow, attachment, dan Team Chair scope;
3. pulihkan test runner agar `php artisan test` mencapai summary; existing failure boleh tetap dicatat sebagai accepted baseline issue bila keputusan terpisah menyetujuinya;
4. sediakan browser runtime dan prosedur kredensial non-produksi yang aman;
5. ambil screenshot dengan naming plan dan selesaikan manual keyboard/interaction checklist;
6. klasifikasikan empat missing modal opener serta approval modal error gap sebagai preserve-existing atau defect scope terpisah sebelum W6.

Redesign W1 tidak boleh dimulai dengan menganggap gap di atas telah lulus.
