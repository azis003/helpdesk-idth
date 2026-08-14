# Design System Decisions

Dokumen ini menambahkan disposition FASE 3.3 terhadap register `19-DESIGN-OPEN-QUESTIONS.md` tanpa menghapus pertanyaan, rationale, atau evidence history sebelumnya. `RESOLVED` berarti arah/strategi final dapat dibekukan sekarang. `PROVISIONAL` berarti candidate exact sudah dipilih untuk specification discussion tetapi wajib divalidasi terhadap manual baseline. `OPEN — MANUAL BASELINE REQUIRED` berarti tidak ada exact decision yang diambil.

## Decision Summary

| Disposition | Count | IDs |
|---|---:|---|
| RESOLVED | 4 | OPEN-001, OPEN-021, OPEN-022, OPEN-023 |
| PROVISIONAL | 9 | OPEN-002–OPEN-010 |
| OPEN — MANUAL BASELINE REQUIRED | 10 | OPEN-011–OPEN-020 |
| Total register | **23** | OPEN-001–OPEN-023 |

Provisional decisions are design-system dispositions, not production values. They remain validation-gated but are no longer unspecified questions. The remaining unresolved/open count is therefore **10**.

## OPEN-021 Decision

**Disposition: RESOLVED**

Decision: metadata disclosure state is **not persisted across page navigation or browser sessions** in redesign scope.

Rules:

1. A disclosure may use transient/local page DOM state when a future authorized wave implements it.
2. No disclosure state is written to `localStorage`, `sessionStorage`, cookie, server session, database, hidden form field, or query parameter.
3. Navigating to another page/record or performing a full reload uses the documented default for that page/variant.
4. Role, ticket, and safe-projection changes never inherit a stale hidden/open state from another context.
5. Disclosure does not move hidden form inputs, authorized actions, errors, or sensitive data outside their frozen contract.
6. Exact default-open sections and mobile order remain blocked by OPEN-015/016; this decision resolves persistence only.

Rationale: persistence would add interaction/storage state, may leave important information stale-hidden, and is unnecessary for presentation redesign.

Affected: SIG-031; UI-008/UI-019/UI-021; W4/W6. Acceptance must cover initial state, toggle, keyboard/expanded semantics, navigation away/back, reload, role/record change, and absence of new storage keys.

## OPEN-022 Decision

**Disposition: RESOLVED**

Decision: adopt skip-to-content as an explicit **ACCESSIBILITY PRESENTATION / INTERACTION IMPROVEMENT**, not a baseline defect repair.

Implementation status: **NOT IMPLEMENTED**. It may be implemented only after the manual implementation hold is removed and the authorized W1/W2 change includes acceptance coverage.

### Target and first-focus behavior

| Layout/context | Approved target behavior | First-focus acceptance |
|---|---|---|
| Guest layout | One unambiguous main-content target containing the current guest task | First Tab exposes the skip link; activation moves focus/viewport to main; next Tab reaches the first form/control in logical order |
| Authenticated layout | One main target for page content, separate from desktop/mobile navigation and utilities | First Tab exposes the skip link; activation bypasses shell navigation and focuses the main target; next Tab reaches the first page control/link |
| Mobile navigation | Skip behavior is defined in the closed-nav page order and must not become focusable behind an open navigation overlay | Closed: same first-focus behavior. Open: existing accepted containment/close/return behavior wins; no duplicate skip target inside drawer |
| Standalone 403 | One main target containing the denial heading/copy and safe destination | First Tab exposes skip link; activation focuses main/heading target; next Tab reaches the safe destination link |

Acceptance rules:

1. There is exactly one visible-on-focus skip control and one valid target per rendered document.
2. It is the first keyboard-focusable control on initial page load.
3. Activation changes focus as well as scroll position; focus indicator is visible.
4. Target semantics do not add an extra conflicting `<main>` or duplicate ID.
5. Browser back/forward, Livewire navigation re-init, validation redirect, and global loading do not duplicate the link/target or trap focus.
6. Guest/auth/mobile/403 are tested keyboard-only at 100%, 200%, and narrow viewport.
7. No role destination, action visibility, route, payload, modal, or server authorization changes.

Related: VIS-011, DSP-010, SIG-001/003/037, DEC-039, W1/W2. This decision does not remove the manual baseline requirement for existing first-focus/nav/modal behavior.

## OPEN-001–010 Resolution

### OPEN-001 — Font family strategy

**Disposition: RESOLVED**

Decision: use a system-accessible sans stack for UI, data, and long-form content; add no font dependency.

Candidate stack:

```text
ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif
```

Evidence:

- `resources/css/app.css` currently imports Inter from Google Fonts and already contains system fallback values.
- `resources/css/theme-modern.css` repeats Inter plus a broad system stack.
- repository contains no local font assets;
- Tailwind v4 exposes current UI typography through `@theme`/CSS;
- PDF export separately uses DejaVu Sans and remains outside runtime UI font change.

Rationale: the system stack supports Indonesian text, punctuation, numerals, dense UI, and long content without a new network/local dependency or Frappe-derived identity. Inter is not selected merely because the reference uses it. Any future removal of the existing Google Fonts import belongs to authorized W1 implementation after hold removal; FASE 3.3 changes nothing.

Validation still required: Windows/macOS/Linux/browser fallback rendering, long Indonesian strings, numerals, weight availability, layout shift, and PDF unaffected check. These validations may refine fallback ordering but do not reopen the no-new-dependency strategy.

### OPEN-002 — Exact type scale, weight, line-height, tracking

**Disposition: PROVISIONAL**

Candidate roles and values are recorded in `20-SIHATI-DESIGN-SYSTEM.md` and TOK-TYPE rows: exceptional 32/40, page title 28/36, section 20/28, record 15/22, body 14/20, long body 15/24, label 13/18, metadata/helper/error 12/18, control 14/20, badge 12/16, KPI 24/32 with restrained 400/500/600/700 weights.

Final concepts: routine pages have no oversized display type; metadata meets normal-text contrast; headings follow semantic order; long content wraps; KPI has scope/period. Exact metrics remain provisional because manual screenshots and platform font metrics do not exist.

Evidence required: UI-001–023 representative headings/forms/tables, VIS-017 long content, D-01–D-06 KPI values, 200%/400% zoom, both viewports, cross-platform stack render.

### OPEN-003 — Neutral foundation palette

**Disposition: PROVISIONAL**

Candidate: cool neutral canvas `#F6F8FA`, primary `#FFFFFF`, muted `#EEF1F4`, hover `#E7EDF2`, selected `#D7F0F5`, border `#D6DEE5`, strong border `#7A8793`, text `#161C22`, muted `#4E5A66`, subtle `#667381`.

Evidence/rationale: source already uses cool slate/teal foundations, and the direction requires border-led hierarchy. Candidate text pairs calculate above WCAG-oriented normal-text targets; strong border candidate calculates above 3:1 on white. Passive divider contrast is not treated as an essential control boundary. Exact hue/value remains provisional pending screenshots, forced-colors, zoom, and surface nesting review.

### OPEN-004 — Brand accent and usage

**Disposition: PROVISIONAL**

Candidate: institutional teal `#135D72`, hover `#154D5E`, soft `#D7F0F5`, foreground `#FFFFFF`.

Usage final-concept: restrained identity, primary action, current navigation marker, selected context, and supporting public-audience marker. It is not a large page background, generic card fill, or substitute for feedback/status.

Evidence/rationale: teal already appears in existing source/branding direction, so this evolves an established SIHATI cue rather than copying Frappe or inventing a new decorative palette. White-on-accent candidate is about 7.4:1. Exact hue and branding relationship require UI-020/login/shell/dashboard screenshots and logo variants.

### OPEN-005 — Eleven status colors

**Disposition: PROVISIONAL**

Final semantic grouping:

- NEW: Baru;
- ACTIVE: Diproses, Dikerjakan;
- WAITING: Menunggu Persetujuan, Menunggu Pemohon, Menunggu Pihak Ketiga, Menunggu Konfirmasi;
- SUCCESS/CLOSED: Ditutup;
- NEGATIVE/REJECTED: Ditolak, Tidak Disetujui;
- NEUTRAL/CANCELLED: Dibatalkan.

Each status keeps its exact canonical label and a distinct family/context cue. Candidate aliases use info, accent, warning, success, danger, and neutral pairs respectively. Exact hues/SVG cues are provisional; shared color never equates workflow semantics.

Evidence required: `UI-006-status-priority-matrix` desktop/mobile, representative detail/list/dashboard/filter states, 11/11 labels, forced-colors, zoom, and adjacent-state distinguishability.

### OPEN-006 — Priority cue and tones

**Disposition: PROVISIONAL**

Final non-color concept: a fixed four-segment indicator with 4/4 Kritis, 3/4 Tinggi, 2/4 Sedang, 1/4 Rendah plus canonical text and screen-reader expansion. Candidate tones are danger, warning, info, and muted respectively.

This is one coherent urgency scale, not four unrelated pills. Priority remains separate from status, SLA, and destructive actions. Segment geometry/tone are provisional pending compact/mobile/forced-colors tests.

### OPEN-007 — Spacing scale and density

**Disposition: PROVISIONAL**

