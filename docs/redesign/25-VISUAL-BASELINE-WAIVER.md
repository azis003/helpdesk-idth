# Visual Baseline Waiver

Dokumen ini adalah keputusan eksplisit project owner untuk FASE 3.4B. Dokumen ini tidak menyatakan bahwa screenshot UI lama pernah dibuat, tidak menggantinya dengan bukti sintetis, dan tidak mengubah implementation aplikasi. Dokumen ini hanya mengubah readiness gate dengan menerima risiko visual yang dijelaskan di bawah.

## Decision

**MANUAL BEFORE-REDESIGN VISUAL BASELINE:**<br>
**WAIVED BY PROJECT OWNER**

Keputusan ini berarti:

- 27 pasangan / 54 gambar pada minimum baseline tidak lagi diwajibkan sebelum W1;
- corpus screenshot Priority 0/1/2 bukan lagi blocker implementation redesign;
- perbandingan pixel-by-pixel sebelum/sesudah terhadap UI SIHATI lama sengaja tidak tersedia;
- jumlah screenshot before-redesign tetap **0/54**, tetapi ketiadaan tersebut sekarang merupakan keputusan risiko yang diterima, bukan pekerjaan yang tanpa sengaja belum selesai;
- screenshot lama tidak boleh digantikan dengan screenshot sintetis, hasil render HTML, mockup, rekonstruksi, atau bukti palsu.

`12-SCREENSHOT-INDEX.md` dan `24-MANUAL-BASELINE-CAPTURE.md` tetap disimpan sebagai evidence perencanaan/historis. `13-BASELINE-READINESS-GATE.md` tetap benar untuk kondisi pada saat dokumen itu ditulis. Untuk keperluan implementation gate, dokumen ini secara eksplisit **supersedes** persyaratan pada dokumen-dokumen tersebut bahwa 54 screenshot harus selesai sebelum W1. Tidak ada dokumen historis yang dihapus atau ditulis ulang.

## Reason

Project owner menerima waiver karena:

- aplikasi existing telah berfungsi;
- sasaran redesign terbatas pada presentation layer;
- contract freeze UI/aplikasi telah tersedia;
- regression suite dan deterministic UI fixture telah tersedia;
- role, variant, HTTP, form, query, modal, dan authorization contracts telah dipetakan;
- design direction dan design-system specification telah selesai;
- hilangnya pembanding pixel-by-pixel terhadap UI lama diterima secara sadar sebagai project risk.

Prinsip proyek tetap: **UBAH WAJAHNYA, JANGAN BONGKAR MESINNYA.**

## What Is Waived

Waiver hanya mencakup kewajiban evidence visual **sebelum** redesign:

- minimum 27 pasangan / 54 screenshot desktop dan mobile;
- full Priority 0/1/2 screenshot corpus sebagai prasyarat redesign;
- photographic golden dari spacing, layout, wrapping, overflow, responsive behavior, focus, contrast, posisi/scroll dialog, dan keyboard flow UI lama;
- pixel-perfect before/after comparison dengan UI lama;
- penyelesaian manual capture sebagai syarat untuk membuka W1.

Waiver tidak menyatakan screenshot baseline `COMPLETED`. Status faktualnya tetap **0/54 dan WAIVED**. Screenshot UI lama tidak boleh direkonstruksi setelah implementation berubah lalu diberi label sebagai before-redesign baseline.

## What Is Not Waived

Waiver tidak mengizinkan perubahan atau regresi terhadap:

- route, route name/alias, URL, atau HTTP method;
- controller, request, validation, policy, middleware, Gate, atau `DomainAuthorization`;
- authentication, session behavior, authorization, role, atau data visibility;
- form action/payload, query behavior, pagination, modal contract, dan JavaScript behavior contract;
- workflow/state machine, SLA, approval, waiting state, completion, confirmation, reopen, third party, SVC-02, atau SVC-03;
- attachment authorization atau Team Chair safe projection;
- current/stale Approver behavior dan multi-role precedence;
- reporting/export, notification, audit, job/queue/scheduler, service/domain, model, enum, migration, atau database contract;
- dormant feature/no-trigger restriction;
- regression dan frontend build gate.

Existing defect tetap merupakan separate scope. Waiver bukan izin untuk memperbaiki defect, menambah fitur, mengaktifkan dormant surface, atau mengubah backend/domain.

## Accepted Risks

Project owner secara eksplisit menerima:

- tidak ada perbandingan pixel-perfect before/after;
- tidak ada photographic evidence untuk spacing/layout lama;
- tidak ada photographic evidence untuk responsive rendering lama;
- tidak ada photographic evidence untuk clipping/overflow sebelumnya;
- forensic comparison menjadi lebih sulit bila kelak muncul sengketa visual.

