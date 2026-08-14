# Baseline Readiness Gate

Dokumen ini meratifikasi hasil W0.4 — Final Baseline Readiness Gate pada 14 Agustus 2026 (Asia/Jakarta). W0.4 tidak melakukan redesign, bug fix, refactor, perubahan fixture/test, atau perubahan implementation. Keputusan readiness dipisahkan antara pekerjaan analisis desain yang tidak mengubah code dan pekerjaan implementation yang mengubah pixel atau markup.

## Baseline Documents

Seluruh dokumen authoritative berikut telah dibaca ulang dan tetap berlaku:

| Dokumen | Fungsi authoritative | Status W0.4 |
|---|---|---|
| `00-REDESIGN-GUARDRAILS.md` | Scope presentation, area locked, invariant domain, dan HTML/form contract | RATIFIED |
| `01-UI-INVENTORY.md` | 23 active screens, 15 structural variants, active responses, dan dormant views | RATIFIED |
| `02-UI-CONTRACT-MATRIX.md` | 112 HTTP contracts, payload, route, selector, modal, redirect, flash, dan compatibility aliases | RATIFIED |
| `03-COMPONENT-INVENTORY.md` | 18 Blade components, 16 reusable partials aktif, duplicate patterns, dan client-state boundary | RATIFIED |
| `04-ROLE-VARIANT-MATRIX.md` | Pure-role, multi-role, safe projection, current Approver, dan data visibility | RATIFIED |
| `05-IMPLEMENTATION-WAVES.md` | W1 sampai W6, dependency, regression gate, dan definition of done | RATIFIED |
| `06-BASELINE-EVIDENCE.md` | Snapshot W0 awal dan gap yang saat itu masih memblokir | HISTORICAL; superseded where later evidence exists |
| `07-REGRESSION-BASELINE-DIAGNOSIS.md` | Diagnosis W0.1 terhadap tiga test infrastructure issues | RATIFIED AS DIAGNOSIS HISTORY |
| `08-REGRESSION-BASELINE-HARDENING.md` | W0.1b, hardening test tanpa production behavior change | RATIFIED |
| `09-UI-FIXTURE-CATALOG.md` | Deterministic local/testing fixture pack dan catalog ID | RATIFIED |
| `10-BASELINE-VISUAL-ISSUES.md` | 17 registered existing issues/risks/evidence gaps | RATIFIED |
| `11-VISUAL-BASELINE.md` | W0.3 HTTP/source evidence dan visual-golden limitation | RATIFIED |
| `12-SCREENSHOT-INDEX.md` | Authoritative filename queue dan manual runtime checklist | RATIFIED |

Bila status historis berbeda, evidence fase terbaru yang secara eksplisit menutup gap sebelumnya menjadi sumber keputusan. Karena itu, status regression dan fixture pada dokumen ini memakai W0.1b/W0.2/W0.3, bukan status `BLOCKED` historis pada akhir W0 awal.

## Contract Freeze Summary

Contract freeze tetap konsisten:

| Contract | Baseline authoritative |
|---|---:|
| Active screens | **23** |
| Primary structural variants | **15** |
| Non-vendor HTTP contracts | **112** |
| Read/response contracts | **31** |
| Mutation/form-action contracts | **81** |
| Ticket modal IDs | **14** |
| Canonical roles | **6** |
| Canonical ticket statuses | **11** |
| Canonical priorities | **4** |

Seluruh route name, URL, HTTP method, route aliases, controller/Form Request contract, validation, middleware, Gate/Policy, `DomainAuthorization`, model, enum, migration/schema, domain workflow, SLA, approval, waiting state, attachment authorization, reporting/export, authentication/session, notification, audit, job/queue/scheduler, dan Team Chair safe projection tetap locked.

Redesign mendatang wajib mempertahankan form `action`, method, CSRF, method spoofing, exact input names dan nesting, hidden markers, error keys, `old()` behavior, query parameters, pagination query preservation, route names, download links, visibility/policy conditions, modal IDs, dan `data-*` selectors. Selector atau modal markup hanya boleh dimigrasikan atomik bersama seluruh JavaScript consumer dengan behavior yang terbukti identik.

