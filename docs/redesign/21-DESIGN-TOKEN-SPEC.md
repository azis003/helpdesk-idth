# SIHATI Design Token Specification

Token ID di dokumen ini adalah identifier spesifikasi, bukan CSS custom property, Tailwind key, Blade API, atau izin implementation. Candidate exact value selalu dibaca bersama kolom `Status` dan evidence manual. Light presentation only; tidak ada dark-theme token set.

## Reading Rules

- `FINAL-CONCEPT`: semantic token/alias dan hubungan maknanya final; bila candidate menunjuk token lain, exact hasil tetap mengikuti status token sumber.
- `PROVISIONAL-VALUE`: candidate exact dapat diuji, tetapi belum production-authoritative.
- `DEFERRED-VALUE`: dilarang memilih exact value sebelum evidence manual yang disebutkan tersedia.
- Contrast angka adalah perhitungan candidate, bukan browser/forced-colors/zoom evidence.
- “Pair” berarti surface foreground/background yang harus diuji bersama; normal text target ≥4.5:1, large text ≥3:1, essential non-text/focus ≥3:1.

## COLOR

### Foundation, surfaces, text, and actions

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-COLOR-SURFACE-CANVAS | `color.surface.canvas` | COLOR | Page/workbench background | `#F6F8FA` | PROVISIONAL-VALUE | SIG-001/034–040 | Pair with dark text; no meaning alone | Guest/auth/403 and both viewports; contrast/zoom |
| TOK-COLOR-SURFACE-PRIMARY | `color.surface.primary` | COLOR | Main work surface | `#FFFFFF` | PROVISIONAL-VALUE | SIG-001/007–011/017/022–032/039/040 | Base pair for all text/control candidates | Representative form/list/detail/modal screenshots |
| TOK-COLOR-SURFACE-MUTED | `color.surface.muted` | COLOR | Supporting/readonly context | `#EEF1F4` | PROVISIONAL-VALUE | SIG-007/017/026/031/034/039 | Body text must remain ≥4.5:1 | Metadata/internal/readonly screenshots |
| TOK-COLOR-SURFACE-HOVER | `color.surface.hover` | COLOR | Pointer reinforcement | `#E7EDF2` | PROVISIONAL-VALUE | SIG-002/005/006/014–019/033 | Never sole interactive cue | Pointer + keyboard comparison |
| TOK-COLOR-SURFACE-SELECTED | `color.surface.selected` | COLOR | Existing selected/active context | `#D7F0F5` | PROVISIONAL-VALUE | SIG-002/014/017/033 | Pair with accent text ≈6.23:1; add border/weight | Active nav/tab/filter/row where selection exists |
| TOK-COLOR-SURFACE-FLOATING | `color.surface.floating` | COLOR | Dropdown/overflow surface | Alias `color.surface.primary` (`#FFFFFF`) | FINAL-CONCEPT | SIG-003/033/038 | Strong boundary + shadow; focus must remain visible | Notification/dropdown/modal interaction captures |
| TOK-COLOR-SURFACE-MODAL | `color.surface.modal` | COLOR | Dialog surface | Alias `color.surface.primary` (`#FFFFFF`) | FINAL-CONCEPT | SIG-032 | Dialog text/control contrast target unchanged | Ten reachable modal pairs |
| TOK-COLOR-BORDER-DEFAULT | `color.border.default` | COLOR | Quiet divider/passive boundary | `#D6DEE5` | PROVISIONAL-VALUE | SIG-001/004/007–019/022–040 | Not sole essential-control boundary | Tables/forms/surfaces at 100%/400% zoom |
| TOK-COLOR-BORDER-STRONG | `color.border.strong` | COLOR | Essential boundary/selected separation | `#7A8793` | PROVISIONAL-VALUE | SIG-001/003/007–011/017/032/033/039 | ≈3.67:1 on white; target ≥3:1 | Controls/floating surfaces/forced-colors |
| TOK-COLOR-TEXT-DEFAULT | `color.text.default` | COLOR | Primary text | `#161C22` | PROVISIONAL-VALUE | All text-bearing SIG | ≈17.17:1 on white | All representative screens/zoom |
| TOK-COLOR-TEXT-MUTED | `color.text.muted` | COLOR | Secondary readable text | `#4E5A66` | PROVISIONAL-VALUE | SIG-001–040 | ≈7.05:1 on white; normal text safe candidate | Metadata/helper across surfaces |
| TOK-COLOR-TEXT-SUBTLE | `color.text.subtle` | COLOR | Lowest normal metadata tier | `#667381` | PROVISIONAL-VALUE | SIG-002/004/017–031/039/040 | ≈4.85:1 on white; do not use below normal target | Small metadata and mobile screenshots |
| TOK-COLOR-TEXT-INVERSE | `color.text.inverse` | COLOR | Text on strong semantic/action surface | `#FFFFFF` | PROVISIONAL-VALUE | SIG-005/006/035/038 | Must be tested per paired strong color | Primary/destructive/loading surfaces |
| TOK-COLOR-ACTION-PRIMARY | `color.action.primary` | COLOR | Primary action/active identity | `#135D72` | PROVISIONAL-VALUE | SIG-002–006/014–016/023/033/038 | White ≈7.4:1; do not flood large surfaces | Buttons/nav/focus-adjacent states |
| TOK-COLOR-ACTION-PRIMARY-HOVER | `color.action.primary.hover` | COLOR | Primary hover/active | `#154D5E` | PROVISIONAL-VALUE | SIG-002/005/006/014–016/033 | White ≈9.31:1; hover not sole cue | Pointer and pressed states |
| TOK-COLOR-ACTION-PRIMARY-SOFT | `color.action.primary.soft` | COLOR | Selected/quiet accent surface | `#D7F0F5` | PROVISIONAL-VALUE | SIG-002/012/014/018/025/033 | Accent text ≈6.23:1 | Active/sidebar/message candidates |
| TOK-COLOR-ACTION-PRIMARY-FOREGROUND | `color.action.primary.foreground` | COLOR | Label/icon on primary action | Alias `color.text.inverse` (`#FFFFFF`) | FINAL-CONCEPT | SIG-005/006 | Pair target ≥4.5:1 | Primary buttons at all states |
| TOK-COLOR-ACTION-DESTRUCTIVE | `color.action.destructive` | COLOR | Existing destructive/final action | `#A21F37` | PROVISIONAL-VALUE | SIG-005/023/032/033 | White text target ≥4.5:1; consequence text required | Delete/reject/cancel confirmations |
| TOK-COLOR-ACTION-DESTRUCTIVE-HOVER | `color.action.destructive.hover` | COLOR | Destructive hover/active | `#86182E` | PROVISIONAL-VALUE | SIG-005/023/032/033 | White text target ≥4.5:1 | Pointer/pressed destructive states |
| TOK-COLOR-ACTION-DESTRUCTIVE-FOREGROUND | `color.action.destructive.foreground` | COLOR | Label/icon on destructive action | Alias `color.text.inverse` (`#FFFFFF`) | FINAL-CONCEPT | SIG-005/023/032 | Text plus consequence; never color-only | Destructive controls/mobile |
| TOK-COLOR-ACTION-DISABLED-SURFACE | `color.action.disabled.surface` | COLOR | Rendered disabled control background | `#EEF1F4` | PROVISIONAL-VALUE | SIG-005–011/019/032 | Must not resemble hidden authorization | Existing disabled/loading examples only |
| TOK-COLOR-ACTION-DISABLED-TEXT | `color.action.disabled.text` | COLOR | Rendered disabled label/icon | `#667381` | PROVISIONAL-VALUE | SIG-005–011/019/032 | Readable state; native semantics required | Existing disabled/loading examples only |

