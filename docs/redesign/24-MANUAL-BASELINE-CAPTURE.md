# Manual Baseline Capture

Dokumen ini adalah manifest dan handoff manusia untuk FASE 3.4A. Ia menyiapkan minimum manual baseline sebelum W1 tanpa mengubah Blade, CSS, JavaScript, backend, route, test, fixture logic, atau design token. Dokumen ini tidak menyatakan screenshot telah dibuat.

## Status

**FASE 3.4A READY FOR HUMAN CAPTURE**

**SCREENSHOT BASELINE: NOT COMPLETE — 0/54 PNG**

**REDESIGN IMPLEMENTATION: HOLD — MANUAL VISUAL BASELINE REQUIRED**

## Prepared Environment

Environment default berikut disiapkan dan diverifikasi pada `2026-08-14T12:51:06+07:00`:

| Item | Actual value | Verification |
|---|---|---|
| `APP_URL` | `http://127.0.0.1:8765` | `/login` merespons HTTP 200 dengan title `Masuk - SIHATI` |
| Port | `8765` | Laravel local server, host `127.0.0.1`, `--no-reload` |
| `APP_ENV` | `testing` | Bukan production environment |
| Database | `C:\Users\Personal\Herd\helpdesk-idth\storage\framework\testing\manual-visual-baseline-phase34a\ui-baseline-act05.sqlite` | SQLite baru dan disposable di bawah `storage/framework/testing/` |
| Current Approver profile | `ACT-05` | Tepat satu active assignment, username `ui_test_approver_current` |
| Session cookie | `sihati_manual_visual_act05_phase34a` | Terisolasi untuk profile ACT-05 |
| Private fixture files | `storage/app/private/ui-baseline/` | Tepat 17 expected synthetic attachment files; tidak ada file non-fixture pada directory tersebut |
| Evidence destination | `docs/redesign/baseline/` | Tersedia dan kosong; tidak berada di `public/` atau `resources/` |

Server default ACT-05 sedang dijalankan dengan konfigurasi tersebut. Bila server perlu dimulai ulang, gunakan environment dan command authoritative pada bagian berikut; jangan mengandalkan `.env` aplikasi sehari-hari.

## Regression Gate

| Command | Actual result |
|---|---|
| `XDEBUG_MODE=off php artisan test` | PASS — 140 total, 139 passed, 0 failed, 0 errors, 1 intentional PostgreSQL concurrency skip pada SQLite, 1.780 assertions, 27,61 detik |
| `npm run build` | PASS — Vite 6.4.3, 59 modules transformed, 3,45 detik |

Hasil sama dengan baseline authoritative. Visual capture boleh disiapkan; hasil ini bukan izin memulai redesign.

## Default ACT-05 Fixture

Command fixture yang dijalankan terhadap database disposable baru:

```powershell
php artisan migrate --seed --force --no-interaction
php artisan app:seed-ui-baseline --current-approver=ACT-05 --no-interaction
php artisan serve --host=127.0.0.1 --port=8765 --no-reload
```

Environment proses wajib tetap:

```powershell
$env:APP_ENV='testing'
$env:APP_URL='http://127.0.0.1:8765'
$env:DB_CONNECTION='sqlite'
$env:DB_DATABASE='C:\Users\Personal\Herd\helpdesk-idth\storage\framework\testing\manual-visual-baseline-phase34a\ui-baseline-act05.sqlite'
$env:SESSION_DRIVER='file'
$env:SESSION_COOKIE='sihati_manual_visual_act05_phase34a'
$env:CACHE_STORE='array'
$env:QUEUE_CONNECTION='sync'
$env:MAIL_MAILER='array'
$env:XDEBUG_MODE='off'
```

Jangan menjalankan command tersebut bila target database bukan file disposable baru atau bila environment bukan `local`/`testing`.

### Verified fixture summary

| Fixture item | Count |
|---|---:|
| Actors | 13 |
| Work teams | 3 |
| Tickets | 22 |
| Canonical statuses | 11/11 |
| Canonical priorities | 4/4 |
| Service codes | 7/7 |
| Comments | 7 |
| Attachments, termasuk retained soft delete | 17 |

