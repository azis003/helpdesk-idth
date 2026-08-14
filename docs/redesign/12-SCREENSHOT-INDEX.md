# Manual Screenshot Capture Plan

## Status

**MANUAL SCREENSHOT CAPTURE REQUIRED**

Browser runtime/control tidak tersedia pada W0.3. Screenshot yang benar-benar dibuat: **0**. Dokumen ini adalah queue capture deterministic; ia tidak menyatakan file PNG di bawah sudah ada.

Target directory:

```text
docs/redesign/baseline/
```

Tidak boleh menyimpan evidence ke `public/` atau `resources/`.

## Filename expansion rule

Setiap row bertanda **PAIR** mempunyai dua filename exact:

```text
docs/redesign/baseline/{basename}-desktop.png
docs/redesign/baseline/{basename}-mobile.png
```

`desktop` selalu 1440×900. `mobile` selalu 390×844. Row bertanda **DESKTOP** atau **MOBILE** sudah mencantumkan filename tunggal exact.

## Deterministic preparation

### 1. Regression gate

Jalankan dari repository root:

```powershell
$env:XDEBUG_MODE='off'
php artisan test
npm run build
```

Stop bila hasil bukan 140 total / 139 passed / 1 intentional skip atau build gagal.

### 2. Default ACT-05 database

Gunakan file SQLite baru yang memang disposable. Contoh berikut bukan database aplikasi sehari-hari:

```powershell
$captureRoot = [System.IO.Path]::GetFullPath((Join-Path (Get-Location) 'storage/framework/testing/manual-visual-baseline'))
New-Item -ItemType Directory -Force -Path $captureRoot | Out-Null
$captureDb = Join-Path $captureRoot 'ui-baseline-act05.sqlite'
New-Item -ItemType File -Path $captureDb

$env:APP_ENV='testing'
$env:APP_URL='http://127.0.0.1:8765'
$env:DB_CONNECTION='sqlite'
$env:DB_DATABASE=$captureDb
$env:SESSION_DRIVER='file'
$env:SESSION_COOKIE='sihati_manual_visual_act05'
$env:CACHE_STORE='array'
$env:QUEUE_CONNECTION='sync'
$env:MAIL_MAILER='array'
$env:XDEBUG_MODE='off'

php artisan migrate --seed --force --no-interaction
php artisan app:seed-ui-baseline --current-approver=ACT-05 --no-interaction
php artisan serve --host=127.0.0.1 --port=8765 --no-reload
```

Jangan memakai `migrate:fresh` kecuali absolute database target sudah diverifikasi sebagai database disposable. Jangan lanjut bila `storage/app/private/ui-baseline/` berisi file non-fixture yang perlu dipertahankan.

Expected fixture summary: 13 actors, 3 teams, 22 tickets, 11 statuses, 4 priorities, 7 services, 7 comments, 17 attachments.

### 3. Credentials

Semua actor memakai password:

```text
Ui-Baseline-Local-2026!
```

| Actor | Username |
|---|---|
| ACT-01 | `ui_test_super_admin` |
| ACT-02 | `ui_test_requester` |
| ACT-03 | `ui_test_agent_t1` |
| ACT-04 | `ui_test_agent_t2` |
| ACT-05 | `ui_test_approver_current` |
| ACT-06 | `ui_test_approver_stale` |
| ACT-07 | `ui_test_team_chair` |
| ACT-08 | `ui_test_admin_t1` |
| ACT-09 | `ui_test_requester_approver` |
| ACT-10 | `ui_test_team_chair_t1` |
| ACT-11 | `ui_test_team_chair_admin` |
| SUP-02 | `ui_test_agent_t2_unrelated` |

### 4. Browser capture discipline

