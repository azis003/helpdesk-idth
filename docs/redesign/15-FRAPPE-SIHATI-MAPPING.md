# Frappe → SIHATI Design Mapping

Dokumen ini menerjemahkan hasil audit Frappe Helpdesk pada commit `34e0e8f41a35342b3066da8ae5681ecfa7730d38` ke konteks SIHATI. Ia memetakan reference, bukan mengubah behavior, bukan final design system, dan bukan implementation plan baru.

## Mapping Principles

1. **Contract freeze lebih tinggi daripada reference.** Route, method, named route, controller/service workflow, form fields, query string, validation, old/error behavior, redirect, flash, modal ID, `data-*`, policy, safe projection, dan authorization SIHATI tetap authoritative.
2. **Hierarchy dapat dipelajari; arsitektur tidak dipindahkan.** Vue, Vue Router, Pinia, Frappe resources, Socket.IO, PWA, client cache, Frappe UI, and Reka UI tidak menjadi target.
3. **Role tidak dipetakan berdasarkan nama.** Frappe agent/customer/manager/admin bukan equivalence untuk Super Admin, Pemohon, Agen Tier 1, Agen Tier 2, Approver, atau Ketua Tim Kerja.
4. **Same appearance tidak berarti same contract.** Public message, requester reply, internal note, system event, approval decision, and attachment tetap mempunyai policy/form/action terpisah.
5. **Responsive presentation tidak boleh menghilangkan capability.** Authorized action yang disembunyikan di viewport tertentu wajib mempunyai equivalent affordance; unauthorized action tidak boleh muncul.
6. **Server tetap sumber kebenaran.** Filter, sort, pagination, visibility, current approver, and multi-role precedence tidak dipindahkan ke client state.
7. **Tidak ada feature expansion.** Knowledge base, telephony, saved replies, command palette, bulk actions, realtime presence, saved views, user-configurable columns, customizable dashboard, dark theme, dan Frappe settings architecture tidak ikut redesign.
8. **Manual visual gate masih berlaku.** Mapping dapat direview sekarang; implementation tetap `HOLD` sampai prerequisite `13-BASELINE-READINESS-GATE.md` dipenuhi.

## ADOPT / ADAPT / IGNORE Definition

| Classification | Arti pada fase ini | Batas implementasi nanti |
|---|---|---|
| **ADOPT** | Konsep visual/interaksi hampir langsung cocok dengan SIHATI | Tetap ditulis ulang native Blade/Tailwind/JS; tidak ada source/token/asset Frappe yang disalin. |
| **ADAPT** | Konsep bernilai tetapi harus diterjemahkan karena workflow, role, server rendering, density, responsive, atau contract | Existing controller/policy/form/query behavior tetap; hanya presentation layer berubah. |
| **IGNORE** | Tidak relevan, tidak mempunyai feature SIHATI, atau akan memperkenalkan arsitektur/behavior baru | Tidak masuk design system atau implementation wave tanpa scope produk terpisah. |

Jumlah catalog authoritative dari audit: **40 FRP patterns — 8 ADOPT, 20 ADAPT, 12 IGNORE**.

## Global Pattern Mapping

“SIHATI candidate” di bawah hanya nama arsitektur dokumentasi untuk fase desain berikut; tidak ada component yang dibuat pada fase 3.1.