### Login-ready verification

Password hash, active flag, `must_change_password=false`, dan role berikut diverifikasi secara read-only. Gunakan credential fixture synthetic yang ditetapkan di `09-UI-FIXTURE-CATALOG.md` dan `12-SCREENSHOT-INDEX.md`; jangan menampilkan credential pada screenshot.

| Actor | Username | Authoritative role | Login-ready |
|---|---|---|---|
| ACT-01 | `ui_test_super_admin` | Super Admin | YES |
| ACT-02 | `ui_test_requester` | Pemohon | YES |
| ACT-03 | `ui_test_agent_t1` | Agen Tier 1 | YES |
| ACT-04 | `ui_test_agent_t2` | Agen Tier 2 | YES |
| ACT-05 | `ui_test_approver_current` | Approver, current pada default profile | YES |
| ACT-07 | `ui_test_team_chair` | Ketua Tim Kerja | YES |

ACT-09 hanya boleh diverifikasi/captured setelah switch ke profile alternatif yang terisolasi di bawah. Jangan memindahkan active assignment di database ACT-05.

## Viewport and Capture Rules

| Setting | Required value |
|---|---|
| Desktop viewport | `1440 × 900` |
| Mobile viewport | `390 × 844` |
| Browser | Browser yang sama untuk seluruh 54-image corpus; catat nama dan versi aktual saat capture |
| Zoom | `100%` |
| Device scale | Konsisten untuk seluruh corpus; catat nilai aktual |
| Locale | Indonesia |
| Default capture | Viewport screenshot |
| Authorized exception | `UI-006-status-priority-matrix` boleh full-page sesuai `12-SCREENSHOT-INDEX.md` |

Sebelum setiap capture:

- tunggu asset, font, network, dan loading selesai;
- pertahankan sidebar dalam keadaan expanded kecuali manifest menyatakan lain;
- jangan mengubah DOM secara manual;
- jangan menyembunyikan elemen agar screenshot terlihat lebih rapi;
- jangan memakai browser extension yang mengubah appearance;
- logout di antara akun actor dan jangan reuse authorization state;
- gunakan hanya fixture synthetic dan jangan tampilkan credential atau PII nyata;
- catat timestamp aktual, URL aktual, actor, fixture, browser, viewport, zoom, dan device scale pada execution note.

## Minimum Capture Manifest

Manifest ini berisi **exactly 27 basenames**, masing-masing satu desktop dan satu mobile image: **27 pairs / 54 PNG planned**. Seluruh status tetap `PENDING` sampai manusia membuat dan mereview file aktual.

### MB-001 — Login

- ID: `MB-001` (`UI-001`)
- basename: `UI-001-login`
- actor: Guest
- route: `/login`
- fixture: Tidak memerlukan actor session; gunakan aplikasi ACT-05 default
- purpose: Merekam guest shell dan login existing
- desktop filename: `docs/redesign/baseline/UI-001-login-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-001-login-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-002 — Dashboard Super Admin

- ID: `MB-002` (`UI-002`, `D-01`)
- basename: `UI-002-dashboard-d01-super-admin`
- actor: ACT-01 — Super Admin
- route: `/dashboard`
- fixture: Default ACT-05 pack; pure Super Admin
- purpose: Merekam shell dan dashboard admin tanpa implied operational capability
- desktop filename: `docs/redesign/baseline/UI-002-dashboard-d01-super-admin-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-002-dashboard-d01-super-admin-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-003 — Dashboard Pemohon

- ID: `MB-003` (`UI-002`, `D-02`)
- basename: `UI-002-dashboard-d02-requester`
- actor: ACT-02 — Pemohon
- route: `/dashboard`
- fixture: Default ACT-05 pack; requester TEAM-A dengan long display name
- purpose: Merekam requester hero, summary, task hierarchy, dan long identity
- desktop filename: `docs/redesign/baseline/UI-002-dashboard-d02-requester-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-002-dashboard-d02-requester-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-004 — Dashboard Agen Tier 1

- ID: `MB-004` (`UI-002`, `D-03`)
- basename: `UI-002-dashboard-d03-tier1`
- actor: ACT-03 — Agen Tier 1
- route: `/dashboard`
- fixture: Default ACT-05 pack; queue dan operational work tersedia
- purpose: Merekam dashboard operasional Tier 1 dan SLA/work hierarchy existing
- desktop filename: `docs/redesign/baseline/UI-002-dashboard-d03-tier1-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-002-dashboard-d03-tier1-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-005 — Dashboard Agen Tier 2