1. Gunakan browser yang sama untuk seluruh corpus.
2. Set zoom 100%, device scale konsisten, locale Indonesia, dan viewport exact.
3. Gunakan viewport screenshot, bukan resize manual berdasarkan perkiraan. Full-page capture hanya untuk row `status-priority-matrix`.
4. Tunggu font/assets/network stabil dan pastikan global loading overlay tidak aktif kecuali state loading memang sedang direkam.
5. Jangan menyembunyikan/mengedit elemen untuk membuat gambar lebih rapi.
6. Logout di antara actor. Jangan reuse authorization state lintas actor.
7. Baseline sidebar harus expanded kecuali filename menyebut `collapsed`/`persisted`.
8. Untuk file input, gunakan file synthetic kecil tanpa data nyata.
9. Rekam actual URL, actor, fixture ticket number, viewport, dan timestamp pada commit/PR note; jangan menaruh credential dalam gambar.

## Priority 0 — primary screen corpus

Seluruh row berikut adalah **PAIR**.

| Basename | UI/variant | Actor | Route / fixture | State/purpose |
|---|---|---|---|---|
| `UI-001-login` | UI-001 | Guest | `/login` | Existing login |
| `UI-002-dashboard-d01-super-admin` | D-01 | ACT-01 | `/dashboard` | Pure Super Admin |
| `UI-002-dashboard-d02-requester` | D-02 | ACT-02 | `/dashboard` | Requester hero/summary |
| `UI-002-dashboard-d03-tier1` | D-03 | ACT-03 | `/dashboard` | Helpdesk T1 |
| `UI-002-dashboard-d04-tier2` | D-04 | ACT-04 | `/dashboard` | Technician T2 |
| `UI-002-dashboard-d05-current-approver` | D-05 | ACT-05 | `/dashboard` | Current Approver |
| `UI-002-dashboard-d06-team-chair` | D-06 | ACT-07 | `/dashboard` | Team safe monitoring |
| `UI-003-change-password` | UI-003 | ACT-02 | `/password/change` | Empty form |
| `UI-004-ticket-list-l01-requester` | L-01 | ACT-02 | `/tickets?tab=semua&per_page=10` | Requester list |
| `UI-004-ticket-list-l02-operational` | L-02 | ACT-03 | `/tickets?per_page=10` | Operational scoped list |
| `UI-004-ticket-list-l03-team-chair` | L-03 | ACT-07 | `/tickets` | Safe team projection list |
| `UI-005-ticket-queue-default` | UI-005 | ACT-03 | `/tickets/queue?tab=queue` | Shared T1 queue |
| `UI-006-all-tickets` | UI-006 | ACT-03 | `/tickets/all?per_page=10` | All-ticket screen |
| `UI-007-create-catalog` | UI-007 | ACT-02 | `/tickets/create` | Catalog selection |
| `UI-007-create-normal-svc06` | UI-007 | ACT-02 | select SVC-06 from catalog | Normal service form |
| `UI-007-create-svc01-location` | UI-007 | ACT-02 | select SVC-01 | Required location |
| `UI-007-create-svc02` | UI-007 | ACT-02 | select SVC-02 | Data-export request fields |
| `UI-007-create-svc03` | UI-007 | ACT-02 | select SVC-03 | Database-change request fields |
| `UI-007-create-svc05-hardware-location` | UI-007 | ACT-02 | select SVC-05 | Hardware + location |
| `UI-007-create-svc07-dynamic` | UI-007 | ACT-02 | select SVC-07 | Long/dynamic request fields |
| `UI-008-detail-t01-super-admin-svc07` | T-01 | ACT-01 | `/tickets/19`, CHG-2026-91010 | Full read, no operational write |
| `UI-008-detail-t02-requester-svc07` | T-02 | ACT-02 | `/tickets/19` | Requester-safe detail |
| `UI-008-detail-t03-tier1-svc07` | T-03 | ACT-03 | `/tickets/19` | Internal fields/comments |
| `UI-008-detail-t04-tier2-assigned` | T-04 | ACT-04 | `/tickets/3`, REQ-2026-91002 | Assigned T2 |
| `UI-008-detail-t05-current-approver` | T-05 | ACT-05 | `/tickets/4`, CHG-2026-91001 | Pending approval |
| `UI-008-detail-t06-team-chair` | T-06 | ACT-07 | `/tickets/20`, REQ-2026-91008 | Separate safe view |
| `UI-009-notifications` | UI-009 | ACT-02 | `/notifications` | Unread/read list |
| `UI-010-approvals` | UI-010 | ACT-05 | `/approvals` | Pending inbox |
| `UI-011-reports-populated` | UI-011 | ACT-03 | `/reports?month=2026-08` | Populated report |
| `UI-012-users` | UI-012 | ACT-01 | `/admin/users?per_page=10` | Table/card + actions |
| `UI-013-user-create` | UI-013 | ACT-01 | `/admin/users/create` | Standalone create |
| `UI-014-user-edit` | UI-014 | ACT-01 | `/admin/users/2/edit` | Edit ACT-02 |
| `UI-015-reset-password` | UI-015 | ACT-01 | `/admin/users/2/reset-password` | Standalone reset |
| `UI-016-locations` | UI-016 | ACT-01 | `/admin/locations` | Building/floor list |
| `UI-017-teams` | UI-017 | ACT-01 | `/admin/teams` | Team cards/list |
| `UI-018-skills` | UI-018 | ACT-01 | `/admin/skills` | Skill list |
| `UI-019-audit-log` | UI-019 | ACT-01 | `/admin/audit-logs` | Populated audit list |
| `UI-020-branding` | UI-020 | ACT-01 | `/admin/branding` | Current preview/form |
| `UI-021-services` | UI-021 | ACT-01 | `/admin/services` | Service list |
| `UI-022-announcements` | UI-022 | ACT-01 | `/admin/announcements` | Existing announcements |
| `UI-023-403-super-admin-tickets` | UI-023 | ACT-01 | `/tickets` | Authorization denial |

