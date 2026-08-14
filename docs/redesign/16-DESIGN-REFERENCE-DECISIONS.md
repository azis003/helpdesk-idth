# Design Reference Decisions

Register ini mengunci keputusan atas 40 Frappe reference patterns. Ia tidak mengizinkan implementation, tidak menetapkan final design system, dan tidak mengubah implementation hold di `13-BASELINE-READINESS-GATE.md`.

## Decision Rules

- Setiap `DEC-*` mempunyai hubungan one-to-one dengan `FRP-*` agar traceability dan count deterministic.
- `ADOPT` berarti mengadopsi prinsip melalui implementation baru native SIHATI; bukan menyalin source, token, asset, atau dependency.
- `ADAPT` berarti reference harus tunduk pada route/form/query/policy/workflow/role/responsive contracts SIHATI.
- `IGNORE` berarti pattern tidak masuk implementation waves. Scope produk terpisah diperlukan untuk mengubah keputusan itu.
- Wave mengikuti `05-IMPLEMENTATION-WAVES.md`: W1 foundation; W2 shell/common; W3 admin collections; W4 role/data-heavy; W5 ticket entry/list/approval; W6 critical ticket detail/workflows.
- Semua non-ignored decisions tetap diblokir sampai manual baseline captures yang relevan di `11-VISUAL-BASELINE.md` dan `12-SCREENSHOT-INDEX.md` tersedia.
- “Screenshot prerequisite” juga mencakup keyboard/focus interaction capture ketika visual screenshot saja tidak cukup.

## Register A — Architecture, Shell, and Foundation

| Decision | Reference pattern | SIHATI target | Class | Rationale | Contract risk | Role risk | Responsive implication | Wave | Manual screenshot prerequisite |
|---|---|---|---:|---|---|---|---|---|---|
| DEC-001 | FRP-001 dual portal SPA root | Application root/routing | **IGNORE** | Vue Router/session-selected roots would replace Laravel routing and cannot model six SIHATI roles safely. | CRITICAL — routes/session/auth | CRITICAL — role flattening | N/A | NONE | N/A; do not implement |
| DEC-002 | FRP-002 collapsible desktop sidebar | Authenticated app navigation | **ADAPT** | Collapsed rail can reduce chrome, but visible/active destinations and current branch behavior remain server-derived. | HIGH — links/data attributes/global nav JS | CRITICAL — SA/T1 and Team Chair multi-role | Desktop expanded/collapsed; mobile uses separate treatment | W2 | YES — global shell for ACT-01…ACT-11 at desktop; key collapsed/expanded states |
| DEC-003 | FRP-003 mobile drawer/header | Mobile authenticated shell | **ADAPT** | Drawer is useful at 390px, but existing close/focus/link/notification behavior must be preserved. | HIGH — nav handlers | HIGH — hidden destination leakage/loss | Different mobile layout; no capability loss | W2 | YES — authenticated mobile shell, open drawer, focus/Escape/backdrop/return |
| DEC-004 | FRP-004 audience-specific nav groups | Role/policy navigation | **ADAPT** | Grouping may improve scanability; Frappe agent/customer model cannot replace SIHATI branch precedence. | HIGH — route visibility | CRITICAL — current Approver, Team Chair, SA+T1 | Same authorized destinations regrouped per viewport | W2 | YES — role navigation matrix including ACT-08/09/10/11 at both viewports |
| DEC-005 | FRP-005 compact page header | Shared page header/breadcrumb/actions | **ADAPT** | Strong common hierarchy; must preserve back/query context and action conditions. | MEDIUM globally; CRITICAL on UI-008 | HIGH on action-heavy pages | Actions wrap/collapse into safe overflow; all remain reachable | W2 foundation; W3–W6 per page | YES — representative common pages plus UI-008 action header desktop/mobile |
| DEC-006 | FRP-006 neutral border-led surfaces | Global surface language | **ADOPT** | Reduces card noise and supports dense work screens without source/token copying. | LOW presentation; contrast still gated | LOW directly | Same hierarchy at both viewports | W1 | YES — guest/auth shell, tables/cards/dialogs at both viewports; contrast review |
| DEC-007 | FRP-007 compact muted typography | Global information hierarchy | **ADAPT** | Size/weight relationships are useful; exact type scale waits for design-system phase and long-content evidence. | MEDIUM — truncation/wrapping | MEDIUM — muted data must not hide role-critical context | Responsive title/action wrapping and readable long content | W1 | YES — long subject/comment/filename fixtures and dashboard/table variants |
| DEC-008 | FRP-008 button hierarchy | All actions/forms/dialogs | **ADOPT** | Clear primary/secondary/ghost/icon/destructive hierarchy can be rewritten without changing forms. | HIGH where button owns form/method | HIGH where action is policy-conditional | Icon-only only with accessible name; stack/wrap mobile | W1, then validated per wave | YES — submit/loading/destructive/action-menu states, both viewports |
| DEC-009 | FRP-009 form control anatomy | Shared form presentation | **ADAPT** | Label/input/helper/error rhythm is valuable; HTML names, hidden values, old/error keys and validation remain frozen. | CRITICAL — form contracts | HIGH — conditional/read-only fields | One-column mobile; preserve focus/error order | W1 primitive; W3–W6 usage | YES — login/password, UI-007 dynamic form, admin modal/form, workflow form errors |
| DEC-010 | FRP-010 status/priority redundant cues | Badges and ticket states | **ADOPT** | Icon/shape plus text avoids color-only meaning; canonical SIHATI values stay authoritative. | MEDIUM — class/label mapping | MEDIUM — requester/safe labels may differ | Compact in rows; full labels retained or accessible mobile | W1 | YES — status/priority matrix desktop/mobile, contrast/forced-color checks |
| DEC-011 | FRP-011 notification panel/mobile page | UI-009 and nav indicator | **ADAPT** | Contextual density is useful; ownership, read forms, pagination, and link targets remain existing. | HIGH — POST forms/notification links | MEDIUM — user-owned data only | Panel may become page/drawer; same records/actions | W2 | YES — UI-009 list/empty/paginator plus desktop dropdown/mobile navigation behavior |

