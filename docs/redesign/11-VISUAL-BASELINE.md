# Visual Baseline

Dokumen ini adalah hasil W0.3 — Visual & Interaction Baseline untuk UI **existing**. W0.3 tidak melakukan redesign, bug fix, perubahan Blade/CSS/JavaScript, atau perubahan backend. Karena environment tidak menyediakan browser runtime, baseline yang berhasil direkam pada run ini adalah:

1. reproducible server-rendered screen/variant evidence pada fixture disposable;
2. authorization dan visibility evidence melalui HTTP;
3. source-backed interaction/modal/accessibility evidence;
4. deterministic manual screenshot plan.

Tidak ada screenshot yang dipalsukan atau diklaim sebagai evidence visual.

## Environment

| Item | Nilai |
|---|---|
| Tanggal observasi | 14 Agustus 2026, Asia/Jakarta |
| OS | Microsoft Windows 11 Home Single Language |
| PHP | 8.4.19 |
| Laravel | 12.64.0 |
| Node.js | v25.5.0 |
| npm | 11.8.0 |
| Frontend build | Vite 6.4.3, 59 modules transformed |
| Server observasi | Laravel local server pada `127.0.0.1:8765` |
| Database | Dua SQLite testing disposable di `storage/framework/testing/w03/`; bukan database production/shared |
| Session/cache/queue/mail | file session terisolasi / array cache / sync queue / array mail |

### Baseline gate sebelum observasi

| Command | Hasil |
|---|---|
| `XDEBUG_MODE=off php artisan test` | PASS — 140 total, 139 passed, 0 failed, 0 errors, 1 intentional PostgreSQL worker-concurrency skip, 1,780 assertions |
| `npm run build` | PASS — Vite 6.4.3, 59 modules transformed |

Hasil sama dengan authoritative W0.2 baseline, sehingga observasi dilanjutkan.

## Fixture Profile

### Default profile

Command fixture: `php artisan app:seed-ui-baseline` pada database testing disposable.

| Item | Hasil |
|---|---:|
| Current approver | ACT-05 — `ui_test_approver_current` |
| Actors | 13 |
| Work teams | 3 |
| Tickets | 22 |
| Canonical statuses | 11 / 11 |
| Canonical priorities | 4 / 4 |
| Service codes | 7 / 7 |
| Comments | 7 |
| Attachments, termasuk retained soft delete | 17 |

### Alternate ACT-09 profile

Profile dibangun pada database disposable kedua menggunakan `php artisan app:seed-ui-baseline --current-approver=ACT-09`.

- current assignment yang dibaca langsung dari database: user ID 9, `ui_test_requester_approver`;
- pending approval IDs 1 dan 2 menunjuk ticket IDs 4 dan 5 serta `approver_id=9`;
- ACT-09 mendapat dashboard requester section + approver section, requester ticket-list variant, approval inbox, pending detail, dan report navigation;
- ACT-05 menjadi stale/non-current dan mendapat HTTP 403 pada approval inbox maupun pending detail.

Tidak ada invariant active approver yang dibypass.

## Browser/Tooling

Browser control yang tersedia di environment diperiksa sebelum capture. Hasil discovery adalah **tidak ada browser runtime yang tersedia**. Sesuai guardrail:

- tidak ada Playwright/Puppeteer/browser dependency yang ditambahkan;
- tidak ada perubahan `package.json` atau lockfile;
- tidak ada browser runtime yang di-install;
- tidak ada screenshot sintetis atau hasil render palsu;
- screenshot status: **MANUAL CAPTURE REQUIRED**.

Tooling yang tetap digunakan untuk evidence non-piksel:

- authenticated HTTP render terhadap Laravel local server;
- source inspection terhadap Blade, CSS, JavaScript, controller/policy/service contract yang relevan;
- query read-only terhadap database fixture untuk memastikan assignment dan deterministic IDs;
- invalid submission yang sengaja tidak dapat mengubah workflow untuk mengamati validation redirect/old/error/modal marker;
- authorized/denied attachment download pada database disposable;
- report export ke memory response, tanpa menyimpan output ke repository.

