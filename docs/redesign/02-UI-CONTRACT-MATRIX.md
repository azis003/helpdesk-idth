# UI Contract Matrix

Dokumen ini membekukan kontrak antara presentation layer dan aplikasi. Baseline route berisi **112 non-vendor HTTP contracts**: **31 read/response contracts** dan **81 mutation/form-action contracts**. Dari 81 mutation, 59 named routes direferensikan oleh Blade aktif dan 22 adalah compatibility/no-active-trigger contracts. Seluruhnya tetap dicatat.

## Cara membaca

- Semua form mutation wajib mempertahankan `@csrf`.
- PUT/DELETE dari Blade wajib mempertahankan method spoofing.
- Semua input file harus mempertahankan `multipart/form-data` dan nested name yang berlaku.
- “Back” berarti redirect ke URL referer/previous response Laravel; query dan fragment browser yang ada ikut menentukan tujuan.
- “Standard validation redirect” berarti kembali dengan error bag dan old input.
- `—` berarti memang tidak ada contract tambahan, bukan bebas untuk menambahkan payload.
- Nama dengan `[]` atau `[key]` adalah literal HTML name contract.
- Authorization pada kolom matrix adalah ringkasan; policy/domain service tetap authoritative.

## Contract universal

| Contract | Freeze |
|---|---|
| CSRF | Semua POST/PUT/DELETE form mengirim token CSRF. |
| Method spoofing | `@method('PUT')` dan `@method('DELETE')` harus tetap ada bila HTML form menggunakan POST. |
| Old input | Password/file tidak direpopulasi; text/select/radio/checkbox mengikuti branch `old()` yang sekarang. |
| Error routing | Hidden context seperti `_action_modal`, `_user_form`, `_location_form`, `_skill_form`, `_team_create`, `_team_edit`, `_service_edit`, dan `_service_tab` tidak boleh hilang. |
| Flash | `success`, `warning`, dan validation errors dibaca oleh `components.flash` lalu ditampilkan melalui SweetAlert2. |
| Submit state | Global overlay dan handler per-form mencegah double submit/menampilkan status; bukan pengganti server idempotency/concurrency check. |
| Authorization | Visibility Blade adalah affordance. Middleware, Gate/Policy, Form Request authorization, `DomainAuthorization`, dan domain service tetap memutuskan. |
| Pagination | Link paginator harus menjaga query yang sekarang dijaga oleh `withQueryString()`. |

## A. High-risk contract freeze

### A1. Ticket create dan dynamic requester fields

| Item | Contract beku |
|---|---|
| UI/source | UI-007; `tickets.create`, `tickets._catalog`, `tickets._form`, `components.tickets.dynamic-field`. |
| Page selector | `GET tickets.create?service_type_id={id}`. Controller memprioritaskan old `service_type_id`, lalu query. |
| Submit | `POST tickets.store`; `multipart/form-data`. |
| Input utama | `requester_id`, `service_type_id`, `subject`, `description`, `priority`, `floor_id`. |
| Dynamic requester fields | `fields[{key}]`; multi-select `fields[{key}][]`; boolean mengirim hidden `0` dan checkbox `1`. |
| Attachments | `attachments[{attachmentPolicyId}][]`, `multiple`; accepted MIME/extensions dan jumlah/ukuran tetap berasal dari policy server. |
| Hidden | `service_type_id`; requester actor dapat berupa hidden `requester_id`; CSRF. |
| Error/old | `requester_id`, `service_type_id`, `subject`, `description`, `priority`, `floor_id`, `fields`, `fields.{key}`, `attachments`, dan nested attachment keys. Old service harus mengembalikan user ke form service yang sama. |
| JS selectors | `data-ticket-form`, `data-ticket-submit`, `data-ticket-submit-label`, `data-ticket-submit-loading`, `data-ticket-field`, `data-ticket-field-input`. |
| Authorization | `TicketPolicy::create`, `createSelf`, `createForOther`; Team Chair override; `StoreTicketRequest`; service/location/attachment validation dan `TicketCreationService`. |
| Redirect/flash | `tickets.show` untuk tiket baru; success menyebut ticket number. |
| Service invariants | SVC-01/SVC-05 location requirement; visibility field; active definition/version; snapshot; file policy. |

### A2. Ticket filters, sorting, dan pagination

| Screen | Query contract | Sorting/pagination | Authorization/data contract |
|---|---|---|---|
| UI-004 daftar tiket | Operational: `q`, `per_page`, `page`. Requester-only: tambahan `tab ∈ {semua, aktif, tindakan, selesai}`, `class ∈ {INC, REQ, CHG}`, canonical `status`, `from`, `to`; `per_page ∈ {10,25,50}`; invalid option dinormalisasi dan reversed date range ditukar. Team Chair fixed 15/no filter. | `submitted_at DESC, id DESC` untuk requester/operational; paginator menjaga query. Search requester juga mencakup description, sedangkan operational search mencakup requester/assignee snapshots sesuai query. | `TicketPolicy::viewAny`; requester scope, union agent scope, atau `TeamChairTicketProjection`. |
| UI-005 queue | `tab ∈ {queue, mine, assigned, completed}`, `page`. | 20/page; queue memakai `orderForTierOneQueue`; mine/assigned/completed `updated_at DESC, id DESC`. | Ability dipilih sesuai tab (`viewQueue`, `viewAssigned`, `viewCompleted`); Tier 2 scope sendiri. |
| UI-006 all | `q`, `per_page ∈ {10,25,50}`, `page`. | `submitted_at DESC, id DESC`; paginator menjaga query. | `TicketPolicy::viewAll` (Tier 1, bukan Team Chair). |
| UI-008 detail | `from=all` hanya untuk context back link/nav active. | Tidak ada pagination. | Jangan memakai `from` untuk memperluas data scope atau policy. |

### A3. Internal fields SVC-07

| Item | Contract beku |
|---|---|
| UI/source | UI-008; `tickets.show`, `components.tickets.internal-field`. |
| Submit | `PUT tickets.internal-fields.update`. |
| Input | `internal_fields[{key}]`/`[]`; hidden `internal_field_versions[{key}]`; boolean hidden `0` + checkbox `1`. |
| Compatibility payload | Form Request menerima legacy `fields` sebagai fallback `internal_fields`, dan `field_versions` sebagai fallback `internal_field_versions`. Primary UI tetap memakai nama `internal_*`. |
| Selector/handler | `data-ticket-internal-fields-form`, `data-ticket-internal-field`, `data-ticket-internal-field-input` dan submit-state handler. |
| Authorization | `TicketPolicy::updateInternalFields`: assigned agent, SVC-07, status aktif yang diizinkan, bukan Team Chair. Definition/version validation dan domain save tetap server-side. |
| Redirect/flash | `tickets.show`; success “Field internal SVC-07 berhasil disimpan.” |

### A4. Attachments dan visibility

