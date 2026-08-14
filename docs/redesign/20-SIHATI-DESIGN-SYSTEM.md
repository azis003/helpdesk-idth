# SIHATI Design System

Dokumen ini adalah authoritative **design specification** untuk W1–W6. Ia menerjemahkan guardrail, contract freeze, baseline W0, keputusan referensi, dan UI grammar ke bahasa sistem desain tanpa membuat CSS variable, Tailwind theme, Blade component, JavaScript module, font, icon asset, atau implementation code.

## Status and Authority

| Concern | Status |
|---|---|
| Design-system phase | FASE 3.3 — documentation/design specification only |
| North star | **FINAL DESIGN RULE — CALM OPERATIONAL WORKBENCH** |
| Qualitative component grammar | FINAL DESIGN RULE, tunduk pada contract server |
| Exact token candidates | **PROVISIONAL TOKEN** sampai dibandingkan dengan manual baseline |
| OPEN-011–OPEN-020 | **DEFERRED UNTIL MANUAL BASELINE** |
| Redesign implementation | **HOLD — MANUAL VISUAL BASELINE REQUIRED** |
| Production authorization | NONE |

Urutan sumber kebenaran tetap: authorization/domain/Form Request/controller/route/projection lebih tinggi daripada presentation. Bila dokumen ini bertentangan dengan `00-REDESIGN-GUARDRAILS.md`, `02-UI-CONTRACT-MATRIX.md`, `04-ROLE-VARIANT-MATRIX.md`, atau implementation server yang dibekukan, contract aplikasi menang.

Tiga status keputusan digunakan secara ketat:

- `FINAL-CONCEPT`: semantic role atau aturan desain sudah final; exact value dapat tetap bergantung pada token bawahannya.
- `PROVISIONAL-VALUE`: candidate exact dapat dipakai untuk prototype/spec discussion, tetapi bukan production truth sebelum screenshot, contrast, zoom, dan responsive validation.
- `DEFERRED-VALUE`: exact value tidak boleh dipilih sebelum evidence manual yang disebutkan tersedia.

## North Star

### Calm Operational Workbench

SIHATI adalah meja kerja operasional yang tenang: identitas record, keadaan, audience, waktu, penanggung jawab, SLA, tindakan berikutnya, dan jejak kerja lebih menonjol daripada dekorasi.

Aturan final:

1. Neutral foundation menjadi mayoritas tampilan.
2. Border dan divider membentuk hierarchy; shadow hanya untuk elevation sementara.
3. Control compact, tetapi pemisahan antar-task group lebih besar.
4. Satu restrained accent mendukung identity, selected state, dan primary action; ia bukan warna semua surface.
5. Status, priority, feedback, focus, dan destructive meaning memakai semantic color dengan cue redundant.
6. Komposisi mengikuti role/data/action server, bukan persona generik atau label role.
7. Public dan internal communication memiliki audience identity yang eksplisit dan terpisah.
8. Interaction tetap server-native: form/redirect/flash, GET filter, dan numbered paginator.
9. Responsive berarti **reprioritize, not shrink**.
10. State UI harus truthful: initial, loading, empty, filtered empty, error, 403, 404, dan success bukan interchangeable.
11. Tidak ada dark theme, gradient dekoratif, glassmorphism, card soup, giant KPI hero, atau motion dekoratif.

Signature visual SIHATI tetap **jejak kerja yang terlihat**: satu alur identity → state → next action → work → supporting context → chronology. Ia diwujudkan melalui rhythm, divider, audience marker, dan operational spine—bukan motif visual baru.

## Token Philosophy

- Naming semantic, bukan page-specific: `color.surface.*`, `color.status.*`, `type.*`, `space.*`, `size.*`, `radius.*`, `border.*`, `shadow.*`, `motion.*`, `focus.*`, dan `layer.*`.
- Foundation value boleh mempunyai alias semantic. Consumer menggunakan semantic role, bukan hex/px arbitrary.
- Satu token tidak boleh mengandung policy, role calculation, workflow transition, route, atau input contract.
- Status dan priority mapping terpusat secara konseptual, tetapi tetap menerima enum/value server.
- Candidate value FASE 3.3 berada di `21-DESIGN-TOKEN-SPEC.md`; tidak ada CSS variable yang dibuat pada fase ini.
- Exact layout geometry tidak diturunkan dari spacing/size scale. Token geometri yang terkait OPEN-011–020 tetap `DEFERRED-VALUE`.
- Light presentation only. DEC-038 tetap `IGNORE`; tidak ada alternate dark token set.

## Color

### Foundation and brand

Candidate palette mengambil arah dari neutral slate dan teal yang sudah ada di `resources/css/theme-modern.css`, tetapi menurunkan gradient/decorative usage menjadi border-led workbench. Nilai ini **provisional**, bukan salinan token Frappe.

| Semantic role | Candidate | Pair / contrast direction | Rule |
|---|---:|---|---|
| Canvas | `#F6F8FA` | Dark text | Page background only; bukan status surface. |
| Surface | `#FFFFFF` | Dark text | Primary work surface. |
| Surface muted | `#EEF1F4` | Dark text | Supporting context/readonly grouping. |
| Surface hover | `#E7EDF2` | Dark text | Pointer reinforcement, never sole affordance. |
| Surface selected | `#D7F0F5` | Accent-dark text | Selected/active context plus border/weight cue. |
| Border default | `#D6DEE5` | Passive divider | Tidak menjadi satu-satunya boundary control penting. |
| Border strong | `#7A8793` | About `3.67:1` on white | Essential boundary/non-text contrast target ≥3:1. |
| Text | `#161C22` | About `17.17:1` on white | Default text. |
| Text muted | `#4E5A66` | About `7.05:1` on white | Secondary text. |
| Text subtle | `#667381` | About `4.85:1` on white | Metadata only; do not lower further for normal text. |
| Accent | `#135D72` | White about `7.4:1` | Restrained primary action, active navigation marker, identity cue. |
| Accent hover | `#154D5E` | White about `9.31:1` | Hover/active action state. |
| Accent soft | `#D7F0F5` | Accent text about `6.23:1` | Selected/brand-support surface. |
| Accent foreground | `#FFFFFF` | On accent | Primary action text/icon. |

Contrast values are calculated candidates, not manual-browser evidence. Target rules: normal text ≥4.5:1, large text ≥3:1, essential non-text boundary/focus ≥3:1, with forced-colors verification still required.

### Feedback families

| Family | Text/strong candidate | Soft surface | Contrast direction | Meaning |
|---|---:|---:|---|---|
| Success | `#0C6249` | `#E9F8F2` | Dark-on-light, about `6.71:1` | Operation succeeded or completed truthfully. |
| Warning | `#8A5700` | `#FFF7E8` | Dark-on-light, about `5.73:1` | Attention/waiting/near-limit; not generic emphasis. |
| Danger | `#A21F37` | `#FDEEF1` | Dark-on-light, about `6.70:1` | Error, denied outcome, destructive consequence, or critical risk. |
| Info | `#175A86` | `#EAF4FB` | Dark-on-light, about `6.62:1` | Explanatory context or intake/new state. |

### Status semantic grouping

Grouping is visual only. Statuses in one family retain distinct workflow meaning, canonical label, and server value.

