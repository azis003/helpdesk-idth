# UI Fixture Catalog

Dokumen ini adalah evidence authoritative untuk W0.2 — Deterministic UI Fixture Pack. Fixture di dokumen ini hanya untuk database disposable pada environment `local` atau `testing`; fixture bukan bagian dari production seed.

## Architecture Decision (recorded before implementation)

- Entry point adalah command eksplisit `php artisan app:seed-ui-baseline`.
- Command memanggil dedicated `UiBaselineFixtureSeeder`; seeder tidak didaftarkan pada `DatabaseSeeder`.
- Guard environment diterapkan dua lapis, pada command dan seeder. Keduanya hanya menerima environment Laravel `local` atau `testing`, tidak bergantung pada nama database.
- Seeder menggunakan identifier dan nilai synthetic yang tetap. Rerun dengan profil yang sama mendeteksi manifest lengkap lalu tidak menulis ulang data.
- Bootstrap ticket deterministic dilakukan oleh fixture builder terhadap model/domain schema existing, termasuk snapshot, initial history, field value, dan SLA yang diperlukan. Semua transisi status/workflow yang mempunyai side effect menggunakan public domain service existing sejauh legal dan reasonable.
- Actor, role, catalog, policy, organization, ticket, comment, attachment, approval, dan special-control fixture dibuat terpisah dari helper test agar runtime command tidak bergantung pada namespace `Tests`.
- File attachment kecil dibuat secara synthetic pada disk attachment yang dikonfigurasi; tidak ada dokumen atau PII nyata.
- Reset/recreate dilakukan secara eksplisit pada database local/testing yang disposable, bukan dengan menghapus data ber-prefix dari database yang mungkin berisi data lain.
- Domain hanya mengizinkan satu `approver_assignments.is_active = true`. Karena ACT-05 dan ACT-09 tidak mungkin current bersamaan, command menyediakan profil current approver yang saling eksklusif: ACT-05 sebagai default dan ACT-09 sebagai profil multi-role. Keduanya diuji pada database terisolasi; tidak ada invariant yang dibypass.

## Safety

Fixture mempunyai empat lapis proteksi:

1. Command memeriksa Laravel environment dan hanya menerima `local` atau `testing`.
2. Seeder mengulang pemeriksaan yang sama, sehingga pemanggilan seeder secara langsung juga ditolak di environment lain.
3. First run hanya menerima database disposable yang belum mempunyai user, ticket, work team, membership, chair assignment, approver assignment, building, atau skill. Bila ada data domain existing, command gagal sebelum menulis fixture dan tidak mencoba mengganti approver/data tersebut.
4. Bila marker fixture hanya tersedia sebagian, role/state berubah, atau profil approver berbeda, command gagal dan meminta reset database; command tidak mencoba “memperbaiki” data parsial secara berisiko.

`UiBaselineFixtureSeeder` tidak dipanggil oleh `DatabaseSeeder`. Tidak ada route, controller, Form Request, policy, model, migration, state machine, SLA rule, authentication, authorization, atau production seed flow yang diubah. File synthetic disimpan pada private attachment disk yang sudah dikonfigurasi aplikasi.

Pesan penolakan environment:

> UI baseline fixture ditolak: command hanya boleh dijalankan pada environment local atau testing.

## Fixture Command

Default, dengan pure current Approver ACT-05:

```text
php artisan app:seed-ui-baseline
```

Profil alternatif untuk memverifikasi ACT-09 Pemohon + current Approver:

```text
php artisan app:seed-ui-baseline --current-approver=ACT-09
```

Nilai option yang valid hanya `ACT-05` atau `ACT-09` dan tidak case-sensitive. Successful run menampilkan environment, profil/current username, serta count actor, team, ticket, status, priority, service, comment, dan attachment. Rerun dengan profil yang sama menampilkan `sudah tersedia; tidak ada data ditulis ulang`.

Implementasi:

- `app/Console/Commands/SeedUiBaseline.php` — explicit command dan guard pertama;
- `database/seeders/UiBaselineFixtureSeeder.php` — guard kedua, manifest, fixture construction, dan domain orchestration;
- `tests/Feature/UiBaselineFixtureTest.php` — safety dan fixture contract tests.