Project owner **tidak** menerima route change, authorization change, role/data leakage, form payload change, query/pagination change, modal/workflow contract change, attachment-policy change, Team Chair safe-projection change, current Approver change, regression failure, dormant-feature activation, atau backend/domain change.

Konsekuensinya, keputusan visual exact harus dinilai dari design system, frozen contracts, fixture yang relevan, dan perilaku implementation baru pada browser. Ketiadaan foto lama tidak boleh dipakai untuk menurunkan acceptance bar non-visual.

## Remaining Contract Safeguards

Semua safeguard berikut tetap mandatory:

| Safeguard | Coverage / invariant yang dipertahankan |
|---|---|
| HTTP/UI contract freeze | **112 HTTP contracts**, termasuk named routes, aliases, methods, redirects, flash, download/export, dan compatibility behavior |
| Screen coverage | **23/23 active screens**; dormant/legacy views tetap `DO NOT REDESIGN / DO NOT ACTIVATE` |
| Variant coverage | **15/15 primary variants**: dashboard D-01–D-06, ticket list L-01–L-03, ticket detail T-01–T-06 |
| Role coverage | Enam canonical roles serta critical multi-role precedence tetap server-authoritative |
| Sensitive projections | Team Chair safe projection, private/internal communication, attachment visibility, dan role-scoped data tidak boleh bocor |
| Approver behavior | Current dan stale Approver serta active-assignment checks tetap identik |
| Modal contract | **14 modal IDs**, existing opener/auto-open state, `data-*` selectors, form target, reset/retained state, dan error routing tetap dibekukan |
| Form contract | Exact input names/nesting, hidden inputs, CSRF, method spoofing, `old()`, error keys, file fields, action, dan method tetap identik |
| Query/navigation contract | Query parameter normalization, search/filter/sort, pagination preservation, back context, route aliases, dan download links tetap identik |
| Domain workflow | Status transitions, assignment, approval, request information, completion, requester confirmation, reopen, third party, SVC-02, SVC-03, dan SLA semantics tetap server-owned |
| Cross-cutting behavior | Authentication/session, notification, reporting/export, audit logging, attachment authorization, job/queue/scheduler, serta error/empty/loading behavior tetap dijaga |
| Dormant restriction | Dormant views, missing openers, no-trigger endpoints, dan ignored design destinations tidak boleh diaktifkan |

Setiap authorized implementation wave tetap wajib memakai `00-REDESIGN-GUARDRAILS.md`, `02-UI-CONTRACT-MATRIX.md`, `04-ROLE-VARIANT-MATRIX.md`, deterministic fixtures, automated tests, dan wave-specific acceptance checklist sebagai contract authority.

## Known Defects Still Frozen

Waiver tidak memperbaiki dan tidak memberi izin memperbaiki baseline asymmetry/defect berikut tanpa separate defect scope, explicit approval, acceptance criteria, dan regression tests:

1. empat missing modal openers;
2. approval decision validation auto-open dan `old('decision_note')` gap;
3. ticket modal reset/retained-state semantics;
4. request-information backend attachment capability versus active body-only UI;
5. triage backend capability versus active `self`/`tier_2`/`assigned_to_id` UI;
6. SVC-03 reachability/readiness behavior;
7. SVC-03 HTML requiredness versus nullable compatibility fields;
8. ACT-08 Super Admin + Tier 1 admin-dominant navigation/dashboard precedence;
9. ACT-09 mixed Pemohon/current-Approver presentation;
10. Team Chair safe-projection override dan additional-role caveat;
11. pending-Approver attachment-policy asymmetry terhadap current active assignment;
12. login backend `remember` support versus active UI yang tidak merender control;
13. `tickets.claim` dan `tickets.handle` tanpa active UI trigger;
14. room, attachment-policy, operational-policy, user split, service-field-status, serta dormant/no-trigger surfaces lainnya.

Register `VIS-001`–`VIS-017` tetap authoritative dan classified **17/17**. Waiver tidak mengubah evidence gap menjadi confirmed defect dan tidak mengubah preserved behavior menjadi design requirement baru.

## OPEN-011–020 New Disposition

Tidak ada item di bawah yang dianggap resolved dari screenshot lama. Previous state seluruhnya adalah `OPEN — MANUAL BASELINE REQUIRED`; new state seluruhnya adalah `BASELINE WAIVED — RESOLVE JUST-IN-TIME DURING AUTHORIZED WAVE`.

`IMPLEMENTATION-DERIVED DECISION` berarti exact choice ditentukan dari perilaku surface redesign di browser pada wave yang berwenang, lalu dicatat dan diuji. `DESIGN-SYSTEM PROVISIONAL DECISION` berarti candidate system-level dapat dipilih lebih awal, tetapi baru menjadi accepted exact decision setelah divalidasi pada surface/wave terkait. Kedua kelas tetap tunduk pada frozen contracts dan tidak memberi authorization lintas-wave.

