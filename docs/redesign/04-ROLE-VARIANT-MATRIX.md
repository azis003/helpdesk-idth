# Role Variant Matrix

Matrix ini memetakan behavior aktual dari route, controller, policy, service, safe projection, dan Blade. Ia tidak menyimpulkan kewenangan dari nama role.

## Sumber authoritative

| Concern | Sumber utama |
|---|---|
| Role value/label | `App\Enums\Role` |
| Route-level access | `routes/web.php` middleware `guest`, `auth`, `active`, `role:*` |
| Ticket read/write | `App\Policies\TicketPolicy` + `DomainAuthorization` + workflow services |
| Ketua Tim scope/data | `TeamScopeService`, `TeamChairTicketProjection`, `TeamChairTicketView`, `TeamChairCommentView` |
| Attachment | `AttachmentAccessPolicy` + attachment service/policies |
| Approval | `ApprovalRequestPolicy`, `ApproverAssignment`, `ApproverAssignmentService`, approval workflow |
| Report | `ReportExportPolicy` + report service |
| Administration | Resource-specific policies + Super Admin middleware |
| Announcement | `AnnouncementPolicy` + `role:super_admin,agen_tier_1` middleware |
| Actual control visibility | Policy booleans yang dihitung controller dan branch Blade |

## Aturan precedence dan multi-role

1. Role dapat dikombinasikan; sebagian ability bersifat additive.
2. `Ketua Tim Kerja` adalah explicit **operational ticket read-only override** pada `TicketPolicy`, `ApprovalRequestPolicy`, `ReportExportPolicy`, dan `AttachmentAccessPolicy`.
3. Override tersebut tidak secara otomatis diterapkan oleh semua admin policy. Akun `Ketua Tim Kerja + Super Admin` masih dapat lolos admin policies karena resource policies hanya memeriksa Super Admin. Ini behavior implementasi aktual yang harus diuji, bukan diasumsikan dari label “read-only”.
4. Pure Super Admin bukan agent operasional: ia tidak lolos `TicketPolicy::viewAny`, create, queue, all, atau action agent. Namun `TicketPolicy::view` mengizinkan direct view semua tiket dan `AttachmentAccessPolicy` memberinya akses sesuai attachment rules.
5. Role Approver saja tidak cukup. Ability approval/report/ticket pending memerlukan **current active approver assignment**.
6. Navigation bukan sumber authorization. Contoh: Super Admin layout menampilkan branch administrasi dan tidak menampilkan ticket navigation, meskipun direct ticket detail bisa lolos policy.

## Jumlah primary screen variants

Ada **15 primary role-driven variants**:

- UI-002 Dashboard: 6 variants;
- UI-004 Ticket list: 3 variants;
- UI-008 Ticket detail: 6 variants.

Action visibility pada screen lain dicatat dalam matrix tetapi tidak dihitung sebagai structural variant terpisah. Kombinasi multi-role juga tidak dihitung secara kombinatorial.

## A. Ringkasan role aktual

| Role | Workspace/data utama | Write utama | Restriction penting | Authoritative source |
|---|---|---|---|---|
| Super Admin | Dashboard admin/generic, seluruh admin master/config/audit, report; direct full ticket detail bila URL/record diketahui | User/team/skill/location/service/branding/config/announcement sesuai policy; bukan ticket workflow secara default | Pure role tidak dapat membuka ticket index/queue/all/create. Direct ticket view tidak memberi action operasional. Tidak boleh status/delete/reset dirinya sendiri. | Admin middleware/policies, UserPolicy, TicketPolicy, ReportExportPolicy |
| Pemohon | Dashboard requester, daftar tiket sendiri, create, own/created ticket detail, notifications | Create self; cancel own Baru; requester/public reply sesuai state; confirm/not-satisfied; reopen | Tidak melihat internal comments/fields/attachments; download hanya requester-accessible milik/created ticket; tidak melihat ticket orang lain | TicketPolicy, RequesterTicketList, AttachmentAccessPolicy |
| Agen Tier 1 | Helpdesk dashboard, scoped “tiket saya”, queue/mine/assigned/completed, all tickets, create for other, full ticket detail, reports, announcements | Triage/priority/reject new queue; assignment Tier 2; assigned-agent workflow; public/internal communication; upload; request approval; complete | Operation tetap state/assignee-dependent; role tidak memberi admin resource access kecuali juga Super Admin | TicketPolicy, workflow services, ReportExportPolicy, AnnouncementPolicy |
| Agen Tier 2 | Helpdesk dashboard, tickets assigned to self, mine/completed queue tabs, full assigned ticket detail | Assigned-agent workflow, return Tier 1, comments, request info/approval, third-party wait, completion, attachment, service-specific controls | Tidak melihat unassigned queue/all tickets; download/internal data hanya pada assigned ticket; tidak triage/priority/reject | TicketPolicy, AttachmentAccessPolicy, workflow services |
| Approver | Bila current: approval dashboard/list, pending ticket detail, reports; notifications | Approve/reject pending request; public/internal comment on pending ticket | Pure role tidak dapat ticket index/create/queue/all. Non-current Approver kehilangan approval/report/pending-ticket abilities. Team Chair override menolak. | ApprovalRequestPolicy, TicketPolicy current/pending checks, ReportExportPolicy |
| Ketua Tim Kerja | Dashboard team dan ticket list/detail dengan safe projection dalam team scope | Tidak ada ticket/approval/report mutation; no attachment download | Safe projection only; public comments only; no description, identifiers, location, internal data, audit snapshots, or attachments. Admin/announcement rights may still arise from an additional role because those policies lack this override. | TeamChairTicketProjection, TeamScopeService, TicketPolicy, AttachmentAccessPolicy |