| Context | Input/download contract | Authorization contract |
|---|---|---|
| Ticket create | `attachments[{policyId}][]` | Creation service + active attachment policy; requester/internal filtering berdasarkan actor. |
| Public/internal/requester message | `body`, optional `attachments[{policyId}][]` | `TicketPolicy::commentPublic`, `commentInternal`, atau `replyRequester`; `TicketCommunicationService`; visibility tidak boleh ditentukan bebas oleh client. |
| Ticket-level upload | Required `attachments` array, nested files `attachments.*.*` | `TicketPolicy::uploadAttachment` + attachment policy service. |
| SVC-02 completion | `data_export_result` single file, required hanya SVC-02, max 10 MB menurut Form Request | Assigned-agent completion + special control; file harus requester-accessible sesuai workflow. |
| Download | `GET attachments.download` dengan route-model binding `withTrashed()` | `AttachmentAccessPolicy`; Team Chair selalu ditolak; internal/public/requester-accessible rules harus tetap. |

`AttachmentAccessPolicy` tidak boleh direplikasi dengan CSS/JS. Internal attachment dapat dilihat pure Super Admin/pure Tier 1, assigned Tier 2, atau pending Approver sesuai exact policy expression; requester-accessible attachment mengikuti owner/creator dan role/assignment rules. Nuansa multi-role: keberadaan role Tier 2 membuat internal attachment membutuhkan assignment walaupun actor juga memiliki Super Admin/Tier 1. Nuansa Approver: helper attachment memeriksa pending request assigned + role aktif, tetapi tidak mengulang current `ApproverAssignment` check yang dipakai ticket view. Link harus selalu memakai named route, bukan storage path langsung.

### A5. Communication dan waiting states

| Action | Route/payload | State/policy | UI contract |
|---|---|---|---|
| Public reply | `POST tickets.comments.public`; `body`, `attachments[policy][]` | `commentPublic`; comment visibility public | Inline form, `data-ticket-communication-form`. |
| Internal note | `POST tickets.comments.internal`; `body`, `attachments[policy][]`, `_action_modal=ticket-internal-comment-modal` | `commentInternal`; visibility internal | Modal `ticket-internal-comment-modal`. |
| Request information | `POST tickets.request-information`; `body`, optional attachments accepted server | `requestInformation`; moves to Menunggu Pemohon/pause behavior | Modal `ticket-request-information-modal`; current modal only renders `body`, so redesign must not silently claim upload support unless explicitly scoped. |
| Requester reply | `POST tickets.requester-reply`; `body`, `attachments[policy][]` | `replyRequester`; waiting service resumes/updates workflow | Inline form. |
| Wait third party | `POST tickets.wait-third-party`; `third_party_name`, `follow_up_date`, `note` | assigned agent; from Diproses/Dikerjakan | Modal `ticket-third-party-modal`. |
| Resume third party | `POST tickets.resume-third-party`; optional `reason` | assigned agent; Menunggu Pihak Ketiga → Dikerjakan | Modal yang sama; old/error context tetap. |

### A6. Assignment, triage, dan agent workflow

| Action | Payload | Policy/domain invariant | UI/JS contract |
|---|---|---|---|
| Claim | No domain input | Tier 1; new unassigned queue ticket; concurrency failure uses error key `ticket` | Endpoint aktif, tetapi tidak ditemukan trigger UI aktif; redirect `back`. Jangan diaktifkan tanpa keputusan terpisah. |
| Handle | No domain input | Assigned agent; endpoint aktif tetapi tidak ada trigger UI aktif ditemukan | Jangan dihapus atau diaktifkan tanpa keputusan terpisah. |
| Triage | `outcome`; optional `problem_category_id`, `priority`, `category_reason`, `priority_reason`, `assigned_to_id`, `rejection_reason` | Tier 1; queue ticket atau specific assigned Tier 1 state. Form Request accepts `self`, `tier_2`, `reject`; current modal hanya merender `self` dan `tier_2` + `assigned_to_id`. | `_action_modal=ticket-triage-modal`; `data-ticket-triage-*`; modal ID fixed. |
| Assign Tier 2 | `assigned_to_id`, optional `reason` | Assigned Tier 1, Dikerjakan, proper tier/skill/domain checks | Modal `ticket-assign-tier-two-modal`; `data-ticket-assignment-*`. |
| Return Tier 1 | Required `reason` | Assigned Tier 2, Dikerjakan, `last_triaged_by_id` tersedia | Modal `ticket-return-tier-one-modal`; `data-ticket-return-*`. |
| Priority | `priority`, required `reason` | Tier 1 + new queue ticket only | PUT; modal `ticket-priority-modal`; `data-ticket-priority-*`. |
| Reject | Required `reason` | Tier 1 + new queue ticket only | Modal `ticket-reject-modal`; `data-ticket-reject-*`. |

Catatan: controller saat ini menghitung `$canTriage` melalui policy, tetapi presentasi/action availability juga terikat pada state/data yang dikirim. Redesign tidak boleh memperluas hasil triage yang tersedia hanya karena Form Request menerima value tambahan.

### A7. Approval

| Action | Contract beku |
|---|---|
| Request approval | `POST tickets.request-approval`; optional `reason`; compatibility input `note` dinormalisasi ke `reason`; hidden `_action_modal=ticket-request-approval-modal`; policy assigned Tier 1/2 pada Diproses/Dikerjakan. |
| Approval view | Hanya role Approver aktif yang merupakan current active approver dan bukan Team Chair; hanya pending request yang ditugaskan kepadanya. |
| Approve | `POST approvals.approve`; tanpa domain input; form dapat berada di approvals index atau `x-approval-panel`; redirect `tickets.show`; success flash. |
| Reject decision | `POST approvals.reject`; required `decision_note`; compatibility aliases `note`/`reason` dinormalisasi; redirect `tickets.show`; success flash. |
| Modal | Pada ticket detail: `ticket-approval-decision-modal`; handler `data-approval-decision-form`, `data-approval-submit`. Index tidak menggunakan modal. |
| Workflow | Previous status/assignee restoration, active-approver assignment, pending state, audit, notifications, dan SLA behavior berada di service dan locked. |

### A8. Completion, requester confirmation, reopen, SVC-02, dan SVC-03

| Action | Payload/modal | Policy/state | Compatibility/redirect |
|---|---|---|---|
| Complete | `solution`; SVC-02 additionally required `data_export_result`; `_action_modal=ticket-complete-modal`; multipart hanya SVC-02 | Assigned agent, status Dikerjakan; service special control authoritative | Primary `tickets.complete`; alias `tickets.resolve`; redirect show + success; state Menunggu Konfirmasi. |
| Confirm | No domain input; `_action_modal=ticket-confirmation-modal`; Swal | Pemohon owner, Menunggu Konfirmasi | Primary `tickets.confirm`; alias `tickets.requester-confirm`; redirect show + success; state Ditutup. |
| Not satisfied | Required `reason`; modal yang sama | Pemohon owner, Menunggu Konfirmasi | Primary `tickets.not-satisfied`; alias `tickets.confirmation.not-satisfied`; redirect show + success; kembali Dikerjakan. |
| Reopen | Optional `reason`; `_action_modal=ticket-reopen-modal` | Pemohon owner, Ditutup; reopen limits/window remain service-owned | Redirect show + success; SLA cycle baru. |
| SVC-03 execute | No domain input selain modal marker; modal `ticket-database-change-modal`; `data-ticket-database-change-form`; Swal | Assigned agent, SVC-03, Dikerjakan; three distinct valid evidence required by special-control service | Redirect show; domain failure uses `database_change`. |
| SVC-03 verify | Primary `verification_result`, `verification_notes`; Form Request also accepts legacy `result`, `notes`; same modal | Assigned agent, SVC-03, Dikerjakan; execution must have begun; verifier/history service-owned | Redirect show + success. |