## Register B — Search, Filter, and List Decisions

| Decision | Reference pattern | SIHATI target | Class | Rationale | Contract risk | Role risk | Responsive implication | Wave | Manual screenshot prerequisite |
|---|---|---|---:|---|---|---|---|---|---|
| DEC-012 | FRP-012 command palette | Global search/commands | **IGNORE** | Adds global command model, client search, shortcuts, and dialog behavior outside presentation scope. | HIGH — navigation/actions | HIGH — command authorization | N/A | NONE | N/A; do not implement |
| DEC-013 | FRP-013 list context breadcrumb | UI-004/005/006 and admin collections | **ADAPT** | Current context can be clearer; saved-view persistence is excluded. | MEDIUM — query/back context | MEDIUM — variant title/context | Truncate labels without losing context; no Frappe view picker mobile | W3/W5 | YES — UI-004 L-01/L-02/L-03 and queue/all at both viewports |
| DEC-014 | FRP-014 quick/advanced filter anatomy | Existing server filter bars | **ADAPT** | Progressive disclosure is valuable only if existing GET parameters, normalization, and query preservation remain. | CRITICAL — query/filter/pagination | HIGH — available filters vary by variant | Secondary filters collapse; active filters remain perceivable | W3–W5 depending screen | YES — all documented filter permutations, invalid/normalized/reversed dates, page 2 preservation |
| DEC-015 | FRP-015 multi-sort/configurable columns | Ticket/admin lists | **IGNORE** | Adds persisted personalization and may change ordering/export/list contracts. | CRITICAL — sort/columns/export | HIGH — data visibility by column | N/A | NONE | N/A; do not implement |
| DEC-016 | FRP-016 click-cell filtering | Ticket/admin list rows | **IGNORE** | Would change existing link/click and query behavior, especially on compact mobile cards. | HIGH — row navigation/query | MEDIUM | N/A | NONE | N/A; do not implement |
| DEC-017 | FRP-017 dense list scaffold | UI-004/005/006, UI-012/016/018/019/021 | **ADAPT** | Dense rows and contextual states improve scanning; SIHATI keeps Blade table + mobile card + paginator. | HIGH — links/forms/paginator | HIGH — row data differs by role/projection | Existing desktop table/mobile cards stay semantically equivalent | W3 for admin; W5 for ticket lists | YES — list/empty/search/page-2/long-content states at 1440×900 and 390×844 |
| DEC-018 | FRP-018 selection banner/bulk actions | Ticket lists | **IGNORE** | No frozen bulk workflow/API/policy; introducing it is feature expansion. | CRITICAL — mutations | CRITICAL — mixed selection authorization | N/A | NONE | N/A; do not implement |
| DEC-019 | FRP-019 load more/local page length | Paginated lists | **IGNORE** | Conflicts with numbered server paginator and query preservation baseline. | CRITICAL — pagination | MEDIUM — scoped counts | N/A | NONE | N/A; do not implement |