### Feedback and focus color

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-COLOR-FEEDBACK-SUCCESS-TEXT | `color.feedback.success.text` | COLOR | Successful outcome/closed text | `#0C6249` | PROVISIONAL-VALUE | SIG-012/034–036/038/040 | ≈6.71:1 on success surface | Success/closed/flash states |
| TOK-COLOR-FEEDBACK-SUCCESS-SURFACE | `color.feedback.success.surface` | COLOR | Success callout/badge surface | `#E9F8F2` | PROVISIONAL-VALUE | SIG-012/034/038/040 | Pair with success text; never color-only | Same plus forced-colors |
| TOK-COLOR-FEEDBACK-WARNING-TEXT | `color.feedback.warning.text` | COLOR | Waiting/attention text | `#8A5700` | PROVISIONAL-VALUE | SIG-012/034–036/038/040 | ≈5.73:1 on warning surface | Waiting/SLA/flash states |
| TOK-COLOR-FEEDBACK-WARNING-SURFACE | `color.feedback.warning.surface` | COLOR | Waiting/warning callout surface | `#FFF7E8` | PROVISIONAL-VALUE | SIG-012/034/038/040 | Text/copy distinguishes warning from workflow status | Same plus forced-colors |
| TOK-COLOR-FEEDBACK-DANGER-TEXT | `color.feedback.danger.text` | COLOR | Error/denied/negative outcome text | `#A21F37` | PROVISIONAL-VALUE | SIG-005/012/034/036–040 | ≈6.70:1 on danger surface | Validation/action/403/destructive states |
| TOK-COLOR-FEEDBACK-DANGER-SURFACE | `color.feedback.danger.surface` | COLOR | Error/negative callout surface | `#FDEEF1` | PROVISIONAL-VALUE | SIG-012/034/036–040 | Text/icon required; never empty-state substitute | Same plus forced-colors |
| TOK-COLOR-FEEDBACK-INFO-TEXT | `color.feedback.info.text` | COLOR | Intake/explanatory context | `#175A86` | PROVISIONAL-VALUE | SIG-012/034/035/040 | ≈6.62:1 on info surface | Initial/info/Baru states |
| TOK-COLOR-FEEDBACK-INFO-SURFACE | `color.feedback.info.surface` | COLOR | Info/intake soft surface | `#EAF4FB` | PROVISIONAL-VALUE | SIG-012/034/035/040 | Pair with info text | Same plus forced-colors |
| TOK-COLOR-FOCUS-RING | `color.focus.ring` | COLOR | High-contrast focus-visible ring | `#005FCC` | PROVISIONAL-VALUE | All interactive SIG | ≈5.98:1 on white; essential non-text target ≥3:1 | Keyboard on neutral/accent/danger/floating surfaces |