### A9. Report display/export

| Item | Contract beku |
|---|---|
| Display routes | `reports.index` (`/reports`) dan alias `reports.monthly` (`/reports/monthly`) memanggil controller/view yang sama. |
| Query | Either `month`, or `start_date` + `end_date`; compatibility aliases `from`/`to`; `page` bila table dipaginasi oleh future server output tidak boleh dipakai client untuk mengubah scope. |
| Initial state | Report dihitung hanya bila salah satu query period hadir. |
| Export | GET `reports.monthly.excel` dan `reports.monthly.pdf` dengan period query yang sama. |
| JS | `data-report-filter`, `data-report-export`, fetch blob, `Content-Disposition` filename parsing, failure text, submit/export loading. |
| Authorization | `ReportExportPolicy`: active, not Team Chair, Super Admin atau Tier 1 atau current active Approver. |
| Response | XLSX/PDF content, filename/header, and report export audit logging are locked. |

### A10. User management

| Action | Contract beku |
|---|---|
| Create | `name`, `username`, `email`, `nip`, `temporary_password`, `temporary_password_confirmation`, `role_ids[]`, required `team_id`, `team_position`, optional `skill_ids[]`; hidden `_user_form`; role/skills/team JS only changes presentation. |
| Update | `name`, `username`, `email`, `nip`, `role_ids[]`, required `team_id`, `team_position`, optional `skill_ids[]`, required `is_active`; hidden `_user_form`, `_user_edit`; no password field. |
| Reset password | `temporary_password`, `temporary_password_confirmation`, accepted `confirm_reset`; index modal additionally carries `reset_user_id` for error targeting. |
| Status/delete | Route-param action, no domain fields; self deactivate/delete/reset denied by `UserPolicy`. |
| Split endpoints | `role_ids[]`, `skill_ids[]`, `team_id` (legacy alias `work_team_id` accepted), or no payload for remove team. No active trigger found; keep endpoints frozen but do not surface them automatically. |
| UI/JS | `data-user-form`, `data-user-role`, `data-role-slug`, `data-user-skills`, `data-user-team-input`, `data-password-reset-*`, generic modal, Swal. |
| Redirect | Create/update/delete/reset → `admin.users.index`; status/split actions → back; success flash. |

### A11. Service configuration and versioned fields

| Action | Contract beku |
|---|---|
| Create service | Hidden `_service_create=1`; `code`, `name`, `ticket_class`, `uses_sla`, conditional `target_working_days`, `skill_ids[]`, `description`, dan nested `fields[index][key,label,help_text,field_type,visibility,is_required,sort_order,options_text,validation_rules_text]`. |
| Update service | `name`, `description`, `ticket_class`, `uses_sla` (hidden `0` + checkbox `1`), conditional `target_working_days`; `_service_edit`, `_service_tab=detail`. |
| Skill mapping | `skill_ids[]`; `_service_edit`, `_service_tab=skills`. |
| Add/version field | `key`, `label`, `help_text`, `field_type`, `visibility`, `is_required`, `sort_order`, `options_text`, `validation_rules_text`; `_service_edit`, `_service_tab=formulir`. Version action keeps key; never overwrite historical definition. |
| Field delete | Active UI has no domain payload plus hidden editor/tab context; history must remain intact. |
| Field status | Endpoint exists, but its only Blade trigger is in dormant `_service-edit-modal`; keep frozen and unexposed. |
| Return context | Redirect helper preserves `q`, `per_page`, `page` from referer and adds `service`, `service_tab`. |
| UI/JS | `service-create-modal`, `service-view-modal-{id}`, `service-edit-modal-{id}`, `service-preview-modal-{id}`; `data-service-field-builder`, `data-field-builder`, `data-service-sla-*`, `data-skill-picker`, `data-submit-feedback`. |
| Authorization | Super Admin middleware plus catalog policies and `DomainAuthorization`; service catalog/domain service versioning authoritative. |

## B. Read/query/download contract registry — 31 entries