## Reset/Recreate Procedure

Gunakan hanya database local/testing terpisah yang memang disposable. Verifikasi connection target sebelum reset. Contoh urutan:

```text
php artisan migrate:fresh --seed --env=testing --force
php artisan app:seed-ui-baseline --env=testing
```

Untuk profil ACT-09, ganti baris kedua dengan `--current-approver=ACT-09`. Jangan menjalankan `migrate:fresh` pada database bersama atau database yang perlu dipertahankan. Untuk berpindah profil approver, reset/recreate database disposable terlebih dahulu; command sengaja tidak memindahkan assignment pada pack yang sudah ada.

File attachment fixture berada di direktori `ui-baseline/` pada attachment disk. Reset database tidak otomatis menghapus file lama; pada environment disposable, kosongkan disk testing/local yang khusus untuk instance tersebut sebelum recreate bila storage yang benar-benar bersih diperlukan. Automated tests memakai fake storage dan tidak menyentuh attachment disk aplikasi.

## Credential Strategy

Semua actor login-ready memakai password local yang sama dan deterministic:

```text
Password: Ui-Baseline-Local-2026!
```

Username terdapat pada Actor Matrix dan selalu memakai prefix `ui_test_`. Password dicetak oleh command hanya setelah guard environment lolos. Seluruh email memakai reserved domain `example.invalid`; NIP memakai prefix `UIFIX-`; nama dan identitas sepenuhnya synthetic. Credential ini bukan secret dan tidak boleh digunakan ulang untuk akun production.

## Actor Matrix

| ID | Username | Role authoritative | Purpose | Expected screens/variant |
|---|---|---|---|---|
| ACT-01 | `ui_test_super_admin` | Super Admin | Pure admin, membuktikan admin tidak otomatis operasional | Dashboard/admin configuration; tidak mendapat queue/operational ticket capability tanpa role agen |
| ACT-02 | `ui_test_requester` | Pemohon | Requester utama, TEAM-A member, long display name | Requester dashboard, Tiket Saya, create/detail/action requester |
| ACT-03 | `ui_test_agent_t1` | Agen Tier 1 | Assignee utama dan pembentuk workflow | Work area, queue, all tickets, detail/action, report |
| ACT-04 | `ui_test_agent_t2` | Agen Tier 2 | Assigned technician dengan skill | Personal work area dan assigned ticket detail/action |
| ACT-05 | `ui_test_approver_current` | Approver | Pure current Approver pada default profile | Approval inbox/decision dan report; tidak menjadi requester/agent |
| ACT-06 | `ui_test_approver_stale` | Approver | Historical assignment sudah berakhir | Role tetap ada, tetapi current-assignment checks, approval inbox, dan decision ditolak |
| ACT-07 | `ui_test_team_chair` | Ketua Tim Kerja | Pure TEAM-A chair | Team Chair dashboard/list/detail safe projection, read-only, report dan attachment ditolak |
| ACT-08 | `ui_test_admin_t1` | Super Admin + Agen Tier 1 | Critical admin/operational combination | Admin screens tetap ada; queue/workflow muncul karena role T1, bukan karena admin |
| ACT-09 | `ui_test_requester_approver` | Pemohon + Approver | Critical requester/current-approver combination | Tetap requester list variant; approval capability aktif saat pack dibuat dengan profile ACT-09 |
| ACT-10 | `ui_test_team_chair_t1` | Ketua Tim Kerja + Agen Tier 1 | Critical Team Chair override terhadap T1 | TEAM-B safe/read-only ticket experience; operational write ditolak |
| ACT-11 | `ui_test_team_chair_admin` | Ketua Tim Kerja + Super Admin | Critical admin + Team Chair combination | Admin configuration tetap ada; TEAM-C ticket tetap safe/read-only; report ditolak oleh current policy |
| SUP-01 | `ui_test_requester_outside` | Pemohon | Requester TEAM-B di luar scope ACT-07 | Supporting requester/outside-scope ticket |
| SUP-02 | `ui_test_agent_t2_unrelated` | Agen Tier 2 | Unrelated T2 dengan skill | Negative attachment and assignment authorization |

Total: **13 actor**, seluruhnya active, `must_change_password=false`, tanpa identitas nyata.

## Organization Matrix