## Viewports

| Viewport | Target | Status |
|---|---:|---|
| Desktop | 1440 × 900 | MANUAL CAPTURE REQUIRED |
| Mobile | 390 × 844 | MANUAL CAPTURE REQUIRED |
| Tablet optional | 768 × 1024 | Tidak dijadwalkan sebagai gate minimum |

Tidak ada klaim overflow, clipping, stacking, contrast, atau pixel parity sampai capture manual dilakukan.

## Capture Naming Convention

Target directory: `docs/redesign/baseline/`.

Format:

```text
{UI-ID}-{screen-or-state}-{actor-or-variant}-{fixture-optional}-{viewport}.png
```

Rules:

- lowercase slug setelah UI ID;
- viewport hanya `desktop` atau `mobile` untuk gate minimum;
- actor menggunakan `act01` sampai `act11` bila role label saja ambigu;
- fixture memakai slug catalog, bukan database ID yang tidak bermakna;
- validation/error/modal state selalu masuk filename;
- screenshot tidak pernah ditempatkan di `public/` atau `resources/`.

Daftar filename authoritative berada di `12-SCREENSHOT-INDEX.md`.

## Screen Coverage

Seluruh **23/23 active screens** berhasil dirender pada fixture dan actor yang tepat. Ini adalah HTTP/server-render evidence, bukan screenshot coverage.

| UI ID | Screen | Actor | Route/state | HTTP | Runtime title/marker |
|---|---|---|---|---:|---|
| UI-001 | Login | Guest | `/login` | 200 | `Masuk - SIHATI` |
| UI-002 | Dashboard | ACT-02 representative | `/dashboard` | 200 | `Dasbor — SIHATI` |
| UI-003 | Change password | ACT-02 | `/password/change` | 200 | `Ganti kata sandi - SIHATI` |
| UI-004 | Ticket list | ACT-02 | `/tickets` | 200 | `Tiket saya — SIHATI` |
| UI-005 | Ticket queue | ACT-03 | `/tickets/queue` | 200 | `Monitoring Tiket — SIHATI` |
| UI-006 | All tickets | ACT-03 | `/tickets/all` | 200 | `Semua Tiket — SIHATI` |
| UI-007 | Create ticket | ACT-02 | `/tickets/create` | 200 | `Pilih layanan — SIHATI` |
| UI-008 | Ticket detail | ACT-03 | `/tickets/19` | 200 | `CHG-2026-91010 — SIHATI` |
| UI-009 | Notifications | ACT-02 | `/notifications` | 200 | `Notifikasi — SIHATI` |
| UI-010 | Approvals | ACT-05 | `/approvals` | 200 | `Persetujuan — SIHATI` |
| UI-011 | Reports | ACT-03 | `/reports?month=2026-08` | 200 | `Laporan Bulanan — SIHATI` |
| UI-012 | Users | ACT-01 | `/admin/users` | 200 | `Pengguna — SIHATI` |
| UI-013 | User create | ACT-01 | `/admin/users/create` | 200 | `Tambah Pengguna — SIHATI` |
| UI-014 | User edit | ACT-01 | `/admin/users/2/edit` | 200 | `Edit Pengguna — SIHATI` |
| UI-015 | Reset password | ACT-01 | `/admin/users/2/reset-password` | 200 | `Reset Password — SIHATI` |
| UI-016 | Locations | ACT-01 | `/admin/locations` | 200 | `Manajemen Lokasi — SIHATI` |
| UI-017 | Teams | ACT-01 | `/admin/teams` | 200 | `Tim Kerja — SIHATI` |
| UI-018 | Skills | ACT-01 | `/admin/skills` | 200 | `Keahlian — SIHATI` |
| UI-019 | Audit log | ACT-01 | `/admin/audit-logs` | 200 | `Audit Trail — SIHATI` |
| UI-020 | Branding | ACT-01 | `/admin/branding` | 200 | `Identitas aplikasi — SIHATI` |
| UI-021 | Services | ACT-01 | `/admin/services` | 200 | `Manajemen Layanan — SIHATI` |
| UI-022 | Announcements | ACT-01 | `/admin/announcements` | 200 | `Pengumuman — SIHATI` |
| UI-023 | 403 | ACT-01 | `/tickets` | 403 | Pure Super Admin bukan operational actor |