Compatibility aliases tetap aktif dan tidak boleh dihapus:

- `tickets.complete` / `tickets.resolve`;
- `tickets.confirm` / `tickets.requester-confirm`;
- `tickets.not-satisfied` / `tickets.confirmation.not-satisfied`;
- `reports.index` / `reports.monthly`;
- compatibility catalog, services, locations, dan forms routes sebagaimana dibekukan dalam contract matrix.

Dormant views tetap **DO NOT REDESIGN / DO NOT ACTIVATE**:

- `resources/views/tickets/requester-show.blade.php`;
- `resources/views/admin/catalog/index.blade.php`;
- `resources/views/admin/catalog/_attachments-section.blade.php`;
- `resources/views/admin/catalog/_locations-section.blade.php`;
- `resources/views/admin/catalog/_service-edit-modal.blade.php`;
- `resources/views/admin/forms/index.blade.php`.

## Regression Baseline

Authoritative regression baseline terbaru berasal dari W0.2 dan dikonfirmasi kembali setelah W0.3 capture:

| Gate | Result |
|---|---|
| Total tests | **140** |
| Passed | **139** |
| Failed | **0** |
| Errors | **0** |
| Skipped | **1 intentional PostgreSQL worker-concurrency skip pada SQLite** |
| Assertions | **1,780** |
| `XDEBUG_MODE=off php artisan test` | **PASS**; final W0.3 duration 28.58 s |
| `npm run build` | **PASS**; Vite 6.4.3, 59 modules, final W0.3 duration 4.99 s |

Sebelum dokumen W0.4 ditambahkan, worktree delta sama dengan akhir W0.3: 19 file, 5,683 insertions, dan 41 deletions, tanpa perubahan baru pada production source. Karena tidak ada perubahan repository sejak final W0.3 yang memengaruhi implementation/test/build, command tersebut tidak dijalankan ulang hanya untuk menulis dokumen ini. Penambahan dokumen W0.4 juga tidak memengaruhi runtime.

## Fixture Baseline

Deterministic UI fixture pack diratifikasi:

| Fixture | Coverage |
|---|---:|
| Actors | **13** |
| Work teams | **3** |
| Tickets | **22** |
| Canonical statuses | **11/11** |
| Canonical priorities | **4/4** |
| Canonical services | **7/7** |
| Comments | **7** |
| Attachments, termasuk retained soft delete | **17** |

Current-Approver profiles bersifat mutually exclusive sesuai invariant satu active assignment:

- **ACT-05** adalah profile default, pure current Approver;
- **ACT-09** adalah profile alternatif, Pemohon + current Approver;
- ACT-06 menyediakan stale/non-current Approver negative case.

Fixture berstatus:

- deterministic dengan identifier synthetic tetap;
- idempotent untuk rerun profile yang sama;
- login-ready tanpa memakai data atau identitas nyata;
- hanya boleh digunakan pada Laravel environment `local` atau `testing` dan database disposable;
- tidak dipanggil dari `DatabaseSeeder` dan bukan production seed flow;
- tidak melakukan auto-repair pada manifest parsial atau profile mismatch.

## HTTP Render Baseline

Authenticated server-render evidence W0.3 diratifikasi:

| Coverage | Result |
|---|---:|
| Active screens rendered | **23/23** |
| Primary variants rendered | **15/15** |
| Pure-role profiles covered | **7** — enam canonical role profiles plus stale Approver |
| Critical multi-role profiles covered | **4** |
| Canonical statuses present/rendered | **11/11** |
| Canonical priorities present/rendered | **4/4** |
| Canonical service forms | **7/7** |

HTTP evidence juga mencakup:

- Team Chair in-scope/out-of-scope dan safe read-only projection;
- attachment authorization allow/deny, assigned/unrelated Tier 2, requester/internal, Team Chair denial, dan soft-deleted 404;
- report primary/alias HTML serta XLSX/PDF authorization, MIME, dan filename;
- ticket create validation/`old()` preservation tanpa membuat ticket;
- ticket filter, queue tab, search, dan pagination-query preservation;
- workflow fixture untuk approval, waiting, completion, confirmation, reopen representative, SVC-02, SVC-03, dan SVC-07;
- default ACT-05 dan alternate ACT-09 current-Approver profiles.

HTTP-render evidence membuktikan response, authorization, projection, markup marker, dan contract server. Ia **bukan** bukti pixel, viewport, pointer, focus, keyboard, contrast, clipping, atau responsive layout.

## Role Variant Baseline

Lima belas primary structural variants tetap terdiri atas:

- Dashboard: **6** — pure Super Admin, Pemohon, Tier 1, Tier 2, current Approver, dan Team Chair;
- Ticket list: **3** — requester, operational/scoped, dan Team Chair safe projection;
- Ticket detail: **6** — Super Admin read-only, Pemohon, Tier 1, assigned Tier 2, current pending Approver, dan Team Chair safe detail.

Precedence yang wajib dipertahankan:

1. pure Super Admin tidak otomatis menjadi actor operasional ticket;
2. Team Chair selalu safe/read-only untuk ticket, approval, attachment, dan report, termasuk pada akun multi-role;
3. tambahan Super Admin atau Tier 1 pada Team Chair masih dapat memberi admin/announcement capability sesuai policy resource yang aktual;
4. role Approver saja tidak cukup; current active assignment dan pending request untuk actor tetap authoritative;
5. ACT-08 Super Admin + Tier 1 mempertahankan admin-dominant navigation/dashboard sementara direct operational route tetap mengikuti Tier 1 policy;
6. ACT-09 mempertahankan mixed presentation: requester ticket-list variant plus current-Approver capability pada profile alternatif;
7. internal/public communication dan attachment tidak boleh digabung atau disimpulkan dari label role;
8. Team Chair data harus tetap berasal dari safe view model/projection, bukan full Ticket model yang disembunyikan dengan CSS/JavaScript.

## Workflow Baseline

Workflow evidence berikut tersedia tanpa mengubah state machine selama W0.3:

- ticket create untuk tujuh service forms, nested dynamic fields, location, dan attachment-policy inputs;
- ticket list/requester filters, queue tabs, all-ticket search, dan pagination preservation;
- 11 status dan 4 priority representatives;
- public, requester, dan internal communication visibility;
- request-information dan waiting requester;
- waiting/resume third party state;
- triage, assignment, return, priority, dan rejection form contracts;
- request approval, current/stale Approver, approve/reject presentation, dan rejected decision history;
- completion, requester confirmation, not-satisfied, dan reopen representative;
- SVC-02 before-completion required result dan after-completion requester-accessible result;
- SVC-03 incomplete, pre-execution, executed, dan verified states;
- SVC-07 requester dynamic values, versioned internal fields/history, and safe-projection exclusion;
- report display/export dan attachment download authorization.

Invalid submissions dipilih untuk memeriksa error, `old()`, redirect, dan modal marker tanpa menjalankan transition sukses baru. Existing fixture states dibuat melalui W0.2 pack dan domain services sesuai catalog evidence.

## Modal Baseline

Exact ticket-modal baseline tetap:

| Concern | Result |
|---|---:|
| Modal IDs rendered across fixture set | **14/14** |
| Active/reachable openers | **10/14** |
| Rendered without active opener | **4/14** |
| Validation/error auto-open | **13/14** |
| Reachable modal browser interaction coverage | **0/10** |

Empat modal tanpa active opener adalah existing baseline issues:

- `ticket-internal-comment-modal`;
- `ticket-database-change-modal`;
- `ticket-assign-tier-two-modal`;
- `ticket-return-tier-one-modal`.