Primary corpus: **41 pairs = 82 images**.

## Priority 0 — ACT-09 alternate profile

Stop the default server. Create a new empty disposable SQLite file, repeat migration/seed, and run:

```powershell
php artisan app:seed-ui-baseline --current-approver=ACT-09 --no-interaction
```

Verify the command prints ACT-09 as current. Do not try to switch assignment inside the ACT-05 database.

| Basename | UI | Actor | Route | Purpose |
|---|---|---|---|---|
| `UI-002-dashboard-act09-requester-approver` | UI-002 | ACT-09 | `/dashboard` | Requester section + current Approver section |
| `UI-004-ticket-list-act09-requester-variant` | UI-004 | ACT-09 | `/tickets` | Must remain requester list variant |
| `UI-010-approvals-act09-current` | UI-010 | ACT-09 | `/approvals` | Current-assignment capability |
| `UI-008-detail-act09-pending-approval` | UI-008 | ACT-09 | `/tickets/4` | Pending decision detail |

ACT-09 corpus: **4 pairs = 8 images**.

## Priority 1 — queue, filters, search, pagination

The default requester-list screenshot already covers `semua`; default queue covers `queue`.

| Basename | Actor | Route/state | Purpose |
|---|---|---|---|
| `UI-005-ticket-queue-mine` | ACT-03 | `/tickets/queue?tab=mine` | Mine tab |
| `UI-005-ticket-queue-assigned` | ACT-03 | `/tickets/queue?tab=assigned` | Assigned tab |
| `UI-005-ticket-queue-completed` | ACT-03 | `/tickets/queue?tab=completed` | Completed tab |
| `UI-004-requester-tab-active` | ACT-02 | `/tickets?tab=aktif` | Active tab |
| `UI-004-requester-tab-action` | ACT-02 | `/tickets?tab=tindakan` | Action-needed tab |
| `UI-004-requester-tab-completed` | ACT-02 | `/tickets?tab=selesai` | Completed tab |
| `UI-004-ticket-search-active` | ACT-02 | `/tickets?q=UI-FIXTURE&per_page=10` | Search chip/value |
| `UI-004-ticket-filter-active` | ACT-02 | `/tickets?tab=aktif&class=REQ&status=dikerjakan&from=2026-06-01&to=2026-08-31&per_page=10` | Combined filters |
| `UI-006-pagination-page2-query-preserved` | ACT-03 | `/tickets/all?q=UI-FIXTURE&per_page=10&page=2` | Page 2 and query preservation |