## Register C — Ticket Detail, Communication, and Dynamic Form Decisions

| Decision | Reference pattern | SIHATI target | Class | Rationale | Contract risk | Role risk | Responsive implication | Wave | Manual screenshot prerequisite |
|---|---|---|---:|---|---|---|---|---|---|
| DEC-020 | FRP-020 desktop split ticket workspace | UI-008 desktop hierarchy | **ADAPT** | Conversation-first main area + metadata/workflow rail fits helpdesk work, but six data/action variants stay separate. | CRITICAL — detail/actions/data | CRITICAL — T-01…T-06 | Split desktop; stacked/tabbed mobile; independent scroll requires QA | W6 | YES — all six detail variants plus representative workflows/SVC/long content desktop |
| DEC-021 | FRP-021 dedicated mobile ticket detail | UI-008 mobile hierarchy | **ADAPT** | Mobile may need reprioritized sections rather than shrunken desktop; all authorized actions must remain reachable. | CRITICAL — modal/action reachability | CRITICAL — role-specific data/actions | Different layout/component strategy allowed later, not different authorization | W6 | YES — all T-01…T-06 at 390×844, action overflow, modal scroll, long content |
| DEC-022 | FRP-022 ticket status/action cluster | UI-008 header/action menu | **ADAPT** | Frequent vs overflow grouping is valuable; only controller-computed policy actions may render. | CRITICAL — 14 modal/action contracts | CRITICAL — state/assignment/current approver | Compact/overflow mobile with explicit labels | W6 | YES — union opener/modal matrix and every action state; VIS-001/002/005/015/016 checks |
| DEC-023 | FRP-023 multi-type timeline | UI-008 messages/history/workflow | **ADAPT** | One chronology with distinct renderers improves comprehension; no telephony/reaction/analytics additions. | HIGH — ordering/visibility | CRITICAL — public/internal/safe projection | Timeline wraps; optional filter tabs cannot hide required events | W6 | YES — public/internal/system/approval/wait/SVC events for requester/agents/approver/chair |
| DEC-024 | FRP-024 public vs internal composers | UI-008 public message/requester reply/internal note | **ADOPT** | Explicit audience distinction matches SIHATI’s most important communication safety rule. | CRITICAL — routes/payload/attachments | CRITICAL — private-data leak risk | Both remain distinct and clearly labeled on mobile; never one audience toggle with shared payload | W6 | YES — T-02/T-03/T-04/T-05; Team Chair negative; attachment visibility matrix |
| DEC-025 | FRP-025 collapsible composer/keyboard submit | UI-008 communication forms | **ADAPT** | Space-saving behavior is useful, but open/close/reset/retained value/old/error/loading semantics are frozen. | CRITICAL — form lifecycle | HIGH — form visibility conditions | Sticky/expandable mobile without covering content/actions | W6 | YES — validation failure, close/reopen retained state, Ctrl/Cmd submit, mobile keyboard/scroll; VIS-003 |
| DEC-026 | FRP-026 attachment chip/preview | UI-007/008/020 file links and upload lists | **ADAPT** | File identity can be clearer; all access must still flow through SIHATI authorized download routes. | CRITICAL — attachment authorization | CRITICAL — public/internal/requester/approver/chair rules | Filename wrapping/truncation; touch target; preview viewport | W4 UI-020; W5 UI-007; W6 UI-008 | YES — ATT-01…ATT-04, broken/missing file, long filename, all actor visibility/download cases |
| DEC-027 | FRP-027 collapsible metadata sections | UI-008/detail-heavy admin screens | **ADOPT** | Progressive disclosure can reduce long-page overload if section existence remains server-authorized. | HIGH — hidden inputs/actions must not move accidentally | CRITICAL on Team Chair/internal sections | Expanded/collapsed state visible; mobile headings remain reachable | W6 for ticket; W4 for configuration | YES — each role detail hierarchy, open/closed sections, focus and long content |
| DEC-028 | FRP-028 inline ticket metadata edit | UI-008 metadata/action fields | **IGNORE** | Direct inline mutation would bypass frozen forms, validation, workflow services, audit, and modal contracts. | CRITICAL | CRITICAL | N/A | NONE | N/A; do not implement |
| DEC-029 | FRP-029 dynamic conditional field grid | UI-007 and UI-021 field preview/editor | **ADAPT** | Grouping and responsive field layout are valuable; dynamic definitions and server validation stay exact. | CRITICAL — names/options/dependencies/errors | HIGH — requester vs T1 visibility/create-for-other | One-column mobile, logical error focus/order; file controls fit viewport | W5 UI-007; W4 UI-021 | YES — every field type/visibility/required/error/old state, service catalog→form, mobile |
| DEC-030 | FRP-030 knowledge suggestion | UI-007 subject/create flow | **IGNORE** | Knowledge base is not a SIHATI feature; adding suggestions changes product scope and create flow. | HIGH — create behavior | LOW direct | N/A | NONE | N/A; do not implement |