| Canonical status | Family | Redundant cue | Candidate color | Compact list treatment | Detail treatment | Accessibility note |
|---|---|---|---|---|---|---|
| Baru | NEW | Hollow intake circle | Info `#175A86` on `#EAF4FB` | Cue + full label | Badge + “belum diambil”; owner remains separate | Never imply assignee. |
| Diproses | ACTIVE | Half-filled process circle | Accent `#135D72` on `#D7F0F5` | Cue + full label | Badge + triage context | Distinct cue/label from Dikerjakan. |
| Dikerjakan | ACTIVE | Solid work circle | Accent `#135D72` on `#D7F0F5` | Cue + full label | Badge + assignee/tier separately | Same family does not mean same transition. |
| Menunggu Persetujuan | WAITING | Clock + decision/check marker | Warning `#8A5700` on `#FFF7E8` | Waiting cue + canonical label | Add explicit approval context | Never replace with “Menunggu”. |
| Menunggu Pemohon | WAITING | Clock + person marker | Warning `#8A5700` on `#FFF7E8` | Waiting cue + canonical label | Add requester audience/next step | SLA pause is server data. |
| Menunggu Pihak Ketiga | WAITING | Clock + external marker | Warning `#8A5700` on `#FFF7E8` | Waiting cue + canonical label | Add party/follow-up context if provided | Not an error state. |
| Menunggu Konfirmasi | WAITING | Clock + pending-check marker | Warning `#8A5700` on `#FFF7E8` | Waiting cue + canonical label | Add requester confirmation context | Confirmation timer ≠ SLA. |
| Ditutup | SUCCESS/CLOSED | Check circle | Success `#0C6249` on `#E9F8F2` | Cue + full label | Closure context outside badge | No celebratory treatment. |
| Ditolak | NEGATIVE/REJECTED | Stop/minus marker | Danger `#A21F37` on `#FDEEF1` | Cue + exact label | Reason where policy allows | Do not merge with Tidak Disetujui. |
| Tidak Disetujui | NEGATIVE/REJECTED | Crossed decision marker | Danger `#A21F37` on `#FDEEF1` | Cue + exact label | Approval outcome/context | Exact label communicates domain difference. |
| Dibatalkan | NEUTRAL/CANCELLED | Slashed circle | Muted `#4E5A66` on `#EEF1F4` | Cue + exact label | Cancellation context | Not rendered as error/failure. |

The cue vocabulary is `FINAL-CONCEPT`; exact SVG path, hue, and fill are `PROVISIONAL-VALUE` until the 11-status matrix is captured and checked in normal/forced-colors/zoom modes.

### Priority system

Priority is urgency—not status, SLA, or destructive action. The final non-color concept is a fixed-width four-segment indicator plus canonical text.

| Priority | Level | Non-color cue | Candidate tone | Compact treatment | Detail treatment | Screen-reader label |
|---|---:|---|---|---|---|---|
| Kritis | 4/4 | Four filled segments | Danger `#A21F37` | 4-bar cue + “Kritis” | Label + “tingkat 4 dari 4” | “Prioritas Kritis, tingkat 4 dari 4” |
| Tinggi | 3/4 | Three filled, one outline | Warning `#8A5700` | 4-bar cue + “Tinggi” | Label + “tingkat 3 dari 4” | “Prioritas Tinggi, tingkat 3 dari 4” |
| Sedang | 2/4 | Two filled, two outline | Info `#175A86` | 4-bar cue + “Sedang” | Label + “tingkat 2 dari 4” | “Prioritas Sedang, tingkat 2 dari 4” |
| Rendah | 1/4 | One filled, three outline | Muted `#4E5A66` | 4-bar cue + “Rendah” | Label + “tingkat 1 dari 4” | “Prioritas Rendah, tingkat 1 dari 4” |

Four unrelated colored pills are prohibited. Segment geometry remains visible in monochrome/forced-colors; label is never removed.

## Typography

### Font decision

Source audit:

- `resources/css/app.css` imports Inter from Google Fonts and defines Inter first, followed by system fallbacks.
- `resources/css/theme-modern.css` repeats Inter with `ui-sans-serif`, `system-ui`, `-apple-system`, `Segoe UI`, Roboto, Helvetica Neue, and Arial fallbacks.
- no local `.woff/.woff2/.ttf/.otf` assets exist;
- report PDF intentionally uses DejaVu Sans and is a separate binary export contract;
- current Tailwind v4 theme overrides body text and headline scale in `app.css`.

Decision: the redesign typography strategy is a **system-accessible sans stack**, with no new font dependency. Candidate stack:

```text
ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif
```

This supports Indonesian text, punctuation, numerals, long content, dense records, and cross-platform fallback without relying on remote font delivery. Inter is not selected as the design identity merely because the reference uses it. Removing the existing remote import is an implementation decision for W1 only after the hold is lifted; this phase changes nothing.

### Typography roles

| Role | Candidate family / size / weight / line | Wrapping rule | Mobile relationship | Status |
|---|---|---|---|---|
| `display-exceptional` | System / 32px / 700 / 40px | Full wrap; only exceptional empty/error identity | 28/36 | PROVISIONAL-VALUE |
| `page-title` | System / 28px / 700 / 36px | Natural wrap; no ellipsis without recovery | 24/32 | PROVISIONAL-VALUE |
| `section-title` | System / 20px / 600 / 28px | Natural wrap | 18/26 | PROVISIONAL-VALUE |
| `record-title` | System / 15px / 600 / 22px | Desktop truncation only with accessible recovery; mobile wraps | Same size, more wrap | PROVISIONAL-VALUE |
| `body` | System / 14px / 400 / 20px | Full wrap | Same | PROVISIONAL-VALUE |
| `body-long` | System / 15px / 400 / 24px | Full wrap; preserve whitespace where meaningful | Same | PROVISIONAL-VALUE |
| `form-label` | System / 13px / 600 / 18px | Wrap long Indonesian labels | Same | PROVISIONAL-VALUE |
| `metadata` | System / 12px / 400–500 / 18px | Truncate only with recovery | Same; may stack | PROVISIONAL-VALUE |
| `helper` | System / 12px / 400 / 18px | Always wrap | Same | PROVISIONAL-VALUE |
| `error` | System / 12px / 500 / 18px | Always wrap; never ellipsis | Same | PROVISIONAL-VALUE |
| `control` | System / 14px / 600 / 20px | Action label wraps/stacks where necessary | Same | PROVISIONAL-VALUE |
| `badge` | System / 12px / 600 / 16px | Canonical label retained | Same | PROVISIONAL-VALUE |
| `numeric-kpi` | System / 24px / 700 / 32px; tabular numerals | Never clip; explain unit/period | 22/30 | PROVISIONAL-VALUE |

Routine pages never use `display-exceptional`. Metadata remains normal-text contrast. KPI numbers use tabular numerals but do not become giant heroes.

## Spacing

Candidate scale follows a 4px base: `0, 4, 8, 12, 16, 20, 24, 32, 40, 48`. Exact values remain provisional.

| Semantic usage | Candidate | Rule |
|---|---:|---|
| `space.control-internal` | 8px | Icon/label/control internals; compact, never cramped. |
| `space.field` | 12px | Between label/control/helper/error units. |
| `space.row` | 12px | Dense record-cell rhythm. |
| `space.section` | 24px | Between related sections. |
| `space.page` | 32px | Between page-level task regions; not exact page gutter. |
| `space.modal` | 20px | Body internal grouping; not modal width. |
| `space.timeline` | 16px | Between event anatomy; group boundary may use section spacing. |
| `space.toolbar` | 12px | Search/filter/action grouping. |

Rule: compact spacing inside a task, larger spacing between tasks. `space.page` does not resolve OPEN-013.

## Size

### Control categories

| Category | Candidate visual height | Typical use | Mobile direction |
|---|---:|---|---|
| Compact | 32px | Desktop toolbar, dense table action, sidebar utility | Maintain ≥44px operable hit area through surrounding hit target; do not stack destructive neighbors tightly. |
| Default | 40px | Routine forms, page actions, modal controls | Prefer ≥44px total touch target. |
| Comfortable | 44px | Primary mobile action, file chooser affordance, high-consequence confirmation | Visual and hit target may coincide. |

Icon candidates are 16px compact, 20px default, and 24px emphasis. Exact sidebar/nav row, page width/gutter, modal width, ticket rail, breakpoint, dense admin row, truncation, and dashboard grid values remain `DEFERRED-VALUE`.

## Radius