Dormant views tetap berstatus **DO NOT REDESIGN / DO NOT ACTIVATE** dan tidak dimasukkan sebagai active screen:

- `tickets.requester-show`;
- `admin.catalog.index` dan legacy catalog partials sebagai independent screen;
- `admin.forms.index` sebagai independent screen.

## Primary Variant Coverage

Seluruh **15/15 primary variants** berhasil dirender melalui authenticated HTTP.

| Variant | Actor/fixture | HTTP evidence |
|---|---|---|
| D-01 Super Admin dashboard | ACT-01 | 200; generic `Selamat datang`; no helpdesk dashboard marker |
| D-02 Requester dashboard | ACT-02 | 200; `Butuh bantuan TI?` |
| D-03 Tier 1 dashboard | ACT-03 | 200; `data-helpdesk-dashboard` |
| D-04 Tier 2 dashboard | ACT-04 | 200; `data-helpdesk-dashboard` |
| D-05 Current Approver dashboard | ACT-05 | 200; `Perlu Tindakan Saya` |
| D-06 Team Chair dashboard | ACT-07 | 200; `Pemantauan tiket anggota tim` |
| L-01 Requester list | ACT-02 | 200; requester filters and `Tiket saya` |
| L-02 Operational list | ACT-03 | 200; operational scoped view |
| L-03 Team Chair list | ACT-07 | 200; `Pemantauan tim` |
| T-01 Super Admin detail | ACT-01, ticket 19 | 200; full detail/internal visibility, no operational actions |
| T-02 Requester detail | ACT-02, ticket 19 | 200; requester reply/public projection |
| T-03 Tier 1 detail | ACT-03, ticket 19 | 200; `Bagian internal SVC-07` |
| T-04 Tier 2 detail | ACT-04, ticket 3 | 200; assigned detail and return form rendered |
| T-05 Current Approver detail | ACT-05, ticket 4 | 200; pending approval panel and decision control |
| T-06 Team Chair detail | ACT-07, ticket 20 | 200; `Mode baca saja untuk pemantauan tim` |

Screenshot coverage untuk variants: **0/15 — manual**.

## Role Navigation Coverage

HTML navigation visibility diverifikasi untuk tujuh pure profiles dan empat critical multi-role profiles. Mobile labels berasal dari mobile branch yang dirender; open/close/focus/overflow belum diuji.

| Actor | Desktop navigation existing | Mobile navigation existing |
|---|---|---|
| ACT-01 | Dashboard; Manajemen Pengguna; Tim; Keahlian; Layanan; Lokasi; Pengumuman; Aplikasi; Laporan Bulanan; Audit Trail | Desktop set + Notifikasi + Ganti password |
| ACT-02 | Dasbor; Tiket saya | Desktop set + Notifikasi + Ganti password |
| ACT-03 | Dasbor; Monitoring Tiket; Semua Tiket; Laporan; Pengumuman | Desktop set + Notifikasi + Ganti password |
| ACT-04 | Dasbor; Monitoring Tiket | Desktop set + Notifikasi + Ganti password |
| ACT-05 default | Dasbor; Persetujuan; Laporan | Desktop set + Notifikasi + Ganti password |
| ACT-06 stale | Dasbor | Dasbor + Notifikasi + Ganti password |
| ACT-07 | Dasbor; Tiket tim | Desktop set + Notifikasi + Ganti password |
| ACT-08 | Super Admin navigation only | Super Admin mobile navigation; direct queue tetap authorized |
| ACT-09 alternate | Dasbor; Tiket saya; Persetujuan; Laporan | Desktop set + Notifikasi + Ganti password |
| ACT-10 | Dasbor; Tiket tim; Pengumuman | Desktop set + Notifikasi + Ganti password |
| ACT-11 | Super Admin navigation tanpa report | Super Admin mobile navigation tanpa report; ticket tetap Team Chair safe |