List-state corpus: **9 pairs = 18 images**.

## Priority 1 — create validation and file state

| Basename | Actor | State | Purpose |
|---|---|---|---|
| `UI-007-create-svc07-validation-old` | ACT-02 | Submit SVC-07 without required values after entering a synthetic subject | Validation + `old()` restored |
| `UI-007-create-svc07-file-selected` | ACT-02 | Select a small synthetic allowed file; do not submit | Native file input representation |

Create-state corpus: **2 pairs = 4 images**.

## Priority 1 — additional required workflow details

These rows supplement the six primary UI-008 variants; avoid duplicate files for T2/current-approval/SVC-07/team-in states already covered.

| Basename | Actor | Route / fixture | State |
|---|---|---|---|
| `UI-008-detail-new-queue` | ACT-03 | `/tickets/1`, UI-TKT-NEW-001 | Baru/unassigned actions |
| `UI-008-detail-t1-assigned` | ACT-08 | `/tickets/2`, UI-TKT-T1-001 | Diproses/assigned T1 |
| `UI-008-detail-wait-requester` | ACT-02 | `/tickets/6`, UI-TKT-WAIT-REQUESTER-001 | Menunggu Pemohon |
| `UI-008-detail-wait-third-party` | ACT-03 | `/tickets/7`, UI-TKT-WAIT-THIRD-PARTY-001 | Active third-party wait |
| `UI-008-detail-svc02-result` | ACT-02 | `/tickets/8`, UI-TKT-SVC02-RESULT-001 | Result + confirmation |
| `UI-008-detail-closed-reopen-eligible` | ACT-02 | `/tickets/9`, UI-TKT-CLOSED-ELIGIBLE-001 | Closed/reopen action if still eligible at capture clock |
| `UI-008-detail-rejected` | ACT-03 | `/tickets/12`, UI-TKT-REJECTED-001 | Rejection reason |
| `UI-008-detail-approval-rejected` | ACT-03 | `/tickets/13`, UI-TKT-APPROVAL-REJECTED-001 | Not-approved decision state |

Workflow supplement: **8 pairs = 16 images**.

If wall clock has moved beyond reopen eligibility, record the actual absence of the reopen control and do not alter dates/business logic to force it.

## Priority 1 — SVC-02, SVC-03, SVC-07, status/priority

| Basename | Actor | Route / fixture | Purpose |
|---|---|---|---|
| `UI-008-svc02-before-completion` | ACT-03 | `/tickets/14`, UI-TKT-SVC02-BEFORE-001 | Required result input before completion |
| `UI-008-svc03-incomplete` | ACT-03 | `/tickets/15` | Incomplete evidence checklist/current unreachable presentation |
| `UI-008-svc03-preexec-missing-opener` | ACT-03 | `/tickets/16` | Execute-ready content + missing opener evidence |
| `UI-008-svc03-executed-missing-opener` | ACT-03 | `/tickets/17` | Actor/time + verify form in unreachable modal |
| `UI-008-svc03-verified-missing-opener` | ACT-03 | `/tickets/18` | Verified result in unreachable modal |
| `UI-008-svc07-team-chair-safe` | ACT-07 | `/tickets/19` | Same SVC-07 fixture through safe projection |
| `UI-006-status-priority-matrix` | ACT-03 | `/tickets/all?per_page=50` | Full-page exception; evidence for 11 statuses + 4 priorities |

Special-control corpus: **7 pairs = 14 images**.