| FRP | Reference pattern | Classification | SIHATI candidate | Translation boundary |
|---|---|---:|---|---|
| FRP-001 | Session-selected dual portal SPA root | IGNORE | — | Existing Laravel route/middleware/layout branch tetap. |
| FRP-002 | Collapsible desktop sidebar | ADAPT | App navigation region | Active/visible items tetap dari server role/policy branch; exact collapse belum diputuskan. |
| FRP-003 | Mobile drawer + compact header | ADAPT | Mobile navigation overlay | Preserve current link targets, focus/close, notification, and multi-role visibility. |
| FRP-004 | Audience-specific navigation groups | ADAPT | Policy-derived navigation groups | Gunakan existing capabilities, bukan Frappe agent/customer mapping. |
| FRP-005 | Compact page header / breadcrumb-action bar | ADAPT | Page header region | Preserve route/query/back context and action visibility. |
| FRP-006 | Neutral border-led surface hierarchy | ADOPT | Surface hierarchy principle | Tidak menyalin Frappe tokens/palette. |
| FRP-007 | Compact muted typography | ADAPT | Type hierarchy principle | Exact family/scale/weight menunggu design-system phase. |
| FRP-008 | Button hierarchy | ADOPT | Primary/secondary/ghost/icon/destructive action styles | Form submit target/method/loading/confirmation tetap. |
| FRP-009 | Form control anatomy | ADAPT | Shared form-field anatomy | `name`, hidden inputs, old/error keys, required/readonly rules tetap. |
| FRP-010 | Status dot + text / priority icon + text | ADOPT | Status/priority presentation | Canonical SIHATI labels/classes/semantics tetap authoritative. |
| FRP-011 | Notification panel/mobile page | ADAPT | Notification list/panel | UI-009 ownership, read actions, pagination, and routes tetap. |
| FRP-012 | Command palette + shortcut system | IGNORE | — | Tidak menambah global command/search behavior. |
| FRP-013 | View breadcrumb/saved-view selector | ADAPT | List context header | Breadcrumb anatomy only; saved views diabaikan. |
| FRP-014 | Quick + advanced filters | ADAPT | Server filter bar | Submit GET dengan existing query names; no JSON filter/client resource. |
| FRP-015 | Multi-sort/configurable columns | IGNORE | — | Tidak menambah personalization/persisted list behavior. |
| FRP-016 | Click-cell-to-filter | IGNORE | — | Existing row/cell links dan query behavior tidak berubah. |
| FRP-017 | Dense list scaffold | ADAPT | Responsive record list | Existing desktop tables, mobile cards, SSR, paginator dipertahankan. |
| FRP-018 | Selection banner/bulk actions | IGNORE | — | Tidak ada frozen SIHATI bulk workflow. |
| FRP-019 | Load more/persisted page length | IGNORE | — | Existing numbered pagination and query preservation tetap. |
| FRP-020 | Desktop split ticket workspace | ADAPT | Ticket work surface | Enam detail variants dan projections tetap terpisah. |
| FRP-021 | Dedicated mobile ticket detail | ADAPT | Mobile ticket sections | Different hierarchy boleh; authorized action/data tetap exact. |
| FRP-022 | Status/action header cluster | ADAPT | Ticket action header | Render controller-computed actions only; no generic status mutation. |
| FRP-023 | Multi-type activity timeline | ADAPT | Ticket timeline | Hanya existing public/internal/system/approval/workflow events. |
| FRP-024 | Public reply vs internal comment composers | ADOPT | Two distinct composer families | Visual dapat related; route, payload, attachment, visibility, policy tidak digabung. |
| FRP-025 | Collapsible composer + keyboard submit | ADAPT | Expandable composer behavior | Preserve auto-open, retained/reset state, old/error, loading semantics. |
| FRP-026 | Attachment chip/preview | ADAPT | Authorized attachment link | Existing download route and server authorization always used. |
| FRP-027 | Collapsible metadata sections | ADOPT | Detail section/disclosure | Server controls section existence/data; local persistence optional and not decided. |
| FRP-028 | Inline editable ticket metadata | IGNORE | — | Workflow remains existing forms/modals/services. |
| FRP-029 | Dynamic conditional field grid | ADAPT | Dynamic field group | Existing field definitions/names/options/validation/old/error remain. |
| FRP-030 | Knowledge suggestion | IGNORE | — | Knowledge base is out of scope. |
| FRP-031 | Standard dialog anatomy/focus/loading | ADOPT | Generic modal presentation | Modal IDs, openers, `data-*`, action/method, auto-open/reset/errors remain. |
| FRP-032 | Responsive KPI/chart dashboard | ADAPT | Dashboard section/KPI composition | Six variants and backend-provided sections/filters stay authoritative. |
| FRP-033 | Customizable dashboard | IGNORE | — | No drag/resize/add/remove/persist charts. |
| FRP-034 | Settings split-pane modal | IGNORE | — | Existing standalone admin URLs/screens remain. |
| FRP-035 | Search/filter/list CRUD management | ADAPT | Admin collection pattern | Preserve policy, form contracts, modal targeting, pagination, redirects. |
| FRP-036 | Contextual empty/loading/not-found states | ADOPT | Contextual state panel | HTTP meaning, authorization, result state, and recovery route stay exact. |
| FRP-037 | Realtime presence/typing/live updates | IGNORE | — | No Socket.IO/backend event expansion. |
| FRP-038 | Dark theme/persistence | IGNORE | — | No cross-app theme behavior in current scope. |
| FRP-039 | Keyboard/listbox/focus/reduced-motion patterns | ADOPT | Accessibility behavior contract | Reimplement and regression-test against Blade/JS; do not imitate appearance alone. |
| FRP-040 | Toast/action loading feedback | ADAPT | Existing flash/submit feedback layer | Align to current SweetAlert/flash/global locks; no Frappe resource lifecycle. |