## Status Coverage

Seluruh **11/11 canonical statuses** tersedia pada deterministic fixture dan setiap representative detail berhasil HTTP 200 untuk ACT-03. Ticket number, status label, dan priority label terdapat pada rendered HTML.

| Status | Fixture | Ticket / ID |
|---|---|---|
| Baru | UI-TKT-NEW-001 | INC-2026-91001 / 1 |
| Diproses | UI-TKT-T1-001 | REQ-2026-91001 / 2 |
| Dikerjakan | UI-TKT-T2-001 | REQ-2026-91002 / 3 |
| Menunggu Persetujuan | UI-TKT-APPROVAL-CURRENT-001 | CHG-2026-91001 / 4 |
| Menunggu Pemohon | UI-TKT-WAIT-REQUESTER-001 | REQ-2026-91003 / 6 |
| Menunggu Pihak Ketiga | UI-TKT-WAIT-THIRD-PARTY-001 | REQ-2026-91004 / 7 |
| Menunggu Konfirmasi | UI-TKT-SVC02-RESULT-001 | REQ-2026-91005 / 8 |
| Ditutup | UI-TKT-CLOSED-ELIGIBLE-001 | CHG-2026-91003 / 9 |
| Ditolak | UI-TKT-REJECTED-001 | INC-2026-91002 / 12 |
| Tidak Disetujui | UI-TKT-APPROVAL-REJECTED-001 | CHG-2026-91005 / 13 |
| Dibatalkan | UI-TKT-CANCELLED-001 | REQ-2026-91006 / 11 |

Visual badge/treatment coverage: **0/11 — manual**.

## Priority Coverage

Seluruh **4/4 priorities** dirender pada detail fixture:

| Priority | Representative fixture |
|---|---|
| Kritis | UI-TKT-NEW-001 / UI-TKT-SVC03-PREEXEC-001 |
| Tinggi | UI-TKT-T1-001 |
| Sedang | UI-TKT-T2-001 |
| Rendah | UI-TKT-SVC07-001 |

Visual color/icon/text treatment coverage: **0/4 — manual**.

## Service Coverage

Catalog selection merender **7 service links**. Form yang dipilih merender sebagai berikut:

| Service | `data-ticket-form` | Dynamic input markers | Location field | Attachment input |
|---|---:|---:|---:|---:|
| SVC-01 | Ya | 6 | Ya | Ya |
| SVC-02 | Ya | 7 | Tidak | Ya |
| SVC-03 | Ya | 8 | Tidak | Ya |
| SVC-04 | Ya | 8 | Tidak | Ya |
| SVC-05 | Ya | 7 | Ya | Ya |
| SVC-06 | Ya | 8 | Tidak | Ya |
| SVC-07 | Ya | 9 | Tidak | Ya |

Marker count adalah jumlah rendered `data-ticket-field-input` pada HTML existing; bukan jumlah logical definitions dan bukan contract baru.

Invalid SVC-07 create submission tanpa required values:

- kembali ke `/tickets/create?service_type_id=7`;
- old subject `UI-W03 old subject` dipulihkan;
- validation text dan SweetAlert flash payload tersedia;
- ticket count tidak bertambah.

File-selection preview/state tetap manual karena browser tidak tersedia dan file input memang tidak boleh direpopulasi setelah validation redirect.

## Queue / List / Pagination Coverage

| Surface | State | Result |
|---|---|---|
| UI-005 ACT-03 | `queue`, `mine`, `assigned`, `completed` | Seluruhnya HTTP 200 dan tab query dirender |
| UI-005 ACT-04 | `mine`, `completed` | HTTP 200 |
| UI-005 ACT-04 | `queue` | HTTP 403, sesuai policy |
| UI-004 ACT-02 | `semua`, `aktif`, `tindakan`, `selesai` | Seluruhnya HTTP 200 dan tab query dirender |
| UI-004 requester | `q=UI-FIXTURE&per_page=10` | page 2 tersedia; `q` dan `per_page` dipertahankan |
| UI-006 all | `q=UI-FIXTURE&per_page=10` | page 2 tersedia; `q` dan `per_page` dipertahankan |