### Communication

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-COLOR-COMM-PUBLIC-SURFACE | `communication.public.surface` | COLOR | Requester/public message/composer surface | Alias `color.surface.primary` (`#FFFFFF`) | FINAL-CONCEPT | SIG-025/029 | Audience label remains visible; not color-only | T-02/T-03/T-04/T-05 public messages/composers |
| TOK-COLOR-COMM-PUBLIC-BORDER | `communication.public.border` | COLOR | Public communication boundary | Alias `color.border.default` (`#D6DEE5`) | FINAL-CONCEPT | SIG-025/029 | Add public audience heading/marker | Same plus Team Chair safe projection |
| TOK-COLOR-COMM-PUBLIC-MARKER | `communication.public.marker` | COLOR | Public-audience supporting cue | Alias `color.feedback.info.text` (`#175A86`) | FINAL-CONCEPT | SIG-025/029 | Supplementary icon/text; no meaning alone | Public/requester specimens |
| TOK-COLOR-COMM-INTERNAL-SURFACE | `communication.internal.surface` | COLOR | Protected internal-note surface | `#F1F3F5` | PROVISIONAL-VALUE | SIG-026/029/031 | Dark text ≈10.41:1 candidate; requires lock/private label | Agent/current-Approver positive; requester/Chair negative |
| TOK-COLOR-COMM-INTERNAL-BORDER | `communication.internal.border` | COLOR | Persistent protected boundary | Alias `color.border.strong` (`#7A8793`) | FINAL-CONCEPT | SIG-026/029/031 | Boundary + label + marker, never color-only | Internal notes/composer/fields |
| TOK-COLOR-COMM-INTERNAL-TEXT | `communication.internal.text` | COLOR | Internal heading/body emphasis | `#303A44` | PROVISIONAL-VALUE | SIG-026/029/031 | ≈10.41:1 on internal surface | Long internal comment/field specimens |
| TOK-COLOR-COMM-INTERNAL-MARKER | `communication.internal.marker` | COLOR | Lock/private supporting cue | Alias `color.text.muted` (`#4E5A66`) | FINAL-CONCEPT | SIG-026/029/031 | Accessible audience text remains primary | Internal visibility matrix |

### Eleven canonical statuses

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-COLOR-STATUS-BARU-TEXT | `color.status.baru.text` | COLOR | NEW / Baru foreground | Alias info text `#175A86` | FINAL-CONCEPT | SIG-012/017/018/020–024 | Full label + hollow intake cue | 11-status matrix both viewports/forced-colors |
| TOK-COLOR-STATUS-BARU-SURFACE | `color.status.baru.surface` | COLOR | NEW / Baru background | Alias info surface `#EAF4FB` | FINAL-CONCEPT | SIG-012/017/018/020–024 | Same family does not imply assignee | Same |
| TOK-COLOR-STATUS-DIPROSES-TEXT | `color.status.diproses.text` | COLOR | ACTIVE / Diproses foreground | Alias accent `#135D72` | FINAL-CONCEPT | SIG-012/017/018/020–024 | Full label + process cue | Same |
| TOK-COLOR-STATUS-DIPROSES-SURFACE | `color.status.diproses.surface` | COLOR | ACTIVE / Diproses background | Alias accent soft `#D7F0F5` | FINAL-CONCEPT | SIG-012/017/018/020–024 | Cue distinguishes from Dikerjakan | Same |
| TOK-COLOR-STATUS-DIKERJAKAN-TEXT | `color.status.dikerjakan.text` | COLOR | ACTIVE / Dikerjakan foreground | Alias accent `#135D72` | FINAL-CONCEPT | SIG-012/017/018/020–024 | Full label + work cue | Same |
| TOK-COLOR-STATUS-DIKERJAKAN-SURFACE | `color.status.dikerjakan.surface` | COLOR | ACTIVE / Dikerjakan background | Alias accent soft `#D7F0F5` | FINAL-CONCEPT | SIG-012/017/018/020–024 | Assignee/tier remains separate text | Same |
| TOK-COLOR-STATUS-MENUNGGU-PERSETUJUAN-TEXT | `color.status.menunggu-persetujuan.text` | COLOR | WAITING approval foreground | Alias warning text `#8A5700` | FINAL-CONCEPT | SIG-012/017/018/020–024/028 | Full canonical label + decision cue | Same plus approval specimens |
| TOK-COLOR-STATUS-MENUNGGU-PERSETUJUAN-SURFACE | `color.status.menunggu-persetujuan.surface` | COLOR | WAITING approval background | Alias warning surface `#FFF7E8` | FINAL-CONCEPT | Same | Context text distinguishes waiting types | Same |
| TOK-COLOR-STATUS-MENUNGGU-PEMOHON-TEXT | `color.status.menunggu-pemohon.text` | COLOR | WAITING requester foreground | Alias warning text `#8A5700` | FINAL-CONCEPT | SIG-012/017/018/020–024/028 | Full canonical label + person cue | Same plus waiting requester |
| TOK-COLOR-STATUS-MENUNGGU-PEMOHON-SURFACE | `color.status.menunggu-pemohon.surface` | COLOR | WAITING requester background | Alias warning surface `#FFF7E8` | FINAL-CONCEPT | Same | Not an error; audience explicit | Same |
| TOK-COLOR-STATUS-MENUNGGU-PIHAK-KETIGA-TEXT | `color.status.menunggu-pihak-ketiga.text` | COLOR | WAITING external foreground | Alias warning text `#8A5700` | FINAL-CONCEPT | SIG-012/017/018/020–024/028 | Full canonical label + external cue | Same plus third-party wait |
| TOK-COLOR-STATUS-MENUNGGU-PIHAK-KETIGA-SURFACE | `color.status.menunggu-pihak-ketiga.surface` | COLOR | WAITING external background | Alias warning surface `#FFF7E8` | FINAL-CONCEPT | Same | Party/follow-up text remains | Same |
| TOK-COLOR-STATUS-MENUNGGU-KONFIRMASI-TEXT | `color.status.menunggu-konfirmasi.text` | COLOR | WAITING confirmation foreground | Alias warning text `#8A5700` | FINAL-CONCEPT | SIG-012/017/018/020–024/028 | Full canonical label + pending-check cue | Same plus SVC-02/confirmation |
| TOK-COLOR-STATUS-MENUNGGU-KONFIRMASI-SURFACE | `color.status.menunggu-konfirmasi.surface` | COLOR | WAITING confirmation background | Alias warning surface `#FFF7E8` | FINAL-CONCEPT | Same | Timer context not encoded by color | Same |
| TOK-COLOR-STATUS-DITUTUP-TEXT | `color.status.ditutup.text` | COLOR | SUCCESS/CLOSED foreground | Alias success text `#0C6249` | FINAL-CONCEPT | SIG-012/017/018/020–024/028 | Full label + check circle | Same plus closed/reopen fixture |
| TOK-COLOR-STATUS-DITUTUP-SURFACE | `color.status.ditutup.surface` | COLOR | SUCCESS/CLOSED background | Alias success surface `#E9F8F2` | FINAL-CONCEPT | Same | No celebration-only treatment | Same |
| TOK-COLOR-STATUS-DITOLAK-TEXT | `color.status.ditolak.text` | COLOR | NEGATIVE agent rejection foreground | Alias danger text `#A21F37` | FINAL-CONCEPT | SIG-012/017/018/020–024/028 | Exact label + stop/minus cue | Same plus rejected fixture |
| TOK-COLOR-STATUS-DITOLAK-SURFACE | `color.status.ditolak.surface` | COLOR | NEGATIVE agent rejection background | Alias danger surface `#FDEEF1` | FINAL-CONCEPT | Same | Distinct label/reason from approval denial | Same |
| TOK-COLOR-STATUS-TIDAK-DISETUJUI-TEXT | `color.status.tidak-disetujui.text` | COLOR | NEGATIVE approval denial foreground | Alias danger text `#A21F37` | FINAL-CONCEPT | SIG-012/017/018/020–024/028 | Exact label + crossed-decision cue | Same plus approval-rejected fixture |
| TOK-COLOR-STATUS-TIDAK-DISETUJUI-SURFACE | `color.status.tidak-disetujui.surface` | COLOR | NEGATIVE approval denial background | Alias danger surface `#FDEEF1` | FINAL-CONCEPT | Same | Must not collapse into Ditolak | Same |
| TOK-COLOR-STATUS-DIBATALKAN-TEXT | `color.status.dibatalkan.text` | COLOR | NEUTRAL cancellation foreground | Alias muted text `#4E5A66` | FINAL-CONCEPT | SIG-012/017/018/020–024/028 | Exact label + slash cue | Same plus cancelled fixture |
| TOK-COLOR-STATUS-DIBATALKAN-SURFACE | `color.status.dibatalkan.surface` | COLOR | NEUTRAL cancellation background | Alias muted surface `#EEF1F4` | FINAL-CONCEPT | Same | Not rendered as error | Same |