## Screen Mapping

Hanya 23 active screens dari `01-UI-INVENTORY.md` yang dipetakan. Dormant/legacy views tidak dimasukkan dan tidak diaktifkan.

| UI ID | Existing purpose | Relevant Frappe reference | FRP IDs | Decision | Proposed information hierarchy | Role variant caveat | Frozen contract caveat | Risk | Reference confidence |
|---|---|---|---|---|---|---|---|---:|---:|
| UI-001 | Login | Frappe external login round-trip in `router/index.ts`/`stores/auth.ts`; no comparable in-SPA page | 006, 007, 008, 009, 036, 039, 040 | ADAPT | Brand → page title/context → credentials → inline validation/flash → primary submit | Guest only; authenticated redirect remains middleware-owned | Preserve login action, inputs, CSRF, session/redirect/error behavior | LOW | LOW |
| UI-002 | Six-variant dashboard | `pages/dashboard/Dashboard.vue`, fixed cards/charts; Home only as negative customization reference | 005, 006, 007, 008, 010, 032, 036, 039, 040 | ADAPT | Page/variant identity → period/actions → urgent work/summary → role-specific sections → announcements/secondary guidance | D-01–D-06 remain distinct; current Approver and Team Chair computed server-side; Super Admin not automatically operational | Preserve date aliases/normalization, section data, links, role branches, server ordering | HIGH | HIGH |
| UI-003 | Change own password | Frappe settings form anatomy; no direct routed equivalent | 005, 006, 008, 009, 031, 036, 039, 040 | ADAPT | Context/title → password fields/helper/errors → confirmation action → flash result | All authenticated active actors; writes own password only | Preserve PUT/spoofing, names, no password repopulation, validation/error/redirect | MEDIUM | MEDIUM |
| UI-004 | Role-variant ticket list | `Tickets.vue`, `ListViewBuilder.vue`, view controls | 005, 010, 013, 014, 017, 036, 039, 040 | ADAPT | Variant title/context → allowed filters/tabs → compact result summary/list → requester quick actions if provided → paginator/empty | L-01 requester, L-02 operational, L-03 Team Chair projection; Team Chair branch wins | Preserve `q`, `tab`, `class`, `status`, dates, `per_page`, query preservation, policy scope, 15-page Team Chair behavior | HIGH | HIGH |
| UI-005 | Queue/mine/assigned/completed monitoring | Frappe ticket list toolbar/row/status patterns; no identical queue policy | 005, 010, 014, 017, 036, 039, 040 | ADAPT | Monitoring identity → authorized tab strip/counts → ticket list → contextual empty → paginator | T1 all allowed tabs; T2 mine/completed only; Team Chair denied | Preserve `tab` normalization, policy per tab, 20/page, sorting, no claim/handle trigger invention | HIGH | HIGH |
| UI-006 | Tier 1 all-ticket list | Same Frappe list/view controls | 005, 010, 014, 017, 036, 039, 040 | ADAPT | Page title → search/page-size controls → dense full-scope list → paginator/empty | T1 only; no additional role flattening | Preserve `q`, `per_page`, server scope/sort/query pagination and 403 behavior | HIGH | HIGH |
| UI-007 | Service selection and dynamic ticket create | `TicketNew.vue`, `UniInput.vue`, `TicketField.vue`, dynamic field/form sources | 005, 006, 008, 009, 025, 026, 029, 036, 039, 040 | ADAPT | Step identity → service catalog/about → requester context when allowed → grouped dynamic fields → description/attachments → submit/errors | Pemohon self; T1 can create for other; others denied | Preserve GET `service_type_id`, POST multipart, field/input names, hidden values, old/nested errors, attachment policy, validation | HIGH | HIGH |
| UI-008 | Six-variant ticket detail and all workflows | Agent/customer/mobile detail, timeline, composers, side panel, dialogs | 005, 006, 008, 010, 020–027, 031, 036, 039, 040 | ADAPT | Identity/status/SLA → role-authorized action cluster → primary timeline/conversation → public/internal composers → metadata/workflow sections → attachments/history → action dialogs | T-01–T-06 never share one unrestricted data model; current Approver/current request required; Team Chair separate safe view | Preserve every controller boolean, 112 contracts, 14 modal IDs, forms/routes/fields/data selectors, attachments, approval/SLA/SVC/waiting/confirmation/reopen behavior and frozen defects | **CRITICAL** | HIGH |
| UI-009 | Current-user notifications | Desktop notification panel/mobile page patterns | 005, 011, 036, 039, 040 | ADAPT | Page context/unread summary → mark-all action if allowed → chronological notifications → paginator/empty | Same shell for all active actors; records remain current-user only | Preserve read-one/read-all POST forms, IDs/routes, ownership, latest-first, 20/page | MEDIUM | HIGH |
| UI-010 | Current active approver queue | No direct Frappe approval product; use list/card/action hierarchy only | 005, 008, 010, 017, 031, 036, 039, 040 | ADAPT | Pending count/context → oldest-first approval cards → minimal safe ticket context → approve/reject controls → empty | Role alone insufficient; current active assignment, pending request, and no Team Chair override required | Preserve decision routes/methods, `decision_note`, concurrency/policy behavior, no new modal or auto-open behavior | HIGH | MEDIUM |
| UI-011 | Monthly report and exports | Frappe dashboard filters/cards/chart hierarchy; ticket export dialog only as feedback reference | 005, 006, 008, 009, 032, 036, 039, 040 | ADAPT | Report title → period controls → summary → detailed tables → export actions/state → initial/zero/error state | Super Admin, T1, current Approver; Team Chair denied | Preserve month/date aliases, report-generation conditions, Excel/PDF named links, fetch/blob/filename/error/loading contract | HIGH | MEDIUM |
| UI-012 | User management collection/modal flows | Frappe `Settings/Agents.vue` and management list pattern | 005, 008, 009, 017, 031, 035, 036, 039, 040 | ADAPT | Page/create → search/filter → responsive user list with role/status metadata → row actions/details/forms → paginator/empty | Super Admin policy only; self-target restrictions remain; multi-role assignments not reduced to Frappe Agent/Manager | Preserve routes, `q`, `per_page`, modal/error context, reset/delete/status forms and dependency JS | HIGH | HIGH |
| UI-013 | Standalone create user | Frappe settings form/header patterns | 005, 008, 009, 031, 035, 036, 039, 040 | ADAPT | Page context → identity/account fields → roles/skills/team sections → action/errors | Super Admin + create policy | Preserve POST action/names/arrays/old/errors/redirect; no Frappe invitation workflow | MEDIUM | MEDIUM |
| UI-014 | Standalone edit user | Frappe Agent management row/form patterns | 005, 008, 009, 031, 035, 036, 039, 040 | ADAPT | User identity/status → editable identity → role/skill/team/chair relations → guarded status actions → save/errors | Super Admin; self deactivate/delete restrictions and multi-role semantics remain | Preserve PUT/status forms, identifiers, old/errors, redirects and policy checks | HIGH | MEDIUM |
| UI-015 | Standalone reset user password | Frappe confirm/dialog and form-control anatomy | 005, 008, 009, 031, 036, 039, 040 | ADAPT | Target identity/warning → temporary password → explicit confirmation → reset action/error | Super Admin; cannot reset self | Preserve PUT/spoofing, confirmation field/key, password non-repopulation, redirect/flash | MEDIUM | LOW |
| UI-016 | Building/floor management | Frappe CRUD list/dialog pattern; no location domain analog | 005, 008, 009, 017, 031, 035, 036, 039, 040 | ADAPT | Search/create → building hierarchy → nested floors/status → contextual row actions/modals → paginator/empty | Super Admin + resource policies | Preserve aliases, `q`, `per_page`, per-row modal IDs, hidden identifiers, dependency errors/status forms; do not activate room UI | MEDIUM | MEDIUM |
| UI-017 | Work-team management | Frappe Teams/settings management pattern | 005, 008, 009, 017, 031, 035, 036, 039, 040 | ADAPT | Team collection/create → current chair/member summary → edit/status actions → empty/errors | Super Admin; team-chair role semantics are SIHATI-specific and not Frappe Manager | Preserve create/edit modal error markers and verify actual activate/deactivate/delete triggers before redesign | MEDIUM | HIGH |
| UI-018 | Skill management | Frappe Agent/team settings list pattern | 005, 008, 009, 017, 031, 035, 036, 039, 040 | ADAPT | Search/create → skill usage/count context → status/actions → paginator/empty | Super Admin + SkillPolicy | Preserve `q`, `per_page`, modal target/error context and dependency rules | MEDIUM | MEDIUM |
| UI-019 | Audit trail read-only filters/details | Frappe dense list/filter/disclosure patterns; no audit page analog | 005, 006, 014, 017, 027, 036, 039, 040 | ADAPT | Audit identity → exact filter set → chronological rows → expandable authorized detail → paginator/empty | Super Admin; sensitive payload server-scoped | Preserve allowed query values, newest/25-page order, `<details>` behavior, no mutation/modal invention | HIGH | LOW |
| UI-020 | Branding identity editor/history | Frappe form/settings surface patterns; no comparable branding workflow | 005, 006, 008, 009, 026, 036, 039, 040 | ADAPT | Editor identity → text/logo inputs → live preview → remove/upload controls → version history → save/errors | Super Admin + BrandingPolicy | Preserve PUT multipart, file/remove fields, old/errors, version order/max 12, preview fallback | MEDIUM | LOW |
| UI-021 | Service catalog and dynamic-form configuration | Frappe settings sections/forms/dialog anatomy and dynamic controls | 005, 008, 009, 017, 027, 029, 031, 035, 036, 039, 040 | ADAPT | Search/create → service list/status/counts → selected service editor tabs → SLA/skills → ordered/versioned fields → preview → dialogs/errors | Super Admin; resource policy checks remain distinct | Preserve million-byte-era modal IDs/context without requiring same DOM weight; keep routes, nested names/errors, service/service_tab query, ordering/versioning/status contracts; dormant field-status trigger stays dormant | HIGH | MEDIUM |
| UI-022 | Announcement management | Frappe CRUD/settings list and contextual state patterns | 005, 008, 009, 017, 031, 035, 036, 039, 040 | ADAPT | Create form → scheduled/current list → record disclosure/edit → status actions → empty/errors | Super Admin or T1 per middleware/policy; Team Chair+T1 caveat remains | Preserve create/edit/status forms, `_announcement_id` error routing, schedule order and no search/pagination | MEDIUM | MEDIUM |
| UI-023 | Standalone 403 | Frappe `InvalidPage.vue` | 006, 008, 036, 039 | ADOPT | Clear denial title → concise explanation → safe back action | Any actor can encounter it; do not reveal resource existence or hidden data | Preserve standalone 403 status/rendering, branding/auth state, and destination; do not merge 403 with 404 semantics | LOW | HIGH |