| Role | Candidate | Use |
|---|---:|---|
| None | 0 | Divider-connected rows or surfaces that must read as one work plane. |
| Small | 4px | Compact controls, small cues. |
| Medium | 8px | Routine controls, panels, record cards. |
| Large | 12px | Modal/floating/standalone contextual surface only. |
| Full | 9999px | Avatar, circular icon affordance, true semantic/count chip only. |

Pills are not the default button or metadata shape. Nested rounded cards are prohibited. Values are provisional; usage hierarchy is final.

## Border

| Role | Candidate | Rule |
|---|---|---|
| Default | 1px solid `color.border.default` | Passive grouping/divider; no meaning alone. |
| Strong | 1px solid `color.border.strong` | Essential boundary or selected context. |
| Focus | 2px outline/ring using `focus.ring.color` | Separate from validation border. |
| Error | 1px solid `color.feedback.danger` plus text/icon | Never color-only. |

Border hierarchy precedes shadow. A form control boundary must remain perceivable against its adjacent surface.

## Shadow

| Role | Candidate | Use |
|---|---|---|
| None/default | `none` | Static cards, tables, forms, work surfaces. |
| Floating | `0 8px 24px rgba(22,28,34,.12), 0 2px 6px rgba(22,28,34,.08)` | Dropdown, anchored overflow, temporary sticky separation. |
| Modal | `0 24px 64px rgba(22,28,34,.20), 0 8px 20px rgba(22,28,34,.12)` | Modal only. |

Exact shadows are provisional. No glow, frosted glass, or shadow on every row/card.

## Motion

| Role | Purpose | Candidate | Reduced-motion behavior |
|---|---|---|---|
| Instant/state | Checked/selected/error state truth | 0ms | Same immediate change. |
| Fast/control | Hover/focus/control feedback | 120ms ease-out | 0ms; preserve state cue. |
| Standard/drawer | Spatial orientation for navigation panel | 180ms cubic-bezier(.2,.8,.25,1) | 0ms; panel appears in final position. |
| Modal | Backdrop/panel entry and exit | 160ms ease-out | 0ms; focus transfer unchanged. |
| Disclosure | Expand/collapse orientation | 140ms ease-out | 0ms; expanded state/ARIA unchanged. |

No animation delays work, changes workflow, or supplies the only feedback. Loading spinner may rotate, but reduced-motion uses static progress text/indicator.

## Focus

Candidate focus treatment:

- high-contrast ring/outline `#005FCC`, approximately `5.98:1` against white;
- candidate width 3px and offset 2px;
- error border remains visible inside/outside the focus treatment, so focus is not mistaken for invalid;
- on accent/danger surfaces, use a light separation edge plus the focus color;
- `forced-colors` direction uses system focus color/outline rather than suppressing it;
- focus must never be clipped by overflow or removed without an equivalent.

Color/width/offset are `PROVISIONAL-VALUE`; the visible-focus rule is `FINAL-CONCEPT`.

## Layering

Conceptual order is final: `base < sticky < dropdown < overlay < modal < toast/loading`.

Candidate provisional scale: base 0, sticky 10, dropdown 20, overlay 30, modal 40, toast/loading 50. Component-specific escalation is prohibited; nested contexts must use the smallest sufficient layer.

## Iconography

### Repository audit and decision

- No font-icon, Heroicons, Lucide, Font Awesome, Tabler, Iconify, or other icon package dependency exists.
- No repository `.svg` icon asset files exist.
- Active/related Blade source contains **215 inline `<svg>` instances across 43 files**.
- Current convention uses compact first-party stroke/fill SVG beside text and `aria-hidden` for decorative/supporting icons; implementation consistency is incomplete and several raw SVGs lack a centralized semantic source.
- No Frappe asset may be copied.

Decision: retain a **first-party inline SVG strategy** for redesign scope, no new package. A future dependency requires separate explicit dependency/license decision. Exact SVG paths are provisional visual assets, not domain state.

### Semantic inventory

| Family | Required concepts | Direction |
|---|---|---|
| Navigation | dashboard, tickets, queue, report, users, team, location, service, audit, announcement | 24px viewBox, consistent stroke, text label when expanded. |
| Discovery | search, filter | Supporting icon; field/control keeps accessible name. |
| CRUD | add, edit, delete | Text label for ambiguous/high-consequence action; delete uses danger only when actually destructive. |
| Shell | close, menu, notification, chevron/disclosure, overflow | Icon-only allowed only with accessible name and adequate target. |
| Files | download, upload, attachment | Link/action name includes file purpose/name; icon never bypasses authorized route. |
| Feedback | warning, success, error | Supplementary to message text. |
| Identity/context | user, team, location, service, calendar/time | Decorative when adjacent text already names context. |
| State | status, priority | Status family cue and four-bar priority cue; canonical text remains. |

Source already contains examples for most concepts, but there is no authoritative path registry and no consistent priority cue. Those are missing implementation primitives—not permission to add a package now.

## Surfaces

| Surface | Token direction | Border/elevation | Text hierarchy | Appropriate SIG use |
|---|---|---|---|---|
| Canvas | `color.surface.canvas` | None | Standard dark text | SIG-001 page background. |
| Primary work surface | `color.surface.primary` | Default outer border where enclosure helps; no shadow | Full hierarchy | Forms, tables, ticket conversation, admin work area. |
| Muted/support surface | `color.surface.muted` | Default/subtle border | Muted heading, readable body | Helper groups, readonly context, metadata. |
| Selected surface | `color.surface.selected` | Accent/strong boundary plus weight/cue | Accent-dark text | Active nav/tab/selected row only when selection exists. |
| Floating surface | `color.surface.floating` | Strong border + floating shadow | Normal hierarchy | Dropdown/overflow/notification panel. |
| Modal surface | `color.surface.modal` | Strong boundary + modal shadow | Title/body/footer hierarchy | SIG-032. |
| Danger/warning/info callout | Feedback soft surface | Semantic border + icon/text | Explicit heading/description/recovery | SIG-034/036/037/040 when state meaning matches. |

Static cards default to border + surface. Surface nesting is limited to a real semantic boundary.

## Buttons

| Variant | Emphasis and tokens | Icon | Loading/disabled/focus | Mobile | Use | Prohibited |
|---|---|---|---|---|---|---|
| Primary | Accent surface/hover, accent foreground, no routine shadow | Optional leading; never icon-only for primary workflow | Replace/augment label with progress while retaining action name; native disabled only for supported state; standard focus | Default/comfortable; may fill available width | One main task action per context | Multiple competing primaries; generic Submit; changing method/payload. |
| Secondary | Primary surface, strong/default border, accent/text | Optional leading | Local busy state; clear focus | Stack/wrap after primary | Non-destructive alternative | Styling destructive action as neutral. |
| Subtle/Ghost | Transparent/hover surface, normal text | Often supporting | Hover not sole affordance; focus remains strong | Keep label if ambiguity exists | Low-frequency utility/disclosure | Hiding primary action in ghost treatment. |
| Destructive | Danger surface/strong tone and explicit consequence | Optional warning/delete | Loading bound to action; disabled semantics explicit; focus distinct | Full label, separated from routine primary | Existing destructive/final action only | Generic emphasis or priority/status display. |
| Icon | Transparent/selected surface, square hit area | Required; decorative SVG | Accessible name mandatory; busy/pressed/expanded as applicable | ≥44px operable target | Familiar utility/menu/close | Ambiguous primary/destructive action. |
| Link | Transparent text/underline or clear link cue | Optional | Browser/link focus; download state only if existing | Wrap naturally | Navigation/download | Mutation disguised as link. |

Unauthorized action remains absent. `disabled` means a rendered control is temporarily/permanently unavailable under an existing UI contract; it is not a substitute for authorization.

## Form Controls