## Register D — Dialog, Dashboard, Administration, States, and Feedback

| Decision | Reference pattern | SIHATI target | Class | Rationale | Contract risk | Role risk | Responsive implication | Wave | Manual screenshot prerequisite |
|---|---|---|---:|---|---|---|---|---|---|
| DEC-031 | FRP-031 standard dialog anatomy/focus/loading | Generic and action modals | **ADOPT** | Consistent title/body/footer/close/loading/focus is high value; existing IDs/data/form/auto-open/reset behavior is immutable without defect scope. | CRITICAL — all modal contracts | CRITICAL on UI-008/admin targeting | Viewport gutter, internal scroll, action stack, focus trap/return | W1 shell; per-screen W3–W6 | YES — generic/modal matrix, validation auto-open, Escape/backdrop/Tab/return, mobile scroll; VIS-001–005/013/015/016 |
| DEC-032 | FRP-032 fixed responsive KPI/dashboard composition | UI-002 and UI-011 presentation | **ADAPT** | Border-led cards/filter grouping/contextual chart states can improve hierarchy; data and six variants remain backend-owned. | HIGH — filters/export/data sections | CRITICAL — D-01…D-06 scope | 1/2/multi-column based on content; filters scroll/stack | W4 | YES — all D variants, periods, empty/error, report initial/zero/export states at both viewports |
| DEC-033 | FRP-033 customizable dashboard | UI-002 | **IGNORE** | Drag/resize/add/remove/persist charts is new capability and persistence contract. | CRITICAL — stored layout/API | HIGH — data/card availability | N/A | NONE | N/A; do not implement |
| DEC-034 | FRP-034 Settings split-pane modal | UI-012–022 administration | **IGNORE** | Would collapse standalone named routes, policies, URLs, error contexts, and browser history into client state. | CRITICAL | HIGH — resource policies differ | N/A | NONE | N/A; do not implement |
| DEC-035 | FRP-035 CRUD list/header/search/dialog pattern | UI-012/016/017/018/021/022 | **ADAPT** | Consistent collection hierarchy is useful; each resource’s filters/forms/modals/policies remain distinct. | HIGH to CRITICAL by screen | HIGH — SA-only except announcements | Existing table/card and scrollable modal behavior; avoid dense DOM clipping | W3 simple collections; W4 users/services | YES — each admin screen desktop/mobile, search/empty/errors, per-record modal target, UI-012/UI-021 heavy DOM |
| DEC-036 | FRP-036 contextual state panels | All screens | **ADOPT** | Empty/loading/filtered-empty/denied/not-found/action failure should explain current context without changing HTTP meaning. | MEDIUM — wrong state can mask errors | HIGH where denial/data absence differ | Centered or in-flow based on container; avoid overlaying available action | W1 primitive; validated every wave | YES — inventory state matrix including UI-023, filters, dashboard, ticket access, admin empty |
| DEC-037 | FRP-037 realtime viewers/typing/live updates | Ticket/nav/dashboard | **IGNORE** | Requires Socket.IO, server events, shared state, and new collaboration behavior. | CRITICAL — backend/API | HIGH — presence/data exposure | N/A | NONE | N/A; do not implement |
| DEC-038 | FRP-038 dark theme | Global app | **IGNORE** | Adds theme state, alternate token set, asset/contrast QA, and persistence not approved in current scope. | MEDIUM technically; broad QA | LOW direct | N/A | NONE | N/A; do not implement |
| DEC-039 | FRP-039 keyboard/focus/listbox/reduced-motion | Global interaction/accessibility | **ADOPT** | Accessibility is behavioral quality, not styling; principles must be reimplemented and verified with current Blade/JS. | HIGH — modal/nav/form behavior | HIGH — keyboard must not reach unauthorized controls | Focus order follows responsive visual order; reduced motion alternatives | W1 baseline, then every wave | YES — VIS-011–014 keyboard-first, skip/main decision, Tab/Escape/return, contrast/zoom/forced-color/reduced-motion |
| DEC-040 | FRP-040 toast/action loading feedback | Existing flash/SweetAlert/global submit states | **ADAPT** | Consistent feedback prevents uncertainty; must reuse existing flash/loading/submit locks and preserve redirects. | HIGH — duplicate submit/redirect/flash | MEDIUM — outcome visibility | Toast/overlay must not obscure mobile action/error/focus | W1 feedback primitive; validated every wave | YES — success/error/validation/export/background-like waits and duplicate-submit tests desktop/mobile |