## B. Screen visibility matrix untuk pure-role profiles

Legend: **V** = visible/authorized; **C** = conditional on assignment/ownership/current assignment/record scope; **H** = hidden or server-denied. “Pure role” berarti akun hanya memiliki role di kolom tersebut dan aktif.

| Screen/group | Super Admin | Pemohon | Agen T1 | Agen T2 | Approver | Ketua Tim | Authoritative rule / variant |
|---|---:|---:|---:|---:|---:|---:|---|
| UI-001 Login | H saat authenticated | H | H | H | H | H | `guest`; tersedia saat logged out. |
| UI-002 Dashboard | V | V | V | V | V | V | Auth+active; section dari DashboardService/Blade. Approver operational block hanya jika current. |
| UI-003 Password | V | V | V | V | V | V | Auth+active; read/write own password. |
| UI-004 `/tickets` | H | V | V | V | H | V | `TicketPolicy::viewAny`; role-specific view/query/projection. |
| UI-005 `/tickets/queue` | H | H | V | C | H | H | T1 all permitted tabs; T2 defaults `mine` and may use completed; ability per tab. |
| UI-006 `/tickets/all` | H | H | V | H | H | H | `TicketPolicy::viewAll`. |
| UI-007 Create ticket | H | V | V | H | H | H | `TicketPolicy::create`; T1 for other, Pemohon self. |
| UI-008 Ticket detail | V direct | C own/created | V all | C assigned | C pending assigned approval and current | C team scope, safe | `TicketPolicy::view`; Team Chair projection branch first. |
| UI-009 Notifications | V | V | V | V | V | V | Current user's notifications only. |
| UI-010 Approvals | H | H | H | H | C current | H | `ApprovalRequestPolicy::viewAny`; Team Chair override. |
| UI-011 Reports/export | V | H | V | H | C current | H | `ReportExportPolicy`; Team Chair override. |
| UI-012–021 Admin screens | V | H | H | H | H | H | `role:super_admin` + resource policies. |
| UI-022 Announcements management | V | H | V | H | H | H | Role middleware + AnnouncementPolicy. |
| UI-023 403 | C | C | C | C | C | C | Rendered whenever authorization exception produces 403. |

## C. Primary variant detail

### UI-002 Dashboard — 6 variants