Shared anatomy is `label → required cue → control → helper/metadata → error`. Exact `name`, hidden sibling, old value, error key, required/readonly/disabled semantics, enctype, and submit lifecycle remain caller/server-owned.

| Control | Surface/border | State rules | Long-value/responsive rules | Contract boundary |
|---|---|---|---|---|
| Input | Primary surface; default border; strong focus; danger invalid | Disabled visually muted with native semantics; readonly remains readable/not editable | Full width on mobile; value scrolls inside control, label/error wrap | SIG-007, exact `name`/old/error. |
| Select | Same control shell; native arrow/behavior retained unless separately accepted | Selected option server-provided; invalid + text | Full width; long option labels not clipped in closed context without recovery | SIG-008 option value/order. |
| Textarea | Primary surface; resize/scroll behavior remains usable | Readonly and invalid explicit | Full width, body-long line height, no horizontal overflow | SIG-009 route/payload/modal marker. |
| Checkbox | Native control + clickable label hit area | Checked/focus/disabled visible; hidden `0` companion preserved | Label wraps; stacked groups on mobile | SIG-010 exact boolean pair. |
| Radio | Native group with fieldset/legend direction | Selected/focus/disabled visible | Choices stack when labels are long | SIG-010 only currently exposed outcomes. |
| File input | Native chooser or equivalent progressive shell | Filename, allowed purpose, error, upload progress if existing; no old file restoration | Long filename wraps/recoverable; no drag-only workflow | SIG-011 multipart/nested policy contract. |

Required cue uses visible text/marker plus native/ARIA semantics; color alone is insufficient. Helper and error IDs remain programmatically associated. SIG-007–SIG-011 contracts are preserved.

## Badges

| Family | Purpose | Anatomy | Rules |
|---|---|---|---|
| STATUS | Eleven canonical lifecycle states | Family cue + canonical label; optional contextual text outside badge | Enum/server render only; no dropdown/mutation. |
| PRIORITY | Four urgency levels | Four-segment cue + canonical label | Not status/SLA/destructive; non-color level remains. |
| FILTER | Active query condition | Filter label/value + explicit clear control where existing | Represents exact server query only; no saved view. |
| COUNT | Small quantity/unread/result count | Numeric text, optional accessible expansion | Does not imply urgency by color alone. |
| ROLE / IDENTITY | Context label only where existing UI genuinely requires it | Exact canonical label | Never enforces or implies capability; unused `role-badge` is not auto-activated. |

One generic pill for every metadata item is prohibited.

## Tables and Mobile Records

Desktop table specification:

- header uses `type.form-label`/metadata emphasis, muted surface or quiet divider;
- row uses primary surface, default divider, compact vertical rhythm;
- hover reinforces a real link/action but never creates whole-row mutation/filter behavior;
- focus-visible belongs to the actual link/button, or row focus only if baseline semantics already make the row interactive;
- selected treatment exists only where baseline already has selection; redesign adds no selection/bulk state;
- identity column dominates; metadata, status, priority, time, and authorized actions follow;
- server sort and Laravel pagination remain authoritative.

Mobile record cards preserve the same server record and action set with hierarchy: identity → status/priority → most relevant metadata → time → authorized action. Exact switch breakpoint is deferred under OPEN-017. No whole-page horizontal scroll; contained table overflow is allowed only when comparison cannot be represented safely.

## Dashboard

Reusable visual primitives:

| Primitive | Emphasis rule |
|---|---|
| KPI | Compact value + label + scope/period/denominator; never a giant hero. |
| Work Section | Highest operational emphasis; urgent/action-needed records before analytics. |
| Summary Row | Quiet aggregate context after actionable work. |
| Contextual State | Truthful empty/initial/error for the exact section. |
| Period Control | Existing GET query and validation, visually grouped with scope. |
| Announcement/Guidance | Supporting information after current work, except genuine active warning. |

D-01–D-06 share primitives but not feed, section, action, or capability. Exact grid/column composition remains deferred under OPEN-020.

## Ticket Workbench

| Primitive | Specification | Contract boundary |
|---|---|---|
| Ticket Header | Number/subject, status, priority, requester context, SLA/back context, authorized action region | `showActions`, `from=all`, role/data variant remain server-owned. |
| Action Cluster | Frequent existing action visible; lower-frequency existing actions may move to labeled overflow | Exact policy booleans, modal IDs/openers, routes, methods; no generic status dropdown. |
| Timeline | One chronological spine with event-type label, actor/time, body/evidence | Order and projection server-owned; no event filtering that leaks/hides required history. |
| Public Message | Public audience label, author/time, neutral surface, authorized attachments | Public route/policy/download only. |
| Internal Note | Persistent private label/lock marker, protected muted surface, author/time, internal attachments | Never requester/Team Chair; separate route/policy. |
| System Event | Compact verb/result/actor/time | Pure render; no control disguised as event. |
| Workflow Event | Approval/wait/completion/reopen/SVC label and consequence | Domain meanings remain distinct. |
| Composer | Audience heading, textarea, permitted files, helper/error, specific submit/loading | Public/requester/internal forms remain separate. |
| Attachment | File identity/purpose, authorized download action, truthful missing/denied state | Named authorized route; no raw storage URL. |
| Metadata Section | Heading + label/value groups; disclosure only after wave acceptance | No cross-page/session persistence; no hidden sensitive data. |

Exact desktop main/rail ratio, rail/sticky behavior, mobile order, composer stickiness, and disclosure defaults remain deferred under OPEN-015/016.

## Communication

Public and internal use separate semantic namespaces:

| Concern | `communication.public.*` | `communication.internal.*` |
|---|---|---|
| Audience | “Balasan ke Pemohon” / requester-facing identity | “Catatan Internal · Hanya tim berwenang” |
| Surface | Primary white/neutral conversation surface | Muted protected graphite surface candidate `#F1F3F5` |
| Border/marker | Default border + accent audience marker | Strong neutral border + persistent lock/private marker + left/header boundary |
| Heading | Public audience text | Private audience text before body |
| Icon | Optional message/user marker | Optional lock/shield marker |
| Composer | Separate public/requester form | Separate internal form/modal |
| Attachment | Requester/public-compatible policy only | Internal policy only |

Difference includes label, surface, border pattern, heading/audience cue, and optional marker—not color alone. A one-form audience toggle is prohibited.

## Modal

| Variant | Intended use | Width status |
|---|---|---|
| Narrow | Confirmation or one short field | DEFERRED-VALUE |
| Standard | Routine create/edit/action form | DEFERRED-VALUE |
| Wide | Existing high-density user/service/workflow editor only | DEFERRED-VALUE |
| Mobile | Near/full viewport presentation with safe gutter and reachable actions | DEFERRED-VALUE |

Anatomy is final-concept: backdrop → panel → labeled header/close → scrollable body → inline error/loading → divided footer → cancel + primary/destructive action. Header/footer remain reachable; actions stack on narrow screens if required.

All 14 ticket IDs, generic `data-*` protocol, opener availability, `_action_modal`, auto-open, old/error, reset/retained-state behavior, focus, route/method/payload, redirect, and flash remain frozen. FASE 3.3 does not add the four missing openers or repair approval auto-open.

## Contextual States

| State | Heading/description | Recovery/action | Surface/announcement rule |
|---|---|---|---|
| Initial | Explain what has not yet been selected/generated | Existing next action only | Quiet info/support surface; no “empty data” claim. |
| Loading | Name current navigation/submit/export action | Prevent duplicate action; preserve context | Local/global presentation follows existing lifecycle; announce once. |
| Empty | State that valid collection has no records | Existing authorized create/navigation if any | In-flow; restrained optional icon. |
| Filtered Empty | Name active query context and no result | Clear/reset existing filter | Must not claim global collection is empty. |
| Validation Error | Identify fields/action to correct | Focus/return only after acceptance; retain old values | Inline error plus summary only where accepted. |
| Action Error | Explain failed operation without stale success | Safe retry/back only if existing | Danger callout; no internal detail. |
| 403 | Explicit access denial | Existing safe destination | Standalone forbidden state; never empty/404. |
| 404 | Resource/path unavailable if existing renderer returns it | Existing safe destination | Never empty/403; do not leak hidden resource. |
| Success/Flash | State what changed and next state/destination | Existing redirect/link only | Existing flash bridge; avoid duplicate toast + inline contradiction. |