| ID | UI/source | Named route; method/path | Query/input and selectors | Authorization | Response/compatibility |
|---|---|---|---|---|---|
| READ-001 | Global | unnamed; GET `/` | — | Public; auth state inspected | `HomeController` redirect; do not convert to screen. |
| READ-002 | UI-022 `admin.announcements.index` | `admin.announcements.index`; GET `/admin/announcements` | — | `auth`, `active`, role Super Admin/Tier 1, AnnouncementPolicy | HTML active screen. |
| READ-003 | UI-019 `admin.audit-logs.index` | `admin.audit-logs.index`; GET `/admin/audit-logs` | `action`, `outcome ∈ {succeeded, denied}`, `actor`, `from`, `to`, `page` | Super Admin + policy | 25/page, query preserved; invalid query uses standard validation redirect. |
| READ-004 | UI-020 `admin.branding.index` | `admin.branding.index`; GET `/admin/branding` | — | Super Admin + policy | Current branding + 12 versions. |
| READ-005 | UI-016/UI-021 compatibility | `admin.catalog.index`; GET `/admin/catalog` | `section ∈ {services, forms, locations, attachments}`; plus downstream `q`, `per_page`, `page`, `service` | Super Admin + catalog authorization | Services/locations render delegated screens; forms/attachments redirect. Dormant catalog view must not activate. |
| READ-006 | UI-021 compatibility | `admin.forms.index`; GET `/admin/forms` | optional `service` | Super Admin | Redirect `admin.services.index`, preserves `service`. |
| READ-007 | UI-016 `admin.locations.index` | `admin.locations.index`; GET `/admin/locations` | `q`, `per_page ∈ {10,25,50}`, `page` | Super Admin + location policy | HTML; query preserved. |
| READ-008 | UI-021 `admin.services.index` | `admin.services.index`; GET `/admin/services` | `q`, `per_page ∈ {10,25,50}`, `page`, `service`, `service_tab` | Super Admin + catalog policies | HTML; `service`/tab auto-open editor context. |
| READ-009 | UI-018 `admin.skills.index` | `admin.skills.index`; GET `/admin/skills` | `q`, `per_page ∈ {10,25,50}`, `page` | Super Admin + SkillPolicy | HTML; query preserved. |
| READ-010 | UI-017 `admin.teams.index` | `admin.teams.index`; GET `/admin/teams` | — | Super Admin + WorkTeamPolicy | HTML; name ordering. |
| READ-011 | UI-012 `admin.users.index` | `admin.users.index`; GET `/admin/users` | `q`, `per_page ∈ {10,25,50}`, `page` | Super Admin + UserPolicy | HTML; query preserved. |
| READ-012 | UI-013 | `admin.users.create`; GET `/admin/users/create` | — | Super Admin + create policy | Standalone create screen. |
| READ-013 | UI-014 | `admin.users.edit`; GET `/admin/users/{user}/edit` | Route model `{user}` | Super Admin + update policy | Standalone edit screen/404 binding. |
| READ-014 | UI-015 | `admin.users.reset-password.edit`; GET `/admin/users/{user}/reset-password` | Route model `{user}` | Super Admin + resetPassword policy, not self | Standalone reset screen. |
| READ-015 | UI-010 | `approvals.index`; GET `/approvals` | — | Current active Approver, not Team Chair | Pending approvals only. |
| READ-016 | UI-008 attachment link | `attachments.download`; GET `/attachments/{attachment}/download` | Route model with trashed | `AttachmentAccessPolicy::view` | Authorized file; no storage URL substitution. |
| READ-017 | Layout/branding | `branding.logo`; GET `/branding/logo` | — | Public asset controller | Image/file response. |
| READ-018 | UI-002 | `dashboard`; GET `/dashboard` | `start_date`, `end_date`; aliases `from`, `to`; `data-reporting-guide-trigger` is local UI | Auth+active; server scopes each dashboard segment | HTML; validation errors on dates. |
| READ-019 | Infrastructure | `health.ready`; GET `/health/ready` | — | Public | JSON readiness, not UI. |
| READ-020 | UI-001 | `login`; GET `/login` | — | Guest middleware | Login HTML. |
| READ-021 | UI-009 | `notifications.index`; GET `/notifications` | `page` | Auth+active; own notifications | 20/page. |
| READ-022 | UI-003 | `password.change`; GET `/password/change` | — | Auth+active | Change-password HTML. |
| READ-023 | UI-011 | `reports.index`; GET `/reports` | `month`, `start_date`, `end_date`; aliases `from`, `to` | ReportExportPolicy | Primary report screen. |
| READ-024 | UI-011 alias | `reports.monthly`; GET `/reports/monthly` | Same as READ-023 | Same policy | Compatibility screen alias. |
| READ-025 | UI-011 export | `reports.monthly.excel`; GET `/reports/monthly/export/excel` | Same period query; `data-report-export` | ReportExportPolicy | XLSX download + export audit. |
| READ-026 | UI-011 export | `reports.monthly.pdf`; GET `/reports/monthly/export/pdf` | Same period query; `data-report-export` | ReportExportPolicy | PDF download + export audit. |
| READ-027 | UI-004 | `tickets.index`; GET `/tickets` | `q`, `per_page ∈ {10,25,50}`, `page`; requester-only additionally `tab`, `class`, `status`, `from`, `to` | TicketPolicy `viewAny`; role-specific query/projection | May render requester-index or tickets.index; Team Chair ignores filters and uses safe 15/page projection. |
| READ-028 | UI-006 | `tickets.all`; GET `/tickets/all` | `q`, `per_page ∈ {10,25,50}`, `page` | TicketPolicy `viewAll` | Tier 1 all-ticket view. |
| READ-029 | UI-007 | `tickets.create`; GET `/tickets/create` | `service_type_id`; old input takes precedence | TicketPolicy `create` | Catalog/form branch on same route. |
| READ-030 | UI-005 | `tickets.queue`; GET `/tickets/queue` | `tab`, `page` | Ability depends on tab | Queue/mine/assigned/completed screen. |
| READ-031 | UI-008 | `tickets.show`; GET `/tickets/{ticket}` | `{ticket}`, optional `from=all` | TicketPolicy `view`; Team Chair safe projection | Full or projected detail; 403/404 remain distinct. |

## C. Mutation/form-action registry — 81 entries

Setiap row di bawah adalah contract terpisah. Hidden markers yang tidak divalidasi tetap presentation contract karena mengarahkan old/error state ke form atau modal yang benar.

### C1. Administration contracts (`ACT-001`–`ACT-047`)