| Variant | Visible sections/actions | Data visibility | Read/write | Notes |
|---|---|---|---|---|
| D-01 Pure Super Admin | Admin/generic hero, role chips, announcements when active; admin navigation; report navigation | No agent/requester/team operational feed for pure role; Blade hides overall block for Super Admin | Dashboard read-only; admin write occurs on separate screens | Super Admin is not automatically operational. |
| D-02 Pemohon-only | Requester hero, reporting guide, own totals/status/action-needed tickets, create/list links, announcements | Own-ticket aggregates only | Navigation to create and own follow-up actions | `isRequesterOnly` also remains true for Pemohon+Approver if no SA/T1/T2/TeamChair. |
| D-03 Agen Tier 1 | `dashboard.helpdesk`, queue/assigned/waiting/SLA/operational summaries, period filter, overall data as service permits | Agent/team/global operational data produced by DashboardService | Links to queue/all/report/announcements; actual mutations elsewhere | Not admin unless additional Super Admin. |
| D-04 Agen Tier 2 | `dashboard.helpdesk` focused on own assignments/work; period filter | Assigned/scope-limited operational data | Links to own queue/detail | No unassigned/global ticket mutation rights. |
| D-05 Current Approver | Generic hero + period filter, “Perlu Tindakan Saya”, pending approvals, overall data as service permits, report link | Pending approvals assigned to actor; current-approver aggregates | Decision occurs in approval/ticket screens | Non-current pure Approver sees generic dashboard without approver/report access. |
| D-06 Ketua Tim Kerja | Generic hero + period filter + “Pemantauan tiket anggota tim”; no `overallDashboard` block | Team names/member count and safe ticket rows: status, SLA, assignee, latest public reply, solution | Read-only links to safe detail | No attachments/internal/private data; all rows originate from Team Chair scope/projection. |

### UI-004 Ticket list — 3 variants

| Variant | Actor condition | Blade/data | Actions/data restrictions |
|---|---|---|---|
| L-01 Requester list | Has Pemohon and lacks SuperAdmin/T1/T2/TeamChair (Approver does not disqualify) | `tickets.requester-index`; `RequesterTicketList` paginator | Own tickets only; quick-action labels from server policy; filters/pagination allowed. |
| L-02 Operational/scoped list | T1/T2 or qualifying multi-role, not Team Chair | `tickets.index` with Ticket models scoped by union of Pemohon own + T1 requester/creator/assignee + T2 assignee | Search/per-page; links to full detail if `view` allows. T1 queue flag; not an all-ticket replacement. |
| L-03 Ketua Tim list | Has Ketua Tim, regardless other ticket roles | `tickets.index` with `TeamChairTicketView`, fixed 15/page, no filters, `isTeamChair=true` | Team-scope metadata only; no operational controls. Team Chair branch executes before all others. |

### UI-008 Ticket detail — 6 variants

| Variant | Visibility/data | Actions | Restrictions/source |
|---|---|---|---|
| T-01 Super Admin | Full model detail and `canSeeInternal=true`; requester/internal attachments subject to attachment policy | Pure role has no ticket mutation/comment action | `TicketPolicy::view` explicitly allows Super Admin; other ticket abilities do not. |
| T-02 Pemohon | Own/created full requester-facing detail; public comments, requester-visible fields/attachments, solution/timeline sanitized by `canSeeInternal=false` | Depending state: requester reply/public communication, cancel, confirm, not-satisfied, reopen | `TicketPolicy` owner/status checks; no internal data. |
| T-03 Agen Tier 1 | Full detail for all tickets; internal data and attachments | New queue triage/priority/reject; assigned-agent actions; assign T2; comments/attachments/approval/completion as conditions allow | Every action uses separate policy flag and workflow state. |
| T-04 Agen Tier 2 assigned | Full assigned-ticket detail; internal data; attachments only while assigned | Assigned-agent actions, return T1, communication, waiting, approval, completion, SVC controls as state/service allow | Direct view and attachment denied when not assigned. |
| T-05 Current pending Approver | Full pending ticket context; `canSeeInternal=true`; public/internal comments and allowed attachments | Approve/reject; comments while pending | Requires current assignment **and** pending ApprovalRequest assigned to actor. |
| T-06 Ketua Tim | Separate `tickets.team-chair-show` with safe view model | No action menu/forms/download | Team scope; safe projection; operational read-only override wins over other ticket roles. |

## D. Ticket action matrix

Legend: **C** = conditionally allowed by record/status/policy; **—** = not allowed for pure role. This matrix describes server ability, not merely whether a button is currently present.