### Four canonical priorities

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-COLOR-PRIORITY-KRITIS-TONE | `color.priority.kritis.tone` | COLOR | Priority level 4 tone | Alias danger text `#A21F37` | FINAL-CONCEPT | SIG-013/017/018/022 | Four-bar cue + full label | 4-priority matrix/forced-colors/mobile |
| TOK-COLOR-PRIORITY-KRITIS-SURFACE | `color.priority.kritis.surface` | COLOR | Level 4 soft support | Alias danger surface `#FDEEF1` | FINAL-CONCEPT | Same | Priority ≠ destructive action | Same |
| TOK-COLOR-PRIORITY-TINGGI-TONE | `color.priority.tinggi.tone` | COLOR | Priority level 3 tone | Alias warning text `#8A5700` | FINAL-CONCEPT | SIG-013/017/018/022 | Three-bar cue + full label | Same |
| TOK-COLOR-PRIORITY-TINGGI-SURFACE | `color.priority.tinggi.surface` | COLOR | Level 3 soft support | Alias warning surface `#FFF7E8` | FINAL-CONCEPT | Same | Priority ≠ waiting status | Same |
| TOK-COLOR-PRIORITY-SEDANG-TONE | `color.priority.sedang.tone` | COLOR | Priority level 2 tone | Alias info text `#175A86` | FINAL-CONCEPT | SIG-013/017/018/022 | Two-bar cue + full label | Same |
| TOK-COLOR-PRIORITY-SEDANG-SURFACE | `color.priority.sedang.surface` | COLOR | Level 2 soft support | Alias info surface `#EAF4FB` | FINAL-CONCEPT | Same | Label communicates urgency | Same |
| TOK-COLOR-PRIORITY-RENDAH-TONE | `color.priority.rendah.tone` | COLOR | Priority level 1 tone | Alias muted text `#4E5A66` | FINAL-CONCEPT | SIG-013/017/018/022 | One-bar cue + full label | Same |
| TOK-COLOR-PRIORITY-RENDAH-SURFACE | `color.priority.rendah.surface` | COLOR | Level 1 soft support | Alias muted surface `#EEF1F4` | FINAL-CONCEPT | Same | Must remain readable/not faint | Same |