Fixture sudah cukup untuk pagination; tidak ada arbitrary data tambahan.

## Ticket Workflow Coverage

Required ticket-detail fixtures berikut seluruhnya mempunyai deterministic route dan berhasil dirender oleh actor yang berwenang:

| Fixture | Ticket ID | Primary actor/state evidence |
|---|---:|---|
| UI-TKT-NEW-001 | 1 | ACT-03; priority/reject/triage modal + opener |
| UI-TKT-T1-001 | 2 | ACT-08; assigned T1 workflow controls |
| UI-TKT-T2-001 | 3 | ACT-04; assigned T2, return form rendered |
| UI-TKT-APPROVAL-CURRENT-001 | 4 | ACT-05 default atau ACT-09 alternate; decision panel |
| UI-TKT-WAIT-REQUESTER-001 | 6 | ACT-02/ACT-03; requester/public reply state |
| UI-TKT-WAIT-THIRD-PARTY-001 | 7 | ACT-03; resume state |
| UI-TKT-SVC02-RESULT-001 | 8 | ACT-02; confirmation + result attachment |
| UI-TKT-CLOSED-ELIGIBLE-001 | 9 | ACT-02; reopen modal rendered pada baseline clock/run |
| UI-TKT-REJECTED-001 | 12 | ACT-02/ACT-03; rejected reason/final state |
| UI-TKT-APPROVAL-REJECTED-001 | 13 | ACT-03; decision note/final state |
| UI-TKT-SVC07-001 | 19 | ACT-02/ACT-03/ACT-07 visibility variants |
| UI-TKT-TEAM-IN-001 | 20 | ACT-07 safe team detail |

Tidak ada workflow transition sukses yang dijalankan untuk memperoleh visual state. Invalid submissions dipilih agar state domain tetap identik.

## SVC-02 Coverage

| State | Evidence |
|---|---|
| Before — ticket 14 | HTTP 200; completion modal dirender; `data_export_result` ada dan `required`; result filename belum tampil |
| After — ticket 8 | HTTP 200 untuk requester; confirmation modal dirender; `UI-ATT-03-data-export-result.csv` tampil dan download authorized |

## SVC-03 Coverage

| State | Evidence existing |
|---|---|
| Incomplete — ticket 15 | Checklist menunjukkan dua evidence missing; execute form tetap dirender, tetapi domain menolak execution sampai tiga evidence valid |
| Pre-execution — ticket 16 | Tiga evidence tersedia; execute form dirender |
| Executed — ticket 17 | Actor/time execution tampil; verification form dirender |
| Verified — ticket 18 | Actor/time execution dan verified result/notes tampil |

Pada keempat state, `ticket-database-change-modal` tidak mempunyai active opener. Ini adalah **BASELINE MISSING OPENER**, bukan perubahan yang dilakukan W0.3.

## SVC-07 Coverage

| Actor | Public comment | Internal comment | ATT-01 | ATT-02 | Internal fields | Raw description | Safe notice |
|---|---:|---:|---:|---:|---:|---:|---:|
| ACT-02 requester | Ya | Tidak | Ya | Tidak | Tidak | Ya | Tidak |
| ACT-03 T1 | Ya | Ya | Ya | Ya | Ya | Ya | Tidak |
| ACT-07 Team Chair | Ya | Tidak | Tidak | Tidak | Tidak | Tidak | Ya |

Long public/internal comments, long subject, long filenames, seven versioned internal fields, and timeline markers tersedia. Wrapping dan clipping masih manual.

## Team Chair Coverage

