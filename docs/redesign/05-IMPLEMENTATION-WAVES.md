# Implementation Waves

Dokumen ini adalah urutan implementasi yang direkomendasikan setelah Fase 2 disetujui. **Tidak ada implementation yang dilakukan pada fase dokumentasi ini.**

Urutan mengikuti dependency dan risiko:

> LOW — shared visual foundation → MEDIUM — common pages → HIGH — role/data/workflow UI → CRITICAL — ticket detail, action modal, dan dynamic workflow.

## Prinsip sequencing

1. Satu wave harus stabil sebelum wave berikutnya dimulai.
2. Contract IDs dari `02-UI-CONTRACT-MATRIX.md` menjadi checklist perubahan.
3. Perubahan visual primitive harus selesai sebelum screen kompleks mengadopsinya.
4. Authorization/data projection tidak dipindahkan ke JavaScript.
5. Compatibility alias dan dormant view tidak dijadikan target redesign.
6. Setiap wave harus dapat dirilis/di-review secara independen tanpa backend change.
7. Existing tests tidak boleh dilemahkan. `npm run build` dan relevant PHP feature tests wajib lulus.

## Ringkasan wave

| Wave | Risk | Fokus | Gate sebelum lanjut |
|---|---|---|---|
| W0 | Baseline | Capture contract, test fixtures, screenshots, role accounts | Baseline reproducible; no code change required by this document |
| W1 | LOW | Visual tokens dan shared primitives | Build, accessibility primitives, no route/form changes |
| W2 | MEDIUM | App/guest shell dan common pages | All role navigation and global JS behavior equivalent |
| W3 | MEDIUM | Straightforward admin collections | CRUD/filter/modal contracts stable |
| W4 | HIGH | Role/data-heavy dashboard, report, user, service config | Multi-role/data scope/export/versioned form regression green |
| W5 | HIGH | Ticket lists, creation, approvals | Dynamic form, pagination, assignment visibility, approval tests green |
| W6 | **CRITICAL** | Ticket detail and all action workflows | Full workflow/role/service/attachment regression green |

## W0 — Baseline capture and regression gate

### Scope

- Ratify the six Fase 2 documents.
- Capture representative screenshots for desktop/mobile and each primary variant.
- Prepare test actors/data for six pure roles and critical multi-role combinations.
- Record current route list and HTML contracts needed by automated/manual checks.
- Do not redesign or modify application behavior.

### Files

- Documentation in `docs/redesign/` only for the current phase.
- Existing tests are evidence: `tests/Feature/**` and `tests/performance/k6.js`; do not edit during Fase 2.

### Dependencies/prerequisites

- Stable test database/fixtures.
- Current active approver assignment.
- Tickets representing all 11 statuses, four priorities, SVC-02/SVC-03/SVC-07, attachment visibility, SLA states, and team scopes.
- Accounts: six pure roles plus SuperAdmin+Tier1, Pemohon+Approver, KetuaTim+Tier1, KetuaTim+SuperAdmin.

### Risk

No implementation risk; high risk if skipped because later visual differences cannot be separated from behavior regressions.

### Mandatory regression tests

- `php artisan route:list --except-vendor` matches baseline 112 routes.
- Full `php artisan test` baseline result recorded.
- `npm run build` baseline result recorded.
- Manual baseline: 23 active screens, 15 primary variants, 14 ticket modal IDs, mobile/desktop, keyboard navigation.

### Definition of done

- Every screen/variant has an owner and test fixture.
- Known baseline asymmetries are accepted as “preserve” or separately scoped.
- Dormant views and no-trigger endpoints are explicitly excluded.
- No implementation file changed.

## W1 — LOW RISK: shared visual foundation

### Scope

- Establish typography, color, spacing, radius, shadow, focus, motion, and breakpoint tokens.
- Redesign low-risk primitives without altering semantic/payload contracts.
- Define visual states: default, hover, focus-visible, active, disabled, error, success, warning, empty.

### Candidate files

- `resources/css/app.css`
- `resources/css/theme-modern.css`
- visual assets referenced by CSS/layout
- `resources/views/components/page-header.blade.php`
- `resources/views/components/empty-state.blade.php`
- `resources/views/components/field-error.blade.php`
- `resources/views/components/form-field.blade.php`
- `resources/views/components/status-badge.blade.php`
- `resources/views/components/priority-badge.blade.php`

Excluded: unused `role-badge`, `tickets.fact-list`, `tickets.journey` unless separately approved.

### Dependencies/prerequisites

- W0 approved.
- Canonical role/status/priority labels frozen.
- Contrast and focus target agreed.

### Risk

LOW, except enum-aware badges/form error ARIA are MEDIUM contract touchpoints.

### Mandatory regression tests