Coverage: **UI-001 through UI-023 = 23/23 active screens**. Tidak ada dormant view yang dipetakan atau diaktifkan.

## Dashboard Variant Mapping

| Variant | Existing authority | Frappe reference / FRP | Mapping | Proposed hierarchy | Non-negotiable caveat |
|---|---|---|---|---|---|
| D-01 Pure Super Admin | Admin/generic dashboard; no operational feed | Fixed dashboard structure; 005, 006, 008, 032, 036 | ADAPT | Generic/admin welcome → allowed admin/report shortcuts → role/announcement context | Jangan tampilkan queue/agent operational cards hanya karena Frappe admin/manager dapat melihatnya. |
| D-02 Pemohon-only | Own ticket totals, needs-action, create/list | Customer-oriented clarity; 005, 006, 008, 010, 032, 036 | ADAPT | Help request CTA → own action-needed → own status summary → recent/help guidance | Pemohon+current Approver can still use requester variant while gaining separate approval/report navigation. |
| D-03 Tier 1 | Queue/global/assigned/SLA operations | Dashboard cards/filters; 005, 010, 032, 036 | ADAPT | Urgent queue and SLA → operational counts → assigned/waiting → report/announcement links | Not admin unless additional role; actions remain on other screens. |
| D-04 Tier 2 | Own assigned scope | Personal-stat/dashboard composition; 005, 010, 032, 036 | ADAPT | Own workload/SLA → waiting/completed context → recent assigned work | No unassigned/global data or T1 controls. |
| D-05 Current Approver | Pending decisions + report access | Filtered work-summary hierarchy; 005, 008, 010, 032, 036 | ADAPT | “Perlu tindakan” first → oldest pending → summary/report → generic sections | Active role alone insufficient; current assignment + pending request authoritative; Team Chair override denies. |
| D-06 Team Chair | Safe team monitoring only | Read-only dashboard composition; 005, 006, 010, 032, 036 | ADAPT | Team summary → safe SLA/status rows → latest public response/solution → no operational action | Only safe projected fields; no overall block, attachment, internal/private detail, or write affordance. |