| Ability/action | Super Admin | Pemohon | Agen T1 | Agen T2 | Current Approver | Ketua Tim | Authoritative condition |
|---|---:|---:|---:|---:|---:|---:|---|
| `viewAny` list | — | C | C | C | — | C | Active + enumerated roles; Team Chair branch safe. |
| `view` detail | C all | C owner/creator | C all | C assigned | C pending assigned | C team scope | `TicketPolicy::view`. |
| `create` | — | C self | C self/other active requester | — | — | — | `create`, `createSelf`, `createForOther`. |
| `viewQueue` | — | — | C | — | — | — | Tier 1, active, not Team Chair. |
| `viewAssigned`/`viewCompleted` | — | — | C | C own scope | — | — | Tier 1/2, not Team Chair. |
| `viewAll` | — | — | C | — | — | — | Tier 1 only. |
| `claim` | — | — | C | — | — | — | New queue/domain concurrency; no active UI trigger. |
| `handle` | — | — | C assigned | C assigned | — | — | Assigned agent; no active UI trigger. |
| Triage | — | — | C | — | — | — | New queue or specified assigned T1 state. |
| Change priority | — | — | C | — | — | — | New queue only. |
| Reject ticket | — | — | C | — | — | — | New queue only. |
| Assign Tier 2 | — | — | C | — | — | — | Assigned T1, Dikerjakan. |
| Return Tier 1 | — | — | — | C | — | — | Assigned T2, Dikerjakan, last triager exists. |
| Cancel | — | C | — | — | — | — | Owner Pemohon, Baru. |
| Public comment | — | C | C | C assigned | C pending | — | Not closed; requester-only branch is Menunggu Pemohon, while requester-reply is separate. |
| Internal comment | — | — | C assigned | C assigned | C pending | — | Allowed statuses or pending approval. |
| Request information | — | — | C assigned | C assigned | — | — | Diproses/Dikerjakan. |
| Requester reply | — | C owner | — | — | — | — | Not a closed-case status. |
| Start/resume third party | — | — | C assigned | C assigned | — | — | Start from Diproses/Dikerjakan; resume from Menunggu Pihak Ketiga. |
| Request approval | — | — | C assigned | C assigned | — | — | Diproses/Dikerjakan + active approver workflow. |
| Approve/reject approval | — | — | — | — | C pending | — | ApprovalRequestPolicy `decide`. |
| Complete | — | — | C assigned | C assigned | — | — | Dikerjakan + special-control readiness. |
| Upload ticket attachment | — | — | C assigned | C assigned | — | — | Diproses/Dikerjakan. |
| Update internal fields | — | — | C assigned | C assigned | — | — | SVC-07 + allowed active statuses. |
| Execute/verify SVC-03 | — | — | C assigned | C assigned | — | — | SVC-03, Dikerjakan, evidence/sequencing. |
| Confirm/not satisfied | — | C owner | — | — | — | — | Menunggu Konfirmasi. |
| Reopen | — | C owner | — | — | — | — | Ditutup + service window/count rules. |

## E. Data visibility matrix

| Data class | Super Admin direct view | Pemohon owner | Agen T1 | Agen T2 assigned | Current pending Approver | Ketua Tim |
|---|---|---|---|---|---|---|
| Ticket number/subject/status/priority/service | Full | Full own | Full | Full assigned | Full pending | Safe projection |
| Description | Visible | Visible own | Visible | Visible assigned | Visible pending | **Excluded** |
| Requester identity/snapshot, NIP | Visible | Own context | Visible | Visible assigned | Visible pending | Only safe requester name/team; sensitive identifiers **excluded** |
| Location/building/floor/room | Visible | Visible own where rendered | Visible | Visible assigned | Visible pending | **Excluded** |
| Requester-visible dynamic fields | Visible | Visible own | Visible | Visible assigned | Visible pending | **Excluded** from safe view model |
| Internal fields/history | Visible (`canSeeInternal`) | **Hidden** | Visible | Visible assigned | Visible pending | **Excluded** |
| Public comments | Visible | Visible own | Visible | Visible assigned | Visible pending | Public comments only, projected to safe view |
| Internal comments | Visible | **Hidden** | Visible | Visible assigned | Visible pending | **Excluded** |
| Requester-accessible attachments | Download allowed by policy | Own/created ticket only | Allowed | Assigned only | Pending request assigned; attachment policy itself does not re-check current assignment | **All downloads denied** |
| Internal attachments | Allowed for pure role | **Denied** | Allowed for pure role | Assigned only | Pending request assigned; same current-assignment asymmetry | **Denied** |
| Approval request/details | Visible via `canSeeInternal` | Not passed to view | Visible | Visible assigned | Visible/decidable | Excluded |
| Solution | Visible | Visible own | Visible | Visible assigned | Visible pending | Included in safe projection |
| SLA | Full calculated metrics | Requester-facing metrics as rendered | Full | Full assigned | Full pending | Safe metrics allow-list only |
| Audit/assignment/priority/category histories | Full/internal timeline | Sanitized requester timeline | Full | Full assigned | Full pending context | Audit snapshots/internal histories excluded; safe timeline only |