| Fixture | Type | Chair/member | UI/contract purpose |
|---|---|---|---|
| `UI Fixture TEAM-A` | Work team | Chair ACT-07; members ACT-02 dan ACT-04 | In-scope pure Team Chair, requester member, skilled T2 member |
| `UI Fixture TEAM-B` | Work team | Chair ACT-10; members SUP-01 dan SUP-02 | Outside scope ACT-07 dan safe scope multi-role ACT-10 |
| `UI Fixture TEAM-C` | Work team | Chair ACT-11; member ACT-09 | Safe scope admin + Team Chair dan requester + Approver |
| `UI Fixture Database dan Infrastruktur` | Skill | ACT-04 dan SUP-02; mapped ke SVC-03/SVC-05 | Skill label, suggestion, assigned/unrelated T2 cases |
| `Gedung Uji A` | Building | Active | Canonical location hierarchy |
| `Lantai 1 — UI Fixture` | Floor | Active, child Gedung Uji A | Required location untuk SVC-01 dan SVC-05 |

## Ticket Matrix

Semua subject dimulai dengan `[UI-FIXTURE:<Fixture ID>|<aliases>]`; ID/alias dapat dicari dari search existing tanpa schema tambahan.

| Fixture ID | Ticket number | Service | Final status | Priority | Requester | Assignee | Expected actor | Expected UI purpose |
|---|---|---|---|---|---|---|---|---|
| UI-TKT-NEW-001 | `INC-2026-91001` | SVC-01 | Baru | Kritis | ACT-02 | — | ACT-03/ACT-08 | TKT-STATUS-01, WF-01, unassigned Tier 1 queue, required location |
| UI-TKT-T1-001 | `REQ-2026-91001` | SVC-06 | Diproses | Tinggi | ACT-02 | ACT-08 (T1) | ACT-08 | TKT-STATUS-02, WF-02, claimed T1 and admin+T1 behavior |
| UI-TKT-T2-001 | `REQ-2026-91002` | SVC-05 | Dikerjakan | Sedang | ACT-02 | ACT-04 (T2) | ACT-04/SUP-02 | TKT-STATUS-03, WF-03, hardware/location, attachment allow/deny |
| UI-TKT-APPROVAL-CURRENT-001 | `CHG-2026-91001` | SVC-04 | Menunggu Persetujuan | Rendah | ACT-02 | ACT-03 | Current profile actor | TKT-STATUS-04, WF-06, pending approval current |
| UI-TKT-APPROVAL-STALE-001 | `CHG-2026-91002` | SVC-04 | Menunggu Persetujuan | Tinggi | ACT-02 | ACT-03 | ACT-06 negative | WF-07, same pending state but stale actor cannot decide |
| UI-TKT-WAIT-REQUESTER-001 | `REQ-2026-91003` | SVC-06 | Menunggu Pemohon | Sedang | ACT-02 | ACT-03 | ACT-02/ACT-03 | TKT-STATUS-05, WF-04, active requester wait and question |
| UI-TKT-WAIT-THIRD-PARTY-001 | `REQ-2026-91004` | SVC-06 | Menunggu Pihak Ketiga | Rendah | ACT-02 | ACT-03 | ACT-03 | TKT-STATUS-06, WF-05, vendor/follow-up state |
| UI-TKT-SVC02-RESULT-001 | `REQ-2026-91005` | SVC-02 | Menunggu Konfirmasi | Tinggi | ACT-02 | ACT-03 | ACT-02/ACT-03 | TKT-STATUS-07, WF-08, requester-accessible result |
| UI-TKT-CLOSED-ELIGIBLE-001 | `CHG-2026-91003` | SVC-04 | Ditutup | Sedang | ACT-02 | ACT-03 | ACT-02 | TKT-STATUS-08, WF-09, reopen eligible at baseline clock |
| UI-TKT-CLOSED-INELIGIBLE-001 | `CHG-2026-91004` | SVC-04 | Ditutup | Rendah | ACT-02 | ACT-03 | ACT-02 | WF-10, closure outside working-day reopen window |
| UI-TKT-CANCELLED-001 | `REQ-2026-91006` | SVC-06 | Dibatalkan | Sedang | ACT-02 | — | ACT-02 | TKT-STATUS-11, WF-11, requester cancellation |
| UI-TKT-REJECTED-001 | `INC-2026-91002` | SVC-01 | Ditolak | Rendah | ACT-02 | — | ACT-03/ACT-02 | TKT-STATUS-09, WF-12, queue rejection reason |
| UI-TKT-APPROVAL-REJECTED-001 | `CHG-2026-91005` | SVC-04 | Tidak Disetujui | Kritis | ACT-02 | ACT-03 | Current profile actor | TKT-STATUS-10, WF-13, approval decision note |
| UI-TKT-SVC02-BEFORE-001 | `REQ-2026-91007` | SVC-02 | Dikerjakan | Sedang | ACT-02 | ACT-03 | ACT-03 | Completion form before required export exists |
| UI-TKT-SVC03-INCOMPLETE-001 | `CHG-2026-91006` | SVC-03 | Dikerjakan | Tinggi | ACT-02 | ACT-03 | ACT-03 | Only change script exists; two evidence requirements missing |
| UI-TKT-SVC03-PREEXEC-001 | `CHG-2026-91007` | SVC-03 | Dikerjakan | Kritis | ACT-02 | ACT-03 | ACT-03 | Three evidence files complete; execution not started |
| UI-TKT-SVC03-EXECUTED-001 | `CHG-2026-91008` | SVC-03 | Dikerjakan | Tinggi | ACT-02 | ACT-03 | ACT-03 | Execution actor/time/history present; verification pending |
| UI-TKT-SVC03-VERIFIED-001 | `CHG-2026-91009` | SVC-03 | Dikerjakan | Sedang | ACT-02 | ACT-03 | ACT-03 | Execution and verification actor/time/result/history present |
| UI-TKT-SVC07-001 | `CHG-2026-91010` | SVC-07 | Dikerjakan | Rendah | ACT-02 | ACT-03 | ACT-02/ACT-03/ACT-07 | Versioned internal fields, comment timeline, attachment visibility, long content |
| UI-TKT-TEAM-IN-001 | `REQ-2026-91008` | SVC-06 | Dikerjakan | Tinggi | ACT-02 | ACT-03 | ACT-07 | TEAM-CHAIR-IN-SCOPE and safe public comment |
| UI-TKT-TEAM-OUT-001 | `REQ-2026-91009` | SVC-06 | Dikerjakan | Sedang | SUP-01 | ACT-03 | ACT-07 negative/ACT-10 positive | TEAM-CHAIR-OUTSIDE-SCOPE and long historical service snapshot |
| UI-TKT-TEAM-MULTI-001 | `REQ-2026-91010` | SVC-06 | Baru | Rendah | ACT-09 | — | ACT-09/ACT-11 | Requester+Approver list variant and admin+chair TEAM-C projection |