- ID: `MB-005` (`UI-002`, `D-04`)
- basename: `UI-002-dashboard-d04-tier2`
- actor: ACT-04 — Agen Tier 2
- route: `/dashboard`
- fixture: Default ACT-05 pack; assigned technician dengan skill
- purpose: Merekam dashboard personal-work Tier 2
- desktop filename: `docs/redesign/baseline/UI-002-dashboard-d04-tier2-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-002-dashboard-d04-tier2-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-006 — Dashboard Current Approver

- ID: `MB-006` (`UI-002`, `D-05`)
- basename: `UI-002-dashboard-d05-current-approver`
- actor: ACT-05 — Approver
- route: `/dashboard`
- fixture: Default ACT-05 pack; ACT-05 adalah satu-satunya current Approver
- purpose: Merekam `Perlu Tindakan Saya` dan current-assignment presentation
- desktop filename: `docs/redesign/baseline/UI-002-dashboard-d05-current-approver-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-002-dashboard-d05-current-approver-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-007 — Dashboard Ketua Tim Kerja

- ID: `MB-007` (`UI-002`, `D-06`)
- basename: `UI-002-dashboard-d06-team-chair`
- actor: ACT-07 — Ketua Tim Kerja
- route: `/dashboard`
- fixture: Default ACT-05 pack; pure TEAM-A chair
- purpose: Merekam monitoring safe projection dan read-only hierarchy
- desktop filename: `docs/redesign/baseline/UI-002-dashboard-d06-team-chair-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-002-dashboard-d06-team-chair-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-008 — Ticket List Pemohon

- ID: `MB-008` (`UI-004`, `L-01`)
- basename: `UI-004-ticket-list-l01-requester`
- actor: ACT-02 — Pemohon
- route: `/tickets?tab=semua&per_page=10`
- fixture: Default ACT-05 pack; owned requester tickets dan pagination-ready data
- purpose: Merekam requester tabs, filters, list/table-card parity, dan long content
- desktop filename: `docs/redesign/baseline/UI-004-ticket-list-l01-requester-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-004-ticket-list-l01-requester-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-009 — Ticket List Operational

- ID: `MB-009` (`UI-004`, `L-02`)
- basename: `UI-004-ticket-list-l02-operational`
- actor: ACT-03 — Agen Tier 1
- route: `/tickets?per_page=10`
- fixture: Default ACT-05 pack; operational scoped records
- purpose: Merekam operational list variant dan server-owned scope
- desktop filename: `docs/redesign/baseline/UI-004-ticket-list-l02-operational-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-004-ticket-list-l02-operational-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-010 — Ticket List Ketua Tim

- ID: `MB-010` (`UI-004`, `L-03`)
- basename: `UI-004-ticket-list-l03-team-chair`
- actor: ACT-07 — Ketua Tim Kerja
- route: `/tickets`
- fixture: Default ACT-05 pack; TEAM-A in-scope safe projection
- purpose: Merekam list monitoring read-only tanpa private/internal data
- desktop filename: `docs/redesign/baseline/UI-004-ticket-list-l03-team-chair-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-004-ticket-list-l03-team-chair-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-011 — Create Ticket Catalog

- ID: `MB-011` (`UI-007`)
- basename: `UI-007-create-catalog`
- actor: ACT-02 — Pemohon
- route: `/tickets/create`
- fixture: Default ACT-05 pack; seven-service catalog
- purpose: Merekam service-selection state sebelum form dipilih
- desktop filename: `docs/redesign/baseline/UI-007-create-catalog-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-007-create-catalog-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-012 — Create Ticket SVC-06

- ID: `MB-012` (`UI-007`)
- basename: `UI-007-create-normal-svc06`
- actor: ACT-02 — Pemohon
- route: `/tickets/create?service_type_id=6`
- fixture: SVC-06 — form layanan normal dari default ACT-05 pack
- purpose: Merekam representative normal form, grouping, controls, dan attachment input
- desktop filename: `docs/redesign/baseline/UI-007-create-normal-svc06-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-007-create-normal-svc06-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-013 — Create Ticket SVC-07 Dynamic