Optional icons are supplementary. Every state has textual heading/description; essential feedback must not disappear solely because a toast times out.

## Feedback

- Feedback maps to existing `success`, `warning`, validation/error bag, SweetAlert, global loading, local submit, and report export lifecycle.
- Success confirms actual server result after redirect/response; no optimistic workflow state.
- Warning explains pending/attention condition and next step; it is not a status alias.
- Danger is reserved for failure/destructive/denied meaning.
- Loading disables only the action being processed where implementation allows; the global existing guard remains a frozen behavior until migrated with tests.
- Copy is specific Bahasa Indonesia and uses the same action name before/during/after.

## Accessibility

Final rules:

1. Semantic HTML and heading/landmark order precede ARIA.
2. Normal text target ≥4.5:1; large text ≥3:1; essential non-text/focus boundary ≥3:1.
3. Focus-visible is high contrast, persistent, and not clipped; focus and invalid are visually distinct.
4. Status and priority always include canonical text plus non-color cue.
5. Icon-only control has an accessible name; decorative/supporting SVG is hidden from assistive technology when redundant.
6. Helper/error is associated with the field; required state is not color-only.
7. Modal has accessible name, close path, focus entry/containment/return behavior, and reachable error—subject to acceptance evidence.
8. Reduced motion preserves state and focus outcomes without spatial animation.
9. Touch-target direction is ≥44×44px operable area on mobile even if visual control is compact.
10. 200%/400% zoom, reflow, long Indonesian content, forced-colors, and screen-reader announcements require manual/acceptance validation.
11. Skip-to-content is approved as an accessibility presentation/interaction improvement, but implementation waits for hold removal and guest/auth/mobile/403 acceptance tests.

## Component State Model

Applicable interactive/semantic patterns use: default, hover, focus-visible, active, selected, disabled, readonly, loading, invalid, success, warning, and danger. `22-COMPONENT-STATE-MATRIX.md` records applicability per SIG.

Critical rules:

- `UNAUTHORIZED ≠ DISABLED`. Unauthorized action/data remains absent or server-denied according to baseline; it is never rendered as a disabled teaser.
- Disabled is used only when the existing presentation renders a control that is unavailable or while preventing duplicate submission.
- Readonly communicates available data that cannot be edited; it does not imply forbidden data was loaded then visually hidden.
- Hover never carries unique information or action.
- Selected means a real existing selection/route/query state; no new row selection or client state is invented.
- Loading, success, warning, and danger reflect server/interaction truth, not decorative variants.

## Role-sensitive Rules

| Role | Final presentation rule |
|---|---|
| Super Admin | Administrative identity must not imply operational ticket actions; pure role may direct-view ticket read-only according to policy. |
| Pemohon | Own/requester context dominates; internal/private data and controls are absent. |
| Agen Tier 1 | Queue/triage/work action appearance follows exact state, assignment, and controller booleans. |
| Agen Tier 2 | Assigned-only context is explicit; no Tier 1 queue/priority/triage affordance. |
| Approver | Current assignment + pending request is explicit; stale Approver does not look actionable. |
| Ketua Tim Kerja | Safe projected, read-only monitoring is unmistakable; no full model, internal data, attachment, approval, or report residue. |

ACT-08/09/10/11 composition remains baseline. Role/identity badges never enforce capability. Shared visual primitives accept already-authorized data/actions; they do not calculate role precedence.

## Responsive Rules

- SAME CONTENT / REFLOWED for forms, KPI, metadata.
- SAME CONTRACT / DIFFERENT PRESENTATION for table→record card and rail→sections.
- COLLAPSED only for secondary content, with local page state and explicit expanded semantics.
- OVERFLOW ACTIONS only for lower-frequency authorized actions; primary/critical action remains reachable.
- DEDICATED MOBILE HIERARCHY is permitted for UI-008 variants after OPEN-016 evidence.
- No cross-page/session persistence for metadata disclosure.
- No exact breakpoint, page gutter, drawer geometry, modal width, detail ratio/order, truncation limit, or dashboard grid is authoritative in this phase.

## SIG Integration

All 40 SIG are mapped. “Deferred” means only the affected exact geometry/interaction value is deferred; the component concept remains valid.