`ticket-approval-decision-modal` reachable, tetapi reject validation tidak auto-open karena decision forms tidak mengirim `_action_modal`; textarea juga tidak merepopulasi `old('decision_note')`.

Keempat missing opener dan approval-decision error-routing gap **bukan redesign task**. Semuanya harus dipertahankan kecuali separate defect scope disetujui dengan acceptance/regression tests. Jangan menambahkan opener untuk kebutuhan screenshot.

## Known Existing Defects

Authoritative issue register berisi **17 records** (`VIS-001` sampai `VIS-017`):

- **7** confirmed existing behavior/known functional asymmetries;
- **4** role/information-hierarchy atau DOM-density risks;
- **6** manual visual, responsive, interaction, long-content, atau accessibility evidence gaps.

Angka 17 adalah jumlah registered baseline issues, bukan klaim bahwa ada 17 unique backend defects. Sepuluh rows diberi tanda bahwa functional change diperlukan bila behavior tersebut hendak diubah; sebagian rows overlap pada satu concern, terutama SVC-03. Manual evidence gaps juga tidak boleh diubah menjadi klaim defect sebelum capture.

### Preserve during redesign unless separate defect scope approved

1. Empat missing modal openers pada modal IDs yang disebut di atas.
2. Approval decision validation auto-open dan `old('decision_note')` gap.
3. Ticket-modal close/reset semantics: tidak ada `data-ui-modal-form`, `data-reset-on-close`, atau `data-clear-on-close` pada modal ticket.
4. Request-information backend menerima attachment, tetapi active UI hanya menampilkan `body`.
5. Triage backend menerima fields/outcome tambahan, tetapi active UI hanya menampilkan `self`, `tier_2`, dan `assigned_to_id`.
6. SVC-03 modal tidak reachable; incomplete state tetap merender execute form dan domain menolak submission sampai evidence lengkap.
7. SVC-03 HTML requiredness dan server nullable compatibility fields tidak boleh diselaraskan melalui redesign.
8. Super Admin + Tier 1 dashboard/navigation precedence pada ACT-08.
9. ACT-09 mixed requester/current-Approver presentation.
10. Team Chair ticket/report/attachment override dan additional-role admin/announcement caveat.
11. Pending-Approver attachment-policy asymmetry terhadap current active assignment check.
12. Login backend menerima `remember`, tetapi active UI tidak merender control.
13. `tickets.claim` dan `tickets.handle` tidak mempunyai active UI trigger.
14. Room, attachment-policy, operational-policy, user split, dan service-field-status endpoints tertentu tidak mempunyai active trigger; dormant surfaces tetap dormant.

Additional visual risks/evidence gaps tetap dicatat, bukan otomatis diperbaiki:

- services/users DOM density;
- belum adanya skip-to-content link;
- responsive overflow/clipping/modal scroll belum diamati;
- focus, keyboard, contrast, zoom, and long-content pixels belum mempunyai golden evidence.

## Visual Golden Status

| Evidence | Status |
|---|---|
| Automated screenshots | **0** |
| Desktop golden images | **0** |
| Mobile golden images | **0** |
| Browser reason | Runtime browser tidak tersedia selama W0.3 |
| Current action | **MANUAL SCREENSHOT CAPTURE REQUIRED** |

HTTP/server-render evidence tidak boleh ditafsirkan sebagai pixel evidence. Tidak ada bukti before-state untuk membandingkan spacing, layout, wrapping, overflow, responsive behavior, focus, contrast, dialog position/scroll, ataupun keyboard flow.

## Manual Capture Strategy

Seluruh filename pada `12-SCREENSHOT-INDEX.md` tetap authoritative. Tidak ada Priority 1/2 item yang dihapus. Capture dipisahkan menjadi minimum sebelum perubahan pertama dan just-in-time sebelum wave yang menyentuh surface terkait.

### Must capture before first pixel/markup implementation

Core minimum menggunakan desktop/mobile **PAIR** pada queue authoritative:

| Scope | Authoritative basename set | Pairs / images |
|---|---|---:|
| Login | `UI-001-login` | 1 / 2 |
| Application shell + six dashboard variants | seluruh `UI-002-dashboard-d01-*` sampai `d06-*` | 6 / 12 |
| Three ticket-list variants | seluruh `UI-004-ticket-list-l01-*` sampai `l03-*` | 3 / 6 |
| Representative ticket-create states | `UI-007-create-catalog`, `UI-007-create-normal-svc06`, `UI-007-create-svc07-dynamic` | 3 / 6 |
| Six ticket-detail variants | seluruh `UI-008-detail-t01-*` sampai `t06-*` | 6 / 12 |
| ACT-09 mixed profile | empat rows pada Priority 0 ACT-09 alternate profile | 4 / 8 |
| Representative admin screen | `UI-012-users` | 1 / 2 |
| Representative report | `UI-011-reports-populated` | 1 / 2 |
| **Core minimum** | 25 pairs | **25 / 50** |

Cross-coverage yang disengaja:

- authenticated shell desktop/mobile tercakup pada seluruh authenticated pairs;
- Team Chair safe detail tercakup oleh T-06;
- long-content representative tercakup oleh SVC-07 requester/Tier 1 detail;
- mobile representative tercakup karena setiap row adalah desktop/mobile pair.

Karena W1 saat ini direncanakan menyentuh badges dan form/error primitives, dua pair berikut juga wajib ditangkap sebelum file terkait diubah:

- `UI-006-status-priority-matrix`;
- `UI-007-create-svc07-validation-old`.

Dengan scope W1 yang sekarang, minimum efektif sebelum implementation adalah **27 pairs / 54 images**, ditambah execution note untuk visible focus, contrast, error association, dan any known baseline gap relevant to the exact W1 files.

### Conditional modal minimum

Jika upcoming wave menyentuh modal markup, `x-ui.modal-panel`, modal selector protocol, action-menu opener, focus behavior, atau generic modal JavaScript, seluruh **10 reachable modal rows** pada `12-SCREENSHOT-INDEX.md` wajib ditangkap lebih dahulu pada desktop dan mobile: **10 pairs / 20 images**.

Jika wave juga menyentuh validation/error routing, sepuluh Priority 2 desktop validation/error images wajib ditangkap just-in-time. Empat unreachable modal tidak boleh diberi temporary production opener untuk kebutuhan capture.

### Capture just-in-time before relevant wave

| Wave | Just-in-time evidence dari queue authoritative |
|---|---|
| W1 — visual foundation | Status/priority matrix, representative old/error state, focus/contrast/reduced-motion observation |
| W2 — shell/common pages | Remaining role-navigation/mobile-menu rows, sidebar persistence, notification dropdown, loading, SweetAlert, `pageshow`, Livewire re-init, login/password/notification/403 states |
| W3 — admin collections | Relevant admin collection, responsive table/card, filter, empty/error, and modal states before each touched screen |
| W4 — dashboard/report/users/services | Remaining dashboard/multi-role, report states, user/service density, editor/preview, long-content and export interaction evidence |
| W5 — ticket entry/lists/approvals | Queue tabs, requester filters/search/pagination, all create-service/file/validation states, approval profiles |
| W6 — ticket detail/workflow | Workflow supplement, SVC-02/03/07, status/priority, attachment/denial, all reachable modals, modal validation states, Team Chair and critical multi-role details |

Priority 1/2 items yang belum diperlukan W1 tetap berada di queue dan menjadi mandatory sebelum wave yang menyentuh behavior/surface tersebut.

## Design Analysis Readiness

**Status: READY**

Evidence sudah cukup untuk pekerjaan non-implementation berikut:

- mempelajari external UI reference;
- menentukan visual direction;
- membuat mapping **ADOPT / ADAPT / IGNORE**;
- menyusun information hierarchy dan design-system specification;
- merencanakan component architecture dan implementation waves;
- memetakan setiap konsep desain ke screen, role variant, dan frozen contract.