Coverage: **D-01 through D-06 = 6/6 variants**.

## Ticket List Variant Mapping

| Variant | Existing authority | Frappe reference / FRP | Mapping | Proposed hierarchy | Non-negotiable caveat |
|---|---|---|---|---|---|
| L-01 Requester list | `tickets.requester-index`, own paginator/filter/action labels | Ticket list + contextual filter/empty; 005, 010, 014, 017, 036 | ADAPT | “Tiket saya” → requester tabs/filters → own ticket cards/table → server quick action → paginator | Keep own-only scope, canonical filter normalization, requester action labels, desktop/mobile variants. |
| L-02 Operational/scoped | `tickets.index`, union scope for T1/T2/multi-role | Dense list/status/filter; 005, 010, 014, 017, 036 | ADAPT | Operational context → q/page-size → scoped list → queue link when allowed → paginator | Not an all-ticket replacement; T2 scope and union clauses remain policy/query driven. |
| L-03 Team Chair | `TeamChairTicketView`, fixed 15/page, no filters | Read-only list scaffold; 005, 010, 017, 036 | ADAPT | “Pemantauan tim” → safe metadata rows → safe status/SLA → paginator | No full model, filters, attachment, internal data, or action. Team Chair precedence wins over other ticket roles. |

Coverage: **L-01 through L-03 = 3/3 variants**.