| ID | UI/source | Action; named route; method | Input, hidden, error, dan `old()` dependency | Modal / `data-*` / JS | Authorization dependency | Redirect, flash, alias/status |
|---|---|---|---|---|---|---|
| ACT-001 | UI-022 `admin.announcements.index` create | Store announcement; `admin.announcements.store`; POST | `title`, `body`, `starts_at`, `ends_at`, `is_active`; errors/old same keys | No modal; global submit | Super Admin/Tier 1 middleware + AnnouncementPolicy `create` + DomainAuthorization | Back; success “Pengumuman global berhasil dibuat.” |
| ACT-002 | UI-022 edit disclosure | Update; `admin.announcements.update`; PUT | Same fields; hidden `_announcement_id`; errors/old targeted by ID | No modal; global submit | Middleware + policy `update` | Back; success updated. |
| ACT-003 | UI-022 status form | Activate/deactivate; `admin.announcements.status`; POST | No body field; `{status}` route param constrained; no old | `data-swal-confirm` on deactivate | Middleware + policy `setStatus` | Back; success active/inactive. |
| ACT-004 | UI-020 `admin.branding.index` | Update branding; `admin.branding.update`; PUT multipart | `organization_name`, `application_name`, `tagline`, `footer_text`, `logo`, `remove_logo`; errors/old same; file never repopulated | `data-branding-form`, `data-branding-field`, `data-branding-logo-input`, preview/remove/submit selectors | Super Admin + BrandingPolicy/DomainAuthorization | Back; success includes new version number. |
| ACT-005 | No active trigger; dormant attachment section only | Create attachment policy; `admin.catalog.attachment-policies.store`; POST | `service_type_id`, `type_key`, `label`, `max_file_size_kb`, `max_file_count`, `allowed_mimes_text`, `allowed_extensions_text`, `visibility`, `is_active` | No active modal/handler | Super Admin + AttachmentPolicy policy/domain service | Back; success. **Do not activate from redesign.** |
| ACT-006 | No active trigger | Update attachment policy; `admin.catalog.attachment-policies.update`; PUT | Same payload/errors as ACT-005 | None active | Same policy/domain service | Back; success. **Do not activate.** |
| ACT-007 | No active trigger | Policy status; `admin.catalog.attachment-policies.status`; POST | `{status}` is `activate` or `deactivate`; no body | None active | Same policy/domain service | Back; success. **Do not activate.** |
| ACT-008 | UI-016 `_building-form` | Create building; `admin.catalog.buildings.store`; POST | `name`; hidden `_location_form=building-create`; error/old `name` gated by marker | `location-building-create-modal`; generic `data-ui-modal-*` | Super Admin + BuildingPolicy/domain service | Back; success; validation reopens create modal. |
| ACT-009 | UI-016 `_building-form` | Update building; `admin.catalog.buildings.update`; PUT | `name`; `_location_form=building-edit-{id}`; error/old targeted | `location-building-edit-modal-{id}`; generic modal | Same | Back; success. |
| ACT-010 | UI-016 `_floor-form` | Create floor; `admin.catalog.floors.store`; POST | `name`, `sort_order`; `_location_form=floor-create-{buildingId}`; errors/old same | `location-floor-create-modal-{buildingId}`; generic modal | Super Admin + FloorPolicy/domain service; parent building binding | Back; success. |
| ACT-011 | UI-016 row form | Building status; `admin.catalog.buildings.status`; POST | `{status}` route param; no domain input | `data-swal-confirm` on deactivate | BuildingPolicy/domain invariant for active children | Back; success or validation/domain error. |
| ACT-012 | UI-021 field editor | Remove field; `admin.catalog.fields.destroy`; DELETE | No domain payload; hidden `_service_edit`, `_service_tab=formulir` for context | In `service-edit-modal-{serviceId}`; `data-swal-confirm`, `data-submit-feedback` | Super Admin + ServiceFieldDefinition policy/catalog service | Redirect service editor preserving list query; success; history retained. |
| ACT-013 | UI-021 `admin.forms._field-form` | Create field version; `admin.catalog.fields.versions.store`; POST | `key` hidden/current; `label`, `help_text`, `field_type`, `visibility`, `is_required`, `sort_order`, `options_text`, `validation_rules_text`; editor/tab markers; errors/old same | Service edit modal; `data-field-builder`, type/options selectors, submit feedback | Catalog field update/version policy/domain service | Redirect editor tab `formulir`; success. |
| ACT-014 | No active trigger; dormant catalog modal only | Activate/deactivate field; `admin.catalog.fields.status`; POST | `{status}`; legacy hidden `_service_edit`, `_service_tab=formulir` | No active modal/handler | Catalog field policy/domain service | Redirect editor `formulir`; active/inactive success. **Do not activate from redesign.** |
| ACT-015 | UI-016 `_floor-form` | Update floor; `admin.catalog.floors.update`; PUT | `name`, `sort_order`; `_location_form=floor-edit-{id}` | `location-floor-edit-modal-{id}`; generic modal | Super Admin + FloorPolicy/domain service | Back; success. |
| ACT-016 | No active trigger | Create room; `admin.catalog.rooms.store`; POST | `name`; Form Request errors `name` | No active modal/JS | Super Admin + RoomPolicy/domain service; parent floor | Back; success. **Do not activate.** |
| ACT-017 | UI-016 row form | Floor status; `admin.catalog.floors.status`; POST | `{status}`; no domain input | `data-swal-confirm` on deactivate | FloorPolicy/domain invariant | Back; success/error. |
| ACT-018 | No active trigger | Update room; `admin.catalog.rooms.update`; PUT | `name` | None active | Super Admin + RoomPolicy/domain service | Back; success. **Do not activate.** |
| ACT-019 | No active trigger | Room status; `admin.catalog.rooms.status`; POST | `{status}` | None active | Same | Back; success. **Do not activate.** |
| ACT-020 | UI-021 service editor | Update service; `admin.catalog.services.update`; PUT | `name`, `description`, `ticket_class`, `uses_sla` hidden/checkbox, `target_working_days`; `_service_edit`, `_service_tab=detail`; field errors/old | `service-edit-modal-{id}`; `data-service-sla-form/toggle/target`, submit feedback | Super Admin + ServiceType update policy/catalog service | Redirect `admin.services.index` preserving `q/per_page/page`, adding `service` + selected tab; success. |
| ACT-021 | UI-021 field editor | Add field; `admin.catalog.fields.store`; POST | `key`, `label`, `help_text`, `field_type`, `visibility`, `is_required`, `sort_order`, `options_text`, `validation_rules_text`; editor/tab markers | Service edit modal; `data-field-builder`; submit feedback | Super Admin + field create policy/catalog service | Redirect editor `formulir`; success. |
| ACT-022 | UI-021 skills tab | Update service skills; `admin.catalog.services.skills.update`; PUT | `skill_ids[]`; hidden `_service_edit`, `_service_tab=skills`; errors/old `skill_ids` | `service-edit-modal-{id}`; `data-skill-picker`, summary, submit feedback | Super Admin + service update/skill mapping policy | Redirect editor `skills`; success. |
| ACT-023 | UI-021 service status | Activate/deactivate service; `admin.catalog.services.status`; POST | `{status}`; no domain input | In edit modal; `data-swal-confirm` on deactivate, submit feedback | Super Admin + ServiceType status policy/catalog service | Redirect editor, success. |
| ACT-024 | No active trigger | Replace approver; `admin.operational-policies.approver.update`; PUT | `replacement_user_id`, accepted `transfer_pending_approvals`, required `reason`; errors/old same | No active form/handler | Super Admin + operational policy/domain service; target must satisfy approver rules | Back; success and pending approvals transferred. **Do not activate.** |
| ACT-025 | No active trigger | Update calendar; `admin.operational-policies.calendar.update`; PUT | `timezone=Asia/Jakarta`, `working_days[]`, `opens_at`, `closes_at`, `holidays_text` | None active | Super Admin + operational policy/service | Back; success. **Do not activate.** |
| ACT-026 | No active trigger | Update operational settings; `admin.operational-policies.settings.update`; PUT | `sla_warning_percent`, `requester_wait_working_days`, `confirmation_wait_working_days`, `reopen_window_working_days`, `max_reopen_count` | None active | Super Admin + operational policy/service | Back; success. **Do not activate.** |
| ACT-027 | UI-021 `_create-modal` | Create service; `admin.services.store`; POST | Hidden `_service_create=1`; `code`, `name`, `ticket_class`, `uses_sla`, conditional `target_working_days`, `skill_ids[]`, `description`, nested `fields[index][...]`; nested errors/old | `service-create-modal`; `data-service-field-builder`, row template, SLA and skill picker, submit feedback | Super Admin + create policies/catalog service | Redirect services with `service={newId}`; success includes code. |
| ACT-028 | UI-018 create modal | Create skill; `admin.skills.store`; POST | Active UI: `name`, `description`; hidden `_skill_form=create`; Form Request also accepts optional `slug`; errors/old same | `skill-create-modal`; generic modal/submit | Super Admin + SkillPolicy/domain service | Back; success; modal reopens on error. |
| ACT-029 | UI-018 edit modal | Update skill; `admin.skills.update`; PUT | Active UI: `name`, `description`; `_skill_form=edit-{id}`, `_skill_edit={id}`; Form Request also accepts optional `slug` | `skill-edit-modal-{id}`; generic modal | Same | Back; success. |
| ACT-030 | UI-018 row action | Soft-delete skill; `admin.skills.destroy`; DELETE | No body | `data-swal-confirm` | SkillPolicy delete/domain service; mapping history retained | Back; success. |
| ACT-031 | UI-018 row action | Activate skill; `admin.skills.activate`; POST | No body | Global submit | SkillPolicy status/domain service | Back; success. |
| ACT-032 | UI-018 row action | Deactivate skill; `admin.skills.deactivate`; POST | No body | `data-swal-confirm` | Same | Back; success. |
| ACT-033 | UI-017 create modal | Create team; `admin.teams.store`; POST | `name`, `description`; hidden `_team_create=1`; errors/old same | `team-create-modal`; `data-team-create-*` bespoke handler | Super Admin + WorkTeamPolicy/domain service | Back; success; modal auto-opens on marker/error. |
| ACT-034 | UI-017 edit modal | Update team; `admin.teams.update`; PUT | `name`, `description`; hidden `_team_edit={id}` | `team-edit-modal-{id}`; generic modal | Same | Back; success. |
| ACT-035 | Endpoint active; verify trigger before redesign | Soft-delete team; `admin.teams.destroy`; DELETE | No body | No guaranteed active trigger in audited screen state | WorkTeamPolicy/domain constraints | Back; success. Do not add control casually. |
| ACT-036 | Endpoint active; verify trigger before redesign | Activate team; `admin.teams.activate`; POST | No body | No guaranteed active trigger | Same | Back; success. |
| ACT-037 | Endpoint active; verify trigger before redesign | Deactivate team; `admin.teams.deactivate`; POST | No body | No guaranteed active trigger | Same; member/chair invariants | Back; success/error. |
| ACT-038 | UI-012 modal / UI-013 page | Create user; `admin.users.store`; POST | `name`, `username`, `email`, `nip`, `temporary_password`, `temporary_password_confirmation`, `role_ids[]`, `team_id`, `team_position`, `skill_ids[]`; `_user_form`; errors/old except passwords | `user-create-modal` or page; `data-user-form/role/skills/team-*`, generic modal | Super Admin + UserPolicy create; user/team/role/skill domain service | Redirect `admin.users.index`; success with secure-delivery reminder. |
| ACT-039 | UI-012 modal / UI-014 page | Update user; `admin.users.update`; PUT | `name`, `username`, `email`, `nip`, `role_ids[]`, `team_id`, `team_position`, `skill_ids[]`, `is_active`; hidden `_user_form`, `_user_edit` | `user-edit-modal-{id}` or page; user dependency JS | Super Admin + UserPolicy update; self/status constraints in policy/service | Redirect user index; success. |
| ACT-040 | UI-012 row action | Delete user; `admin.users.destroy`; DELETE | No body | `data-swal-confirm` | UserPolicy delete; cannot delete self; domain service | Redirect user index; success. |
| ACT-041 | UI-014 and endpoint action | Activate user; `admin.users.activate`; POST | No body | Standard submit | UserPolicy status/domain service | Back; success. |
| ACT-042 | UI-014 and index context | Deactivate user; `admin.users.deactivate`; POST | No body | `data-swal-confirm` | UserPolicy status; cannot deactivate self | Back; success. |
| ACT-043 | UI-012 password modal / UI-015 page | Reset password; `admin.users.reset-password`; PUT | `temporary_password`, `temporary_password_confirmation`, accepted `confirm_reset`; modal adds hidden `reset_user_id` and hidden confirmation mirror | `password-reset-modal`; `data-password-reset-*` or standalone form | UserPolicy resetPassword; cannot reset self | Redirect user index; success. |
| ACT-044 | No active split-form trigger | Update roles; `admin.users.roles.update`; PUT | `role_ids[]`; errors/old same | None active | UserPolicy updateRoles/domain role sync | Back; success. **Keep but do not activate automatically.** |
| ACT-045 | No active split-form trigger | Update skills; `admin.users.skills.update`; PUT | `skill_ids[]` | None active | UserPolicy updateSkills/domain sync | Back; success. |
| ACT-046 | No active split-form trigger | Assign team; `admin.users.team.update`; PUT | Primary `team_id`; compatibility input `work_team_id` normalized; service assigns `TeamPosition::Member` | None active | UserPolicy updateTeam/domain team invariant | Back; success. |
| ACT-047 | No active split-form trigger | Remove team; `admin.users.team.remove`; DELETE | No body | None active | UserPolicy removeTeam/domain invariant | Back; success. |