## Priority 1 — critical multi-role, Team Chair denial, attachment denial

| Basename | Actor | Route | Purpose |
|---|---|---|---|
| `UI-023-team-chair-outside-scope-act07` | ACT-07 | `/tickets/21` | TEAM-B denial |
| `UI-002-dashboard-act10-chair-t1` | ACT-10 | `/dashboard` | Combined role hero/nav |
| `UI-004-ticket-list-act10-chair-t1` | ACT-10 | `/tickets` | Team Chair list precedence |
| `UI-008-detail-act10-chair-t1-safe` | ACT-10 | `/tickets/21` | Safe TEAM-B detail |
| `UI-023-report-denied-act10-chair-t1` | ACT-10 | `/reports` | Team Chair report denial |
| `UI-002-dashboard-act11-chair-admin` | ACT-11 | `/dashboard` | Super Admin navigation + Team Chair role chip |
| `UI-012-users-act11-chair-admin` | ACT-11 | `/admin/users` | Additional admin capability remains |
| `UI-008-detail-act11-chair-admin-safe` | ACT-11 | `/tickets/22` | Safe TEAM-C ticket |
| `UI-023-report-denied-act11-chair-admin` | ACT-11 | `/reports` | Report denial despite Super Admin |
| `UI-002-dashboard-act08-admin-t1` | ACT-08 | `/dashboard` | Admin-dominant dashboard/nav |
| `UI-005-queue-act08-direct` | ACT-08 | `/tickets/queue` | Direct operational capability |
| `UI-012-users-act08-admin-t1` | ACT-08 | `/admin/users` | Admin capability |
| `UI-023-attachment-att05-unrelated-t2` | SUP-02 | `/attachments/16/download` | HTTP 403 denial |
| `UI-023-attachment-att06-team-chair` | ACT-07 | `/attachments/17/download` | HTTP 403 denial |

Critical-role/denial corpus: **14 pairs = 28 images**.

ATT-01, ATT-02, ATT-03, and ATT-04 allowed/hidden presentation is cross-covered by the SVC-07, SVC-02, and T2 detail screenshots. ATT-07 is verified as 404 and must not be exposed merely to create a screenshot.

## Priority 1 — reachable ticket modals

Open through the active UI opener. Do not create openers for the four unreachable modal IDs.

| Basename | Actor | Fixture/route | Modal |
|---|---|---|---|
| `UI-008-modal-priority` | ACT-03 | ticket 1 | `ticket-priority-modal` |
| `UI-008-modal-reject` | ACT-03 | ticket 1 | `ticket-reject-modal` |
| `UI-008-modal-request-information` | ACT-08 | ticket 2 | `ticket-request-information-modal` |
| `UI-008-modal-complete-svc02` | ACT-03 | ticket 14 | `ticket-complete-modal` |
| `UI-008-modal-confirmation` | ACT-02 | ticket 8 | `ticket-confirmation-modal` |
| `UI-008-modal-reopen` | ACT-02 | ticket 9 | `ticket-reopen-modal` |
| `UI-008-modal-approval-decision` | ACT-05 default | ticket 4 | `ticket-approval-decision-modal` |
| `UI-008-modal-request-approval` | ACT-08 | ticket 2 | `ticket-request-approval-modal` |
| `UI-008-modal-third-party-resume` | ACT-03 | ticket 7 | `ticket-third-party-modal` |
| `UI-008-modal-triage` | ACT-03 | ticket 1 | `ticket-triage-modal` |

Reachable modal corpus: **10 pairs = 20 images**.

For each modal, record separately in the execution note: opener, initial focus, Tab, Shift+Tab, Escape, close button, backdrop, focus return, scroll at mobile, and whether body overflow returns to normal.

## Priority 1 — navigation and global interaction images

### Mobile menu open — 11 single images