- ID: `MB-013` (`UI-007`)
- basename: `UI-007-create-svc07-dynamic`
- actor: ACT-02 — Pemohon
- route: `/tickets/create?service_type_id=7`
- fixture: SVC-07 — nine rendered dynamic input markers dari default ACT-05 pack
- purpose: Merekam long dynamic form, labels, field order, dan responsive stacking
- desktop filename: `docs/redesign/baseline/UI-007-create-svc07-dynamic-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-007-create-svc07-dynamic-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-014 — Ticket Detail Super Admin SVC-07

- ID: `MB-014` (`UI-008`, `T-01`)
- basename: `UI-008-detail-t01-super-admin-svc07`
- actor: ACT-01 — Super Admin
- route: `/tickets/19`
- fixture: UI-TKT-SVC07-001 / `CHG-2026-91010`
- purpose: Merekam full read detail tanpa operational action untuk pure Super Admin
- desktop filename: `docs/redesign/baseline/UI-008-detail-t01-super-admin-svc07-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-008-detail-t01-super-admin-svc07-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-015 — Ticket Detail Pemohon SVC-07

- ID: `MB-015` (`UI-008`, `T-02`)
- basename: `UI-008-detail-t02-requester-svc07`
- actor: ACT-02 — Pemohon
- route: `/tickets/19`
- fixture: UI-TKT-SVC07-001 / `CHG-2026-91010`
- purpose: Merekam requester-safe detail, public communication, dan long content
- desktop filename: `docs/redesign/baseline/UI-008-detail-t02-requester-svc07-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-008-detail-t02-requester-svc07-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-016 — Ticket Detail Tier 1 SVC-07

- ID: `MB-016` (`UI-008`, `T-03`)
- basename: `UI-008-detail-t03-tier1-svc07`
- actor: ACT-03 — Agen Tier 1
- route: `/tickets/19`
- fixture: UI-TKT-SVC07-001 / `CHG-2026-91010`
- purpose: Merekam public/internal separation, internal fields, attachments, dan timeline
- desktop filename: `docs/redesign/baseline/UI-008-detail-t03-tier1-svc07-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-008-detail-t03-tier1-svc07-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-017 — Ticket Detail Tier 2 Assigned

- ID: `MB-017` (`UI-008`, `T-04`)
- basename: `UI-008-detail-t04-tier2-assigned`
- actor: ACT-04 — Agen Tier 2
- route: `/tickets/3`
- fixture: UI-TKT-T2-001 / `REQ-2026-91002`, assigned ke ACT-04
- purpose: Merekam assigned-only Tier 2 detail dan active workflow controls existing
- desktop filename: `docs/redesign/baseline/UI-008-detail-t04-tier2-assigned-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-008-detail-t04-tier2-assigned-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-018 — Ticket Detail Current Approver

- ID: `MB-018` (`UI-008`, `T-05`)
- basename: `UI-008-detail-t05-current-approver`
- actor: ACT-05 — current Approver
- route: `/tickets/4`
- fixture: UI-TKT-APPROVAL-CURRENT-001 / `CHG-2026-91001`
- purpose: Merekam pending approval detail dan decision control existing
- desktop filename: `docs/redesign/baseline/UI-008-detail-t05-current-approver-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-008-detail-t05-current-approver-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-019 — Ticket Detail Ketua Tim