### C2. Authentication, approval, notification, dan password (`ACT-048`–`ACT-054`)

| ID | UI/source | Action; named route; method | Input, hidden, error, dan `old()` dependency | Modal / `data-*` / JS | Authorization dependency | Redirect, flash, alias/status |
|---|---|---|---|---|---|---|
| ACT-048 | UI-010 atau UI-008 `x-approval-panel` | Approve; `approvals.approve`; POST | No domain input; CSRF only. Current component does **not** send `_action_modal`. | Index inline; detail modal `ticket-approval-decision-modal`; `data-approval-decision-form`, `data-approval-submit` | `ApprovalRequestPolicy::decide`: current active Approver, pending request assigned to actor, not Team Chair | Redirect `tickets.show`; success with ticket number. |
| ACT-049 | UI-010 atau UI-008 `x-approval-panel` | Reject approval; `approvals.reject`; POST | Primary `decision_note`; compatibility `note`/`reason`; error `decision_note`. Current forms neither repopulate `old('decision_note')` nor send `_action_modal`. | Same handler/modal as ACT-048 | Same policy; ApprovalDecisionRequest + approval service | Redirect `tickets.show` on success; validation returns previous page. Existing missing modal marker/old repopulation is a documented baseline gap, not an implicit redesign task. |
| ACT-050 | UI-001 `auth.login` | Login; `login.store`; POST | `username`, `password`; backend accepts optional `remember`, active UI does not render it; old only `username`; auth/rate errors use `username` | No modal; global submit/flash | Guest middleware; LoginRequest; rate limit 5; active account credential check | Success intended redirect, default dashboard; failure back + username error; session regenerated. |
| ACT-051 | `layouts.app` desktop/mobile | Logout; `logout`; POST | No domain input | Global submit/navigation | Auth middleware | Session invalidated + token regenerated; redirect login + success. |
| ACT-052 | UI-009 | Mark all read; `notifications.read-all`; POST | No domain input | Standard form/global loading | Auth+active; only current user's unread relation | Back + success. |
| ACT-053 | Layout dropdown / UI-009 | Mark one read; `notifications.read`; POST | `{notification}` string route param; no body | Standard form/global loading | Lookup constrained to current user's notifications; foreign/missing ID logs denied and returns 404 | Redirect ticket detail when payload contains `ticket_id`, otherwise notifications; no success flash. |
| ACT-054 | UI-003 | Change password; `password.update`; PUT | `current_password`, `password`, `password_confirmation`; errors same; password values never repopulated | No modal; global submit | Auth+active; current-password and strong password Form Request | Redirect dashboard + success; password hash/audit locked. |

### C3. Ticket and workflow contracts (`ACT-055`–`ACT-081`)