| Exact filename | Profile |
|---|---|
| `docs/redesign/baseline/GLOBAL-mobile-nav-act01-super-admin-mobile.png` | ACT-01 |
| `docs/redesign/baseline/GLOBAL-mobile-nav-act02-requester-mobile.png` | ACT-02 |
| `docs/redesign/baseline/GLOBAL-mobile-nav-act03-tier1-mobile.png` | ACT-03 |
| `docs/redesign/baseline/GLOBAL-mobile-nav-act04-tier2-mobile.png` | ACT-04 |
| `docs/redesign/baseline/GLOBAL-mobile-nav-act05-current-approver-mobile.png` | ACT-05 default |
| `docs/redesign/baseline/GLOBAL-mobile-nav-act06-stale-approver-mobile.png` | ACT-06 |
| `docs/redesign/baseline/GLOBAL-mobile-nav-act07-team-chair-mobile.png` | ACT-07 |
| `docs/redesign/baseline/GLOBAL-mobile-nav-act08-admin-t1-mobile.png` | ACT-08 |
| `docs/redesign/baseline/GLOBAL-mobile-nav-act09-requester-approver-mobile.png` | ACT-09 alternate profile |
| `docs/redesign/baseline/GLOBAL-mobile-nav-act10-chair-t1-mobile.png` | ACT-10 |
| `docs/redesign/baseline/GLOBAL-mobile-nav-act11-chair-admin-mobile.png` | ACT-11 |

### Other interaction evidence — 16 single images

| Exact filename | Viewport | State |
|---|---|---|
| `docs/redesign/baseline/GLOBAL-sidebar-collapsed-desktop.png` | Desktop | Collapse once |
| `docs/redesign/baseline/GLOBAL-sidebar-collapsed-persisted-desktop.png` | Desktop | Reload; confirm key persists |
| `docs/redesign/baseline/GLOBAL-notification-dropdown-desktop.png` | Desktop | Dropdown open |
| `docs/redesign/baseline/GLOBAL-notification-dropdown-mobile.png` | Mobile | Dropdown/menu representation |
| `docs/redesign/baseline/UI-009-notifications-after-read-one-desktop.png` | Desktop | One unread marked read |
| `docs/redesign/baseline/UI-009-notifications-after-read-all-desktop.png` | Desktop | All read |
| `docs/redesign/baseline/GLOBAL-loading-navigation-desktop.png` | Desktop | Navigation pending overlay |
| `docs/redesign/baseline/GLOBAL-loading-navigation-mobile.png` | Mobile | Navigation pending overlay |
| `docs/redesign/baseline/GLOBAL-loading-form-desktop.png` | Desktop | Form submission overlay |
| `docs/redesign/baseline/GLOBAL-loading-form-mobile.png` | Mobile | Form submission overlay |
| `docs/redesign/baseline/GLOBAL-swal-destructive-desktop.png` | Desktop | Destructive confirmation |
| `docs/redesign/baseline/GLOBAL-swal-destructive-mobile.png` | Mobile | Destructive confirmation |
| `docs/redesign/baseline/GLOBAL-swal-flash-desktop.png` | Desktop | Success/error flash |
| `docs/redesign/baseline/GLOBAL-swal-flash-mobile.png` | Mobile | Success/error flash |
| `docs/redesign/baseline/GLOBAL-pageshow-reset-desktop.png` | Desktop | Back/forward reset, no stuck overlay |
| `docs/redesign/baseline/GLOBAL-livewire-reinitialized-desktop.png` | Desktop | Internal navigation after re-init |

Navigation/interaction image count: **27**.

## Priority 2 — modal validation/error evidence

These are desktop-only. Generate validation errors without allowing a successful domain transition. Native `required`/`maxlength` may need temporary client-only manipulation through browser developer tools; do not edit repository files, do not bypass server authorization, and record the exact technique. Reset/recreate the disposable database afterward.