## Ticket Detail Variant Mapping

| Variant | Existing data/action model | Frappe reference / FRP | Mapping | Proposed hierarchy | Non-negotiable caveat |
|---|---|---|---|---|---|
| T-01 Super Admin | Full/internal-visible direct read; no pure-role mutation | Split detail, header cluster, sections; 020–027, 031, 036 | ADAPT | Identity/status → read-only summary → public/internal timeline → metadata/attachments/history → no operational action bar | Do not infer operational rights from full visibility. |
| T-02 Pemohon | Own/created requester projection and state actions | Customer detail/conversation/mobile tabs; 020, 021, 023–027, 031 | ADAPT | Identity/status → requester actions → public conversation/reply → requester fields/attachments → solution/SLA | Never render internal comments/fields/attachments/approval data. Preserve cancel/confirm/not-satisfied/reopen conditions. |
| T-03 Tier 1 | Full ticket + state/assignment-specific actions | Agent workspace/header/timeline/dialogs; 020–027, 031 | ADAPT | Queue/work identity → allowed primary/overflow actions → activity → public/internal composers → workflow/SLA/service controls → metadata/history | Every action remains separate policy boolean/service transition; missing openers stay missing. |
| T-04 Assigned Tier 2 | Full assigned ticket + assigned-agent actions | Agent/mobile workspace; 020–027, 031 | ADAPT | Assigned context → return/communication/wait/approval/completion controls → activity → service detail | Access, attachments, and writes vanish when no longer assigned; no T1 queue/triage. |
| T-05 Current Approver | Pending assigned approval context | Dialog/action hierarchy + activity; 020, 022–027, 031 | ADAPT | Pending decision summary → approve/reject → relevant ticket context → public/internal timeline/attachments as policy allows | Requires current active assignment and pending request; approval reject auto-open defect remains frozen. |
| T-06 Team Chair | Separate safe `team-chair-show`, read-only | Information hierarchy only; 020, 021, 023, 027, 036 | ADAPT | Read-only banner → safe identity/status/SLA → safe public timeline/solution → no attachments/actions | Must never render full Ticket then hide it. Public safe comments only; all internal/sensitive/write surfaces absent. |

Coverage: **T-01 through T-06 = 6/6 variants**.

## Role / Multi-role Caveats

| Actor/case | Mapping rule | Reference conflict to avoid |
|---|---|---|
| Super Admin | Preserve admin navigation, report eligibility, direct full ticket view, and absence of pure-role operational index/actions | Frappe `isAdmin`/`isManager` cannot be mapped to operational agent capability. |
| Pemohon | Own/created ticket scope, requester list/detail/create/reply/confirmation/reopen only | Frappe customer portal behavior is not SIHATI authorization. |
| Agen Tier 1 | Operational queues/all/create-for-other and state-specific workflow | Do not import Frappe generic status mutation, inline edit, merge/split, bulk actions, or full custom actions. |
| Agen Tier 2 | Assigned-only view/write/attachment with mine/completed queue | A shared “agent” shell must not leak unassigned/global controls. |
| Approver | Effective only when active, current assignment exists, and pending request is assigned | Never show approval/report/ticket capability based only on role chip. |
| Ketua Tim Kerja | Ticket list/detail always safe projection and read-only; approval/report denied | Frappe has no safe-projection analog. Never request full model or hide private fields with CSS. |
| Super Admin + T1 | Policy may allow direct operational URLs, but current admin layout/dashboard suppresses operational navigation | Do not “fix” navigation by making all additive capabilities visible. VIS-006 is preserved. |
| Pemohon + Approver | Requester list variant can coexist with current-approver dashboard/navigation/approval/report | Do not force one global persona. VIS-007 is preserved. |
| Any ticket role + Ketua Tim | Team Chair override wins for ticket list/detail and ticket writes | Additional admin/announcement policies may still apply elsewhere; do not make global app read-only. |
| Ketua Tim + Super Admin | Admin resources remain accessible under current policies; tickets stay safe/read-only; report denied | Keep resource-specific policy differences. VIS-008 is preserved. |
| SuperAdmin/T1 + T2 | Internal attachment exact policy can still require assignment because T2 role is present | Never simplify attachment visibility from a union of role labels. |
| Navigation generally | Navigation is presentation; server policy is enforcement | Frappe optimistic active state and route guard are not authorization substitutes. |