## Decision Count

| Class | Count | Decision IDs |
|---|---:|---|
| ADOPT | **8** | DEC-006, DEC-008, DEC-010, DEC-024, DEC-027, DEC-031, DEC-036, DEC-039 |
| ADAPT | **20** | DEC-002–005, DEC-007, DEC-009, DEC-011, DEC-013–014, DEC-017, DEC-020–023, DEC-025–026, DEC-029, DEC-032, DEC-035, DEC-040 |
| IGNORE | **12** | DEC-001, DEC-012, DEC-015–016, DEC-018–019, DEC-028, DEC-030, DEC-033–034, DEC-037–038 |
| **Total** | **40** | DEC-001 through DEC-040 |

## Ten Highest-value Decisions

Priority ini menilai value untuk presentation redesign, bukan urutan implementation. Wave/gates di register tetap authoritative.

| Priority | Decision / pattern | Value to SIHATI |
|---:|---|---|
| 1 | DEC-024 / FRP-024 public vs internal composers | Mengurangi risiko salah audience pada area privacy/authorization paling sensitif. |
| 2 | DEC-020 / FRP-020 split ticket workspace | Memberi hierarchy conversation-first untuk screen paling kompleks. |
| 3 | DEC-021 / FRP-021 mobile ticket hierarchy | Menjawab density/action reachability UI-008 pada 390×844. |
| 4 | DEC-031 / FRP-031 dialog anatomy | Meningkatkan konsistensi dan focus/loading di banyak workflow tanpa mengubah contract bila dilakukan disiplin. |
| 5 | DEC-014 / FRP-014 server filter presentation | Meningkatkan usability list/report/admin sambil mempertahankan query/pagination. |
| 6 | DEC-017 / FRP-017 dense list scaffold | Memberi pola scan yang konsisten untuk ticket dan admin collections. |
| 7 | DEC-010 / FRP-010 status/priority redundant cues | Membuat state cepat dipahami dan tidak bergantung warna. |
| 8 | DEC-006 / FRP-006 border-led surfaces | Mengurangi visual noise pada aplikasi yang data-dense. |
| 9 | DEC-039 / FRP-039 accessibility behavior | Menjaga keyboard/focus/reduced-motion sebagai acceptance behavior. |
| 10 | DEC-005 / FRP-005 compact page header | Memberi orientation/action hierarchy konsisten pada 23 screens. |