| Actor/check | Result |
|---|---|
| ACT-07 dashboard/list/ticket 20 in-scope | 200; safe/read-only markers present |
| ACT-07 ticket 21 outside TEAM-A | 403 |
| ACT-07 report | 403 |
| ACT-10 TEAM-B ticket 21 | 200; safe/read-only despite T1 role |
| ACT-10 TEAM-A ticket 20 | 403 |
| ACT-10 report | 403 |
| ACT-10 announcements | 200, additional T1 policy remains active |
| ACT-11 TEAM-C ticket 22 | 200; safe/read-only despite Super Admin role |
| ACT-11 admin users | 200 |
| ACT-11 report | 403 |

Safe projection tidak diuji dengan mencari field pada full model; evidence memakai separate rendered Team Chair view dan negative visibility markers.

## Attachment Visibility Coverage

HTTP download menggunakan named route existing. Semua result cocok dengan `AttachmentAccessPolicy` baseline.

| Attachment | Actor | Expected | HTTP |
|---|---|---|---:|
| ATT-01 public/requester-visible | ACT-02 | allow | 200 |
| ATT-01 | ACT-03 | allow | 200 |
| ATT-01 | ACT-07 | deny | 403 |
| ATT-02 internal | ACT-02 | deny | 403 |
| ATT-02 | ACT-03 | allow | 200 |
| ATT-02 | ACT-07 | deny | 403 |
| ATT-03 data export result | ACT-02 | allow | 200 |
| ATT-04 assigned T2 | ACT-04 | allow | 200 |
| ATT-04 | SUP-02 unrelated T2 | deny | 403 |
| ATT-05 internal T2 | ACT-04 assigned | allow | 200 |
| ATT-05 | SUP-02 unrelated T2 | deny | 403 |
| ATT-06 Team Chair test | ACT-07 | deny | 403 |
| ATT-06 | ACT-03 | allow | 200 |
| ATT-07 retained soft delete | ACT-03 | hidden/not downloadable | 404 |

## Report Coverage

| Contract | Result |
|---|---|
| `/reports?month=2026-08` | 200, HTML |
| `/reports/monthly?month=2026-08` alias | 200, identical response length during run |
| ACT-05 current report | 200 |
| XLSX export | 200; XLSX MIME; `laporan-tiket-bulanan-2026-08.xlsx` |
| PDF export | 200; PDF MIME; `laporan-tiket-bulanan-2026-08.pdf` |
| ACT-07/ACT-10/ACT-11 report | 403 |

Export loading/error presentation tetap manual; export audit writes hanya terjadi pada disposable database.

## Modal Coverage

### Classification

| # | Modal ID | Rendered | Active opener | Validation auto-open | Classification |
|---:|---|---:|---:|---:|---|
| 1 | `ticket-priority-modal` | Ya | Ya | Ya | REACHABLE + VALIDATION AUTO-OPEN |
| 2 | `ticket-reject-modal` | Ya | Ya | Ya | REACHABLE + VALIDATION AUTO-OPEN |
| 3 | `ticket-internal-comment-modal` | Ya | Tidak | Ya melalui invalid direct form action | NOT REACHABLE; inline internal form tetap aktif |
| 4 | `ticket-request-information-modal` | Ya | Ya | Ya | REACHABLE + VALIDATION AUTO-OPEN |
| 5 | `ticket-database-change-modal` | Ya | Tidak | Ya melalui expected domain error | NOT REACHABLE / BASELINE MISSING OPENER |
| 6 | `ticket-complete-modal` | Ya | Ya | Ya | REACHABLE + VALIDATION AUTO-OPEN |
| 7 | `ticket-confirmation-modal` | Ya | Ya | Ya | REACHABLE + VALIDATION AUTO-OPEN |
| 8 | `ticket-reopen-modal` | Ya | Ya | Ya | REACHABLE + VALIDATION AUTO-OPEN |
| 9 | `ticket-approval-decision-modal` | Ya | Ya | **Tidak** | REACHABLE + NO AUTO-OPEN / KNOWN GAP |
| 10 | `ticket-request-approval-modal` | Ya | Ya | Ya | REACHABLE + VALIDATION AUTO-OPEN |
| 11 | `ticket-third-party-modal` | Ya | Ya | Ya | REACHABLE + VALIDATION AUTO-OPEN |
| 12 | `ticket-triage-modal` | Ya | Ya | Ya | REACHABLE + VALIDATION AUTO-OPEN |
| 13 | `ticket-assign-tier-two-modal` | Ya | Tidak | Ya melalui invalid direct form action | NOT REACHABLE / BASELINE MISSING OPENER |
| 14 | `ticket-return-tier-one-modal` | Ya | Tidak | Ya melalui invalid direct form action | NOT REACHABLE / BASELINE MISSING OPENER |