| SIG | Component/system family | Token families | Status | Wave | Contract sensitivity | Manual screenshot prerequisite |
|---|---|---|---|---|---|---|
| SIG-001 Shell | Shell/surface/layer | COLOR, SPACE, SIZE, BORDER, FOCUS, LAYER | FINAL-CONCEPT; geometry DEFERRED | W2 | HIGH/global; role CRITICAL | Guest/auth/403 pairs, six roles, ACT-08–11, both viewports |
| SIG-002 Desktop Sidebar | Navigation | COLOR, TYPE, SPACE, SIZE, FOCUS, MOTION, LAYER | FINAL-CONCEPT; widths DEFERRED | W2 | HIGH; role CRITICAL | Expanded/collapsed/persisted plus ACT-01–11 desktop |
| SIG-003 Mobile Navigation | Navigation/overlay | COLOR, TYPE, SPACE, SIZE, SHADOW, MOTION, FOCUS, LAYER | FINAL-CONCEPT; geometry DEFERRED | W2 | HIGH; role CRITICAL | Eleven mobile-nav files and keyboard/touch run |
| SIG-004 Page Header | Heading/action layout | TYPE, SPACE, SIZE, BORDER | FINAL-CONCEPT; page geometry DEFERRED | W2–W6 | HIGH on actions | Representative headers and T-01–T-06 long titles |
| SIG-005 Button | Action | COLOR, TYPE, SPACE, SIZE, RADIUS, BORDER, MOTION, FOCUS | FINAL-CONCEPT; values PROVISIONAL | W1+ | CRITICAL on mutation | Default/hover/focus/loading/disabled/destructive/mobile |
| SIG-006 Icon Button | Action/icon | COLOR, SIZE, RADIUS, MOTION, FOCUS | FINAL-CONCEPT; values PROVISIONAL | W1/W2+ | HIGH | Keyboard/touch/name/toolbar at both viewports |
| SIG-007 Form Field | Form | COLOR, TYPE, SPACE, SIZE, BORDER, RADIUS, FOCUS | FINAL-CONCEPT; values PROVISIONAL | W1+ | CRITICAL | Old/error/readonly/dynamic/long-label pairs |
| SIG-008 Select | Form | COLOR, TYPE, SPACE, SIZE, BORDER, RADIUS, FOCUS | FINAL-CONCEPT; values PROVISIONAL | W1+ | CRITICAL | Long options, conditional values, keyboard/mobile |
| SIG-009 Textarea | Form/communication | COLOR, TYPE, SPACE, SIZE, BORDER, RADIUS, FOCUS | FINAL-CONCEPT; values PROVISIONAL | W1+ | CRITICAL | Long text, validation, modal scroll, audience variants |
| SIG-010 Checkbox and Radio | Form/choice | COLOR, TYPE, SPACE, SIZE, BORDER, FOCUS | FINAL-CONCEPT; values PROVISIONAL | W1/W4–W6 | CRITICAL | Boolean pairs, triage current outcomes, keyboard |
| SIG-011 File Input | Form/file | COLOR, TYPE, SPACE, SIZE, BORDER, RADIUS, FOCUS | FINAL-CONCEPT; values PROVISIONAL | W4–W6 | CRITICAL | Policy groups, invalid/count/size/long filename/SVC-02 |
| SIG-012 Status Badge | Semantic badge | COLOR, TYPE, SPACE, RADIUS, BORDER | FINAL-CONCEPT; colors PROVISIONAL | W1 | MEDIUM | 11/11 status matrix, both viewports, forced-colors |
| SIG-013 Priority Badge | Semantic badge | COLOR, TYPE, SPACE, SIZE, BORDER | FINAL-CONCEPT; colors/segments PROVISIONAL | W1 | MEDIUM | 4/4 priority matrix beside status, forced-colors |
| SIG-014 Tabs | Navigation/query | COLOR, TYPE, SPACE, SIZE, BORDER, FOCUS | FINAL-CONCEPT; breakpoint DEFERRED | W3–W6 | CRITICAL | Valid/invalid tabs, query/modal context, overflow |
| SIG-015 Filter Bar | Filter/query | COLOR, TYPE, SPACE, SIZE, BORDER, FOCUS | FINAL-CONCEPT; geometry DEFERRED | W3–W5 | CRITICAL | Every documented filter, page 2, mobile collapse |
| SIG-016 Search | Filter/query | COLOR, TYPE, SPACE, SIZE, BORDER, FOCUS | FINAL-CONCEPT; geometry DEFERRED | W3–W5 | HIGH | Empty/result/no-result/long query/page preservation |
| SIG-017 Table | Collection | COLOR, TYPE, SPACE, SIZE, BORDER, FOCUS | FINAL-CONCEPT; density/breakpoint DEFERRED | W3–W5 | HIGH; data role CRITICAL | Populated/empty/search/page2/long content/all list variants |
| SIG-018 Mobile Record Card | Collection | COLOR, TYPE, SPACE, SIZE, RADIUS, BORDER, FOCUS | FINAL-CONCEPT; breakpoint/order detail DEFERRED | W3–W5 | HIGH; role CRITICAL | Desktop/mobile parity, Team Chair safe cards, long content |
| SIG-019 Pagination | Navigation/query | COLOR, TYPE, SPACE, SIZE, BORDER, FOCUS | FINAL-CONCEPT; values PROVISIONAL | W2–W5 | CRITICAL | First/middle/last/current/disabled/filter page 2 |
| SIG-020 Dashboard KPI | Dashboard | COLOR, TYPE, SPACE, SIZE, BORDER | FINAL-CONCEPT; grid DEFERRED | W4 | HIGH; role CRITICAL | D-01–D-06, zero/large values, period pairs |
| SIG-021 Dashboard Work Section | Dashboard | COLOR, TYPE, SPACE, BORDER | FINAL-CONCEPT; grid DEFERRED | W4 | HIGH; role CRITICAL | D-01–D-06, ACT-08–11, empty/action states |
| SIG-022 Ticket Header | Ticket workbench | COLOR, TYPE, SPACE, SIZE, BORDER, FOCUS | FINAL-CONCEPT; geometry/order DEFERRED | W6 | CRITICAL | T-01–T-06, long identity, action states |
| SIG-023 Action Cluster | Ticket action | COLOR, TYPE, SPACE, SIZE, BORDER, FOCUS, LAYER | FINAL-CONCEPT; mobile/sticky DEFERRED | W6 | CRITICAL | Action matrix, 10 reachable/4 missing, VIS-001/002/005/015/016 |
| SIG-024 Timeline | Ticket chronology | COLOR, TYPE, SPACE, SIZE, BORDER | FINAL-CONCEPT; values PROVISIONAL | W6 | HIGH; projection CRITICAL | Public/internal/system/workflow/SVC events and safe views |
| SIG-025 Public Message | Communication | COLOR, TYPE, SPACE, BORDER, RADIUS | FINAL-CONCEPT; values PROVISIONAL | W6 | CRITICAL | Public/requester messages, attachment access, Team Chair safe |
| SIG-026 Internal Note | Communication/protected | COLOR, TYPE, SPACE, BORDER, RADIUS | FINAL-CONCEPT; values PROVISIONAL | W6 | CRITICAL | Internal visibility matrix, pending/stale/Chair denial |
| SIG-027 System Event | Chronology | COLOR, TYPE, SPACE, BORDER | FINAL-CONCEPT; values PROVISIONAL | W3/W6 | HIGH | Representative event types and sanitized projections |
| SIG-028 Approval/Workflow Event | Chronology/workflow | COLOR, TYPE, SPACE, BORDER | FINAL-CONCEPT; values PROVISIONAL | W6 | CRITICAL | Approval/wait/completion/reopen/SVC fixtures |
| SIG-029 Composer | Communication form | COLOR, TYPE, SPACE, SIZE, BORDER, RADIUS, FOCUS | FINAL-CONCEPT; sticky/order DEFERRED | W6 | CRITICAL | Every composer, validation/retained state/mobile keyboard/VIS-003/004 |
| SIG-030 Attachment | File/evidence | COLOR, TYPE, SPACE, SIZE, BORDER, FOCUS | FINAL-CONCEPT; truncation DEFERRED | W4–W6 | CRITICAL | ATT fixtures, long/missing/deleted, all actors |
| SIG-031 Metadata Section | Supporting detail | COLOR, TYPE, SPACE, BORDER, MOTION, FOCUS | FINAL-CONCEPT; detail layout DEFERRED | W4/W6 | HIGH–CRITICAL | T variants, open/closed, long metadata, focus order |
| SIG-032 Modal | Overlay/dialog | COLOR, TYPE, SPACE, SIZE, RADIUS, BORDER, SHADOW, MOTION, FOCUS, LAYER | FINAL-CONCEPT; widths DEFERRED | W3–W6 | CRITICAL | 10 reachable pairs, validation set, mobile scroll, known gaps |
| SIG-033 Dropdown/Overflow | Floating action | COLOR, TYPE, SPACE, SIZE, RADIUS, BORDER, SHADOW, MOTION, FOCUS, LAYER | FINAL-CONCEPT; placement PROVISIONAL | W2–W6 | HIGH; role CRITICAL | Open/focus/Escape/outside/edge placement/all actors |
| SIG-034 Empty State | Contextual state | COLOR, TYPE, SPACE, BORDER | FINAL-CONCEPT; values PROVISIONAL | Every wave | MEDIUM; role HIGH | Initial/true/filtered/role-specific empty pairs |
| SIG-035 Loading State | Feedback | COLOR, TYPE, SPACE, SIZE, MOTION, FOCUS, LAYER | FINAL-CONCEPT; values PROVISIONAL | Every wave | HIGH lifecycle | Navigation/form/modal/export/failure/pageshow |
| SIG-036 Error State | Feedback/error | COLOR, TYPE, SPACE, BORDER, FOCUS | FINAL-CONCEPT; values PROVISIONAL | Every wave | CRITICAL | Nested/error bags/concurrency/export/modal/mobile |
| SIG-037 403 State | Contextual error | COLOR, TYPE, SPACE, BORDER, FOCUS | FINAL-CONCEPT; geometry DEFERRED | W2 | HIGH; role CRITICAL | Auth/guest, 403 vs 404, keyboard/mobile |
| SIG-038 Toast/Flash | Feedback/floating | COLOR, TYPE, SPACE, RADIUS, SHADOW, MOTION, FOCUS, LAYER | FINAL-CONCEPT; values PROVISIONAL | Every wave | HIGH | Success/warning/validation/long/mobile/Livewire re-init |
| SIG-039 Admin Collection | Collection/workbench | All token families as applicable | FINAL-CONCEPT; density/breakpoint DEFERRED | W3/W4 | HIGH–CRITICAL | Every active resource, UI-012/UI-021 heavy states |
| SIG-040 Report/Export State | Report/feedback | COLOR, TYPE, SPACE, SIZE, BORDER, FOCUS | FINAL-CONCEPT; grid/breakpoint DEFERRED | W4 | CRITICAL | Initial/populated/zero/invalid/export success/failure/aliases |

Coverage: **SIG-001–SIG-040 = 40/40**.