- `npm run build`.
- Badge snapshot/manual coverage for every 11 status and 4 priorities, including unknown fallback.
- Form-field: old value, password non-repopulation, required indicator, help/error `aria-describedby`.
- Keyboard focus visibility and reduced-motion review.
- No named route/input/data selector diff.

### Definition of done

- Tokens work in light presentation across all existing content densities.
- Primitive APIs remain compatible.
- WCAG-oriented contrast/focus checks pass.
- No layout, navigation, modal, form action, or business visibility change.

## W2 — MEDIUM RISK: shared shell and common pages

### Scope

- Redesign authenticated and guest shells.
- Preserve desktop/mobile navigation branches, notification dropdown, logout/password flows, flash/loading, branding, and Livewire navigation re-init.
- Apply foundation to low-complexity common pages.

### Candidate files

- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/views/components/flash.blade.php`
- `resources/views/components/global-loading-overlay.blade.php`
- `resources/views/vendor/pagination/tailwind.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/change-password.blade.php`
- `resources/views/notifications/index.blade.php`
- `resources/views/errors/403.blade.php`
- relevant shell/global portions of `resources/js/app.js`

### Dependencies/prerequisites

- W1 primitives stable.
- Role navigation matrix reviewed line by line.
- Global selector migration map complete before any selector change.

### Risk

MEDIUM. `layouts.app` is globally shared and contains role/current-approver logic, so a small markup change has broad impact.

### Mandatory regression tests

- `AuthenticationTest`, `PasswordResetTest`, `TicketNotificationTest`, `RoleAuthorizationTest`, `SecurityHeadersTest`.
- Login failure/rate-limit/old username; successful intended redirect; logout session/token behavior.
- All six roles: exact desktop/mobile nav visibility.
- Multi-role nav: SuperAdmin+Tier1, KetuaTim+Tier1, KetuaTim+SuperAdmin, Pemohon+Approver.
- Notification ownership/read redirect/read-all; dropdown count.
- Sidebar persist/collapse; mobile menu; Livewire navigation re-init; global overlay reset on `pageshow`.
- Pagination retains query and exposes correct current/disabled semantics.

### Definition of done

- Shell works at mobile/tablet/desktop without horizontal overflow.
- Keyboard can reach, open, close, and leave every disclosure/menu.
- Route names, POST logout/read actions, policy visibility, branding asset, flash JSON, and global selectors remain equivalent.
- Common pages preserve all error/empty/loading states.

## W3 — MEDIUM RISK: straightforward administration collections

### Scope

- Redesign admin screens whose workflow is conventional CRUD/read-only listing.
- Standardize responsive table/card, filter bar, modal shell, action feedback, and empty states.
- Do not activate room, attachment-policy, or operational-policy UI.

### Candidate files

- `resources/views/admin/locations/index.blade.php`
- `resources/views/admin/locations/_building-form.blade.php`
- `resources/views/admin/locations/_floor-form.blade.php`
- `resources/views/admin/teams/index.blade.php`
- `resources/views/admin/skills/index.blade.php`
- `resources/views/admin/audit-logs/index.blade.php`
- `resources/views/admin/audit-logs/_details.blade.php`
- `resources/views/admin/branding/index.blade.php`
- `resources/views/admin/announcements/index.blade.php`
- generic modal, filter, branding, team, and submit behavior in `resources/js/app.js`

### Dependencies/prerequisites

- W2 layout/modal strategy stable.
- Old/error context markers documented and covered: `_location_form`, `_team_create`, `_team_edit`, `_skill_form`, `_skill_edit`, `_announcement_id`.
- Filter names/options fixed.

### Risk

MEDIUM; audit data sensitivity, nested location constraints, and modal error routing are the main hazards.

### Mandatory regression tests

- `OrganizationManagementTest`, `AuditLogPageTest`, `BrandingManagementTest`, relevant `AuditAndRetentionTest`.
- Super Admin allowed; every other pure role denied by route/policy, except announcements also allowed Tier 1.
- Search/per-page/page preservation for locations/skills/audit.
- Create/edit validation reopens correct modal with correct old values.
- Activate/deactivate/delete constraints and exact methods.
- Branding multipart upload/remove/preview/fallback/version history.
- Audit before/after/context remains fully readable only within authorized screen; no data truncation caused by responsive design.

### Definition of done

- CRUD behaviors, redirect/back, flash, and confirmation remain identical.
- Responsive table/card shows equivalent essential data/actions.
- Modal keyboard/focus/error behavior passes.
- No dormant/no-trigger endpoint is newly exposed.

## W4 — HIGH RISK: role/data-heavy screens and configuration

### Scope

- Redesign dashboard variants, report/export UI, user management, and service/form configuration.
- Stabilize high-density information architecture before ticket workflow detail.
- Preserve server projections, nested payloads, versioned fields, and export behavior.

### Candidate files

- `resources/views/dashboard.blade.php`
- `resources/views/dashboard/helpdesk.blade.php`
- `resources/views/reports/monthly.blade.php`
- `resources/views/reports/monthly-pdf.blade.php` only if export visual is explicitly in scope; content contract stays locked
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/users/create.blade.php`
- `resources/views/admin/users/edit.blade.php`
- `resources/views/admin/users/reset-password.blade.php`
- `resources/views/admin/users/_form.blade.php`
- `resources/views/admin/users/_detail.blade.php`
- `resources/views/admin/services/index.blade.php`
- active `resources/views/admin/services/_*.blade.php`
- active `resources/views/admin/forms/_field-form.blade.php`
- active `resources/views/admin/catalog/_form-preview.blade.php`
- dashboard/report/user/service-related portions of `resources/js/app.js`