- ID: `MB-019` (`UI-008`, `T-06`)
- basename: `UI-008-detail-t06-team-chair`
- actor: ACT-07 — Ketua Tim Kerja
- route: `/tickets/20`
- fixture: UI-TKT-TEAM-IN-001 / `REQ-2026-91008`, TEAM-A in scope
- purpose: Merekam separate safe view, public-only projection, dan read-only notice
- desktop filename: `docs/redesign/baseline/UI-008-detail-t06-team-chair-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-008-detail-t06-team-chair-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-020 — Dashboard ACT-09 Pemohon + Approver

- ID: `MB-020` (`UI-002`)
- basename: `UI-002-dashboard-act09-requester-approver`
- actor: ACT-09 — Pemohon + current Approver
- route: `/dashboard`
- fixture: Alternate ACT-09 database only
- purpose: Merekam mixed requester/current-Approver presentation
- desktop filename: `docs/redesign/baseline/UI-002-dashboard-act09-requester-approver-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-002-dashboard-act09-requester-approver-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-021 — Ticket List ACT-09 Requester Variant

- ID: `MB-021` (`UI-004`, requester variant)
- basename: `UI-004-ticket-list-act09-requester-variant`
- actor: ACT-09 — Pemohon + current Approver
- route: `/tickets`
- fixture: Alternate ACT-09 database only
- purpose: Memastikan ticket list tetap requester variant walaupun approval capability aktif
- desktop filename: `docs/redesign/baseline/UI-004-ticket-list-act09-requester-variant-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-004-ticket-list-act09-requester-variant-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-022 — Approvals ACT-09 Current

- ID: `MB-022` (`UI-010`)
- basename: `UI-010-approvals-act09-current`
- actor: ACT-09 — Pemohon + current Approver
- route: `/approvals`
- fixture: Alternate ACT-09 database; two pending requests assigned ke ACT-09
- purpose: Merekam current-assignment approval inbox pada multi-role actor
- desktop filename: `docs/redesign/baseline/UI-010-approvals-act09-current-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-010-approvals-act09-current-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-023 — Ticket Detail ACT-09 Pending Approval

- ID: `MB-023` (`UI-008`)
- basename: `UI-008-detail-act09-pending-approval`
- actor: ACT-09 — Pemohon + current Approver
- route: `/tickets/4`
- fixture: Alternate ACT-09 database; UI-TKT-APPROVAL-CURRENT-001 / `CHG-2026-91001`
- purpose: Merekam pending decision detail bagi requester/current-Approver multi-role actor
- desktop filename: `docs/redesign/baseline/UI-008-detail-act09-pending-approval-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-008-detail-act09-pending-approval-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-024 — User Management

- ID: `MB-024` (`UI-012`)
- basename: `UI-012-users`
- actor: ACT-01 — Super Admin
- route: `/admin/users?per_page=10`
- fixture: Default ACT-05 pack; 13 synthetic actors
- purpose: Merekam representative high-density admin table/mobile cards dan existing actions
- desktop filename: `docs/redesign/baseline/UI-012-users-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-012-users-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-025 — Reports Populated

- ID: `MB-025` (`UI-011`)
- basename: `UI-011-reports-populated`
- actor: ACT-03 — Agen Tier 1
- route: `/reports?month=2026-08`
- fixture: Default ACT-05 pack; populated August 2026 report
- purpose: Merekam representative report hierarchy, period filter, table, dan export actions
- desktop filename: `docs/redesign/baseline/UI-011-reports-populated-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-011-reports-populated-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-026 — Status and Priority Matrix

- ID: `MB-026` (`UI-006`)
- basename: `UI-006-status-priority-matrix`
- actor: ACT-03 — Agen Tier 1
- route: `/tickets/all?per_page=50`
- fixture: Default ACT-05 pack; 11/11 statuses dan 4/4 priorities
- purpose: Merekam seluruh status/priority treatment existing; full-page capture exception diizinkan
- desktop filename: `docs/redesign/baseline/UI-006-status-priority-matrix-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-006-status-priority-matrix-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

### MB-027 — SVC-07 Validation and Old Input

- ID: `MB-027` (`UI-007`)
- basename: `UI-007-create-svc07-validation-old`
- actor: ACT-02 — Pemohon
- route: `/tickets/create?service_type_id=7` setelah invalid SVC-07 POST
- fixture: Default ACT-05 pack; masukkan subject synthetic `UI-W03 old subject`, biarkan required values tidak valid, dan jangan lakukan successful submission
- purpose: Merekam field-level validation, error association, service context, dan `old()` restoration
- desktop filename: `docs/redesign/baseline/UI-007-create-svc07-validation-old-desktop.png`
- mobile filename: `docs/redesign/baseline/UI-007-create-svc07-validation-old-mobile.png`
- desktop status: `PENDING`
- mobile status: `PENDING`
- interaction note: `PENDING`
- review status: `PENDING`

