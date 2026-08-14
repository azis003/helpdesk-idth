# Component Inventory

Inventaris ini mencatat arsitektur komponen yang ada tanpa membuat atau mengubah komponen. Baseline memiliki **18 file Blade component** di `resources/views/components/`: 15 digunakan oleh UI aktif (termasuk dua include-only global helpers) dan 3 tidak memiliki consumer aktif.

Selain itu terdapat **16 reusable partial aktif** di luar folder component. File pagination vendor dicatat sebagai framework view override, bukan application component.

## Definisi

- **Blade component**: file di `resources/views/components/`; dapat dipanggil dengan `<x-...>` atau, untuk dua global helpers, lewat `@include`.
- **Reusable partial**: `_*.blade.php` atau focused view fragment yang di-include oleh active screen.
- **State**: presentation state/old input/error/variant yang dibaca komponen, bukan global client store.
- **Business dependency**: enum, model/view-model, policy result, route, atau domain-derived data yang tidak boleh direka ulang di komponen.

## A. Existing Blade components — 18

| # | File / tag | Pengguna aktif | Props / slot | State dan JS dependency | Business/policy dependency | Redesign risk | Rekomendasi |
|---|---|---|---|---|---|---|---|
| 1 | `components/approval-panel.blade.php`; `<x-approval-panel>` | `tickets._action-modals` | Props `approvalRequest`, `ticket`, `canDecide=false`; no public slot | Pending/approved state; `data-approval-decision-form`, `data-approval-submit` | ApprovalRequest model state, approve/reject named routes, `canDecide` result from policy | HIGH | Pertahankan contract. Kelak display dan decision form boleh dipisah secara presentasional, tetapi current-approver/pending rule tidak boleh dihitung ulang. |
| 2 | `components/empty-state.blade.php`; `<x-empty-state>` | 21 Blade source files aktif/dormant campuran; dipakai luas di active lists/dashboard | Props `title`, optional `description`, `action`, `actionLabel`; attributes; no slot | Pure render | Action URL/label disediakan caller | LOW | Pertahankan sebagai primitive shared; standardisasi icon/variant kelak tanpa mengubah action visibility. |
| 3 | `components/field-error.blade.php`; `<x-field-error>` | 11 Blade source files, banyak occurrences | Prop `message`; attributes; no slot | Error bag datang dari caller | Laravel validation error key/ARIA IDs | LOW | Pertahankan; dapat menjadi canonical inline error. |
| 4 | `components/flash.blade.php`; include-only | `layouts.app`, `layouts.guest` | No props/slot; reads session | Emits JSON `data-swal-flash`; SweetAlert handler | Session keys `success`, `warning`, `$errors` | MEDIUM | Pertahankan contract JSON dan error aggregation; visual toast dapat diubah terkontrol. |
| 5 | `components/form-field.blade.php`; `<x-form-field>` | Login, change password, branding, reset password | Props `name`, `label`, `type=text`, `autocomplete`, `help`, `value`, `required`; attributes | `old($name)`, `@error($name)`; no custom JS | Exact field/error key; password deliberately not repopulated | MEDIUM | Pertahankan; jangan pakai untuk field kompleks tanpa memastikan name/error/old parity. |
| 6 | `components/global-loading-overlay.blade.php`; include-only | Kedua layout | No props/slot | `data-global-loading-overlay`, `data-global-loading-message`; global form/navigation handler | None, tetapi timing harus tidak menutup server error/navigation | MEDIUM | Pertahankan atau migrasikan atomik dengan JS; accessible live status/focus behavior wajib. |
| 7 | `components/page-header.blade.php`; `<x-page-header>` | 20 Blade source files | Props `eyebrow`, `title`, optional `description`, `backUrl`, `backLabel`, `dotClass`; default slot untuk actions | Pure render | Caller controls authorized actions/back route | LOW | Pertahankan sebagai page primitive. Slot tidak boleh diberi action yang melampaui policy. |
| 8 | `components/priority-badge.blade.php`; `<x-priority-badge>` | Dashboard dan ticket screens | Prop `priority`; no slot | Normalizes enum/string; tone mapping | `App\Enums\Priority` value/label | MEDIUM | Pertahankan enum normalization; visual tone boleh berubah, canonical label/value tidak. |
| 9 | `components/role-badge.blade.php`; `<x-role-badge>` | Tidak ada consumer | Prop `label`; no slot | Pure render | Caller label only | LOW | **Unused.** Jangan otomatis dipakai; evaluasi hapus/merge hanya pada refactor terpisah. |
| 10 | `components/status-badge.blade.php`; `<x-status-badge>` | Dashboard dan ticket screens | Prop `status`; no slot | Normalizes enum/string; tone mapping | `App\Enums\TicketStatus` value/label/group | MEDIUM | Pertahankan; jangan menyederhanakan 11 status menjadi value baru. |
| 11 | `components/tickets/detail-header.blade.php`; `<x-tickets.detail-header>` | Active `tickets.show` dan `tickets.team-chair-show` | Props `backUrl`, `backLabel`, `ticketLabel`, `status`, `priority`, `submittedAt`, `showActions`; named slot `actions` | `<details data-ticket-action-menu>` handler | Back context, policy-derived `showActions`, badge enums | HIGH | Pertahankan shared header; action slot harus tetap server-gated. |
| 12 | `components/tickets/dynamic-field.blade.php`; `<x-tickets.dynamic-field>` | `tickets._form` | Props `field`, `service`; no slot | `old('fields.{key}')`, nested errors; `data-ticket-field/input`; boolean hidden+checkbox | Active ServiceFieldDefinition/options/version/visibility/required/rules | HIGH | Pertahankan contract renderer. Kelak share visual primitives, tetapi jangan ubah generated `name`, key, version meaning, atau type semantics. |
| 13 | `components/tickets/fact-list.blade.php`; `<x-tickets.fact-list>` | Tidak ada consumer | Prop `facts=[]`; no slot | Pure render | Caller array `label`/`value` | LOW | **Unused.** Kandidat evaluasi ulang, bukan otomatis diaktifkan. |
| 14 | `components/tickets/internal-field.blade.php`; `<x-tickets.internal-field>` | `tickets.show` | Props `field`, optional `value`; no slot | `old('internal_fields.{key}')`, error; hidden version; `data-ticket-internal-field/input` | SVC-07 internal definition/version/value; server policy decides visibility/write | **CRITICAL** | Pertahankan sebagai separate semantic component. Jangan merge penuh dengan requester dynamic field hingga version/name/visibility contract teruji. |
| 15 | `components/tickets/journey.blade.php`; `<x-tickets.journey>` | Tidak ada consumer | Prop `steps=[]`; no slot | Pure state tone (`done/current/default`) | Caller-supplied workflow representation | LOW | **Unused.** Jangan digunakan untuk mengarang state machine baru. |
| 16 | `components/tickets/message.blade.php`; `<x-tickets.message>` | Active `tickets.show`; dormant requester-show juga mereferensikan | Props `message`, `variant=default`, `requesterId=null`; no slot | Variant branches; attachment links | Accepts `TicketComment` atau safe `TeamChairCommentView`; public/internal label; authorized attachment route | HIGH | Pertahankan dual data-type awareness atau pisahkan eksplisit kelak. Jangan membuat attachment URL atau visibility dari client. |
| 17 | `components/tickets/timeline.blade.php`; `<x-tickets.timeline>` | `tickets.show`, `tickets.team-chair-show`; dormant requester-show | Props `events=[]`, `variant=default`; no slot | Variant/tone/current markers | Timeline array sudah disanitasi/disusun controller; Team Chair event set berbeda | HIGH | Pertahankan data-source boundary; visual timeline aman diubah tanpa menambah internal events ke safe projection. |
| 18 | `components/ui/modal-panel.blade.php`; `<x-ui.modal-panel>` | 14 ticket action modals dalam `_action-modals` | Props `id`, `labelledby`, `autoOpen=false`, `maxWidth=max-w-2xl`; default slot | `data-ui-modal`, `data-auto-open`, close selectors; generic manager/focus trap/restore/Escape/reset | Caller supplies policy-gated content; `old('_action_modal')` controls error reopen | HIGH | Pertahankan sebagai dialog primitive. ID, opener, ARIA, focus, auto-open, dan error behavior harus dimigrasikan bersama. |