## TYPOGRAPHY

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-TYPE-FAMILY-UI | `type.family.ui` | TYPOGRAPHY | Primary UI/data/long-form family | `ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif` | FINAL-CONCEPT | SIG-001–040 | No remote/local dependency; Indonesian/system coverage | Cross-platform render comparison and long strings |
| TOK-TYPE-FAMILY-MONO | `type.family.mono` | TYPOGRAPHY | Technical IDs/code only where existing | `ui-monospace, "SFMono-Regular", Consolas, "Liberation Mono", monospace` | FINAL-CONCEPT | SIG-019/024/027/039/040 where actually used | Do not use for ordinary ticket number unless existing semantics need it | Audit/report/technical specimens |
| TOK-TYPE-DISPLAY-EXCEPTIONAL | `type.display-exceptional` | TYPOGRAPHY | Rare exceptional state anchor | System / 32px / 700 / 40px; mobile 28/36 | PROVISIONAL-VALUE | SIG-034/037 | Large-text ≥3:1; never routine hero | Login/403/exceptional empty both viewports |
| TOK-TYPE-PAGE-TITLE | `type.page-title` | TYPOGRAPHY | Current page/task identity | System / 28px / 700 / 36px; mobile 24/32 | PROVISIONAL-VALUE | SIG-004/022/037/039/040 | Natural wrap; heading order semantic | 23 screens, long title, zoom |
| TOK-TYPE-SECTION-TITLE | `type.section-title` | TYPOGRAPHY | Work-group heading | System / 20px / 600 / 28px; mobile 18/26 | PROVISIONAL-VALUE | SIG-004/020/021/024/031/032/039/040 | Remains identifiable as heading | Forms/dashboard/detail/modal specimens |
| TOK-TYPE-RECORD-TITLE | `type.record-title` | TYPOGRAPHY | Ticket/user/service identity | System / 15px / 600 / 22px | PROVISIONAL-VALUE | SIG-017/018/022/025/026/039 | Truncate only with recovery | VIS-017 long records |
| TOK-TYPE-BODY | `type.body` | TYPOGRAPHY | Default UI reading voice | System / 14px / 400 / 20px | PROVISIONAL-VALUE | SIG-001–040 | Normal text target ≥4.5:1 | All surfaces/zoom |
| TOK-TYPE-BODY-LONG | `type.body-long` | TYPOGRAPHY | Description/message/guidance | System / 15px / 400 / 24px | PROVISIONAL-VALUE | SIG-009/024–029/034/036/037 | Full wrap; no ellipsis | Long description/comments/modal text |
| TOK-TYPE-FORM-LABEL | `type.form-label` | TYPOGRAPHY | Field/control label | System / 13px / 600 / 18px | PROVISIONAL-VALUE | SIG-007–011/015/016/032/040 | Visible label; wraps | Long Indonesian labels/errors |
| TOK-TYPE-METADATA | `type.metadata` | TYPOGRAPHY | Time/owner/supporting identity | System / 12px / 400–500 / 18px | PROVISIONAL-VALUE | SIG-002/017–031/039/040 | Normal-text contrast, no unreadable faint text | Table/card/timeline/mobile |
| TOK-TYPE-HELPER | `type.helper` | TYPOGRAPHY | Requirement/recovery/helper | System / 12px / 400 / 18px | PROVISIONAL-VALUE | SIG-007–011/029/032/034/036 | Always wrap and associate | Dynamic/file/admin forms |
| TOK-TYPE-ERROR | `type.error` | TYPOGRAPHY | Field/action error | System / 12px / 500 / 18px | PROVISIONAL-VALUE | SIG-007–011/032/036/040 | Text + association; never color-only | Validation-old/modal error pairs |
| TOK-TYPE-CONTROL | `type.control` | TYPOGRAPHY | Button/tab/filter/action label | System / 14px / 600 / 20px | PROVISIONAL-VALUE | SIG-002–006/014–016/019/023/032/033 | Accessible name retained | Long actions and mobile stack |
| TOK-TYPE-BADGE | `type.badge` | TYPOGRAPHY | Status/priority/filter/count | System / 12px / 600 / 16px | PROVISIONAL-VALUE | SIG-012/013/015/018/022 | Full canonical labels | 11/4 matrices and zoom |
| TOK-TYPE-NUMERIC-KPI | `type.numeric-kpi` | TYPOGRAPHY | KPI/count emphasis | System / 24px / 700 / 32px, tabular numerals; mobile 22/30 | PROVISIONAL-VALUE | SIG-020/040 | Label/period/denominator mandatory | D-01–D-06, zero/large values |

## SPACE

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-SPACE-0 | `space.0` | SPACE | Zero gap | 0px | PROVISIONAL-VALUE | All as needed | Avoid accidental control adjacency | Representative specimens |
| TOK-SPACE-1 | `space.1` | SPACE | Micro gap | 4px | PROVISIONAL-VALUE | SIG-005/006/012/013/027 | Not touch-target separation alone | Controls/badges |
| TOK-SPACE-2 | `space.2` | SPACE | Compact internal gap | 8px | PROVISIONAL-VALUE | SIG-002–019/022–033 | Maintain readable grouping | Dense controls/rows |
| TOK-SPACE-3 | `space.3` | SPACE | Field/row gap | 12px | PROVISIONAL-VALUE | SIG-004–019/022–040 | Do not crowd errors/actions | Forms/tables/mobile |
| TOK-SPACE-4 | `space.4` | SPACE | Standard content gap | 16px | PROVISIONAL-VALUE | SIG-001–040 | Supports readable flow | Representative all screens |
| TOK-SPACE-5 | `space.5` | SPACE | Modal/support gap | 20px | PROVISIONAL-VALUE | SIG-007–011/024–032/034–040 | Long text remains grouped | Forms/modal/detail |
| TOK-SPACE-6 | `space.6` | SPACE | Section gap | 24px | PROVISIONAL-VALUE | SIG-001/004/020–040 | Distinguish task groups | Dashboard/detail/admin |
| TOK-SPACE-8 | `space.8` | SPACE | Page-region gap | 32px | PROVISIONAL-VALUE | SIG-001/004/020/021/039/040 | Not exact page gutter | Page pairs |
| TOK-SPACE-10 | `space.10` | SPACE | Large exception gap | 40px | PROVISIONAL-VALUE | SIG-001/034/037 | Use sparingly; no decorative emptiness | Guest/403/empty |
| TOK-SPACE-12 | `space.12` | SPACE | Largest approved candidate gap | 48px | PROVISIONAL-VALUE | SIG-001/037 | Exceptional only | Guest/error wide view |
| TOK-SPACE-CONTROL-INTERNAL | `space.control-internal` | SPACE | Icon/label/control internals | Alias `space.2` | FINAL-CONCEPT | SIG-002–016/019/023/032/033 | Compact but legible | Controls both viewports |
| TOK-SPACE-FIELD | `space.field` | SPACE | Label/control/helper/error unit rhythm | Alias `space.3` | FINAL-CONCEPT | SIG-007–011/029/032/039 | Association remains visually clear | Form error/long label |
| TOK-SPACE-ROW | `space.row` | SPACE | Dense record rhythm | Alias `space.3` | FINAL-CONCEPT | SIG-017/018/024–028/039/040 | Maintain target separation | Table/card/timeline |
| TOK-SPACE-SECTION | `space.section` | SPACE | Between work groups | Alias `space.6` | FINAL-CONCEPT | SIG-004/020–040 | Larger than internal gap | Dashboard/detail/modal |
| TOK-SPACE-PAGE | `space.page` | SPACE | Between page-level regions | Alias `space.8` | FINAL-CONCEPT | SIG-001/004/020/021/039/040 | Does not set page gutter | 23 screen pairs |
| TOK-SPACE-MODAL | `space.modal` | SPACE | Dialog body grouping | Alias `space.5` | FINAL-CONCEPT | SIG-032 | Not modal width | Reachable modal pairs |
| TOK-SPACE-TIMELINE | `space.timeline` | SPACE | Chronology event rhythm | Alias `space.4` | FINAL-CONCEPT | SIG-024/027/028 | Event order remains readable | Public/internal/workflow timeline |
| TOK-SPACE-TOOLBAR | `space.toolbar` | SPACE | Filter/search/action grouping | Alias `space.3` | FINAL-CONCEPT | SIG-004/014–019/023/033/039/040 | Keyboard order follows visual order | List/admin/report toolbar |