## ACT-09 Alternate Profile Switch Procedure

Jalankan prosedur ini hanya setelah seluruh **23 default-profile basenames / 46 images** selesai. Empat basename ACT-09 adalah MB-020 sampai MB-023.

1. Logout dari ACT-05/default profile dan stop server default pada port `8765`.
2. Pastikan database ACT-05 tetap utuh; jangan mengubah `approver_assignments` di dalamnya.
3. Dari repository root, tetapkan path database alternatif di bawah `storage/framework/testing/` dan pastikan file belum ada. Jangan memakai production/shared database.
4. Buat file SQLite kosong, migrate/seed, lalu jalankan fixture dengan `--current-approver=ACT-09`.
5. Gunakan session cookie berbeda agar state authorization ACT-05 tidak terbawa.
6. Start server pada local URL, login sebagai ACT-09, lalu pastikan `/approvals` dapat dibuka dan UI menyatakan ACT-09 sebagai current Approver sebelum capture.

```powershell
$captureRoot = [System.IO.Path]::GetFullPath((Join-Path (Get-Location) 'storage/framework/testing/manual-visual-baseline-phase34a'))
$allowedRoot = [System.IO.Path]::GetFullPath((Join-Path (Get-Location) 'storage/framework/testing'))
if (-not $captureRoot.StartsWith($allowedRoot, [System.StringComparison]::OrdinalIgnoreCase)) {
    throw 'Target ACT-09 berada di luar storage/framework/testing.'
}
if (-not (Test-Path -LiteralPath $captureRoot)) {
    New-Item -ItemType Directory -Path $captureRoot | Out-Null
}

$act09Db = Join-Path $captureRoot 'ui-baseline-act09.sqlite'
if (Test-Path -LiteralPath $act09Db) {
    throw "ACT-09 database harus baru dan kosong: $act09Db"
}
New-Item -ItemType File -Path $act09Db | Out-Null

$env:APP_ENV='testing'
$env:APP_URL='http://127.0.0.1:8765'
$env:DB_CONNECTION='sqlite'
$env:DB_DATABASE=$act09Db
$env:SESSION_DRIVER='file'
$env:SESSION_COOKIE='sihati_manual_visual_act09_phase34a'
$env:CACHE_STORE='array'
$env:QUEUE_CONNECTION='sync'
$env:MAIL_MAILER='array'
$env:XDEBUG_MODE='off'

php artisan migrate --seed --force --no-interaction
php artisan app:seed-ui-baseline --current-approver=ACT-09 --no-interaction
php artisan serve --host=127.0.0.1 --port=8765 --no-reload
```

Expected command result tetap 13 actors, 3 teams, 22 tickets, 11 statuses, 4 priorities, 7 services, 7 comments, dan 17 attachments, tetapi current Approver harus `ACT-09` / `ui_test_requester_approver`. Bila profile, count, environment, atau database target berbeda, stop dan jangan capture.

## W1 Interaction Observation Notes

Observasi berikut direkam tanpa judgement atau perbaikan. Gunakan `PASS`, `FAIL`, `YES`, `NO`, `NOT OBSERVABLE`, atau catatan faktual; jangan mengubah source/DOM untuk memaksa hasil.

| Observation | Required execution | Result |
|---|---|---|
| Visible focus — representative button | Tekan Tab pada button yang terlihat di representative default page; catat apakah focus terlihat | `PENDING` |
| Visible focus — representative input | Tekan Tab menuju input SVC-06/SVC-07; catat apakah focus terlihat | `PENDING` |
| Visible focus — representative link | Tekan Tab menuju link shell/list; catat apakah focus terlihat | `PENDING` |
| Error association | Pada MB-027, identifikasi field terkait, apakah old subject kembali, dan apakah validation message terlihat | `PENDING` |
| Status completeness | Pada MB-026, catat apakah seluruh 11 canonical status terlihat | `PENDING` |
| Priority completeness | Pada MB-026, catat apakah seluruh 4 canonical priority terlihat | `PENDING` |
| Color-only dependency | Pada MB-026, catat secara observasional apakah makna status/priority bergantung hanya pada warna | `PENDING` |
| Obvious contrast concern | Catat elemen yang secara kasat mata tampak low-contrast; jangan menyatakan hasil WCAG hanya dari screenshot | `PENDING` |
| Reduced motion | Bila reasonably observable, catat browser preference dan behavior source/runtime; jangan mengubah desain | `PENDING` |