### Komponen yang tidak digunakan

Tiga component files tidak memiliki active consumer:

- `components/role-badge.blade.php`;
- `components/tickets/fact-list.blade.php`;
- `components/tickets/journey.blade.php`.

Statusnya pada Fase 2: dokumentasikan saja. Jangan menghapus, mengaktifkan, atau menjadikannya dasar desain tanpa memeriksa intent/tests.

## B. Reusable partial aktif — 16

| # | File | Pengguna | Expected context / props informal | State/JS dependency | Business/policy dependency | Risk | Rekomendasi |
|---|---|---|---|---|---|---|---|
| 1 | `admin/audit-logs/_details.blade.php` | Audit desktop/mobile rows | `auditLog`, `formatJson` | Native `<details>`/render only | Sensitive before/after/context payload already authorized | HIGH | Pertahankan redaction/scope; boleh konsolidasikan duplicate desktop/mobile shell. |
| 2 | `admin/catalog/_form-preview.blade.php` | Active service preview modal | `serviceType`, `fieldTypes` | Render-only preview | Active field definition/options/visibility labels | MEDIUM | Pertahankan sebagai preview renderer; tidak boleh dianggap actual validation engine. |
| 3 | `admin/forms/_field-form.blade.php` | Active service edit modal, create/version | `mode`, `field`, `serviceType`, field/visibility options, next order, keep-open | `data-field-builder`, field type/options panel, submit feedback | Versioned field route/payload; historical key | HIGH | Pertahankan single source untuk add/version forms. |
| 4 | `admin/locations/_building-form.blade.php` | Location create/edit modals | action, method, formId, prefix, building, isModal | Generic modal, `_location_form`, old/error targeting | Building validation/policy | MEDIUM | Pertahankan shared create/edit form. |
| 5 | `admin/locations/_floor-form.blade.php` | Floor create/edit modals | action, method, formId, prefix, building, floor, isModal | Generic modal, `_location_form` | Parent building, uniqueness/order rules | MEDIUM | Pertahankan shared form. |
| 6 | `admin/services/_create-field-row.blade.php` | Service create modal field builder | row index + field options/defaults | `data-service-field-row` family | Nested CreateServiceTypeRequest schema | HIGH | Pertahankan row payload shape; visual row may be refactored. |
| 7 | `admin/services/_create-modal.blade.php` | Services index | skills/types/visibilities/classes + old/error | Generic modal, service field builder, SLA, skill picker | Create service + nested fields transaction | HIGH | Pertahankan intact until service editor regression suite exists. |
| 8 | `admin/services/_edit-modal.blade.php` | Services index per service | service + skills/types/visibilities + auto-open | Generic modal, tabs, field/SLA/skill/submit handlers | Update service, versioned fields, skill mapping, status | **CRITICAL** | Redesign late; multiple independent contracts share one dialog. |
| 9 | `admin/services/_preview-modal.blade.php` | Services index per service | service, field types | Generic modal | Preview data only | MEDIUM | Safe after modal primitive stabilizes. |
| 10 | `admin/services/_view-modal.blade.php` | Services index per service | service, field types | Generic modal | Read-only catalog metadata | MEDIUM | Candidate to share display sections with preview only where semantics match. |
| 11 | `admin/users/_detail.blade.php` | User view modal | user | Render only | Roles, team membership/chair, skills, account state | MEDIUM | Pertahankan; authorization stays at parent/controller. |
| 12 | `admin/users/_form.blade.php` | User create/edit modal and standalone pages | action, method, formId, prefix, optional user, isEdit/isModal, roles/teams/skills | Generic modal; `data-user-*`; `_user_form`, `_user_edit` | Role/team/skill consistency, technician skill requirement, user policy | HIGH | Pertahankan shared form; do not fork modal/page payload. |
| 13 | `dashboard/helpdesk.blade.php` | Dashboard Tier 1/Tier 2 variant | dashboard arrays, period formatting, flags | Render only/global nav | DashboardService scoped aggregations; role branch | HIGH | Keep data contract; may split into smaller visual partials later without recomputing aggregates. |
| 14 | `tickets/_action-modals.blade.php` | Ticket detail | ticket, policy flags, options, attachment policies, approval, special readiness, assignees | Generic modal + almost all `data-ticket-*` handlers; old `_action_modal` | TicketPolicy, state machine, SLA, approval, attachments, SVC-02/03, assignments | **CRITICAL** | Last implementation wave. Split only contract-by-contract with focused tests. |
| 15 | `tickets/_catalog.blade.php` | Ticket create service-selection branch | serviceTypes/actor/access flags | Navigation/global loading | Active services/order and create policy | MEDIUM | Safe before ticket form, preserving query selection. |
| 16 | `tickets/_form.blade.php` | Ticket create selected-service branch | actor/service/fields/attachment policies/location/requesters | Ticket form/dynamic-field/submit handler | StoreTicketRequest, creation service, location and attachment rules | HIGH | Redesign after primitive forms and dynamic field tests. |