## SIZE

### Controls and icons

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-SIZE-CONTROL-COMPACT | `size.control.compact` | SIZE | Dense desktop utility/control | 32px visual height | PROVISIONAL-VALUE | SIG-002/005/006/014–019/023/033/039 | Mobile operable area still ≥44px | Toolbar/table/sidebar specimens |
| TOK-SIZE-CONTROL-DEFAULT | `size.control.default` | SIZE | Routine form/action control | 40px visual height | PROVISIONAL-VALUE | SIG-005–011/015/016/019/029/032/040 | Mobile total target ≥44px | Forms/modals both viewports |
| TOK-SIZE-CONTROL-COMFORTABLE | `size.control.comfortable` | SIZE | Mobile/high-consequence control | 44px visual height | PROVISIONAL-VALUE | SIG-003/005/006/011/023/029/032 | Meets touch direction candidate | Mobile creation/detail/modal |
| TOK-SIZE-TOUCH-MIN | `size.touch.minimum` | SIZE | Minimum mobile operable area | 44×44px | PROVISIONAL-VALUE | All mobile interactive SIG | Adjacent destructive targets require separation | Touch run at 390×844 |
| TOK-SIZE-ICON-SMALL | `size.icon.small` | SIZE | Compact supporting icon | 16px | PROVISIONAL-VALUE | SIG-005/012/013/027/034–038 | Never sole accessible name | Dense row/badge/control |
| TOK-SIZE-ICON-DEFAULT | `size.icon.default` | SIZE | Routine functional icon | 20px | PROVISIONAL-VALUE | SIG-002/003/006/011/030/033 | Icon-only target larger than glyph | Shell/forms/files |
| TOK-SIZE-ICON-LARGE | `size.icon.large` | SIZE | Contextual state/emphasis icon | 24px | PROVISIONAL-VALUE | SIG-022/034/037 | Decorative icon hidden when redundant | Header/state specimens |
| TOK-SIZE-LOADING-INDICATOR | `size.loading.indicator` | SIZE | Compact busy indicator | 16px | PROVISIONAL-VALUE | SIG-005/006/035/038/040 | Text/busy semantics remains | Navigation/form/export loading |