Coverage: **22 tickets**, **11/11 canonical statuses**, **4/4 priorities**, dan **7/7 canonical service codes**.

## Approval Matrix

| Scenario | Assignment state | Pending requests | Expected result |
|---|---|---|---|
| Default `ACT-05` | ACT-06 dibuat current lebih dahulu, lalu diganti secara authoritative oleh ACT-05 | WF-06 dan WF-07 assigned ke ACT-05 | ACT-05 dapat membuka/memutus pending approval; ACT-06 gagal current checks |
| Alternate `ACT-09` | ACT-06 dibuat stale, lalu ACT-09 menjadi satu-satunya current assignment | Dua pending request assigned ke ACT-09 | Requester list tetap `tickets.requester-index`; approval capability tetap tersedia |
| Rejected decision | Current profile actor memutus WF-13 | Approval row `rejected` dengan decision note | Ticket menjadi Tidak Disetujui melalui `TicketApprovalService` |

Assignment dibuat hanya melalui `ApproverAssignmentService::replace`; role Approver saja tidak dipakai sebagai pengganti authoritative assignment. Database invariant hanya mengizinkan satu assignment aktif.

## Team Chair Matrix

| Chair | Scope | Positive ticket | Negative/override | Verified restrictions |
|---|---|---|---|---|
| ACT-07 pure Ketua Tim | TEAM-A | UI-TKT-TEAM-IN-001 | UI-TKT-TEAM-OUT-001 tidak dapat dilihat | Read-only; no internal comments, internal fields, attachment relation/download, description/private identifiers, write action, atau report |
| ACT-10 Ketua Tim + T1 | TEAM-B | UI-TKT-TEAM-OUT-001 | T1 operational ability tetap ditimpa Team Chair | Safe projection/read-only walau role T1 ada |
| ACT-11 Ketua Tim + Super Admin | TEAM-C | UI-TKT-TEAM-MULTI-001 | Admin configuration rights tetap ada; ticket/report mengikuti Team Chair override | Safe projection/read-only; report denied oleh `ReportExportPolicy` |