| ID | UI/source | Action; named route; method | Input, hidden, error, dan `old()` dependency | Modal / `data-*` / JS | Authorization/policy dependency | Redirect, flash, alias/status |
|---|---|---|---|---|---|---|
| ACT-055 | UI-007 `_form`/dynamic-field | Create ticket; `tickets.store`; POST multipart | `requester_id`, `service_type_id`, `subject`, `description`, `priority`, `floor_id`, `fields[...]`, `attachments[policyId][]`; errors/nested old as A1 | No modal; `data-ticket-form`, field/input and submit selectors | TicketPolicy create/self/for-other; StoreTicketRequest; creation/location/attachment/dynamic-field services | Redirect show + success. |
| ACT-056 | UI-008 `_action-modals` | Assign Tier 2; `tickets.assign-tier-2`; POST | Required `assigned_to_id`, optional `reason`; `_action_modal=ticket-assign-tier-two-modal`; errors/old same | Modal `ticket-assign-tier-two-modal`; `data-ticket-assignment-form/submit/...` | TicketPolicy `assignTierTwo`; assigned Tier 1, Dikerjakan; workflow/skill constraints | Redirect show + success. |
| ACT-057 | UI-008 ticket attachment panels | Upload ticket attachment; `tickets.attachments.store`; POST multipart | Required nested `attachments[policyId][]`; errors `attachments` and nested keys; file old unavailable | No modal; `data-ticket-attachment-form`, `data-ticket-attachment-submit` | TicketPolicy `uploadAttachment`; assigned agent, allowed state; attachment policy service | Redirect show + success. |
| ACT-058 | UI-008 action menu | Cancel ticket; `tickets.cancel`; POST | No domain input | `data-swal-confirm`; no modal | TicketPolicy `cancel`: Pemohon owner, Baru, not Team Chair | Redirect show + success; state Dibatalkan. |
| ACT-059 | No active trigger found | Claim queue ticket; `tickets.claim`; POST | No domain input; concurrency failure error key `ticket` | No active modal/selector | TicketPolicy `claim` + workflow new/unassigned guard | Back; success or `ticket` error. Endpoint frozen; do not activate by redesign. |
| ACT-060 | UI-008 inline or modal | Internal note; `tickets.comments.internal`; POST multipart | `body`, `attachments[policyId][]`; modal variant adds `_action_modal=ticket-internal-comment-modal`; errors `body`, attachment keys; old `body` | Inline panel or `ticket-internal-comment-modal`; `data-ticket-communication-form/submit` | TicketPolicy `commentInternal`; internal visibility; pending Approver or assigned agent states; Team Chair denied | Back + success; internal audit/notification visibility locked. |
| ACT-061 | UI-008 inline reply | Public comment; `tickets.comments.public`; POST multipart | `body`, `attachments[policyId][]`; errors/old same; no modal marker | `data-ticket-communication-form/submit` | TicketPolicy `commentPublic`; public visibility; owner/agent/pending Approver rules | Back + success. |
| ACT-062 | UI-008 completion modal | Complete; `tickets.complete`; POST, multipart for SVC-02 | `solution`; conditional `data_export_result`; `_action_modal=ticket-complete-modal`; errors/old `solution`, file error | `ticket-complete-modal`; `data-ticket-resolution-form/submit` | TicketPolicy `complete`; assigned agent, Dikerjakan; SVC-02/SVC-03 special readiness and resolution service | Redirect show + success; state Menunggu Konfirmasi. Primary for ACT-077 alias. |
| ACT-063 | UI-008 confirmation modal | Confirm result; `tickets.confirm`; POST | No domain input; `_action_modal=ticket-confirmation-modal` | `ticket-confirmation-modal`; `data-swal-confirm` | TicketPolicy `confirm`: active Pemohon owner, Menunggu Konfirmasi, not Team Chair | Redirect show + success; Ditutup. Primary for ACT-075 alias. |
| ACT-064 | Compatibility only; no primary active form | Not satisfied alias; `tickets.confirmation.not-satisfied`; POST | Required `reason`; optional modal marker if legacy caller sends it; error/old `reason` | No active form points to alias | TicketPolicy `notSatisfied`; same service as ACT-069 | Redirect show + success; compatibility alias for ACT-069. |
| ACT-065 | UI-008 SVC-03 modal | Start DB change execution; `tickets.database-change.execute`; POST | No validated domain input; `_action_modal=ticket-database-change-modal`; domain readiness error `database_change` | `ticket-database-change-modal`; `data-ticket-database-change-form`, `data-swal-confirm` | TicketPolicy `startDatabaseChange`; assigned agent, SVC-03, Dikerjakan; three evidence/service readiness | Redirect show + success; execution actor/time/history recorded. |
| ACT-066 | UI-008 same SVC-03 modal | Verify DB change; `tickets.database-change.verify`; POST | Primary `verification_result`, `verification_notes`; compatibility `result`, `notes`; marker `_action_modal`; errors/old all accepted keys, UI uses primary | Same modal/handler | TicketPolicy `verifyDatabaseChange`; assigned agent, SVC-03, Dikerjakan; special-control sequencing | Redirect show + success. |
| ACT-067 | No active trigger found | Mark handled; `tickets.handle`; POST | No domain input; failure error key `ticket` | No active modal/selector | TicketPolicy `handle`; assigned Tier 1/2; workflow concurrency | Back; success or `ticket` error. Endpoint frozen; do not activate. |
| ACT-068 | UI-008 internal fields | Update SVC-07 fields; `tickets.internal-fields.update`; PUT | Primary `internal_fields[...]`, `internal_field_versions[...]`; compatibility `fields`, `field_versions`; errors/old nested | No modal; `data-ticket-internal-fields-form/submit/...` | TicketPolicy `updateInternalFields`; assigned agent, SVC-07, allowed status; versioned-field service | Redirect show + success. |
| ACT-069 | UI-008 confirmation modal | Not satisfied; `tickets.not-satisfied`; POST | Required `reason`; `_action_modal=ticket-confirmation-modal`; error/old `reason` | Same confirmation modal; `data-ticket-resolution-form/submit` | TicketPolicy `notSatisfied` | Redirect show + success; back to Dikerjakan; primary for ACT-064. |
| ACT-070 | UI-008 priority modal | Change priority; `tickets.priority.update`; PUT | Required `priority`, `reason`; `_action_modal=ticket-priority-modal`; errors/old same | `ticket-priority-modal`; `data-ticket-priority-form/submit` | TicketPolicy `changePriority`: Tier 1, new unassigned queue ticket | Redirect show + success; priority history/audit locked. |
| ACT-071 | UI-008 reject modal | Reject ticket; `tickets.reject`; POST | Required `reason`; `_action_modal=ticket-reject-modal`; error/old same | `ticket-reject-modal`; `data-ticket-reject-form/submit` | TicketPolicy `reject`: Tier 1, new unassigned queue ticket | Redirect show + success; state Ditolak. |
| ACT-072 | UI-008 reopen modal | Reopen; `tickets.reopen`; POST | Optional `reason`; `_action_modal=ticket-reopen-modal`; error/old `reason` | `ticket-reopen-modal`; `data-ticket-resolution-form/submit` | TicketPolicy `reopen`: active Pemohon owner, Ditutup; window/count service rules | Redirect show + success; Dikerjakan + new SLA cycle. |
| ACT-073 | UI-008 request-approval modal | Request approval; `tickets.request-approval`; POST | Optional `reason`; compatibility `note`; `_action_modal=ticket-request-approval-modal`; error/old reason | `ticket-request-approval-modal`; `data-approval-request-form/submit` | TicketPolicy `requestApproval`; assigned Tier 1/2, Diproses/Dikerjakan; active approver service | Redirect show + success; Menunggu Persetujuan. |
| ACT-074 | UI-008 inline and modal request-information | Request information; `tickets.request-information`; POST | Required `body`; Form Request accepts optional `attachments[policyId][]`; modal adds `_action_modal=ticket-request-information-modal`; current active forms render no attachment inputs | Inline or modal; `data-ticket-communication-form/submit` | TicketPolicy `requestInformation`; assigned agent, Diproses/Dikerjakan; waiting service | Back + success; Menunggu Pemohon. |
| ACT-075 | Compatibility only | Confirm alias; `tickets.requester-confirm`; POST | Same as ACT-063 | No active form points to alias | Same policy/service | Redirect show + success; alias of ACT-063. |
| ACT-076 | UI-008 requester reply form | Requester reply; `tickets.requester-reply`; POST multipart | `body`, `attachments[policyId][]`; errors/old `body` + attachments | Inline; `data-ticket-communication-form/submit` | TicketPolicy `replyRequester`: active owner Pemohon, not closed, not Team Chair; waiting service | Back + success. |
| ACT-077 | Compatibility only | Complete alias; `tickets.resolve`; POST | Same `solution`/conditional `data_export_result` as ACT-062; legacy caller may send marker | No active form points to alias | Same policy/service as complete | Redirect show + success; alias of ACT-062. |
| ACT-078 | UI-008 third-party modal | Resume third-party wait; `tickets.resume-third-party`; POST | Optional `reason`; `_action_modal=ticket-third-party-modal`; error/old `reason` | `ticket-third-party-modal`; `data-ticket-communication-form/submit` | TicketPolicy `resumeThirdPartyWait`; assigned agent, Menunggu Pihak Ketiga | Back + success; Dikerjakan/SLA resume service semantics. |
| ACT-079 | UI-008 return modal | Return to Tier 1; `tickets.return-to-tier-1`; POST | Required `reason`; `_action_modal=ticket-return-tier-one-modal`; error/old same | `ticket-return-tier-one-modal`; `data-ticket-return-form/submit` | TicketPolicy `returnToTierOne`; assigned Tier 2, Dikerjakan, last triager exists | Redirect show + success. |
| ACT-080 | UI-008 triage modal | Triage; `tickets.triage`; POST | Required `outcome`; optional `problem_category_id`, `priority`, `category_reason`, `priority_reason`, `assigned_to_id`, `rejection_reason`; marker `_action_modal=ticket-triage-modal`; errors/old same. Current UI only sends outcome `self` atau `tier_2` dan assignee. | `ticket-triage-modal`; `data-ticket-triage-form/outcome/panel/assignee/suggestion/submit-*` | TicketPolicy `triage`; Tier 1/state checks; TriageTicketRequest; workflow service | Redirect show + success. Do not expose `reject` or extra fields merely because backend accepts them. |
| ACT-081 | UI-008 third-party modal | Start third-party wait; `tickets.wait-third-party`; POST | Required `third_party_name`; optional `follow_up_date`, `note`; `_action_modal=ticket-third-party-modal`; errors/old same | `ticket-third-party-modal`; `data-ticket-communication-form/submit` | TicketPolicy `startThirdPartyWait`; assigned agent, Diproses/Dikerjakan | Back + success; Menunggu Pihak Ketiga/SLA pause semantics. |