### Layout and responsive values intentionally deferred

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-SIZE-SIDEBAR-EXPANDED | `size.shell.sidebar.expanded` | SIZE | Expanded desktop rail width | No exact value; candidate range may be discussed after measurement | DEFERRED-VALUE | SIG-001/002 | Long labels/active cue/focus must fit | OPEN-011 shell/role screenshots |
| TOK-SIZE-SIDEBAR-COLLAPSED | `size.shell.sidebar.collapsed` | SIZE | Collapsed desktop rail width | No exact value | DEFERRED-VALUE | SIG-001/002 | Accessible names and targets remain | OPEN-011 collapsed/persisted files |
| TOK-SIZE-MOBILE-DRAWER | `size.shell.mobile-drawer` | SIZE | Mobile navigation panel geometry | No exact value | DEFERRED-VALUE | SIG-001/003 | No clipping/focus behind overlay | OPEN-012 eleven mobile-nav files |
| TOK-SIZE-PAGE-GUTTER-MOBILE | `size.page.gutter.mobile` | SIZE | Narrow viewport content gutter | No exact value | DEFERRED-VALUE | SIG-001/004/007/017/018/022/032/039/040 | Zoom/reflow/touch edge clearance | OPEN-013 screen pairs |
| TOK-SIZE-PAGE-GUTTER-DESKTOP | `size.page.gutter.desktop` | SIZE | Wide viewport content gutter | No exact value | DEFERRED-VALUE | Same | Density and alignment | OPEN-013 screen pairs |
| TOK-SIZE-PAGE-MAX-READABLE | `size.page.max-readable` | SIZE | Readable form/long-content measure | No exact value | DEFERRED-VALUE | SIG-001/004/007/009/037 | Long lines remain readable | OPEN-013 guest/form/detail/report |
| TOK-SIZE-MODAL-NARROW | `size.modal.narrow` | SIZE | Confirmation/short dialog width | No exact value | DEFERRED-VALUE | SIG-032 | Footer/error/focus remains reachable | OPEN-014 modal pairs |
| TOK-SIZE-MODAL-STANDARD | `size.modal.standard` | SIZE | Routine dialog width | No exact value | DEFERRED-VALUE | SIG-032 | Same | OPEN-014 modal pairs |
| TOK-SIZE-MODAL-WIDE | `size.modal.wide` | SIZE | High-density editor width | No exact value | DEFERRED-VALUE | SIG-032/039 | Avoid clipping/hidden footer | OPEN-014 + UI-012/UI-021 |
| TOK-SIZE-TICKET-RAIL-RATIO | `size.ticket.rail-ratio` | SIZE | Desktop main/metadata rail proportion | No exact value | DEFERRED-VALUE | SIG-022–031 | Conversation must not be squeezed | OPEN-015 T-01–T-06 desktop |
| TOK-SIZE-TICKET-MOBILE-STICKY | `size.ticket.mobile-sticky-offset` | SIZE | Any accepted sticky action/composer clearance | No exact value | DEFERRED-VALUE | SIG-023/029/031 | Must not cover content/error/mobile keyboard | OPEN-016 T variants/mobile workflow |
| TOK-SIZE-BREAKPOINT-SHELL | `size.breakpoint.shell` | SIZE | Desktop/mobile shell transition | No exact value | DEFERRED-VALUE | SIG-001–003 | Destination parity across transition | OPEN-017 intermediate exploration |
| TOK-SIZE-BREAKPOINT-COLLECTION | `size.breakpoint.collection` | SIZE | Table/card transition | No exact value | DEFERRED-VALUE | SIG-017–019/039/040 | Same data/action remains reachable | OPEN-017 list/admin pairs |
| TOK-SIZE-BREAKPOINT-DETAIL | `size.breakpoint.detail` | SIZE | Ticket/form/detail hierarchy transition | No exact value | DEFERRED-VALUE | SIG-007–011/022–032 | Focus order follows visual order | OPEN-017 T/form/modal exploration |
| TOK-SIZE-ADMIN-DENSITY | `size.admin.row-density` | SIZE | UI-012/UI-021 dense record geometry | No exact value | DEFERRED-VALUE | SIG-017–019/032/039 | Modal target/focus order preserved | OPEN-018 users/services pairs |
| TOK-SIZE-TRUNCATION-LIST | `size.truncation.list-lines` | SIZE | Optional list-title line clamp | No exact value; full recovery mandatory | DEFERRED-VALUE | SIG-004/017/018/039 | Essential identity recoverable by keyboard/AT | OPEN-019 VIS-017 list fixtures |
| TOK-SIZE-TRUNCATION-DETAIL | `size.truncation.detail-lines` | SIZE | Optional detail/metadata clamp | No exact value; helper/error never clamp | DEFERRED-VALUE | SIG-022/024–031 | Long content remains accessible | OPEN-019 T-02/T-03/T-06 |
| TOK-SIZE-DASHBOARD-GRID | `size.dashboard.grid-composition` | SIZE | KPI/work-section grid geometry | No exact value | DEFERRED-VALUE | SIG-020/021/040 | Reading/action order must remain role-correct | OPEN-020 D-01–D-06/report |

## RADIUS

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-RADIUS-NONE | `radius.none` | RADIUS | Connected/divider-led surfaces | 0px | PROVISIONAL-VALUE | SIG-017/024/027/028 | Boundaries still perceivable | Table/timeline specimens |
| TOK-RADIUS-SMALL | `radius.small` | RADIUS | Compact controls/cues | 4px | PROVISIONAL-VALUE | SIG-005/006/012–016/019/033 | Focus ring follows shape | Controls/badges |
| TOK-RADIUS-MEDIUM | `radius.medium` | RADIUS | Routine control/panel/card | 8px | PROVISIONAL-VALUE | SIG-001/005–011/017/018/025/026/029/034–040 | Avoid nested rounded-card noise | Cross-screen surface review |
| TOK-RADIUS-LARGE | `radius.large` | RADIUS | Floating/modal/standalone state | 12px | PROVISIONAL-VALUE | SIG-032/033/037/038 | Focus/viewport edge clear | Modal/dropdown/403 |
| TOK-RADIUS-FULL | `radius.full` | RADIUS | Avatar/circle/true chip only | 9999px | PROVISIONAL-VALUE | SIG-006/012/013/018/025/026/038 | Not generic button/metadata shape | Badge/avatar/icon review |

## BORDER

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-BORDER-DEFAULT | `border.default` | BORDER | Routine grouping/divider | 1px solid `color.border.default` | PROVISIONAL-VALUE | SIG-001/004/007–040 | Not sole essential control cue | Forms/tables/surfaces |
| TOK-BORDER-STRONG | `border.strong` | BORDER | Essential/selected/floating boundary | 1px solid `color.border.strong` | PROVISIONAL-VALUE | SIG-003/007–011/017/032/033/039 | Candidate boundary ≥3:1 on white | Controls/floating/forced-colors |
| TOK-BORDER-FOCUS | `border.focus` | BORDER | Focus separation/outline role | 2px solid `color.focus.ring` | PROVISIONAL-VALUE | All interactive SIG | Distinct from invalid; must not clip | Keyboard specimens |
| TOK-BORDER-ERROR | `border.error` | BORDER | Invalid/error boundary | 1px solid `color.feedback.danger.text` | PROVISIONAL-VALUE | SIG-007–011/032/036/040 | Always paired with text/icon association | Validation/modal/action errors |

## SHADOW

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-SHADOW-NONE | `shadow.none` | SHADOW | Static surface default | `none` | FINAL-CONCEPT | SIG-001/004/007–031/034/039/040 | Border/surface hierarchy remains | Card-soup comparison |
| TOK-SHADOW-FLOATING | `shadow.floating` | SHADOW | Dropdown/overflow/sticky separation | `0 8px 24px rgba(22,28,34,.12), 0 2px 6px rgba(22,28,34,.08)` | PROVISIONAL-VALUE | SIG-003/033/038 | Boundary also present; no shadow-only state | Dropdown/notification/toast captures |
| TOK-SHADOW-MODAL | `shadow.modal` | SHADOW | Dialog elevation | `0 24px 64px rgba(22,28,34,.20), 0 8px 20px rgba(22,28,34,.12)` | PROVISIONAL-VALUE | SIG-032 | Panel boundary/focus still visible | Ten modal pairs |