## Deferred Decisions

OPEN-011–OPEN-020 remain open. Pair basenames expand to `-desktop.png` and `-mobile.png` under `docs/redesign/baseline/` according to `12-SCREENSHOT-INDEX.md`.

| OPEN | Topic | May specify now | MUST NOT fix now | Exact evidence required | Wave blocked |
|---|---|---|---|---|---|
| OPEN-011 | Desktop sidebar dimensions/gutter | Expanded/collapsed concept, grouping, accessible names | Exact widths, default geometry, internal gutter | Six `UI-002-dashboard-d01-*`…`d06-*` pairs; ACT-08/09/10/11 dashboard pairs; exact `GLOBAL-sidebar-collapsed-desktop.png` and `GLOBAL-sidebar-collapsed-persisted-desktop.png`; expanded→collapse→reload notes | W2 shell |
| OPEN-012 | Mobile nav/drawer geometry | Compact header, full authorized destination parity, focus requirements | Exact drawer width/edge/backdrop/utility placement | Exact `GLOBAL-mobile-nav-act01-super-admin-mobile.png`, `act02-requester`, `act03-tier1`, `act04-tier2`, `act05-current-approver`, `act06-stale-approver`, `act07-team-chair`, `act08-admin-t1`, `act09-requester-approver`, `act10-chair-t1`, `act11-chair-admin`; keyboard/touch run | W2 shell |
| OPEN-013 | Page gutters/max widths | Relative content-density classes and alignment principles | Exact mobile/desktop gutters, max widths, full-width exception values | `UI-001-login`, `UI-003-change-password`, L-01–L-03, representative UI-007, T-01–T-06, `UI-011-reports-populated`, `UI-012-users`, `UI-021-services` pairs | W2 foundation; affected W3–W6 screens |
| OPEN-014 | Modal width bands/scroll thresholds | Narrow/standard/wide/mobile anatomy | Exact widths, max-height/scroll switch | Ten reachable modal pairs: `UI-008-modal-priority`, `reject`, `request-information`, `complete-svc02`, `confirmation`, `reopen`, `approval-decision`, `request-approval`, `third-party-resume`, `triage`; ten Priority-2 `*-validation-desktop.png`; focus/scroll notes | Any modal work W3–W6 |
| OPEN-015 | Desktop ticket main/rail ratio | Conversation-first main area and supporting metadata rail | Exact ratio, rail width/sticky/independent-scroll behavior | Six `UI-008-detail-t01-*`…`t06-*` desktop files; `UI-008-detail-t03-tier1-svc07-desktop.png`; SVC-02/03/07/attachment long-content desktop states | W6 |
| OPEN-016 | Mobile ticket order/sticky/disclosure | Identity/state/action-first principle; local disclosure state allowed | Exact section order per T variant, sticky composer/action geometry, disclosure defaults | Six T-01–T-06 mobile files; `UI-008-detail-new-queue-mobile.png`, waiting/completion/reopen workflow mobile files, SVC-02/03/07 mobile files; mobile keyboard/focus notes | W6 |
| OPEN-017 | Responsive breakpoints | Reflow/table→card/dedicated-hierarchy principles | Exact shell/table/form/detail breakpoint values | Representative 1440×900/390×844 pairs across UI-001–023; intermediate-width exploration; runtime checklist table/card switch and overflow | W2–W6 |
| OPEN-018 | UI-012/UI-021 density | Preserve contract markers while reducing unnecessary presentation repetition | Exact row/modal density, record/page geometry, DOM strategy | `UI-012-users` and `UI-021-services` pairs; per-record modal focus/order; related validation/error states and performance observation | W4 |
| OPEN-019 | Truncation/recovery limits | Full recovery required; helper/error never truncates | Character/line clamps and exact tooltip/disclosure behavior | VIS-017 long fixtures: `UI-008-detail-t02-requester-svc07`, `t03-tier1-svc07`, `t06-team-chair`, L-01–L-03, UI-012, UI-021 pairs; long subject/name/comment/owner/filename checks | W3–W6 |
| OPEN-020 | Dashboard/report grid composition | Actionable work before KPI; role-specific primitives | Exact D-01–D-06 columns/order geometry and report summary grid | Six D-01–D-06 pairs; `UI-002-dashboard-act08-admin-t1`, `act09-requester-approver`, `act10-chair-t1`, `act11-chair-admin`; `UI-011-reports-populated` pair plus populated/zero runtime notes | W4 |

### Authoritative evidence filename sets

For every `PAIR` basename below, the exact files are:

```text
docs/redesign/baseline/{basename}-desktop.png
docs/redesign/baseline/{basename}-mobile.png
```

`E-SHELL-DASHBOARD` (OPEN-011/020):

- `UI-002-dashboard-d01-super-admin`
- `UI-002-dashboard-d02-requester`
- `UI-002-dashboard-d03-tier1`
- `UI-002-dashboard-d04-tier2`
- `UI-002-dashboard-d05-current-approver`
- `UI-002-dashboard-d06-team-chair`
- `UI-002-dashboard-act08-admin-t1`
- `UI-002-dashboard-act09-requester-approver`
- `UI-002-dashboard-act10-chair-t1`
- `UI-002-dashboard-act11-chair-admin`

OPEN-011 also requires these exact single files:

```text
docs/redesign/baseline/GLOBAL-sidebar-collapsed-desktop.png
docs/redesign/baseline/GLOBAL-sidebar-collapsed-persisted-desktop.png
```

`E-MOBILE-NAV` (OPEN-012), exact single files:

```text
docs/redesign/baseline/GLOBAL-mobile-nav-act01-super-admin-mobile.png
docs/redesign/baseline/GLOBAL-mobile-nav-act02-requester-mobile.png
docs/redesign/baseline/GLOBAL-mobile-nav-act03-tier1-mobile.png
docs/redesign/baseline/GLOBAL-mobile-nav-act04-tier2-mobile.png
docs/redesign/baseline/GLOBAL-mobile-nav-act05-current-approver-mobile.png
docs/redesign/baseline/GLOBAL-mobile-nav-act06-stale-approver-mobile.png
docs/redesign/baseline/GLOBAL-mobile-nav-act07-team-chair-mobile.png
docs/redesign/baseline/GLOBAL-mobile-nav-act08-admin-t1-mobile.png
docs/redesign/baseline/GLOBAL-mobile-nav-act09-requester-approver-mobile.png
docs/redesign/baseline/GLOBAL-mobile-nav-act10-chair-t1-mobile.png
docs/redesign/baseline/GLOBAL-mobile-nav-act11-chair-admin-mobile.png
```

`E-PAGE-GEOMETRY` (OPEN-013/017) uses PAIR basenames:

- `UI-001-login`; `UI-003-change-password`;
- `UI-004-ticket-list-l01-requester`; `UI-004-ticket-list-l02-operational`; `UI-004-ticket-list-l03-team-chair`;
- `UI-007-create-normal-svc06`; `UI-007-create-svc07-dynamic`;
- all six basenames in `E-TICKET-VARIANTS` below;
- `UI-011-reports-populated`; `UI-012-users`; `UI-021-services`.

`E-MODAL-PAIR` (OPEN-014) uses PAIR basenames:

- `UI-008-modal-priority`
- `UI-008-modal-reject`
- `UI-008-modal-request-information`
- `UI-008-modal-complete-svc02`
- `UI-008-modal-confirmation`
- `UI-008-modal-reopen`
- `UI-008-modal-approval-decision`
- `UI-008-modal-request-approval`
- `UI-008-modal-third-party-resume`
- `UI-008-modal-triage`

OPEN-014 validation/error evidence uses these exact desktop files:

```text
docs/redesign/baseline/UI-008-modal-priority-validation-desktop.png
docs/redesign/baseline/UI-008-modal-reject-validation-desktop.png
docs/redesign/baseline/UI-008-modal-request-information-validation-desktop.png
docs/redesign/baseline/UI-008-modal-complete-validation-desktop.png
docs/redesign/baseline/UI-008-modal-confirmation-validation-desktop.png
docs/redesign/baseline/UI-008-modal-reopen-validation-desktop.png
docs/redesign/baseline/UI-008-modal-approval-validation-gap-desktop.png
docs/redesign/baseline/UI-008-modal-request-approval-validation-desktop.png
docs/redesign/baseline/UI-008-modal-third-party-validation-desktop.png
docs/redesign/baseline/UI-008-modal-triage-validation-desktop.png
```

`E-TICKET-VARIANTS` (OPEN-015/016) uses PAIR basenames:

- `UI-008-detail-t01-super-admin-svc07`
- `UI-008-detail-t02-requester-svc07`
- `UI-008-detail-t03-tier1-svc07`
- `UI-008-detail-t04-tier2-assigned`
- `UI-008-detail-t05-current-approver`
- `UI-008-detail-t06-team-chair`

Supplemental ticket evidence uses `UI-008-detail-new-queue`, `UI-008-detail-wait-requester`, `UI-008-detail-wait-third-party`, `UI-008-detail-svc02-result`, `UI-008-svc02-before-completion`, all four indexed SVC-03 state basenames, and `UI-008-svc07-team-chair-safe` as PAIR rows.

`E-DENSITY` (OPEN-018) uses `UI-012-users` and `UI-021-services` PAIR basenames plus their indexed per-record modal/validation observations.

`E-LONG-CONTENT` (OPEN-019) uses `UI-008-detail-t02-requester-svc07`, `UI-008-detail-t03-tier1-svc07`, `UI-008-svc07-team-chair-safe`, all three L-01–L-03 ticket-list PAIR basenames, `UI-012-users`, and `UI-021-services`. The required fixture categories are long subject, name, description, public/internal comment, owner, and filename from VIS-017.

`E-DASHBOARD-REPORT` (OPEN-020) uses all `E-SHELL-DASHBOARD` PAIR basenames plus `UI-011-reports-populated`; runtime notes must also record zero-result/initial report and long/zero KPI values even where the index intentionally cross-covers one image.

## Coverage and Traceability Check

| Coverage set | Result | Destination |
|---|---:|---|
| Active screens | **UI-001–UI-023 = 23/23** | SIG mapping in document 17 plus system families above |
| Dashboard variants | **D-01–D-06 = 6/6** | Dashboard system, SIG-020/021 |
| Ticket-list variants | **L-01–L-03 = 3/3** | Table/mobile/filter systems, SIG-014–019 |
| Ticket-detail variants | **T-01–T-06 = 6/6** | Ticket workbench/communication/modal, SIG-022–033 |
| DEC decisions | **DEC-001–DEC-040 = 40/40** | 28 ADOPT/ADAPT map to systems; 12 IGNORE have no token/component destination |
| VIS register | **VIS-001–VIS-017 = 17/17** | Preserved/deferred per `23-DESIGN-SYSTEM-DECISIONS.md` |

### Active-screen coverage

| UI | Design-system destination | Non-negotiable boundary |
|---|---|---|
| UI-001 Login | Guest shell, form controls, primary button, validation/loading/flash | Guest/auth redirect and credential/session contract |
| UI-002 Dashboard | KPI, work section, period control, announcement/contextual state | D-01–D-06 and ACT-08/09/10/11 data/action composition |
| UI-003 Change password | Guest-style authenticated shell, form/error/loading | PUT/spoofing, password old-value exception, session/flash |
| UI-004 Ticket list | Filter/tabs, status/priority, desktop table/mobile record, pagination | L-01–L-03 query/scope/projection differences |
| UI-005 Queue | Tabs, operational records, empty/loading | Per-tab ability/scope/count; no claim/handle trigger activation |
| UI-006 All tickets | Search, status/priority, table/card, pagination | Tier-1-only scope and query preservation |
| UI-007 Create ticket | Catalog, grouped form, dynamic/file controls, validation/loading | Service selection, nested fields/files, location and create-for-other contract |
| UI-008 Ticket detail | Ticket workbench, communication, metadata, attachment, modal, feedback | T-01–T-06, policy flags, 14 modal contracts, all workflow/safe-projection boundaries |
| UI-009 Notifications | Collection, count, read actions, pagination/empty/feedback | Current-user ownership, POST read/read-all, destination |
| UI-010 Approvals | Work records, action cluster, empty/error/loading | Current active Approver and pending-only decision contract |
| UI-011 Reports | Period control, KPI/summary, tables, export/loading/error | Primary/alias query, policy, MIME/filename/blob/audit |
| UI-012 Users | High-density admin collection, responsive records, modal/standalone forms | Targeted old/error markers, arrays, self restrictions |
| UI-013 Create user | Grouped admin form/state | Exact role/team/skill/password payload |
| UI-014 Edit user | Record-context form/actions/state | Update payload and self/status restrictions |
| UI-015 Reset password | Focused consequence form | Target policy, confirmation names, no password repopulation |
| UI-016 Locations | Hierarchical collection, filter, modal/form/state | Building/floor IDs/markers/dependency errors; rooms remain absent |
| UI-017 Teams | Admin record collection and modal/form state | Bespoke markers and only actual active triggers |
| UI-018 Skills | Searchable collection, modal/form/state | Query/paginator/usage/deactivation contracts |
| UI-019 Audit | Read-only collection, filters, disclosure/chronology | Sensitive server scope, no mutation, query preservation |
| UI-020 Branding | Form/file/preview/history/feedback | Multipart logo endpoint/storage/version contract |
| UI-021 Services | High-density catalog/form workbench, tabs/modal/dynamic fields | Nested/versioned payload, service/tab context; dormant field-status UI absent |
| UI-022 Announcements | Form, scheduled records, disclosure/actions/state | Super Admin/T1 policy, `_announcement_id`, no invented search/pagination |
| UI-023 403 | Standalone contextual error, focus/safe navigation | HTTP 403 and no hidden resource/capability leakage |

### Primary-variant coverage

| Variant set | Destination | Count |
|---|---|---:|
| D-01–D-06 | Role-specific Dashboard primitives; exact grid deferred | 6/6 |
| L-01–L-03 | Same collection grammar with requester/operational/safe projection | 3/3 |
| T-01–T-06 | Same workbench grammar with distinct data/action/projection | 6/6 |
| **Total** | Primary structural variants | **15/15** |

The 12 `IGNORE` decisions with **no component/token destination** are DEC-001, DEC-012, DEC-015, DEC-016, DEC-018, DEC-019, DEC-028, DEC-030, DEC-033, DEC-034, DEC-037, and DEC-038. No SPA root, command palette, configurable columns/multi-sort, click-cell filter, bulk action, load-more, inline mutation, knowledge suggestion, customizable dashboard, settings SPA modal, realtime presence, or dark theme is introduced.

## Implementation Readiness Rules

A component is not implementation-ready merely because visual tokens are specified. It becomes eligible only when all are true:

1. relevant manual baseline prerequisite is satisfied and reviewed;
2. affected OPEN item is resolved with evidence, or the implementation avoids its deferred dimension entirely;
3. every frozen route/form/query/modal/data selector contract is mapped;
4. pure-role, current/stale Approver, Team Chair, and critical multi-role variants are mapped;
5. default/keyboard/mobile/loading/error/validation/focus acceptance is defined;
6. relevant regression command/test class and manual fixture are known;
7. the implementation wave authorizes the exact file/surface;
8. known baseline defect is preserved or separately approved—never silently repaired;
9. no ignored DEC destination or dormant/no-trigger feature is activated;
10. full diff remains inside authorized presentation scope after the implementation hold is formally removed.

FASE 3.3 does not satisfy item 1 and therefore does not authorize W1. Design-system specification readiness and implementation readiness remain separate gates.