Automated verification memakai `TicketPolicy`, `AttachmentAccessPolicy`, dan `TeamChairTicketProjection`; bukan inferensi dari role label/navigation.

## SVC-01 Matrix

| Ticket | State | Location | Purpose |
|---|---|---|---|
| UI-TKT-NEW-001 | Baru, unassigned, Kritis | Gedung Uji A / Lantai 1 | New queue, critical badge, location display |
| UI-TKT-REJECTED-001 | Ditolak, Rendah | Gedung Uji A / Lantai 1 | Rejection reason/final state with required location snapshot |

## SVC-02 Matrix

| Ticket | Result attachment | State | Purpose |
|---|---|---|---|
| UI-TKT-SVC02-BEFORE-001 | Tidak ada | Dikerjakan | Completion UI wajib meminta `data_export_result` |
| UI-TKT-SVC02-RESULT-001 | ATT-03 `data_export_result`, visibility `both`, file exists | Menunggu Konfirmasi | Requester dapat melihat/download result setelah completion legal |

Result metadata/file dibuat synthetic pada private disk; completion tetap dilakukan melalui `TicketResolutionService`, yang memeriksa `DatabaseChangeControlService::completionFailure`.

## SVC-03 Matrix

| Ticket | Evidence | Execution | Verification | Expected UI state |
|---|---|---|---|---|
| UI-TKT-SVC03-INCOMPLETE-001 | Change script saja | Belum | Belum | Requirement checklist memperlihatkan rollback/backup masih missing |
| UI-TKT-SVC03-PREEXEC-001 | Change, rollback, backup lengkap dan file usable | Belum | Belum | Tombol/state Mulai Eksekusi tersedia |
| UI-TKT-SVC03-EXECUTED-001 | Lengkap | Actor/time/history tersimpan | Belum | Verification form/state tersedia |
| UI-TKT-SVC03-VERIFIED-001 | Lengkap | Actor/time/history tersimpan | Actor/time/result/notes/history tersimpan | Ready untuk completion, tetapi dibiarkan Dikerjakan agar verified state dapat dilihat |

Eksekusi dan verifikasi memakai `DatabaseChangeControlService`; tidak ada impossible state atau direct status shortcut.

## SVC-05 Matrix

| Ticket | Request fields | Location | Assignee/UI purpose |
|---|---|---|---|
| UI-TKT-T2-001 | Repair, laptop, asset tag synthetic, quantity 1 | Gedung Uji A / Lantai 1 | ACT-04 T2; hardware form snapshots, skill, T2 detail, attachment authorization |

## SVC-07 Matrix

| Contract | Fixture value/evidence | Visibility expectation |
|---|---|---|
| Requester fields | Semua required fields, optional `integrations=null` | Requester/authorized agent sesuai snapshot |
| `internal_fields[...]` | 7 saved fields: assessment, complexity, security risk, TI priority, planned start, owner, follow-up notes | Hanya authorized assigned agent/internal view |
| `internal_field_versions[...]` | Seluruh nilai memakai active definition version `1` | Stale-version contract tetap authoritative |
| Field history | 7 `created` histories dengan actor ACT-03 | Audit/timeline internal tersedia |
| Team Chair projection | UI-TKT-SVC07-001 requester adalah TEAM-A member | Internal values tidak menjadi property/response safe projection |

## Comment Matrix