## MOTION

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-MOTION-INSTANT | `motion.instant-state` | MOTION | Immediate state truth | 0ms | FINAL-CONCEPT | SIG-005–016/019/023/034–040 | Same for reduced motion | State changes/validation |
| TOK-MOTION-FAST | `motion.fast-control` | MOTION | Hover/focus/control transition | 120ms ease-out | PROVISIONAL-VALUE | SIG-002/005/006/014–016/033 | Reduced motion 0ms | Pointer/focus comparison |
| TOK-MOTION-STANDARD | `motion.standard-drawer` | MOTION | Navigation spatial transition | 180ms cubic-bezier(.2,.8,.25,1) | PROVISIONAL-VALUE | SIG-002/003/033 | Reduced motion final-position 0ms | Sidebar/mobile nav runtime |
| TOK-MOTION-MODAL | `motion.modal` | MOTION | Dialog/backdrop entry/exit | 160ms ease-out | PROVISIONAL-VALUE | SIG-032/038 | Focus timing independent; reduced motion 0ms | Ten modal keyboard captures |
| TOK-MOTION-DISCLOSURE | `motion.disclosure` | MOTION | Expand/collapse orientation | 140ms ease-out | PROVISIONAL-VALUE | SIG-014/015/031/033/039 | ARIA/state immediate; reduced motion 0ms | Filters/metadata/audit details |

## FOCUS

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-FOCUS-RING-WIDTH | `focus.ring.width` | FOCUS | Visible focus thickness | 3px | PROVISIONAL-VALUE | All interactive SIG | Essential focus target ≥3:1 | Keyboard across all surface families |
| TOK-FOCUS-RING-OFFSET | `focus.ring.offset` | FOCUS | Separate focus from control edge | 2px | PROVISIONAL-VALUE | All interactive SIG | Must not be clipped | Overflow/table/modal specimens |
| TOK-FOCUS-SEPARATION | `focus.ring.separation` | FOCUS | Light edge on strong surface | 1px light separation + outer focus color | PROVISIONAL-VALUE | SIG-005/006/032/038 | Keeps focus visible on accent/danger | Primary/destructive/modal controls |
| TOK-FOCUS-FORCED-COLORS | `focus.forced-colors` | FOCUS | System focus fallback | System `Highlight` outline; no outline suppression | FINAL-CONCEPT | All interactive SIG | Honors user-selected colors | Forced-colors manual/browser check |

## LAYER / Z-INDEX

| Token ID | Semantic name | Category | Purpose | Candidate value | Status | Used by SIG | Accessibility note | Manual evidence needed |
|---|---|---|---|---|---|---|---|---|
| TOK-LAYER-BASE | `layer.base` | Z-INDEX | Normal document/work surface | 0 | PROVISIONAL-VALUE | SIG-001/004–031/034/039/040 | Natural DOM/focus order | Cross-screen source/runtime review |
| TOK-LAYER-STICKY | `layer.sticky` | Z-INDEX | Existing/approved sticky surface | 10 | PROVISIONAL-VALUE | SIG-001/022/023/029/031 | Must not cover focused content | Shell/detail scroll captures |
| TOK-LAYER-DROPDOWN | `layer.dropdown` | Z-INDEX | Anchored menu/panel | 20 | PROVISIONAL-VALUE | SIG-003/033 | Menu remains inside viewport/focus context | Notification/action menus |
| TOK-LAYER-OVERLAY | `layer.overlay` | Z-INDEX | Backdrop/screen overlay | 30 | PROVISIONAL-VALUE | SIG-003/032/035 | Background not interactable where modal behavior requires | Drawer/modal/loading runtime |
| TOK-LAYER-MODAL | `layer.modal` | Z-INDEX | Dialog panel above backdrop | 40 | PROVISIONAL-VALUE | SIG-032 | Focus contained/returned per acceptance | Ten modal captures |
| TOK-LAYER-TOAST-LOADING | `layer.toast-loading` | Z-INDEX | Existing flash/loading top layer | 50 | PROVISIONAL-VALUE | SIG-035/038/040 | Must not obscure essential error/action/focus | Global loading/SweetAlert/export files |

## Catalog Summary

| Family | Token count |
|---|---:|
| COLOR | 68 |
| TYPOGRAPHY | 15 |
| SPACE | 18 |
| SIZE | 26 |
| RADIUS | 5 |
| BORDER | 4 |
| SHADOW | 3 |
| MOTION | 5 |
| FOCUS | 4 |
| Z-INDEX / LAYER | 6 |
| **Total** | **154** |

| Token status | Count |
|---|---:|
| FINAL-CONCEPT | 52 |
| PROVISIONAL-VALUE | 84 |
| DEFERRED-VALUE | 18 |
| **Total** | **154** |

All 18 deferred tokens are layout/responsive values tied to OPEN-011–OPEN-020. No color, type, spacing, or interaction candidate is presented as a replacement for those missing geometry decisions.

## Usage Boundary

1. Token documentation does not make a SIG implementation-ready.
2. A token cannot authorize an action, decide visibility, or map a role to capability.
3. `DEFERRED-VALUE` tokens must not receive implementation values from Frappe reference, current CSS measurement alone, or aesthetic preference.
4. Exact candidates may change after manual baseline without changing the final semantic names.
5. No token is created for the 12 `IGNORE` decisions, dormant views, or no-active-trigger features.
6. Any future runtime naming must be reviewed separately; this document does not require a one-to-one CSS variable implementation.