Candidate 4px scale: 0, 4, 8, 12, 16, 20, 24, 32, 40, 48. Final semantic roles: control-internal, field, row, section, page, modal, timeline, and toolbar.

Final rule: compact inside related controls/records, larger between task groups. Exact scale and component application are provisional. It does not decide sidebar/page/modal/detail/dashboard geometry and does not close OPEN-011–020.

### OPEN-008 — Radius and elevation

**Disposition: PROVISIONAL**

Candidate radius scale: 0, 4, 8, 12, full. `full` is restricted to avatar, circular affordance, and true compact semantic/count chips. Candidate shadows: none for static surfaces; one floating level; one modal level.

Final rule: border + surface is the static default; elevation is temporary/selective; no glassmorphism, glow, nested rounded cards, or card soup. Exact radius/shadow values require surface/modal/dropdown screenshots.

### OPEN-009 — Focus-visible treatment

**Disposition: PROVISIONAL**

Candidate: `#005FCC` 3px ring with 2px offset, plus a light separation edge on strong accent/danger surfaces; forced-colors uses system Highlight/outline. Candidate color is about 5.98:1 on white.

Final rule: visible, not clipped, and distinct from invalid/error. Exact width/offset/color remain provisional until keyboard specimens cover neutral, semantic, floating, table overflow, mobile nav, and modal surfaces at zoom/forced-colors.

### OPEN-010 — Motion durations/easing

**Disposition: PROVISIONAL**

Candidates:

- instant/state 0ms;
- fast/control 120ms ease-out;
- standard/drawer 180ms cubic-bezier(.2,.8,.25,1);
- modal 160ms ease-out;
- disclosure 140ms ease-out.

Reduced-motion substitute is 0ms/final-position state while focus, expanded, loading, and outcome semantics remain. No decorative animation. Exact candidates require drawer/modal/disclosure/loading runtime comparison and cannot alter focus timing or server lifecycle.

## OPEN-023 Resolution

**Disposition: RESOLVED**

Decision: retain a first-party inline SVG icon strategy; add no icon dependency and copy no Frappe assets.

Repository evidence:

- no icon library/package dependency is declared;
- no repository `.svg` asset file is present;
- 215 inline `<svg>` instances appear across 43 active/related Blade files;
- current convention includes navigation, menu, notification, file, CRUD, status/feedback, identity, and disclosure examples, but paths/strokes/accessibility are not centrally cataloged.

Design-system rule:

1. Reuse/reconcile legally and technically suitable first-party inline SVG concepts already in the project.
2. Use one consistent viewBox/stroke vocabulary per functional family after manual comparison.
3. Supporting/decorative SVG is `aria-hidden` when adjacent text names the concept.
4. Icon-only control gets its accessible name from the control; tooltip or SVG `<title>` is not the sole name.
5. Status and priority icons remain supplementary to canonical text.
6. New library remains a future explicit dependency/license decision, not a fallback within redesign.

Missing system pieces are a centralized semantic path registry, consistent priority 1–4 cue, and consolidated state icon variants. They are future implementation primitives, not feature or dependency gaps.

## OPEN-011–020

All ten remain **OPEN — MANUAL BASELINE REQUIRED**.

| ID | Must remain undecided | Evidence gate | Specification available now |
|---|---|---|---|
| OPEN-011 | Exact expanded/collapsed sidebar widths and gutter | Six role shell pairs, ACT-08–11, collapsed/persisted exact files | Stable navigation/accessible-name concept |
| OPEN-012 | Exact mobile drawer geometry | Eleven exact mobile-nav files + keyboard/touch notes | Compact header/destination parity/focus rules |
| OPEN-013 | Exact page gutters/max-width/full-width exceptions | Guest/form/list/detail/report/users/services pairs | Relative density/readable-measure principles |
| OPEN-014 | Exact narrow/standard/wide/mobile modal width/scroll thresholds | Ten reachable modal pairs + ten validation files + focus/scroll notes | Modal anatomy and variant intent |
| OPEN-015 | Exact desktop ticket main/rail ratio/sticky behavior | T-01–T-06 desktop + SVC/long-content scroll evidence | Conversation-first main + supporting rail concept |
| OPEN-016 | Exact mobile detail order/sticky/disclosure defaults | T-01–T-06 mobile + workflow/keyboard evidence | Identity/state/critical-action-first principle |
| OPEN-017 | Exact responsive breakpoints | 1440×900/390×844 corpus + intermediate exploration | Reflow/table-card/dedicated hierarchy rules |
| OPEN-018 | Exact UI-012/UI-021 density geometry/DOM presentation | Users/services pairs + per-record modal focus/order/performance | Preserve marker/payload contract without preserving byte weight |
| OPEN-019 | Exact truncation/clamp/recovery affordance | VIS-017 long-content list/detail/admin/mobile corpus | Full recovery; helper/error never truncates |
| OPEN-020 | Exact D-01–D-06/report grid composition | Six dashboards, ACT-08–11, report populated/zero evidence | Work-before-KPI and role-specific primitives |