## F. Safe projection Ketua Tim Kerja

`TeamChairTicketProjection` memilih ticket columns secara eksplisit dan tidak melakukan lazy loading terhadap relation yang tidak diizinkan.

### Included

- internal database ID hanya untuk routing internal;
- ticket number, subject;
- requester display name dan team snapshot;
- service code/name/required skills;
- problem category;
- priority, status;
- assignee display name/tier;
- solution;
- submitted/created/updated timestamps;
- safe SLA metrics: uses SLA, target/remaining, percent, near-limit, overdue, paused, compliance;
- public comments transformed menjadi `TeamChairCommentView`.

### Explicitly excluded/restricted

- description;
- NIP atau identifier sensitif lain;
- location data;
- requester/internal dynamic fields;
- internal comments;
- all attachments/downloads;
- audit snapshots dan raw history payload;
- write controls;
- report/approval access.

Redesign tidak boleh meminta full Ticket model untuk kemudian menyembunyikan field dengan CSS. Safe view model harus tetap sumber data.

## G. Active approver contract

Actor dianggap effective Approver hanya bila seluruh kondisi ini benar:

1. account aktif;
2. memiliki role Approver;
3. memiliki record `ApproverAssignment::active()` untuk user tersebut;
4. tidak memiliki role Ketua Tim Kerja untuk approval/report abilities;
5. untuk ticket detail/decision, ada pending `ApprovalRequest` pada ticket yang `approver_id`-nya actor.

Efek aktual:

- dashboard pending-approval section visible;
- `/approvals` visible;
- pending ticket direct view visible;
- public/internal communication dan attachment yang policy izinkan visible selama pending;
- approve/reject visible;
- report visible/exportable;
- setelah request tidak pending atau assignment berganti, access dapat hilang.

Attachment caveat: `AttachmentAccessPolicy::isPendingApprover()` tidak memeriksa record current `ApproverAssignment`; ia hanya memeriksa account aktif, role Approver, dan pending request assigned. Karena itu direct attachment authorization tidak boleh disimpulkan dari current-approver UI visibility. Ini baseline policy asymmetry yang dicatat, bukan diperbaiki di redesign.

## H. Internal versus public communication

| Concern | Public/requester | Internal |
|---|---|---|
| Comment visibility value | Public | Internal |
| Intended readers | Requester/creator dan authorized operational actors/current pending Approver | Super Admin/Tier 1, assigned Tier 2, current pending Approver sesuai policy; never Team Chair/Pemohon |
| Writer policy | `commentPublic` atau `replyRequester` | `commentInternal` |
| Attachment policy set | Public/requester-compatible policies | Internal policies |
| Team Chair | Public comment safe projection only | Never included |
| UI merge rule | Boleh share visual editor/message style | Route, payload context, label, policy, and visibility must remain separate |

## I. Administration and additional-role caveat

Pure-role table menyederhanakan satu role per akun. Untuk akun multi-role:

- Super Admin + Tier 1 tetap memperoleh Tier 1 ticket abilities dari policy, tetapi Super Admin layout branch dan dashboard intentionally suppress operational navigation/helpdesk variant; direct URL behavior harus diuji.
- Pemohon + Tier 1/Tier 2 memakai operational/scoped ticket list, bukan requester-only view; scope merupakan union dari applicable clauses.
- Pemohon + Approver tanpa SA/T1/T2/TeamChair masih memakai requester-only ticket list, sementara current-approver dashboard/approval abilities dapat ikut muncul.
- Any ticket-role + Ketua Tim selalu masuk Team Chair list/detail projection and all ticket writes are denied.
- Ketua Tim + Super Admin masih dapat mengelola admin resources menurut current admin policies; report tetap denied dan ticket tetap safe/read-only.
- Ketua Tim + Tier 1 dapat lolos announcement policy/middleware karena AnnouncementPolicy tidak memiliki Team Chair override, walau ticket workflow tetap read-only.
- SuperAdmin/Tier1 + Tier2 memiliki edge case attachment internal: karena exact policy memeriksa `hasRole(AgenTier2)`, assignment tetap diwajibkan untuk internal attachment walaupun role lain biasanya memiliki akses lebih luas.

Regression testing wajib memasukkan kombinasi tersebut; redesign tidak boleh mengubah precedence lewat penyederhanaan `@if` role di layout.