## Baseline Issue Cross-reference

Definitions in this table:

- **PRESERVE** means the frozen behavior/evidence constraint is retained. For performance records, the contract is preserved, not the measured byte weight.
- **SEPARATE DEFECT DECISION REQUIRED** means any functional correction must be independently approved, specified, and tested; it is not bundled into visual redesign.

| VIS | Reference proposal touched | Decision | Mapping consequence |
|---|---|---|---|
| VIS-001 | FRP-022/031 action cluster/dialog | **SEPARATE DEFECT DECISION REQUIRED** | Do not create openers for internal comment, database change, assign T2, or return T1 while reorganizing actions. |
| VIS-002 | FRP-031 dialog validation/auto-focus | **SEPARATE DEFECT DECISION REQUIRED** | Do not correct approval reject `_action_modal`/`old('decision_note')` auto-open behavior in visual work. |
| VIS-003 | FRP-025/031 composer/dialog lifecycle | **SEPARATE DEFECT DECISION REQUIRED** | Do not silently add reset/clear/data modal form semantics to ticket modals. |
| VIS-004 | FRP-009/026 request-info form/attachment | **SEPARATE DEFECT DECISION REQUIRED** | Do not add request-information file input just because attachment UI is redesigned. |
| VIS-005 | FRP-009/022/031 triage modal | **SEPARATE DEFECT DECISION REQUIRED** | Render only active baseline triage choices; do not expose extra backend payload fields. |
| VIS-006 | FRP-002/004/032 navigation/dashboard | **PRESERVE** | Super Admin branch continues to dominate navigation/dashboard for ACT-08 even when T1 direct URL is authorized. |
| VIS-007 | FRP-004/032 dashboard/list variants | **PRESERVE** | ACT-09 keeps mixed requester-list/current-approver capability; no forced single persona. |
| VIS-008 | FRP-004/020/021 role treatment | **PRESERVE** | Team Chair override applies to tickets/report/approval but not automatically all admin/announcement resources. |
| VIS-009 | FRP-031/035 service admin density | **PRESERVE** (contract, not DOM weight) | UI-021 may improve layout/performance later, but modal IDs, fields, error/tab context, and visibility must remain. |
| VIS-010 | FRP-031/035 user admin density | **PRESERVE** (contract, not DOM weight) | UI-012 may improve presentation, but per-user targeting/error context/form behavior remains. |
| VIS-011 | FRP-039 accessibility | **SEPARATE DEFECT DECISION REQUIRED** | Adding a skip-link/main target is an explicit a11y decision with acceptance tests, not an accidental side effect. |
| VIS-012 | FRP-003/014/017/021/029/031/032/035 responsive | **PRESERVE** | Manual 1440×900 and 390×844 before-captures remain prerequisite; do not assume source breakpoints are visually correct. |
| VIS-013 | FRP-003/031/039 focus/modal/nav | **PRESERVE** | Verify focus trap, Escape, return, Tab loop, and duplicate handlers before refactor; defects require separate decision. |
| VIS-014 | FRP-006/008/010/039 color/focus | **PRESERVE** | Contrast/zoom/forced-color evidence gap remains; new presentation must be tested, not assumed. |
| VIS-015 | FRP-022/031 SVC-03 dialog | **SEPARATE DEFECT DECISION REQUIRED** | SVC-03 modal stays unreachable unless a separately approved workflow defect scope adds opener. |
| VIS-016 | FRP-022/027/031 SVC-03 readiness | **SEPARATE DEFECT DECISION REQUIRED** | Do not change incomplete/pre-execution form availability or readiness semantics in redesign. |
| VIS-017 | FRP-005/017/020/021/023/026 long content | **PRESERVE** | Reuse long-content fixtures and capture wrapping/truncation/clipping at both viewports in relevant waves. |