| ID | Concern | Classification | Affected wave | New disposition / evidence yang sah |
|---|---|---|---|---|
| OPEN-011 | Expanded/collapsed sidebar width dan internal gutter | **A — IMPLEMENTATION-DERIVED DECISION** | W2 | Tentukan dari shell baru dengan enam role, ACT-08–11, collapse/persist/reload, long labels, focus, dan no-leakage verification. |
| OPEN-012 | Mobile navigation/drawer geometry | **A — IMPLEMENTATION-DERIVED DECISION** | W2 | Tentukan dari mobile shell baru; verifikasi destination parity, backdrop, open/close/Escape, focus return, keyboard/touch, dan overflow untuk actor relevan. |
| OPEN-013 | Page gutters, max-width, dan full-width exceptions | **B — DESIGN-SYSTEM PROVISIONAL DECISION** | W1/W2, lalu per-screen wave | W1 boleh menetapkan candidate foundation; W2 dan setiap screen wave wajib memvalidasi readable measure, density, alignment, dan overflow sebelum nilai exact diterima. |
| OPEN-014 | Modal width bands dan scroll thresholds | **A — IMPLEMENTATION-DERIVED DECISION** | W3–W6, hanya saat modal terkait diotorisasi | Tentukan per modal family dari redesigned runtime, validation/error, mobile scroll, focus, footer reachability, serta existing open/reset contract. |
| OPEN-015 | Desktop ticket main/metadata-rail ratio dan sticky behavior | **A — IMPLEMENTATION-DERIVED DECISION** | W6 | Tentukan dengan T-01–T-06, Team Chair safe data, SVC/attachment/long-content, action reachability, dan scroll verification. |
| OPEN-016 | Mobile ticket hierarchy, sticky action/composer, dan disclosure defaults | **A — IMPLEMENTATION-DERIVED DECISION** | W6 | Tentukan per T-01–T-06 dari redesigned mobile runtime, keyboard/focus, workflows, no leakage, dan critical-action reachability. |
| OPEN-017 | Responsive breakpoints | **B — DESIGN-SYSTEM PROVISIONAL DECISION** | W1/W2, lalu divalidasi pada setiap wave | W1/W2 boleh memilih candidate breakpoints; setiap wave wajib menguji intermediate widths, reflow/table-card behavior, hidden controls, dan overflow sebelum menerima applicability-nya. |
| OPEN-018 | UI-012 Users / UI-021 Services density dan DOM presentation | **A — IMPLEMENTATION-DERIVED DECISION** | W4 | Tentukan dari redesigned records/forms/modals dengan marker/payload parity, focus/order, responsive density, validation state, dan performance observation. |
| OPEN-019 | Truncation/clamp dan recovery affordance | **A — IMPLEMENTATION-DERIVED DECISION** | Relevant W3–W6 surfaces | Tentukan per content type/surface dengan VIS-017 long fixtures; full recovery wajib tersedia dan helper/error tidak boleh ditruncate. |
| OPEN-020 | D-01–D-06 dashboard/report grid composition | **A — IMPLEMENTATION-DERIVED DECISION** | W4 | Tentukan dari enam role dashboards, ACT-08–11, populated/zero report states, long values, dan prinsip actionable work before KPI. |

Perubahan disposition ini menghapus ketergantungan pada old-baseline screenshot, bukan menghapus kebutuhan untuk membuat keputusan exact secara terkontrol. Setiap resolution harus dicatat pada wave terkait bersama actor, viewport, acceptance result, dan contract checks; tidak boleh ditulis sebagai “resolved from old baseline screenshot.”

## After-Implementation Visual QA Policy

Karena old screenshots di-waive, setiap implementation wave wajib menjalankan **after-change verification** berikut pada seluruh surface yang diubah:

1. automated regression **PASS**;
2. frontend build **PASS**;
3. relevant deterministic fixture actors exercised;
4. desktop responsive verification;
5. mobile responsive verification;
6. tidak ada authorization atau data leakage;
7. tidak ada authorized action yang hilang;
8. tidak ada unauthorized action baru;
9. tidak ada horizontal page overflow kecuali secara eksplisit diizinkan oleh specification;
10. long text tetap dapat dipulihkan/dibaca sepenuhnya;
11. focus-visible diverifikasi untuk modified interactive controls;
12. validation error dan old-value behavior tetap identik;
13. `git diff` terbatas pada scope wave yang diotorisasi.