| Fixture ID | Ticket | Author | Visibility/type | UI purpose |
|---|---|---|---|---|
| UI-CMT-WAIT-REQUESTER | UI-TKT-WAIT-REQUESTER-001 | ACT-03 | Public question | Active waiting requester prompt |
| UI-CMT-REQUEST | UI-TKT-SVC07-001 | ACT-03 | Public question | Start requester wait |
| UI-CMT-REQUESTER-REPLY | UI-TKT-SVC07-001 | ACT-02 | Public requester reply | Ends wait and returns ticket to Dikerjakan |
| UI-CMT-PUBLIC-LONG | UI-TKT-SVC07-001 | ACT-03 | Public agent reply | Long content/wrapping and ATT-01 |
| UI-CMT-INTERNAL-LONG | UI-TKT-SVC07-001 | ACT-03 | Internal note | Long private content and ATT-02 |
| UI-CMT-TEAM-PUBLIC | UI-TKT-TEAM-IN-001 | ACT-03 | Public agent reply | Included in Team Chair safe projection |
| UI-CMT-TEAM-INTERNAL | UI-TKT-TEAM-IN-001 | ACT-03 | Internal note | Explicitly excluded from Team Chair safe projection |

Timestamp SVC-07 timeline berurutan pada 14:00, 14:10, 14:20, dan 14:30 Asia/Jakarta. Total: **7 comments**.

## Attachment Matrix

| ID/name | Ticket | Type/visibility | Expected access/purpose |
|---|---|---|---|
| ATT-01 `UI-ATT-01-public-requester-visible.txt` | SVC-07 | Supporting / both / public comment | Requester, T1, dan actor authorized dapat melihat |
| ATT-02 `UI-ATT-02-internal-agent-only.txt` | SVC-07 | Supporting / internal / internal comment | Internal agent only; requester dan Team Chair tidak menerima |
| ATT-03 `UI-ATT-03-data-export-result.csv` | SVC-02 result | `data_export_result` / both | Requester-accessible special completion result |
| ATT-04 `UI-ATT-04-assigned-tier-two.txt` | T2 ticket | Supporting / both | Assigned ACT-04 allowed |
| ATT-05 `UI-ATT-05-unrelated-tier-two-denied.txt` | T2 ticket | Supporting / internal | SUP-02 unrelated denied |
| ATT-06 `UI-ATT-06-team-chair-denied.txt` | TEAM-A in-scope | Supporting / both / public comment | ACT-07 tetap denied walau ticket in scope dan attachment public |
| ATT-07 `UI-ATT-07-soft-deleted-retained.txt` | SVC-07 | Supporting / internal / soft deleted | Metadata soft-deleted, synthetic file sengaja retained |
| SVC03-INCOMPLETE | SVC-03 incomplete | 1 internal change script | Partial evidence state |
| SVC03-PREEXEC | SVC-03 pre-execution | 3 internal files | Complete usable evidence before execution |
| SVC03-EXECUTED | SVC-03 executed | 3 internal files | Evidence referenced by execution history |
| SVC03-VERIFIED | SVC-03 verified | 3 internal files | Evidence plus execution/verification history |

Total: **17 attachments termasuk satu soft-deleted retained row**. Semua content kecil, synthetic, dan mempunyai deterministic storage path serta SHA-256 metadata.

## Long Content Matrix

| Stress case | Fixture |
|---|---|
| Long requester display name | ACT-02 |
| Long ticket subject | UI-TKT-SVC07-001 |
| Long historical service label | UI-TKT-TEAM-OUT-001; hanya snapshot ticket, canonical catalog tidak diubah |
| Long attachment filename | ATT-01/02/04/05/06/07 naming set |
| Multiline description | UI-TKT-SVC07-001 |
| Multiline solution | UI-TKT-SVC02-RESULT-001 dan UI-TKT-CLOSED-ELIGIBLE-001 |
| Long public/internal comments | UI-CMT-PUBLIC-LONG dan UI-CMT-INTERNAL-LONG |
| Empty optional values | SVC-02 filter, SVC-04 reproduction, SVC-06 version/constraints, SVC-07 integrations |

## Multi-role Matrix