## D. Modal ID and selector freeze

### Ticket modal IDs

1. `ticket-priority-modal`
2. `ticket-reject-modal`
3. `ticket-internal-comment-modal`
4. `ticket-request-information-modal`
5. `ticket-database-change-modal`
6. `ticket-complete-modal`
7. `ticket-confirmation-modal`
8. `ticket-reopen-modal`
9. `ticket-approval-decision-modal`
10. `ticket-request-approval-modal`
11. `ticket-third-party-modal`
12. `ticket-triage-modal`
13. `ticket-assign-tier-two-modal`
14. `ticket-return-tier-one-modal`

### Shared/modal selectors

- `data-ui-modal`, `data-ui-modal-open`, `data-ui-modal-close`, `data-ui-modal-focus`, `data-ui-modal-form`, `data-ui-modal-submit`, `data-ui-modal-label`, `data-ui-modal-loading`, `data-ui-validation-error`, `data-auto-open`, `data-reset-on-close`, `data-clear-on-close`;
- `data-swal-confirm`, `data-swal-flash`;
- `data-global-loading-overlay`, `data-global-loading-message`;
- `data-submit-feedback`, `data-submit-button`, `data-submit-label`, `data-submit-loading`.

### Screen-specific selector families

- Ticket: `data-ticket-*`, `data-approval-*`;
- User: `data-user-*`, `data-password-reset-*`;
- Service/field: `data-service-*`, `data-field-*`, `data-skill-picker*`;
- Team/location/skill generic modal markers and `data-team-create-*`;
- Report: `data-report-filter`, `data-report-export` and related loading/error targets;
- Branding: `data-branding-*`;
- Navigation: `data-sidebar*`, `data-notification-menu`, `data-livewire-navigation`.

Selector dapat dimigrasikan hanya mengikuti pengecualian terkontrol pada guardrails dan harus diperbarui atomik bersama seluruh consumer JavaScript/Blade.

## E. Known baseline asymmetries — freeze, do not silently “fix”

| Area | Implementasi aktual | Implikasi redesign |
|---|---|---|
| Approval reject in ticket modal | `x-approval-panel` tidak mengirim `_action_modal` dan textarea tidak memakai `old('decision_note')`, walau wrapper modal memiliki auto-open expression. | Catat sebagai issue terpisah; jangan mengubah workflow atau menyelipkan fix tanpa scope/acceptance test. |
| Pending Approver attachment check | `AttachmentAccessPolicy` memeriksa pending request assigned + role Approver, tetapi tidak memeriksa current active `ApproverAssignment`; ticket view/decision/report melakukan current-assignment check. | Jangan menyamakan helper authorization ini di frontend; catat sebagai server-policy asymmetry terpisah. |
| Request-information attachment | Controller/Form Request menerima nested attachments, tetapi inline dan modal aktif hanya merender `body`. | Jangan menambah file input hanya karena backend menerima payload. |
| Triage payload | Form Request menerima kategori/priority/reject fields; active modal hanya memberi `self`, `tier_2`, dan assignee. | Jangan memperluas product behavior melalui redesign. |
| SVC-03 verification requiredness | Active Blade memberi HTML `required` pada `verification_result` dan `verification_notes`; Form Request mendeklarasikan primary/legacy fields nullable, sedangkan sequencing/readiness tetap diperiksa service. | Pertahankan UI contract yang terlihat; perubahan requiredness adalah perubahan behavior dan harus menjadi scope terpisah. |
| Login remember | Backend menerima `remember`; login screen tidak menampilkan checkbox. | Jangan menambah fitur “ingat saya” tanpa keputusan authentication. |
| Claim/handle | Endpoint/controller/policy aktif; tidak ada trigger aktif di Blade. | Jangan membuat tombol baru sebagai bagian kosmetik. |
| Rooms, attachment policy, operational policy | Mutation routes aktif; tidak ada screen trigger aktif. | Freeze endpoint; do not activate. |
| User split endpoints | Backend aktif, tetapi main create/update form menyimpan komposisi sekaligus. | Jangan memecah UX menjadi mutation baru tanpa analisis transaksi/domain. |

## F. Regression assertions minimum untuk contract matrix

- Setiap READ contract mengembalikan status/redirect/response type yang sama untuk authorized dan unauthorized actor.
- Setiap ACT contract mengirim nama input, nesting, method, route, CSRF, dan hidden context yang sama.
- Validation failure mempertahankan old input dan membuka kembali form/modal yang memang bekerja pada baseline.
- Compatibility aliases menghasilkan side effect, redirect, dan flash yang sama dengan primary action.
- Semua ticket transition diuji dengan status sebelum/sesudah, assignee, SLA segment, audit log, notification, dan concurrency case.
- Attachment tests mencakup requester/public/internal, Super Admin, Tier 1, assigned/unassigned Tier 2, pending/non-pending Approver, dan Team Chair denial.
- Report test mencakup primary/alias period params, policy, filename/header, response MIME, dan audit export.
- Pagination/filter tests memastikan query tetap pada page links dan reset behavior tidak mengubah scope.