| Exact filename | Modal/result |
|---|---|
| `docs/redesign/baseline/UI-008-modal-priority-validation-desktop.png` | Error redirect auto-opens priority modal |
| `docs/redesign/baseline/UI-008-modal-reject-validation-desktop.png` | Error redirect auto-opens reject modal |
| `docs/redesign/baseline/UI-008-modal-request-information-validation-desktop.png` | Error redirect auto-opens request-information modal |
| `docs/redesign/baseline/UI-008-modal-complete-validation-desktop.png` | Error redirect auto-opens completion modal |
| `docs/redesign/baseline/UI-008-modal-confirmation-validation-desktop.png` | Not-satisfied error auto-opens confirmation modal |
| `docs/redesign/baseline/UI-008-modal-reopen-validation-desktop.png` | Invalid over-limit reason auto-opens reopen modal |
| `docs/redesign/baseline/UI-008-modal-approval-validation-gap-desktop.png` | Reject error leaves approval modal closed; reopen manually only for second evidence frame if needed |
| `docs/redesign/baseline/UI-008-modal-request-approval-validation-desktop.png` | Over-limit reason auto-opens request-approval modal |
| `docs/redesign/baseline/UI-008-modal-third-party-validation-desktop.png` | Missing third-party name auto-opens modal |
| `docs/redesign/baseline/UI-008-modal-triage-validation-desktop.png` | Missing outcome auto-opens triage modal |

Validation corpus: **10 images**.

The four non-reachable modal IDs remain classified from source/HTTP evidence. Do not add a temporary production opener to capture them.

## Runtime checklist to complete alongside screenshots

Record PASS/FAIL/NOT APPLICABLE in the eventual capture PR/appendix.

| Area | Required sequence |
|---|---|
| Sidebar | expanded → collapse → reload → persisted → expand; verify `sihati.sidebar.collapsed` behavior without exposing unrelated storage |
| Mobile nav | open, Tab through visible links, check overflow, close, restore focus |
| Notification | open/close, read one, read all, ownership preserved |
| Global loading | internal nav, form submit, double-click, back/forward `pageshow` reset |
| Livewire | navigate internal links repeatedly; no duplicate SweetAlert/modal/form handler |
| SweetAlert | cancel destructive action, confirm only on disposable state, flash success/error readability |
| Pagination | page 2 retains `q`, `per_page`, and active filters |
| Each reachable modal | initial focus, Tab, Shift+Tab, Escape, close button, backdrop, focus return, mobile scroll |
| Validation modal | correct error, old value, modal auto-open; approval gap remains as baseline |
| A11y | visible focus, headings, label association, `aria-expanded`, `aria-current`, `aria-labelledby`, error association, text+color meaning |
| Responsive | no undocumented horizontal page overflow; table/card switch; long subject/filename; modal scroll; mobile nav clipping |

## Planned image count

| Capture group | Images |
|---|---:|
| Primary screen corpus | 82 |
| ACT-09 alternate | 8 |
| Queue/filter/search/pagination | 18 |
| Create validation/file | 4 |
| Additional workflow detail | 16 |
| SVC-02/SVC-03/SVC-07/status-priority | 14 |
| Critical multi-role/denial | 28 |
| Reachable modals | 20 |
| Navigation/global interaction | 27 |
| Modal validation/error | 10 |
| **Total planned** | **227** |
| **Captured in W0.3 run** | **0** |

Cross-coverage is intentional: a single image may satisfy screen, role, status, priority, attachment, and responsive evidence simultaneously. Do not create duplicate filenames; if a state is visually identical, retain one image and document the cross-reference.

## Completion and cleanup

1. Confirm every committed PNG has an index row and no credential/PII.
2. Reset/recreate the disposable database after notification/validation/destructive interaction checks.
3. Stop the local server.
4. Remove only the explicitly created disposable database/session artifacts after verifying their absolute paths.
5. Run the full PHP test and frontend build again.
6. Run `git status`, `git diff --stat`, and `git diff`.
7. Expected new paths are this documentation and `docs/redesign/baseline/*.png` only; no `resources/`, `routes/`, controller, policy, model, migration, or service change.