### Dependencies/prerequisites

- W1–W3 primitives/shell/collection/modal behavior stable.
- Six dashboard fixtures and multi-role fixtures available.
- Service fixtures include SLA on/off, every dynamic field type/visibility, skills, historical field versions.
- Report export test data and filename/header assertions available.

### Risk

HIGH. These screens combine role composition, safe aggregate scope, sensitive user administration, nested forms, modal error routing, field versioning, and binary downloads.

### Mandatory regression tests

- `DashboardTest`, `MonthlyReportTest`, `CatalogManagementTest`, `OrganizationManagementTest`, `OperationalPolicyManagementTest`, `RoleAuthorizationTest`.
- Six dashboard variants plus all multi-role precedence cases in role matrix.
- Super Admin dashboard must not become operational merely through navigation redesign.
- Team Chair dashboard must never receive/render excluded fields.
- Report initial/no-data/data/error states; primary/alias params; Excel/PDF content type, filename, fetch failure, and export audit.
- User create/update/reset/self-restrictions; role/team/position/technician-skill dependencies; modal and standalone forms identical payload.
- Service create nested fields; SLA toggle hidden `0`/checkbox `1`; skills; add/version/delete field; `service`/`service_tab` and list query restoration; field-status endpoint remains unexposed.
- Ensure dormant catalog/forms screens remain unreachable.

### Definition of done

- All role/data variants render only server-authorized data.
- Export remains byte-valid and audited.
- User/service mutations preserve every input name, hidden marker, old/error key, redirect query, and version/history effect.
- No business calculation is moved into Blade/JavaScript.

## W5 — HIGH RISK: ticket entry, monitoring, and approval workflow

### Scope

- Redesign ticket list variants, queue/all views, service catalog selection, create-ticket form, and approval list.
- Establish ticket-specific responsive patterns and dynamic form visuals before detail/action redesign.

### Candidate files

- `resources/views/tickets/index.blade.php`
- `resources/views/tickets/requester-index.blade.php`
- `resources/views/tickets/queue.blade.php`
- `resources/views/tickets/all.blade.php`
- `resources/views/tickets/create.blade.php`
- `resources/views/tickets/_catalog.blade.php`
- `resources/views/tickets/_form.blade.php`
- `resources/views/components/tickets/dynamic-field.blade.php`
- `resources/views/approvals/index.blade.php`
- ticket-create/filter/approval portions of `resources/js/app.js`

### Dependencies/prerequisites

- W4 role and modal behaviors stable.
- Fixtures for requester-only, operational union scope, Team Chair projection, all queue tabs, current/non-current Approver.
- All dynamic field types and attachment policy combinations available.

### Risk

HIGH due to role-specific view selection, pagination/query preservation, dynamic names/nesting, attachment policy, and approval current-assignment rule.

### Mandatory regression tests

- `TicketManagementTest`, `TicketTriageTest`, `TicketApprovalTest`, `TeamChairAccessTest`, `TicketCommunicationTest` portions related to create/list.
- UI-004 three variants, including pure Super Admin denial and Team Chair fixed safe paginator/no filters.
- Queue tab ability/scope/count/order for T1/T2; invalid tab behavior.
- All-ticket filter and pagination query preservation.
- Ticket create as Pemohon and Tier 1-for-other; Team Chair/T2/Approver/Super Admin denial.
- Dynamic field names for text/textarea/select/multiselect/boolean/date/datetime; old/error restore.
- SVC-01/SVC-05 location; attachment nested input/policy; multipart; invalid file/count/size.
- Approval list current vs stale Approver; decision forms and known baseline error behavior.

### Definition of done

- List/card data and actions are equivalent across breakpoints.
- No record leaks across requester/agent/team scopes.
- Create form posts identical payload and creates identical snapshots/attachments.
- Approval screen preserves current-active-approver and pending-only restrictions.
- `requester-show` remains dormant.

## W6 — CRITICAL: ticket detail, action dialogs, and dynamic workflow

### Scope