| Actor | Authoritative expectation | Automated evidence |
|---|---|---|
| ACT-08 Super Admin + T1 | Admin management tetap allowed; operational queue/claim berasal dari T1 | `UserPolicy::viewAny=true`, `TicketPolicy::viewQueue=true`, WF-02 assigned kepada ACT-08 |
| ACT-09 Pemohon + current Approver | Requester list variant tetap requester; current approval tersedia pada ACT-09 profile | HTTP view adalah `tickets.requester-index`; authoritative assignment dan dua pending approvals diverifikasi |
| ACT-10 Ketua Tim + T1 | Team Chair override membuat ticket experience safe/read-only | TEAM-B ticket view allowed, `handle=false` |
| ACT-11 Ketua Tim + Super Admin | Admin configuration tetap allowed; ticket safe/read-only; report mengikuti chair override | `UserPolicy::viewAny=true`, TEAM-C projection allowed, `ReportExportPolicy::viewAny=false` |

## Known Fixture Limitations

- ACT-05 dan ACT-09 tidak dapat current bersamaan karena database/domain hanya mengizinkan satu active approver assignment. Kedua kondisi tersedia sebagai profil mutually exclusive dan keduanya diuji pada database terisolasi.
- First run sengaja mensyaratkan database domain yang bersih/disposable. Ini adalah safety boundary, bukan general-purpose demo seeder untuk database developer yang sudah berisi data.
- Manifest tidak melakukan auto-repair. Marker parsial, role/state yang berubah, atau profile mismatch menghasilkan error dan memerlukan reset/recreate.
- Clock fixture dibekukan pada Juni–Agustus 2026 agar timeline visual stabil. WF-09 diverifikasi eligible terhadap baseline clock `2026-08-13 10:00 Asia/Jakarta`; bila manual testing dilakukan jauh setelah baseline date, business rule berbasis current wall clock secara alami dapat menganggapnya di luar reopen window.
- Deterministic ticket number memerlukan base ticket construction langsung terhadap schema existing. Base row dilengkapi snapshot requester/service/location, requester field values, initial status history, dan SLA start; seluruh transisi bermakna sesudahnya memakai domain service existing.
- Attachment rows/file path dibuat oleh fixture builder agar nama dan path deterministic. Authorization/readiness tetap diperiksa oleh policy/service existing; tidak ada public disk exposure.
- Long service label memakai historical ticket snapshot. Canonical service name SVC-06 tidak dimutasi.
- W0.2 menyediakan data dan contract tests, bukan screenshot golden atau redesign UI.

## Idempotency Evidence

Manual isolated run memakai SQLite testing terpisah:

| Metric | Run 1 | Run 2 |
|---|---:|---:|
| Actors | 13 | 13 |
| Work teams | 3 | 3 |
| Tickets | 22 | 22 |
| Statuses | 11 | 11 |
| Priorities | 4 | 4 |
| Services | 7 | 7 |
| Comments | 7 | 7 |
| Attachments termasuk soft delete | 17 | 17 |

Run 1: `UI baseline fixture berhasil dibuat.`

Run 2: `UI baseline fixture sudah tersedia; tidak ada data ditulis ulang.`

Automated idempotency capture membandingkan count users, roles, user-role pivots, teams, memberships, chairs, approver assignments, tickets, status histories, approval requests, comments, attachments, internal fields, audit logs, dan notifications sebelum/sesudah rerun. Seluruh count identik. Manifest juga memeriksa exact actor-role sets, current/stale approver, exact ticket status/assignee, comments, attachment rows/files, serta status/priority coverage.

## Regression Result

Validation pada 2026-08-13:

| Gate | Result |
|---|---|
| New targeted fixture suite | PASS — 4 tests, 90 assertions |
| Full `XDEBUG_MODE=off php artisan test` | PASS — 140 total, 139 passed, 0 failed, 0 errors, 1 intentional PostgreSQL worker-concurrency skip, 1,780 assertions |
| `npm run build` | PASS — Vite 6.4.3, 59 modules transformed |
| Existing baseline regression | NONE — 135 existing pass + 1 existing intentional skip tetap sama |
| Production behavior impact | NONE — hanya explicit local/testing command, dedicated seeder, test, dan documentation |

## W0.2 Gate

**W0.2 PASS.** Actor matrix lengkap melalui dua valid current-approver profiles, current/stale Approver authoritative tersedia, empat critical multi-role tersedia, 11 statuses, 4 priorities, 7 services, Team Chair in/out scope, deterministic identifiers/credentials, idempotent rerun, full regression, dan frontend build seluruhnya lulus.