Readiness ini tidak memberi izin mengubah Blade, CSS, JavaScript, asset runtime, atau production behavior. Visual direction harus tetap ditandai provisional sampai dibandingkan dengan screenshot existing.

## Redesign Implementation Readiness

**Status: HOLD — MANUAL VISUAL BASELINE REQUIRED**

Perubahan pixel/markup pada `resources/views`, `resources/css`, atau `resources/js` belum boleh dimulai. Screenshot count masih nol, sehingga implementation gate tidak dapat diberi status PASS atau READY.

## Conditions to Remove Implementation Hold

Implementation hold hanya dapat dihapus setelah seluruh kondisi berikut terpenuhi:

1. Core minimum manual baseline **25 pairs / 50 images** selesai dengan exact filenames dari `12-SCREENSHOT-INDEX.md`.
2. W1-specific status/priority dan validation-old pairs selesai bila file primitive terkait tetap masuk scope pertama; total minimum efektif menjadi **54 images**.
3. Setiap image memakai viewport exact 1440×900 atau 390×844, browser/zoom/device scale yang konsisten, actor/fixture yang benar, tanpa credential atau PII.
4. Capture note merekam URL, actor, fixture, viewport, timestamp, cross-coverage, dan PASS/FAIL untuk runtime checks relevant to the first wave.
5. Application shell desktop/mobile, six dashboard variants, three ticket-list variants, representative create/detail/admin/report, ACT-09 mixed profile, Team Chair safe detail, long content, and relevant known gaps telah direview.
6. Jika first implementation scope menyentuh modal, 10 reachable modal pairs dan relevant validation/error captures selesai sebelum edit pertama.
7. Perbedaan yang ditemukan diklasifikasikan menurut `Future Change Classification`; existing functional defect tidak diselipkan ke redesign scope.
8. Four missing openers dan approval decision auto-open gap tetap terdokumentasi dan tidak diubah tanpa separate approval.
9. Setelah manual capture/interaction run, full `XDEBUG_MODE=off php artisan test` dan `npm run build` kembali PASS.
10. `git status`, `git diff --stat`, dan `git diff` membuktikan tidak ada production implementation change selama baseline capture.

Setelah conditions tersebut direview dan diterima, status implementation dapat diubah dari HOLD menjadi READY melalui evidence update terpisah; status tidak berubah otomatis hanya karena sebagian screenshot tersedia.

## Future Change Classification

| Finding/change | Classification | Rule |
|---|---|---|
| Redesign visual change | **ALLOWED** | Hanya setelah implementation hold dihapus dan frozen behavior/contract tetap identik. |
| Accessibility presentation improvement | **ALLOWED IF behavior/contract unchanged** | Tidak boleh mengubah authorization, payload, route, state, error semantics, atau data visibility. |
| Existing functional defect | **SEPARATE SCOPE** | Perlu explicit approval, acceptance criteria, dan regression tests; tidak boleh disamarkan sebagai redesign. |
| New feature | **OUT OF SCOPE** | Tidak boleh ditambahkan melalui redesign. |
| Backend/domain change | **OUT OF SCOPE** | Route/controller/request/policy/service/model/schema/auth tetap locked. |
| Dormant feature activation | **OUT OF SCOPE** | Dormant views dan no-trigger endpoints tidak boleh diaktifkan. |

Temuan manual yang belum terverifikasi diklasifikasikan terlebih dahulu sebagai evidence gap, bukan defect. Setiap classification harus merujuk UI ID, contract ID, role/fixture, dan screenshot/source evidence.

## W0 Final Status

**W0 BASELINE RATIFIED**

**DESIGN ANALYSIS: READY**

**REDESIGN IMPLEMENTATION: HOLD — MANUAL VISUAL BASELINE REQUIRED**

Production source changes pada W0.4: **NO**.

Satu-satunya repository change W0.4 adalah dokumen `docs/redesign/13-BASELINE-READINESS-GATE.md`.