- Redesign full and Team Chair ticket detail.
- Redesign all communication areas, attachments, internal fields, timeline, SLA/status, approval display/actions, and 14 ticket action modals.
- Preserve every policy flag, state transition, hidden marker, selector, special control, redirect, flash, audit, notification, and concurrency behavior.

### Candidate files

- `resources/views/tickets/show.blade.php`
- `resources/views/tickets/team-chair-show.blade.php`
- `resources/views/tickets/_action-modals.blade.php`
- `resources/views/components/approval-panel.blade.php`
- `resources/views/components/tickets/detail-header.blade.php`
- `resources/views/components/tickets/internal-field.blade.php`
- `resources/views/components/tickets/message.blade.php`
- `resources/views/components/tickets/timeline.blade.php`
- `resources/views/components/ui/modal-panel.blade.php`
- ticket detail/action/modal/communication/assignment/resolution/SVC portions of `resources/js/app.js`

### Dependencies/prerequisites

- Every earlier wave complete.
- Contract-level automated fixtures for all 11 statuses and six role variants.
- 14 modal IDs and all `data-ticket-*` selector consumers mapped.
- SVC-02/03/07, public/internal attachment, waiting/approval/SLA fixtures available.
- Known baseline asymmetries explicitly classified as preserve or separate defect scope.

### Risk

**CRITICAL.** This is the single page where presentation is most tightly coupled to policy flags, domain state, attachment authorization, service-specific controls, modal error routing, and JavaScript handlers.

### Mandatory regression tests

- `TicketManagementTest`, `TicketCommunicationTest`, `TicketTriageTest`, `TicketApprovalTest`, `TicketResolutionTest`, `TicketInternalFieldTest`, `DatabaseChangeControlTest`, `TicketNotificationTest`, `TeamChairAccessTest`, `OperationalReadinessTest`, `AuditAndRetentionTest`.
- Full action matrix for six pure roles and critical multi-role combinations.
- Every action allowed and denied at every relevant status/assignee condition.
- Claim/handle remain without newly introduced trigger unless separately approved.
- Modal open/close/Escape/backdrop/focus trap/focus restore; auto-open on `_action_modal`; old/error targeting; double-submit lock.
- Public/internal/requester comments and attachments; authorized/unauthorized download, including Team Chair denial.
- Triage/assign/return reason/assignee payload and service suggestion display.
- Waiting Pemohon/third party and SLA pause/resume.
- Approval request/current-Approver decision/previous-state restoration.
- Complete/confirm/not-satisfied/reopen, aliases, reopen count/window, SLA cycle.
- SVC-02 required export result; SVC-03 three evidence, execute actor/time, verify; SVC-07 field versions.
- Team Chair receives only safe projection and no form/action/download in HTML.
- 403 versus 404 and concurrency error `ticket` remain correct.

### Definition of done

- All 27 ticket mutation contracts and relevant read/download contracts pass.
- Full detail and safe detail are visually coherent but retain different data types/scopes.
- All 14 dialog contracts remain keyboard-accessible and validation-safe.
- No authorization, transition, SLA, approval, attachment, notification, audit, or service-specific side effect changes.
- Full PHP test suite and production asset build pass.
- Manual visual QA passes mobile, tablet, desktop, zoom, long content, empty/error/loading, and keyboard-only use.

## Ten highest-risk redesign areas

1. **Ticket detail action visibility** — dozens of independent policy booleans, role combinations, assignee/status conditions.
2. **Ketua Tim safe projection** — data must never be fetched then merely hidden; no attachment access.
3. **Ticket state transitions and SLA** — waiting, approval, completion, confirmation, reopen, pause/resume, new cycle.
4. **Action modal/error routing** — 14 IDs, `_action_modal`, old/error state, focus, submit locking, JS selectors.
5. **Attachment authorization and nested upload contract** — public/internal/requester-accessible policy, file rules, download route.
6. **Public versus internal communication** — similar UI, different visibility, readers, writers, attachment policy, notifications.
7. **SVC-03 database change control** — three evidence, execution sequencing, actor/time/history, verification.
8. **SVC-02 completion** — conditional required `data_export_result`, multipart form, requester access.
9. **Approval/current-approver flow** — role is insufficient; pending assignment, previous-state restoration, known modal error asymmetry.
10. **Dynamic/versioned fields and service editor** — nested names, boolean pairs, field types/options, historical versions, `_service_*` redirect context.

## Cross-wave release checklist

- Only presentation files approved for the wave changed; backend/domain/route/schema remain untouched.
- `git diff` contains no accidental route/controller/policy/model/migration/config change.
- `npm run build` succeeds.
- Relevant feature tests and full `php artisan test` succeed before release.
- No console error, focus loss, double submit, or broken download.
- 23 active screens remain active; 6 dormant views remain dormant.
- 15 primary role variants remain behaviorally equivalent.
- 31 read/response and 81 mutation route contracts remain intact.