Verification harus menggunakan enam canonical roles, relevant multi-role actors, current/stale Approver, Team Chair safe projection, modal/form/query fixtures, serta viewport dan state yang sesuai dengan surface. Temuan visual dinilai terhadap design system dan acceptance criteria baru; temuan contract/behavior dinilai terhadap frozen source-of-truth.

Screenshot setelah redesign bersifat **opsional** dan boleh dibuat untuk review, dokumentasi, QA, stakeholder presentation, atau perbandingan antar-iterasi redesign. Screenshot tersebut tidak pernah menjadi evidence keadaan UI lama dan tidak boleh dinamai atau dilaporkan sebagai before-redesign baseline.

## Regression Gate

Gate dijalankan pada **2026-08-14** tanpa mengubah production source, tests, atau fixtures dalam FASE 3.4B.

| Check | Command | Result |
|---|---|---|
| Full Laravel regression | PowerShell equivalent dari `XDEBUG_MODE=off php artisan test` | **PASS — 140 total; 139 passed; 1 intentional PostgreSQL concurrency skip on SQLite; 0 failed; 0 errors; 1,780 assertions** |
| Frontend production build | `npm run build` | **PASS — Vite 6.4.3; 59 modules transformed** |
| Contract freeze | Review authoritative documents dan phase diff | **INTACT** |
| Known-defect freeze | Review VIS/known-defect register | **INTACT** |

Kedua executable gates lulus. Intentional skip tetap baseline yang telah disetujui dan bukan regression failure.

## Implementation Readiness

Readiness inputs terverifikasi sebagai berikut:

| Input | Status |
|---|---|
| FASE 3.1 reference audit | **PASS** |
| FASE 3.2 design direction | **PASS WITH OPEN QUESTIONS** |
| FASE 3.3 design system | **PASS WITH MANUAL DEFERRED DECISIONS** |
| SIG mapping | **SIG-001–SIG-040 = 40/40** |
| DEC accounting | **DEC-001–DEC-040 = 40/40** |
| Explicitly ignored destinations | **12/12 remain ignored**: DEC-001, DEC-012, DEC-015, DEC-016, DEC-018, DEC-019, DEC-028, DEC-030, DEC-033, DEC-034, DEC-037, DEC-038 |
| Active UI mapping | **UI-001–UI-023 = 23/23** |
| Primary variants | **15/15**: D-01–D-06, L-01–L-03, T-01–T-06 |
| VIS classification | **VIS-001–VIS-017 = 17/17**, unchanged/frozen according to its disposition |
| Token specification | **154 retained**: 68 color, 15 typography, 18 spacing, 26 size, 5 radius, 4 border, 3 shadow, 5 motion, 4 focus, 6 layer; 52 final-concept, 84 provisional, 18 deferred |
| Design-system production implementation | **NONE** |
| FASE 3.4B production source/test/fixture change | **NO** |

Project-owner waiver sekarang menggantikan screenshot prerequisite pada implementation-readiness rule. Contract freeze, design-system specification, regression/build gate, known-defect freeze, and repository-scope restriction semuanya tetap berlaku.

Status sebelumnya:

> **REDESIGN IMPLEMENTATION: HOLD — MANUAL VISUAL BASELINE REQUIRED**

Status baru:

> **REDESIGN IMPLEMENTATION: READY WITH ACCEPTED VISUAL BASELINE RISK**

W1 authorization: **YES — foundation-only**. Authorization ini hanya mengizinkan memasuki W1 melalui task implementation terpisah sesuai `05-IMPLEMENTATION-WAVES.md`, `17-SIHATI-DESIGN-DIRECTION.md`, `18-SIHATI-UI-GRAMMAR.md`, `20-SIHATI-DESIGN-SYSTEM.md`, `21-DESIGN-TOKEN-SPEC.md`, dan `22-COMPONENT-STATE-MATRIX.md`.

W1 tetap terbatas pada visual tokens/shared primitives dan acceptance coverage yang telah ditetapkan. Authorization ini **tidak** mengizinkan perubahan layout/navigation/modal/business visibility dan **tidak** memberi blanket authorization untuk W2–W6. Setiap wave/surface berikutnya memerlukan scope, prerequisites, just-in-time decisions, tests, role/fixture validation, visual QA, serta diff gate-nya sendiri.

## Final Gate

Seluruh kondisi gate terpenuhi:

- project-owner waiver tercatat;
- regression dan build lulus;
- contract freeze tetap intact;
- design-system readiness dan traceability lengkap;
- screenshot absence tidak dipalsukan sebagai evidence;
- known defects tetap frozen;
- tidak ada production source, test, atau fixture yang diubah dalam fase ini;
- satu-satunya perubahan FASE 3.4B adalah dokumen ini.

**FASE 3.4B PASS — IMPLEMENTATION READY WITH ACCEPTED VISUAL BASELINE RISK**