## C. Framework override

`resources/views/vendor/pagination/tailwind.blade.php` adalah Laravel pagination view override. Ia bukan domain component, tetapi presentation contract penting karena semua paginator bergantung pada URL/query yang dihasilkan. Redesign visual diperbolehkan kelak; link semantics, disabled/current state, ARIA, dan query preservation harus tetap.

## D. Duplicated UI patterns

| Pattern duplikat | Lokasi utama | Risiko duplikasi | Arah architecture yang direkomendasikan, tanpa implementasi sekarang |
|---|---|---|---|
| Desktop table + mobile card untuk data yang sama | Ticket index/queue/all/requester; users; skills; audit; services; locations | Column/card dapat berbeda label, link, policy action, atau empty state | Buat future responsive collection primitive yang menerima explicit columns/mobile fields/actions; jangan memindahkan authorization ke component. |
| Search + per-page toolbar | Tickets, users, locations, skills, services | Query name/default/reset/preservation mudah tidak konsisten | Future filter-bar/search/per-page primitives dengan query contract eksplisit. |
| Page title/back/actions | Sebagian memakai page-header, sebagian custom | Spacing/heading hierarchy berbeda | Perluas `x-page-header` secara kompatibel, bukan membuat banyak header baru. |
| CRUD create/edit modal shell | Users, locations, teams, skills, services | Focus trap, auto-open, clear/reset, footer, submit state diulang | Konsolidasikan ke modal-panel setelah bespoke marker/error behavior dipetakan. |
| Form label/help/error wrapper | Banyak inline fields di admin/tickets | ARIA/error ID dan required indicator mudah berbeda | Future input primitives untuk input/select/textarea/checkbox/file; exact `name`/error key tetap caller-controlled. |
| Status/action confirmation form | Users, teams, skills, services, locations, announcements, ticket cancel | Confirmation text dan destructive tone tidak konsisten | Future action-form/confirm-button primitive; route/method/policy tetap caller. |
| Attachment picker group | Ticket create, messages, ticket upload | Policy label, accept, count, nested name diulang | Future attachment-fieldset yang menerima policy objects dan context; jangan mengubah authorization/visibility. |
| Ticket communication editor | Public, requester, internal, request info | Payload mirip tetapi visibility/state berbeda | Share visual editor only; keep separate semantic action, policy, route, attachment scope. |
| Definition-driven field renderer | Requester dynamic field dan SVC-07 internal field | Markup mirip, names/version/visibility berbeda | Share low-level visual controls only; retain separate semantic wrappers. |
| Detail fact/metadata cards | Ticket detail, Team Chair detail, user/service view, approval | Inconsistent label/value layout | Future description-list/fact-grid primitive with pre-sanitized values. |
| Badges/chips | Role labels custom, status/priority components, various inline waiting/active chips | Same concept gets different shapes/colors | Keep enum-aware badges; add generic semantic badge only if it cannot invent domain state. |
| Empty sections | Component plus handwritten `@empty` text | Inconsistent action/spacing | Prefer empty-state component for full collection; compact-empty variant for nested lists. |
| Submit loading labels | `data-submit-*`, generic modal, ticket-specific families, team bespoke | Double-submit handling and text differs | Define one future behavior protocol, migrated atomically and verified for all forms. |
| Desktop/mobile navigation | Duplicated role conditions in app layout | Links/authorization can drift | Future data-driven presentation is desirable, but policy checks and route conditions must remain server-side and equivalent. |