Exact filename/category mapping and blocked waves are recorded in `20-SIHATI-DESIGN-SYSTEM.md` under “Deferred Decisions.” None is closed by candidate token ranges.

## Remaining Open Questions Count

**10** questions remain open: OPEN-011 through OPEN-020.

- RESOLVED/PROVISIONAL design-system dispositions: 13/23.
- Manual-baseline-dependent open decisions: 10/23.
- Implementation hold: unchanged.

## DEC Traceability

Coverage remains **DEC-001–DEC-040 = 40/40**:

- ADOPT: 8/8 respected;
- ADAPT: 20/20 mapped to SIHATI-native systems and frozen contracts;
- IGNORE: 12/12 have no token or component implementation destination.

No destination was created for DEC-001, DEC-012, DEC-015, DEC-016, DEC-018, DEC-019, DEC-028, DEC-030, DEC-033, DEC-034, DEC-037, or DEC-038.

## VIS Compliance

| VIS | FASE 3.3 decision-system treatment | Compliance |
|---|---|---|
| VIS-001 | Four missing openers remain missing; no action/icon/modal spec activates them | PRESERVED |
| VIS-002 | Approval reject auto-open and `old('decision_note')` gap remains; modal spec explicitly freezes it | PRESERVED |
| VIS-003 | Ticket modal retained/reset semantics remain baseline; state spec does not normalize them | PRESERVED |
| VIS-004 | Request-information composer remains body-only; no file control destination added | PRESERVED |
| VIS-005 | Triage UI remains current `self`/`tier_2` presentation; no extra outcome/field destination added | PRESERVED |
| VIS-006 | ACT-08 admin-dominant navigation/dashboard remains a required variant | PRESERVED |
| VIS-007 | ACT-09 mixed requester/current-Approver presentation remains a required variant | PRESERVED |
| VIS-008 | Team Chair safe ticket/report override and additional-role admin/announcement caveat remain | PRESERVED |
| VIS-009 | UI-021 contracts/density risk retained; existing byte weight is not a visual requirement; geometry deferred | PRESERVED / DEFERRED |
| VIS-010 | UI-012 targeting/contracts retained; existing byte weight is not a visual requirement; geometry deferred | PRESERVED / DEFERRED |
| VIS-011 | Skip-to-content approved only as explicit acceptance-gated a11y improvement; not implemented | EXPLICIT DECISION |
| VIS-012 | Responsive evidence remains mandatory; no exact geometry/breakpoint selected | DEFERRED |
| VIS-013 | Existing nav/modal/focus behavior still needs runtime golden; no behavior normalized | DEFERRED |
| VIS-014 | Candidate contrast/focus values remain provisional pending browser/zoom/forced-colors | PROVISIONAL |
| VIS-015 | SVC-03 missing opener remains; no icon/action destination makes it reachable | PRESERVED |
| VIS-016 | SVC-03 incomplete/pre-execution action/readiness semantics remain unchanged | PRESERVED |
| VIS-017 | Long-content fixtures remain required for type/truncation/layout validation | DEFERRED |

Coverage: **VIS-001–VIS-017 = 17/17**.

## Screen and Variant Coverage

| Set | Coverage | Design-system destination |
|---|---:|---|
| Active UI | UI-001–UI-023 = **23/23** | Shell, forms, collections, dashboard, workbench, modal, contextual states |
| Dashboard | D-01–D-06 = **6/6** | KPI/work section with role-specific composition |
| Ticket lists | L-01–L-03 = **3/3** | Server filters, desktop table/mobile record, safe projection |
| Ticket details | T-01–T-06 = **6/6** | Ticket workbench, communication, attachment, metadata, role action rules |

Primary variants: **15/15**.

## Implementation and Gate Effect

- Design-system qualitative rules: stable for review.
- Candidate exact values: provisional until manual comparison.
- Layout/dimension decisions: ten explicitly deferred.
- Blade/CSS/JavaScript/Tailwind/font/icon/package/backend/tests/fixtures/routes changed by FASE 3.3: **NO**.
- Redesign implementation: **STILL HOLD — MANUAL VISUAL BASELINE REQUIRED**.
- Expected phase result after repository-scope validation: **FASE 3.3 PASS WITH MANUAL DEFERRED DECISIONS**.