Untuk MB-027, execution note wajib menjawab secara eksplisit:

- old value restored: `PENDING`;
- validation message visible: `PENDING`;
- relevant field identification: `PENDING`.

## Modal Scope

Tidak ada modal capture dalam minimum 54-image corpus ini. Jangan menambahkan 10 reachable modal pairs kecuali actual W1 scope kemudian disetujui menyentuh modal markup, generic modal component, modal JavaScript, atau modal focus protocol. Modal evidence tetap just-in-time prerequisite untuk wave terkait.

## Frozen Baseline Defects

Jangan memperbaiki atau memanipulasi item berikut untuk kenyamanan capture:

- empat missing modal openers;
- approval decision validation auto-open gap;
- ticket modal reset/retained-state semantics;
- request-information body-only UI;
- triage UI/backend mismatch;
- SVC-03 reachability/readiness;
- ACT-08 navigation precedence;
- ACT-09 mixed presentation;
- Team Chair safe-projection override.

## Capture Completion Gate

FASE 3.4A hanya menyiapkan human capture. Status saat dokumen dibuat:

| Gate item | Status |
|---|---|
| Regression | PASS |
| Frontend build | PASS |
| Disposable default environment | VERIFIED |
| Default ACT-05 fixture | VERIFIED |
| Six required default actors login-ready | VERIFIED |
| Exact minimum manifest | 27/27 basenames, 54 images planned |
| ACT-09 alternate procedure | READY, not yet executed for capture |
| Actual screenshots | 0/54 |
| Production implementation changes | NO |
| Redesign implementation hold | REMAINS ACTIVE |

Do not mark screenshot baseline complete and do not remove the implementation hold until all 54 PNG files, execution notes, and human review are complete and the post-capture regression/build gate passes.

## Human Capture Instructions

1. Buka local URL `http://127.0.0.1:8765` dan pastikan halaman berasal dari disposable `testing` environment yang tercatat di dokumen ini.
2. Login menggunakan fixture actor pada manifest; ambil credential synthetic dari `09-UI-FIXTURE-CATALOG.md`/`12-SCREENSHOT-INDEX.md` dan jangan memasukkan credential ke frame screenshot.
3. Navigasikan ke route dan fixture exact pada row manifest; pastikan actor, ticket number/state, profile ACT-05/ACT-09, serta sidebar expanded sesuai instruksi.
4. Set browser yang dipilih ke zoom 100%, device scale konsisten, locale Indonesia, dan viewport desktop exact `1440 × 900`; tunggu asset/font/loading selesai.
5. Capture viewport ke exact desktop filename pada row; hanya MB-026 boleh memakai full-page exception. Catat timestamp, URL, actor, fixture, browser, viewport, zoom, device scale, dan observasi interaksi.
6. Set viewport mobile exact `390 × 844` tanpa mengganti browser, session actor, atau state halaman yang sedang direkam.
7. Capture ke exact mobile filename pada row; jangan edit DOM, hide element, memakai appearance-changing extension, atau menampilkan credential/PII.
8. Ubah `desktop status` dan `mobile status` menjadi `COMPLETED`, isi `interaction note`, lalu ubah `review status` hanya setelah kedua gambar diperiksa terhadap row yang benar.
9. Logout sebelum berpindah actor; jangan reuse authorization state. Setelah 23 default-profile basenames selesai, lakukan explicit ACT-09 database/cookie switch sebelum empat basename alternatif.
10. Lanjutkan ke actor/state berikutnya sampai 27 pairs lengkap; setelah capture, jalankan kembali full test/build dan repository-safety diff sebelum mengusulkan perubahan gate.