## E. Components yang layak dipertahankan

Prioritas retain:

- `page-header`, `empty-state`, `field-error`, `form-field` sebagai visual primitives;
- `status-badge` dan `priority-badge` karena sudah enum-aware;
- `ui.modal-panel` karena menangani dialog/focus contract;
- `tickets.detail-header`, `tickets.message`, `tickets.timeline` karena dipakai full dan safe-projected detail;
- `tickets.dynamic-field` dan `tickets.internal-field` sebagai dua semantic boundaries yang berbeda;
- `approval-panel` karena mengikat presentasi decision ke policy-derived `canDecide`;
- flash/global loading includes karena menjadi bridge server session ke client feedback;
- active form partials untuk user/location/service agar modal dan standalone payload tidak bercabang.

“Dipertahankan” berarti contract/semantic boundary dipertahankan; tampilan internal dapat diubah pada wave yang sesuai.

## F. Components/pattern yang layak digabung

Rekomendasi architecture, bukan instruksi implementasi Fase 2:

1. modal shell bespoke user/location/team/skill/service menuju satu dialog primitive setelah error-marker migration plan selesai;
2. search/per-page/pagination shell lintas collection screen;
3. desktop row/mobile card data presentation melalui schema presentation eksplisit;
4. form control visual layer untuk text/select/textarea/checkbox/file, tanpa mengubah `name`, `old`, error key, atau disabled semantics;
5. generic submit feedback protocol untuk mengganti keluarga selector secara atomik;
6. compact/full empty-state variants;
7. metadata/fact grid visual primitive;
8. attachment picker visual primitive yang tetap menerima attachment-policy contract dari server.