Totals:

- rendered: **14/14**;
- reachable: **10/14**;
- not reachable: **4/14**;
- validation/error auto-open: **13/14**;
- browser runtime keyboard/focus/close coverage: **0/10 reachable — manual**.

## Global Interaction Coverage

| Behavior | Evidence/result | Status |
|---|---|---|
| Notification read one | Disposable ACT-02 unread count 28 → 27; redirect `/tickets/9` | PASS — server/runtime HTTP |
| Notification read all | 27 → 0; redirect `/notifications`; success flash payload present | PASS — server/runtime HTTP |
| Pagination query | Requester and all-ticket page 2 preserve `q` + `per_page` | PASS — rendered HTML |
| Create validation + old | SVC-07 invalid POST returns correct service form with old subject and errors | PASS — server/runtime HTTP |
| Modal validation routing | 13 true, approval decision false known gap | PASS WITH KNOWN GAP — server/runtime HTTP |
| Report primary/alias/export | HTML/XLSX/PDF responses and filename/MIME correct | PASS — server/runtime HTTP |
| Sidebar collapse/expand/persistence | `sihati.sidebar.collapsed`, ARIA updates, storage fallback present in source | MANUAL BROWSER REQUIRED |
| Mobile navigation open/close/focus/overflow | Native `<details>` and role branches present | MANUAL BROWSER REQUIRED |
| Notification dropdown open/close/focus | Markup present; server actions tested | MANUAL BROWSER REQUIRED |
| Global navigation loading + duplicate click | Source listener and global overlay present | MANUAL BROWSER REQUIRED |
| Form loading + duplicate submit | Source global guard plus form-specific handlers present | MANUAL BROWSER REQUIRED |
| `pageshow` reset | `setGlobalLoadingState(false)` registered | MANUAL BROWSER REQUIRED |
| Livewire navigate/re-init/no duplicate handlers | source init marker + navigate/navigated listeners present | MANUAL BROWSER REQUIRED |
| SweetAlert flash/destructive confirmation | escaped flash payload + pending/confirmed guards present | MANUAL BROWSER REQUIRED |
| Modal opener/focus/Tab/Shift+Tab/Escape/close/backdrop/focus return | source implementation present | MANUAL BROWSER REQUIRED |

## Responsive Coverage

Source-backed responsive patterns ditemukan:

- authenticated sidebar disembunyikan di bawah `lg`; mobile navigation memakai separate branch;
- ticket/user/service lists mempunyai desktop table + mobile card presentation;
- wide tables mempunyai `overflow-x-auto` dan explicit minimum widths;
- ticket detail berubah dari single column menjadi two-column/sticky aside pada `lg`;
- generic modal menggunakan viewport max-height dan `overflow-y-auto`;
- form grids dan action groups memakai breakpoint stacking;
- `prefers-reduced-motion: reduce` tersedia pada stylesheet.

Namun minimum viewport runtime result adalah:

| Representative | Desktop 1440×900 | Mobile 390×844 |
|---|---|---|
| Dashboard | MANUAL | MANUAL |
| Ticket list | MANUAL | MANUAL |
| Ticket create | MANUAL | MANUAL |
| Ticket detail | MANUAL | MANUAL |
| Report | MANUAL | MANUAL |
| Users | MANUAL | MANUAL |
| Services | MANUAL | MANUAL |
| Reachable modal | MANUAL | MANUAL |

## Accessibility Observation

Confirmed from active source/rendered HTML:

- document language is `id`;
- guest and app layouts contain a `<main>` landmark;
- forms generally use explicit label/input IDs;
- key validated inputs use `aria-invalid` and `aria-describedby`;
- active navigation uses `aria-current` and sidebar/disclosure code updates `aria-expanded`;
- generic ticket modals use `role="dialog"`, `aria-modal="true"`, and `aria-labelledby`;
- modal code contains initial focus, Tab/Shift+Tab loop, Escape, backdrop/close button, and focus-return logic;
- loading/export/status copy uses `aria-live` or status roles in key components;
- focus-visible rules and reduced-motion rules exist;
- status and priority always have text labels in addition to color.

Observed gap from source:

- no skip-to-content link or main target was found.

Manual-only checks:

- actual visible focus and logical order;
- modal trap/return behavior;
- native details/mobile nav focus behavior;
- contrast and color-only review;
- 200%/400% zoom, reflow, clipping, touch target, and screen-reader announcement behavior.

## Manual Verification Required

Priority 0 before any pixel-changing redesign wave:

1. execute `12-SCREENSHOT-INDEX.md` at 1440×900 and 390×844;
2. capture 23 primary screens and 15 structural variants;
3. capture all 10 reachable modal states at both viewports;
4. execute keyboard sequence for modal, mobile nav, notification dropdown, and destructive SweetAlert;
5. execute sidebar persistence/reload and `pageshow` reset;
6. verify Livewire internal navigation reinitializes once without duplicate handlers;
7. inspect responsive overflow, long subject, long filenames, table/card switch, and modal scrolling;
8. record contrast/focus/zoom observations;
9. keep the four missing openers and approval auto-open gap unchanged unless a separate defect scope is approved.

## Visual Issue Summary

`10-BASELINE-VISUAL-ISSUES.md` records **17 items**:

- 7 confirmed existing behavior/known functional asymmetries;
- 4 role/information-hierarchy or DOM-density risks;
- 6 manual visual, responsive, interaction, long-content, or accessibility evidence gaps.

Highest-risk areas:

1. missing SVC-03 opener;
2. other three missing ticket modal openers;
3. approval decision validation auto-open/old gap;
4. Team Chair safe-projection precedence;
5. Super Admin + T1 navigation precedence;
6. ACT-09 requester + current Approver mixed variants;
7. attachment visibility/download policy;
8. SVC-07 public/internal data separation;
9. services page DOM/modal density;
10. unverified responsive/modal keyboard runtime.

## Regression After Capture

Disposable servers, SQLite databases, and 17 synthetic attachment files were stopped/removed before the final gate. They can be recreated from the fixture command and contained no production/user data.

| Command | Final result |
|---|---|
| `XDEBUG_MODE=off php artisan test` | PASS — 140 total, 139 passed, 0 failed, 0 errors, 1 intentional skip, 1,780 assertions; 28.58s |
| `npm run build` | PASS — Vite 6.4.3, 59 modules transformed; 4.99s |

Hasil final identik secara outcome dengan baseline sebelum observasi.

## W0.3 Gate Result

## **W0.3 PASS WITH MANUAL GAPS**

Rationale:

- baseline regression dan build lulus sebelum observasi;
- default ACT-05 dan alternate ACT-09 fixture dapat direproduksi;
- 23/23 active screens dan 15/15 primary variants dapat dirender;
- 7 pure profiles dan 4 critical multi-role profiles diverifikasi;
- status 11/11, priority 4/4, services 7/7, required workflow, Team Chair, attachments, reports, dan modal classification tersedia;
- deterministic manual capture path ada dan tidak memerlukan perubahan application code;
- browser runtime tidak tersedia, sehingga screenshot count, responsive pixels, dan keyboard interaction tetap manual;
- tidak ada production behavior yang diubah.

Gate ini **tidak berarti screenshot golden telah lengkap**. Sebelum memulai implementation wave yang mengubah pixel/markup, jalankan Priority 0 manual capture plan. Existing known gaps tetap dibekukan dan tidak boleh dianggap implicitly fixed.