Cross-reference coverage: **VIS-001 through VIS-017 = 17/17**; 9 `PRESERVE`, 8 `SEPARATE DEFECT DECISION REQUIRED`.

## Patterns Explicitly Ignored

### Catalog patterns

| Pattern | Why ignored |
|---|---|
| FRP-001 Dual portal SPA root | Replaces Laravel route/session/layout architecture and cannot represent six SIHATI roles safely. |
| FRP-012 Command palette | New global feature, command model, client search, and shortcuts. |
| FRP-015 Configurable columns/multi-sort | New personalization and persisted list contract. |
| FRP-016 Click-cell filtering | Changes cell/row interaction and query behavior. |
| FRP-018 Bulk selection/actions | Adds bulk workflow/API/authorization not present in baseline. |
| FRP-019 Load more/local page length | Conflicts with server paginator/query preservation contract. |
| FRP-028 Inline metadata edit | Bypasses existing forms, validation, audit, and domain workflows. |
| FRP-030 Knowledge suggestion | Knowledge base is out of product scope. |
| FRP-033 Customizable dashboard | Adds drag/resize/chart persistence behavior. |
| FRP-034 Settings split-pane modal | Conflicts with standalone admin routes/error contexts and may flatten policies. |
| FRP-037 Realtime presence/typing | Requires Socket.IO/backend events/API changes. |
| FRP-038 Dark theme | Adds app-wide theme state and QA scope not approved in this redesign stage. |

### Explicit feature candidates evaluated

| Candidate from Frappe | Decision | Reason |
|---|---|---|
| Customer portal client behavior | IGNORE | Requester visual hierarchy may inspire UI, but SIHATI routing/auth/policy/forms remain existing. |
| Knowledge base | IGNORE | Not a current SIHATI feature. |
| Telephony/call logs/call activity | IGNORE | Requires backend/integration/navigation/workflow expansion. |
| Saved replies and saved-reply actions | IGNORE | Adds content store, composer commands, and mutation behavior. |
| Command palette | IGNORE | FRP-012. |
| Kanban/group-by workboard | IGNORE | Not an active frozen SIHATI screen or list contract. |
| Agent presence/availability | IGNORE | New agent-state model and UI; SIHATI has no equivalent baseline. |
| Realtime-only patterns | IGNORE | FRP-037. |
| Customizable dashboard | IGNORE | FRP-033. |
| Frappe settings architecture | IGNORE | FRP-034. |
| Frappe client router/state/data resources | IGNORE | Architecture replacement, not presentation redesign. |

## Recommended Design Direction

Direction yang direkomendasikan untuk fase desain berikut, **belum menjadi final design system**:

1. **Calm operational workbench.** Gunakan surface netral, border tipis, elevation selektif, dan semantic color hanya untuk status/outcome/action.
2. **Compact but readable.** Page header ringkas, metadata muted, body/message mempunyai line-height cukup, dan whitespace membedakan group daripada menambah card.
3. **Role-first composition.** Satu visual grammar dapat shared, tetapi content/action/data shape mengikuti server variants D/L/T dan resource policies.
4. **Conversation-first ticket detail.** Activity/public/internal/system chronology menjadi pusat; metadata/workflow ditempatkan pada rail/section/tab sesuai viewport.
5. **Explicit audience in communication.** Public/requester and internal composer harus terlihat berbeda dan tetap mempunyai separate contract.
6. **Server-native list controls.** Visual filter hierarchy dapat progressive, tetapi submit/query/sort/pagination tetap Laravel server-side.
7. **Contextual states everywhere.** Empty, filtered-empty, initial, loading, denied/not-found, and export/action failure harus mempunyai context yang tepat.
8. **Responsive reprioritization, not capability loss.** Desktop dapat memakai split surfaces; mobile dapat memakai tabs/disclosures/overflow, dengan authorized action tetap reachable.
9. **Accessibility as behavior.** Focus visibility, modal focus/return, labels, keyboard, reduced motion, contrast, and long-content wrapping harus mempunyai acceptance evidence.
10. **No visual redesign as defect repair.** VIS decisions dan implementation hold tetap berlaku; feature/functional fixes memerlukan scope terpisah.

Fase selanjutnya boleh menyusun design-system specification hanya setelah mapping ini direview dan manual baseline prerequisites dipenuhi. Tidak ada exact palette, typography scale, spacing tokens, final component API, atau production markup yang ditetapkan di sini.