Jangan menggabungkan:

- public dan internal message action;
- requester dynamic field dan internal versioned SVC-07 field pada level semantic form;
- ticket full model dan Team Chair safe view model;
- approve/reject/request-approval menjadi satu mutation;
- complete/confirm/not-satisfied/reopen menjadi client-side state machine;
- primary routes dan aliases dengan menghapus alias.

## G. Generic components yang belum tersedia

| Missing primitive | Kebutuhan | Constraint utama |
|---|---|---|
| Responsive data collection | Table desktop + card mobile | Actions dan data fields harus sudah authorized/scoped oleh caller. |
| Filter/search/per-page bar | Query consistency | Exact query name/default/options/reset/query preservation. |
| Pagination wrapper | Unified appearance | Laravel URLs, current/disabled/ARIA semantics unchanged. |
| Select/textarea/checkbox/radio/file field | Form consistency | Caller controls exact name, old value, error key, required, disabled, enctype. |
| Alert/inline error summary | Non-SweetAlert accessible errors | Tidak mengganti validation bag atau flash keys. |
| Confirmation/action form | Destructive/state actions | Exact route/method/CSRF/policy visibility and confirmation copy. |
| Description list/fact grid | Detail metadata | Only render data already allowed for role/projection. |
| Tabs/disclosure | Queue/service editor sections | Query/tab/modal error context and keyboard semantics. |
| Attachment fieldset/list | Upload/download consistency | Policy ID nested names, MIME/count/size, visibility, authorized download URL. |
| Action menu | Header/table actions | Server-gated items, keyboard/focus/outside-click. |
| Compact empty/loading state | Nested dashboard/detail lists | Do not simulate async state where page is SSR. |

## H. Client state boundary

Tidak ada Redux/Pinia/Vuex/Alpine store atau Livewire component state. State aktif berada pada:

- server session: auth, flash, error bag, old input;
- URL query: filter/tab/page/context;
- Blade locals/props: role/policy flags dan view data;
- DOM `data-*`: modal/submit/form enhancement;
- `localStorage`: sidebar preference;
- transient JavaScript state: open modal, focus, submit/loading, preview, dynamic field rows, export in progress.

Rekomendasi component architecture tidak boleh memperkenalkan client store sebagai sumber authorization, workflow status, attachment visibility, atau business state.