## Ten Decisions to Avoid Most Strongly

| Priority | Decision / pattern | Why avoid |
|---:|---|---|
| 1 | DEC-001 / FRP-001 SPA portal root | Mengganti routing/auth/layout architecture dan meratakan role. |
| 2 | DEC-028 / FRP-028 inline ticket edits | Dapat melewati validation, workflow, authorization, audit, and modal contracts. |
| 3 | DEC-018 / FRP-018 bulk actions | Menambah mutation/API/authorization surface baru. |
| 4 | DEC-037 / FRP-037 realtime presence | Memerlukan backend events/socket/state baru dan dapat mengekspos presence. |
| 5 | DEC-034 / FRP-034 settings modal architecture | Menghilangkan standalone routes/policies/error contexts admin. |
| 6 | DEC-033 / FRP-033 customizable dashboard | Menambah persisted product capability dan client grid behavior. |
| 7 | DEC-015 / FRP-015 configurable columns/multi-sort | Menambah personalization dan mengubah ordering/report assumptions. |
| 8 | DEC-019 / FRP-019 load more/local pagination | Merusak numbered paginator/query preservation baseline. |
| 9 | DEC-012 / FRP-012 command palette | Menambah global command/search/shortcut feature. |
| 10 | DEC-030 / FRP-030 knowledge suggestion | Memasukkan knowledge-base feature ke create flow. |

FRP-016 click-to-filter dan FRP-038 dark theme juga tetap `IGNORE`, meskipun tidak masuk sepuluh risiko tertinggi.

## Cross-cutting Conflict Notes

1. **Frappe role model vs SIHATI policies:** seluruh shell, dashboard, list, detail, attachment, and action decisions harus diuji menggunakan ACT-01 sampai ACT-11, bukan `isAdmin/isManager/isAgent` analog.
2. **Client list vs server list:** visual toolbar/list dapat berubah, tetapi query names, normalization, sorting, pagination, scope, exports, and redirect remain server-side.
3. **Dialog quality vs frozen defects:** better anatomy/focus tidak mengizinkan missing opener, approval auto-open, reset state, request-info attachment, triage payload, or SVC-03 fixes.
4. **Mobile reprioritization vs action reachability:** moving actions to overflow/tabs cannot hide authorized workflow or surface unauthorized one.
5. **Attachment preview vs authorization:** visual chip/preview cannot fetch raw URLs or bypass download policy, especially for Team Chair/Pemohon/current Approver.
6. **Shared component vs safe projection:** T-06/L-03 may share visual primitives but never request or render full Ticket data.
7. **Dashboard visual unity vs six variants:** common cards do not justify merging data feeds, heroes, or navigation capabilities.
8. **Accessibility improvement vs baseline evidence:** improvements are desirable but must be explicit decisions with interaction tests where they alter focus/navigation behavior.
9. **Admin consistency vs resource-specific contracts:** shared list/dialog anatomy cannot flatten standalone routes, self restrictions, nested errors, modal targeting, or permissions.
10. **External reference vs licensing:** direct source use remains a separate compliance decision; this register authorizes none.

## Phase Gate Result

- Reference decisions documented: **40/40**.
- Screen mapping dependency: **UI-001–UI-023 = 23/23**.
- Variant dependency: **D-01–D-06, L-01–L-03, T-01–T-06 = 15/15**.
- Baseline issue dependency: **VIS-001–VIS-017 = 17/17**.
- Design analysis: **READY FOR REVIEW**.
- Design system specification: **NOT CREATED / NEXT PHASE ONLY**.
- Redesign implementation: **HOLD — manual baseline prerequisites remain**.
- Production implementation authorization from this document: **NONE**.
