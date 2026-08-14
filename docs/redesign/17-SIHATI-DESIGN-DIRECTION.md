# SIHATI Design Direction

Dokumen ini menerjemahkan contract freeze dan audit referensi Frappe Helpdesk menjadi arah visual dan grammar konseptual SIHATI. Dokumen ini adalah **design specification only**: tidak menetapkan token final, tidak membuat component implementation, dan tidak memberi izin mengubah Blade, CSS, JavaScript, backend, route, test, fixture, workflow, atau baseline defect.

Sumber authoritative tetap `00-REDESIGN-GUARDRAILS.md` sampai `16-DESIGN-REFERENCE-DECISIONS.md` dan `docs/PRD-helpdesk-internal.md`. Bila arah visual bertabrakan dengan route, form, query, policy, safe projection, atau domain workflow, contract aplikasi menang.

## North Star

### Calm Operational Workbench

**SIHATI harus terasa seperti meja kerja operasional yang tenang: padat secukupnya untuk menangani pekerjaan harian, jelas mengenai keadaan dan tindakan berikutnya, serta tidak pernah membuat dekorasi lebih menonjol daripada tiket, orang, waktu, atau bukti kerja.**

“Calm” berarti visual noise rendah, bukan informasi sedikit. “Operational” berarti hierarchy mengikuti keputusan dan pekerjaan nyata, bukan vanity metrics. “Workbench” berarti halaman menyediakan context, data, dan tindakan dalam susunan stabil yang bisa dipelajari pengguna setiap hari.

| Axis | Direction SIHATI |
|---|---|
| Visual personality | Profesional, institusional, modern, restrained, trustworthy; tidak dingin atau impersonal. |
| Work density | Compact pada control dan record; lebih lapang di batas antar-group dan antar-task. |
| Information hierarchy | Identity dan current state terlebih dahulu, lalu next action, primary work, supporting context, dan history. |
| Whitespace | Menandai hubungan dan pergantian group; bukan ruang kosong dekoratif atau alasan menyembunyikan data penting. |
| Borders | Alat grouping utama untuk shell, rows, sections, fields, dan dialogs. |
| Shadows | Hanya untuk elevation sementara: modal, dropdown, floating/active surface, atau sticky surface yang perlu dipisahkan. |
| Semantic color | Terbatas pada status, priority, feedback, focus, dan destructive meaning; selalu ditemani label atau cue lain. |
| Action hierarchy | Satu primary action per task context bila memungkinkan; secondary/subtle/overflow mengikuti frequency dan consequence. |
| Role-sensitive presentation | Grammar visual boleh sama, tetapi destination, data, action, dan communication visibility selalu berasal dari server. |
| Responsive philosophy | **Reprioritize, not shrink**: contract sama, presentation dapat berbeda, dan capability yang authorized tetap reachable. |

Signature SIHATI adalah **jejak kerja yang terlihat**: ticket identity, status, penanggung jawab, SLA, audience komunikasi, dan event chronology membentuk satu alur visual yang konsisten. Signature ini diwujudkan melalui hierarchy, divider, label, dan timeline—bukan motif dekoratif, gradient, atau warna brand besar.

SIHATI tidak ingin menjadi:

- card-heavy dashboard atau “card soup”;
- aplikasi consumer yang penuh warna, hero besar, gamification, atau motion dekoratif;
- clone visual, source, token, asset, atau arsitektur Frappe;
- SPA, client-state workbench, atau replacement untuk Laravel routes dan server forms;
- agent console dengan bulk action, saved views, command palette, realtime presence, inline mutation, atau feature lain di luar baseline;
- UI yang meratakan enam role menjadi persona agent/customer;
- UI yang tampak minimal karena menyembunyikan context, errors, data sensitif, atau authorized actions.

## Visual Personality

| Quality | Meaning in use | Avoid |
|---|---|---|
| Professional | Susunan stabil, copy spesifik, state dan consequence mudah dipahami. | Ornament yang tidak membantu tugas. |
| Institutional | Kredibel untuk proses dan audit instansi, tanpa menyerupai dokumen birokratis yang berat. | Formalitas berlebihan, teks kapital panjang, atau layout seperti formulir cetak. |
| Modern | Responsive, focused, clear feedback, dan interaction affordance yang familiar. | Menambah feature client modern hanya demi kesan canggih. |
| Restrained | Neutral foundation, satu accent terbatas, semantic color disiplin. | Gradient-heavy, glass effect, glow, dan random accent. |
| Operational | Queue, next action, waiting reason, SLA, owner, dan chronology berada di atas analytics dekoratif. | Giant KPI hero atau chart tanpa keputusan yang didukung. |
| Trustworthy | Label canonical, audience communication, authorization boundary, dan state outcome eksplisit. | Action ambigu, status berbasis warna saja, atau data yang hanya disembunyikan di client. |
| Dense enough | Banyak record dapat dipindai cepat; control rapat namun readable. | Oversized cards/control yang mengurangi record visibility. |
| Readable for employees | Bahasa Indonesia sederhana, body text nyaman, metadata tetap terbaca. | Jargon teknis, abbreviation tanpa konteks, atau muted text yang terlalu lemah. |

Tone visual harus terasa tenang saat tidak ada masalah dan tegas ketika ada action, warning, error, atau destructive consequence. Boldness dipakai pada **clarity of operational state**, bukan pada dekorasi.

## Design Principles

| ID | Name | Statement | Rationale | Related FRP/DEC | Impact ke SIHATI | Anti-pattern | Contract guardrail |
|---|---|---|---|---|---|---|---|
| DSP-001 | Calm surfaces | Gunakan neutral surfaces, border, dan divider sebagai hierarchy utama; elevation hanya saat benar-benar diperlukan. | Data-heavy helpdesk perlu tenang agar exception dan next action menonjol. | FRP-006; DEC-006 | Shell, list, detail, form, dan admin terasa sebagai satu workbench. | Card soup, gradient, shadow pada setiap section. | Surface tidak boleh menyembunyikan visibility/policy branch atau mengubah DOM behavior. |
| DSP-002 | Compact controls, generous grouping | Rapatkan label-control-row yang saling terkait, lalu beri pemisahan lebih besar antar-group/task. | Density kerja dan readability dapat hidup bersama bila skala hubungan jelas. | FRP-007, FRP-009, FRP-017; DEC-007, DEC-009, DEC-017 | Lebih banyak record terlihat tanpa membuat form/timeline menjadi dinding informasi. | Semua gap sama; control besar dengan group boundary lemah. | Tidak mengubah field order, name, error order, paginator, atau action availability. |
| DSP-003 | Hierarchy before decoration | Identity, state, next action, dan primary content membentuk hierarchy sebelum icon, color, card, atau shadow. | Pengguna harus memahami halaman tanpa bergantung pada ornament. | FRP-005, FRP-020; DEC-005, DEC-020 | Page headers, dashboard, dan detail lebih predictable. | Hero besar, icon dekoratif, badge berlebihan. | Back/query semantics dan controller-provided order tetap authoritative. |
| DSP-004 | Semantic redundancy | Status, priority, warning, success, dan danger memakai text plus cue shape/icon/weight; color tidak pernah berdiri sendiri. | Mendukung scanning, accessibility, dan penggunaan lintas perangkat. | FRP-010; DEC-010 | 11 status dan 4 priority konsisten di list, detail, dashboard, dan filter. | Color-only dot atau unlabeled priority bar. | Canonical label/value dan domain meaning tidak berubah. |
| DSP-005 | Server-native interaction | UI menjelaskan request/response server dengan baik, bukan membuat client menjadi sumber workflow, filter, atau authorization. | SIHATI adalah Laravel server-rendered monolith dengan contract form/query yang dibekukan. | FRP-014, FRP-040; DEC-014, DEC-040 | Filters tetap GET, paginator tetap server, forms tetap submit/redirect/flash. | Client-only filter, optimistic workflow, load-more, inline mutation. | Route, method, CSRF, spoofing, input, old/error, redirect, flash, alias tetap exact. |
| DSP-006 | Role-first composition | Shared visual grammar tidak boleh meratakan destination, data, atau action antar-role. | Enam role, current Approver, Team Chair override, dan multi-role precedence tidak mempunyai analog sederhana. | FRP-004, FRP-032; DEC-004, DEC-032 | D/L/T variants tetap berbeda tetapi terlihat satu keluarga. | Menentukan capability dari role chip atau navigation item. | Policy, projection, controller flags, dan current assignment authoritative. |
| DSP-007 | Explicit communication audience | Public/requester communication, internal note, dan system/workflow event harus langsung dapat dibedakan sebelum pengguna membaca isi. | Salah audience adalah risiko privacy dan operasional utama. | FRP-024; DEC-024 | Composer, message surface, label, author metadata, dan attachments mempunyai identity yang jelas. | Satu audience toggle atau satu composer untuk dua endpoint. | Route, payload, attachment policy, visibility, dan writer/reader policy tetap terpisah. |
| DSP-008 | Responsive reprioritization | Mobile menyusun ulang priority dan disclosure; tidak sekadar mengecilkan desktop atau menghapus capability. | Ticket detail, forms, dan collections membutuhkan hierarchy khusus pada layar sempit. | FRP-003, FRP-021; DEC-003, DEC-021 | Tables menjadi record cards, rail menjadi sections, actions dapat masuk overflow. | Tiny desktop table, hidden primary action, horizontal page overflow. | Authorized actions tetap reachable; unauthorized actions tetap absent; same route/form contract. |
| DSP-009 | Contextual state truthfulness | Initial, loading, empty, filtered-empty, error, forbidden, dan not-found harus dijelaskan sebagai state yang berbeda. | Salah state dapat menyamarkan authorization, query, atau failure. | FRP-036; DEC-036 | Setiap collection dan workflow memberi context dan recovery yang benar. | “Tidak ada data” untuk 403/404/error; CTA fiktif. | HTTP meaning, existing recovery route, flash/error keys, dan policy tidak berubah. |
| DSP-010 | Accessibility with evidence | Visible focus, semantic labels, error association, contrast, reduced motion, dan focus management adalah acceptance behavior, bukan kosmetik. | Visual similarity tidak menjamin keyboard/screen-reader behavior. | FRP-039; DEC-039 | Foundation dan setiap wave mempunyai target a11y yang dapat diuji. | Outline removal, ambiguous icon-only action, motion-only feedback. | Interaction change seperti skip-link atau modal behavior memerlukan explicit decision dan acceptance test. |

## Typography Direction

Typography mengutamakan scanning, long-form readability, dan distinction antara kerja utama dengan metadata. Tidak ada font family, ukuran, tracking, atau token final yang diputuskan di fase ini.

| Level | Purpose | Relative emphasis | Weight direction | Line-height direction | Wrapping behavior | Mobile behavior |
|---|---|---|---|---|---|---|
| Display / exceptional | Hanya untuk exceptional empty/error identity atau summary yang benar-benar membutuhkan anchor; bukan routine dashboard hero. | Tertinggi tetapi jarang. | Medium–semibold, bukan ultra-bold. | Cukup lapang. | Wrap utuh; tidak dipotong untuk pesan penting. | Turun proporsional dan tidak mendominasi viewport. |
| Page title | Menamai current destination/task. | Anchor utama setiap page. | Semibold/moderate. | Compact tetapi tidak sesak. | Maksimal beberapa baris; title panjang tetap recoverable. | Wrap sebelum action; action pindah baris/overflow. |
| Section heading | Membagi work groups, timeline, form, atau rail. | Di bawah page title, di atas content. | Medium–semibold. | Compact. | Wrap natural. | Tetap terlihat sebagai heading, bukan label kecil. |
| Card/row title | Subject ticket, user, service, atau record identity. | Lebih kuat dari metadata. | Medium; unread/current dapat lebih kuat bila baseline mendukung. | Compact per-row. | Desktop boleh truncate dengan full recovery; mobile memberi lebih banyak wrap. | Menjadi baris pertama card dan boleh dua/lebih baris bila perlu. |
| Body | Description, message, guidance, dan normal content. | Default reading voice. | Regular. | Lebih lapang daripada control/metadata. | Wrap penuh; line length dijaga. | Tidak dikecilkan; paragraph tetap nyaman dibaca. |
| Form label | Nama field dan expectation. | Jelas tetapi tidak menyaingi heading. | Medium. | Compact. | Wrap label Indonesia panjang. | Selalu di atas/terkait control; tidak memakai fixed label column. |
| Metadata | Timestamp, secondary identity, count, owner, SLA detail. | Secondary tetapi tetap readable. | Regular/medium sesuai state. | Compact. | Truncate hanya jika detail tersedia secara accessible. | Diprioritaskan/ditumpuk; bukan disembunyikan massal. |
| Helper/error | Requirement, recovery, validation, dan constraint. | Helper tenang; error tegas. | Regular; error dapat medium. | Cukup lapang untuk multi-line. | Selalu wrap; tidak ellipsis. | Berada dekat field/action dan tidak keluar viewport. |
| Badge/action text | Label status, priority, button, tab, dan compact controls. | Compact, recognizable. | Medium. | Tight tetapi tetap legible. | Canonical status tidak boleh kehilangan makna; overflow hanya dengan equivalent accessible label. | Prefer text+icon; icon-only hanya untuk action sangat familiar dengan accessible name. |

Aturan umum:

- routine work screen tidak memakai oversized display type;
- metadata muted tidak boleh turun sampai sulit dibaca atau gagal contrast;
- Indonesian labels, errors, subjects, names, dan filenames diuji dengan string panjang;
- angka KPI selalu mempunyai label, period/context, dan denominator bila diperlukan;
- heading order mengikuti document structure, bukan sekadar ukuran visual.

## Color Philosophy

| Function | Direction | Required redundant cue |
|---|---|---|
| Neutral foundation | Page, text, border, divider, dan primary work surfaces; menyediakan mayoritas tampilan. | Hierarchy melalui value/weight/border/spacing. |
| Brand accent | Dipakai hemat untuk identity, selected navigation, primary focus area, atau primary action yang tepat. | Label/shape tetap menjelaskan state/action. |
| Semantic status | Membedakan state lifecycle tanpa mengubah canonical label. | Status text selalu terlihat; optional icon/dot/shape. |
| Semantic priority | Menjelaskan urgency, bukan SLA atau status. | Label plus level/shape/icon cue. |
| Success | Hasil berhasil atau completed meaning yang benar. | Pesan/label outcome. |
| Warning | Perlu perhatian, near-limit, waiting, atau condition yang belum gagal. | Explanation dan next step bila ada. |
| Danger | Error, destructive action, denied outcome, atau critical risk—bukan emphasis umum. | Consequence text atau error message. |
| Info | Context netral yang membantu keputusan, bukan dekorasi. | Label/copy singkat. |
| Focus | High-contrast focus-visible treatment yang konsisten di semua surfaces. | Bentuk/ring/outline, tidak hanya pergantian hue. |
| Disabled | Menunjukkan control tidak tersedia tanpa menyerupai hidden authorization. | Native disabled semantics dan explanation bila reason tidak jelas. |

Rules:

1. Warna tidak menjadi satu-satunya carrier of meaning.
2. Seluruh status memakai canonical text label; seluruh priority memakai label dan urgency cue.
3. Decorative color diminimalkan; chart series hanya dipakai jika data existing dan legend/contrast jelas.
4. Large brand-colored surfaces dihindari pada operational screens.
5. Destructive color hanya untuk destructive/final/denied meaning, bukan semua primary action.
6. “Menunggu” tidak otomatis sama dengan error; “Ditutup” tidak otomatis sama dengan celebration.
7. Status, priority, SLA, approval outcome, dan notification state tidak boleh memakai satu mapping warna yang ambigu.
8. Exact hue, contrast pair, dan theme token menunggu Fase 3.3 serta manual baseline.

## Surface and Elevation

| Surface | Use | Separation method | Do not use for |
|---|---|---|---|
| Page background | Canvas tenang di belakang shell/content. | Perbedaan value sangat ringan dari primary surface. | Status atau role meaning. |
| Primary surface | Main work area, form, table/list, conversation, detail. | Spacing, typography, outer boundary. | Membungkus setiap subsection sebagai card. |
| Secondary/muted surface | Internal note, helper group, selected/hover area, supporting context. | Muted fill plus optional border. | Menyembunyikan low-contrast data penting. |
| Border | Field, list boundary, section, dialog, card yang memang perlu enclosure. | Thin, consistent, semantic focus/error override. | Menebalkan semua hierarchy sekaligus. |
| Divider | Memisahkan rows/events/groups yang satu surface. | Hairline/quiet rule plus spacing. | Menggantikan heading atau group label. |
| Card | Unit yang benar-benar mandiri: KPI, approval item, compact mobile record, guidance. | Border-led; shadow optional dan jarang. | Setiap row, setiap metadata block, atau nested card. |
| Shadow | Elevation temporary/interactive. | Subtle depth. | Static page sections dan routine records. |
| Floating surface | Dropdown, overflow, tooltip, notification panel. | Border + shadow + clear anchor. | Primary navigation destination atau page content permanen. |
| Modal | Focused confirmation/input task yang sudah ada. | Backdrop, elevation, title/body/footer. | Menggabungkan standalone admin routes atau menciptakan workflow baru. |
| Sticky surface | Header, action/composer region yang perlu tetap reachable pada content panjang. | Border/background/stacking, bukan heavy shadow. | Menutupi content atau menggandakan action. |

Hierarchy order: **surface → border/divider → spacing → typography → shadow**. Shadow adalah langkah terakhir, bukan default container style.

## Spacing and Density

Filosofi utama adalah **compact control spacing, larger group spacing**. Exact spacing scale belum ditentukan.

| Area | Density direction | Grouping direction |
|---|---|---|
| Application shell | Compact navigation/control chrome; content tetap mempunyai breathing room. | Utility, primary destinations, admin group, dan profile/logout dipisahkan. |
| Navigation | Row konsisten dan mudah dipindai; icon-label dekat. | Gap lebih besar antar-navigation groups. |
| Page header | Vertikal ringkas; title/context/actions satu region. | Jarak ke content lebih besar daripada gap internal header. |
| Forms | Field anatomy rapat dan predictable. | Section heading/help membentuk group besar; form panjang tidak menjadi satu blok. |
| Lists | Rows padat dengan divider; subject/identity dominan. | Toolbar, result context, records, dan paginator terpisah jelas. |
| Tables | Cell compact tetapi tidak memotong data decision-critical. | Header/body/footer dan row action mempunyai rhythm stabil. |
| Dashboard | KPI compact; urgent/work list memperoleh ruang lebih besar. | Action-needed, summary, guidance, dan announcement tidak dicampur. |
| Ticket detail | Header compact; conversation readable; metadata dense. | Primary work, communication, workflow, dan history mempunyai boundary kuat. |
| Timeline/messages | Event metadata compact; message body lebih lapang. | Event type/audience menjadi group cue. |
| Admin collections | High information density, terutama UI-012/UI-021. | Search/list/record actions/modal forms dipisahkan tanpa nested cards. |
| Modal | Header/footer compact dan stabil; body mengikuti task. | Section gap lebih besar daripada field gap; body scroll bila content panjang. |

## Shape and Radius

- Radius bersifat modest dan konsisten; exact scale menunggu design-system phase.
- Primary work surfaces, tables, large forms, dan ticket panes cenderung rectangular dengan radius terbatas.
- Pills dipakai hanya untuk compact categorical/semantic objects seperti status, priority, active filter, atau count yang benar-benar berfungsi sebagai badge.
- Button tidak otomatis berbentuk pill; shape mengikuti control family dan density kerja.
- Tag/pill tidak dipakai untuk paragraph, metadata panjang, navigation group, atau semua table cells.
- Circular shape dibatasi untuk avatar, status dot, atau icon control yang memang square/circular dan mempunyai accessible name.
- Nested rounded cards dihindari. Enclosure baru harus mempunyai alasan hierarchy atau interaction.

## Iconography

| Icon type | Direction |
|---|---|
| Functional | Membantu mengenali aksi/destination yang sudah ada; label tetap diprioritaskan untuk aksi yang tidak universal. |
| Decorative | Sangat terbatas dan `aria-hidden`; tidak boleh menjadi alasan menambah visual noise. |
| Status | Supplementary cue di samping canonical text; tidak mengganti label. |
| Priority | Menunjukkan level urgency secara redundant melalui bentuk/level plus text. |
| Navigation | Konsisten per destination, tetap disertai label saat expanded dan accessible name/tooltip saat collapsed. |
| Action | Menguatkan verb atau consequence; destructive icon hanya untuk destructive action. |

Setiap icon-only action wajib mempunyai accessible name, visible focus, adequate touch target, dan tooltip hanya sebagai bantuan—bukan satu-satunya nama. Icon ambigu seperti tiga titik hanya berarti “aksi lainnya”, bukan primary action. Tidak ada icon package baru yang dipilih di fase ini dan tidak ada asset Frappe yang disalin.

## Motion

Motion hanya dipakai untuk menjelaskan perubahan spatial atau state:

- drawer membuka/menutup dan menjaga orientation;
- modal muncul/hilang tanpa mengganggu focus transfer;
- disclosure menunjukkan expand/collapse;
- loading transition memberi feedback tanpa layout jump yang tidak perlu;
- hover/focus transition memperjelas interactive state.

Motion harus subtle, singkat, functional, dan menghormati reduced-motion. Tidak ada parallax, bouncing, autoplay, stagger dekoratif, atau animation yang menunda kerja. Final duration/easing belum ditentukan.

## Application Shell

### Desktop shell

| Region | Conceptual specification | Contract boundary |
|---|---|---|
| Sidebar | Stable navigation rail dengan expanded/collapsed concept; grouping berdasarkan existing server branch. | DEC-002/004; visibility ACT-01 sampai ACT-11 tetap exact; collapse tidak mengubah destination. |
| Navigation groups | Primary work, reporting/monitoring, administration, dan utility dipisahkan hanya bila item existing tersedia untuk actor. | Jangan mengisi group kosong dengan feature baru atau menyimpulkan capability dari role name. |
| Active state | Surface/border/weight/icon+label cue; bukan hue saja. | Active route/query logic existing tetap. |
| Utility area | Notification, password/profile context, dan logout berada pada region stabil. | Notification ownership dan logout POST/session contract frozen. |
| Page header | Compact contextual header di atas content; title dan actions predictable. | DEC-005; back/query/action visibility tetap caller/server-owned. |
| Content region | Flexible work area yang menerima page-specific max-width/full-width behavior. | Tidak mengubah Blade view selection atau server-provided data. |

Expanded state memprioritaskan recognition melalui text. Collapsed state hanya menghemat chrome; ia tidak boleh membuat destination ambiguous atau mengubah multi-role visibility. Exact width/default state belum ditentukan.

### Mobile shell

| Region | Conceptual specification | Contract boundary |
|---|---|---|
| Compact header | Menunjukkan current page identity dan navigation trigger. | Tidak mengganti named route atau page heading. |
| Navigation trigger | Clearly labeled/accessible, visible focus, adequate touch target. | Existing open/close/focus behavior harus diuji sebelum perubahan. |
| Drawer/details strategy | Navigation menjadi overlay/drawer atau treatment setara; content tidak menyusut di belakang rail. | DEC-003; authorized links sama, mobile presentation berbeda. |
| Current identity | Title/context tetap terlihat setelah drawer ditutup. | Tidak memaksa satu global persona pada multi-role actor. |
| Notifications/profile | Tetap reachable tanpa mengambil alih primary task. | Existing dropdown/page/read routes dan logout POST tetap. |

Shell tidak menambahkan capability. Khusus ACT-08, ACT-09, ACT-10, dan ACT-11, branch campuran existing tetap dipertahankan sebagaimana VIS-006 sampai VIS-008.

## Page Header

Grammar urutan:

1. context atau breadcrumb bila benar-benar membantu;
2. page title;
3. optional description;
4. primary action;
5. secondary actions;
6. overflow untuk lower-frequency actions;
7. filter context pada list/report bila relevant.

| Variant | Emphasis | Action treatment | Contract notes |
|---|---|---|---|
| Standard | Title + optional concise description. | Satu primary, secondary bila ada. | UI-001/003/009/023 dan common pages. |
| List | Variant/context + result/filter context. | Create/queue/filter actions sesuai screen. | Query, paginator, row scope tetap. |
| Form | Task identity + optional service/record context. | Submit biasanya berada dekat akhir form; header hanya navigation/context bila baseline demikian. | Form target/method tidak dipindahkan secara semantik. |
| Detail | Record identity + state + back context. | Frequent authorized actions visible; lower-frequency ke overflow. | UI-008 controller booleans, modal opener, `from=all` tetap. |
| Admin | Resource identity + create/manage action. | Resource-specific, server authorized. | Standalone routes dan modal contexts tidak diratakan. |
| Report | Report identity + period/filter context. | Generate/export hanya sesuai existing state/policy. | Month/date aliases, filename/download behavior frozen. |

Page header tidak mengubah route/back/query semantics. Truncation hanya digunakan jika full title/context tetap dapat dibaca atau dipulihkan secara accessible.

## Actions and Buttons

| Variant | Purpose/frequency | Placement | Loading/disabled | Mobile behavior |
|---|---|---|---|---|
| Primary | Menjalankan tindakan utama task saat ini; biasanya satu per context. | Dekat form/task, footer modal, atau header bila action page-level. | Label/action tetap spesifik; progress mencegah duplicate submit; disabled reason jelas bila perlu. | Full/strong placement atau sticky hanya bila tidak menutup content. |
| Secondary | Alternative non-destructive atau cancel/back. | Di samping primary dengan emphasis lebih rendah. | Loading hanya pada action yang berjalan. | Stack/wrap dengan urutan task tetap. |
| Subtle/Ghost | Utility, disclosure, low-frequency non-destructive. | Toolbar, row, section, overflow trigger. | Hover bukan satu-satunya affordance. | Bisa icon+label atau masuk overflow; tetap reachable. |
| Destructive | Aksi destructive/final yang sudah ada. | Dipisahkan dari primary routine; confirmation menyebut consequence. | Semantic danger + explicit loading/disabled. | Label tetap eksplisit, tidak menjadi icon ambigu. |
| Icon | Common compact utility atau menu trigger. | Toolbar/header/row bila ruang terbatas. | Accessible name wajib; state visible. | Adequate touch target; tooltip tidak mengganti accessible name. |
| Link action | Navigation atau download, bukan mutation yang disamarkan. | Inline/contextual. | Loading hanya bila existing behavior memerlukannya. | Wrap dan target tetap jelas. |

Button copy memakai verb dan outcome yang dikenali pengguna. Redesign tidak mengubah form `action`, method, CSRF, spoofing, submit name/value, confirmation, redirect, atau flash.

## Forms

### Field anatomy

Urutan konseptual: **Label → required marker → control → helper/optional metadata → error**. Read-only value menggunakan label/value treatment atau native read-only/disabled semantics sesuai contract; bukan control palsu yang tampak editable.

- field spacing compact; group/section spacing lebih besar;
- mobile selalu satu column dengan reading/focus order yang sama;
- desktop multi-column hanya untuk field yang saling terkait, pendek, dan tidak mengorbankan error/helper readability;
- textarea memberi ruang untuk content panjang dan tidak dipakai untuk single-value data;
- select mempertahankan options/value dari server dan native/custom semantics existing;
- checkbox/radio menyatukan control, label, helper, dan error dalam hit target yang jelas;
- file input menyebut purpose/policy yang sudah tersedia, memperlihatkan filename, dan tidak merepopulasi file;
- dynamic fields memakai renderer/server definitions existing; tidak ada client script baru;
- nested validation mengikuti exact error keys dan mengembalikan user ke group/field yang benar;
- old input dipertahankan persis; password/file exceptions tetap;
- long form seperti SVC-07 dikelompokkan secara semantic tanpa mengubah field order/name/version.

Error state tidak hanya mengganti border color: field memiliki error text, association, dan summary bila form panjang membutuhkannya pada implementation phase. Penambahan summary tidak boleh mengubah validation bag atau focus behavior tanpa acceptance test.

## Status and Priority

### Eleven canonical statuses

| Status | Operational family | Visual grammar, without final color |
|---|---|---|
| Baru | Intake/unclaimed | Distinct intake cue + canonical label; owner tidak boleh diimplikasikan. |
| Diproses | Triage | Active-process cue + label; berbeda dari Dikerjakan. |
| Dikerjakan | Active work | Active-work cue + label; assignee ditampilkan terpisah. |
| Menunggu Persetujuan | Paused/decision | Waiting cue + approval context; SLA pause tetap data server. |
| Menunggu Pemohon | Paused/response | Waiting cue + requester audience context. |
| Menunggu Pihak Ketiga | Paused/external | Waiting cue + external dependency context. |
| Menunggu Konfirmasi | Paused/confirmation | Waiting cue + requester confirmation context. |
| Ditutup | Completed/final | Completed cue + label; closure reason/history tetap terpisah. |
| Ditolak | Agent-rejected/final | Negative final cue + exact label; reason tetap visible sesuai policy. |
| Tidak Disetujui | Approval-denied/final | Decision-denied cue + exact label; tidak disamakan dengan Ditolak. |
| Dibatalkan | Requester-cancelled/final | Cancelled cue + exact label; tidak disamakan dengan error. |

### Four canonical priorities

| Priority | Urgency grammar |
|---|---|
| Kritis | Maximum level/shape cue + canonical label; strongest urgency, bukan destructive action. |
| Tinggi | Elevated level cue + canonical label. |
| Sedang | Standard level cue + canonical label. |
| Rendah | Low level cue + canonical label tanpa membuatnya tidak terbaca. |

Usage rules:

- list: compact cue + full label, subject tetap primary;
- detail: full status/priority label dekat identity, assignee/SLA terpisah;
- dashboard: summary boleh mengelompokkan tetapi canonical label dan scope/period jelas;
- filters: text label/value exact, tidak memakai swatch tanpa text;
- mobile: label tidak hilang; bila ruang sempit, metadata lain yang direprioritaskan lebih dahulu;
- status dan priority tidak saling memakai icon/cue yang membingungkan.

## Lists and Tables

### Desktop table

- gunakan table ketika perbandingan antar-record/column membantu keputusan;
- row compact dengan divider, bukan card per row;
- primary column berisi identifier/subject/record identity;
- secondary columns berisi requester, assignee, status, priority, SLA/timestamp sesuai screen;
- status dan priority selalu text + cue;
- row action berada pada akhir/logical end atau explicit action column, server-gated;
- hover membantu pointer; focus-visible dan link/button semantics wajib;
- empty/filtered-empty berada di table context;
- numbered server paginator dan query preservation tidak berubah.

### Mobile record card

- card adalah alternate presentation dari record yang sama, bukan data/query berbeda;
- urutan: identity/subject → status/priority → most relevant metadata → timestamp → authorized action;
- metadata lower priority dapat ditumpuk atau disclosure jika tetap accessible;
- long subject/name/filename dapat wrap; truncation harus mempunyai recovery;
- action menu tidak boleh menyembunyikan satu-satunya authorized primary action;
- no horizontal page scroll sebagai default.

FRP-014/DEC-014 dan FRP-017/DEC-017 hanya menjadi reference hierarchy. Server sort, scope, `per_page`, `page`, tabs, and paginator tetap baseline.

### Three ticket-list variants

| Variant | Direction | Boundary |
|---|---|---|
| L-01 Requester | Own-ticket context, requester tabs/filters, subject/status/next requester action, server paginator. | Own-only scope, requester filter normalization, and policy-provided quick actions remain. |
| L-02 Operational/scoped | Compact operational records with search/page size and queue context when allowed. | Union scope is server-owned; it is not UI-006 “all tickets” and Tier 2 stays assigned-only. |
| L-03 Ketua Tim | Safe team metadata, status/SLA, read-only navigation, fixed paginator. | `TeamChairTicketView`, 15/page, no new filters/actions/internal/private data. |

Coverage: **L-01 through L-03 = 3/3**.

## Filters

Presentation hierarchy:

1. page/list context;
2. search jika screen memang memiliki `q`;
3. primary frequent filters/tabs;
4. secondary/advanced existing filters;
5. active-filter indication;
6. reset/clear existing query;
7. per-page control bila contract menyediakan;
8. result/pagination context.

Rules:

- form filter tetap GET dan exact query names existing;
- invalid/reversed/alias normalization tetap server-side;
- active filters harus terlihat setelah submit dan pada page berikutnya;
- reset hanya menghapus parameter yang memang menjadi filter screen tersebut;
- tabs mempertahankan allowed values dan role visibility;
- no saved view, client multi-sort, configurable columns, click-cell filtering, debounced API filter, atau local result state;
- mobile boleh collapse secondary filters, tetapi active-filter count/context dan reset tetap discoverable;
- Team Chair fixed list tidak diberi filter baru; no-trigger screen tidak memperoleh controls baru.

## Dashboard

Dashboard memakai grammar bersama tanpa meratakan content. Building blocks yang diperbolehkan hanya ketika data/action existing tersedia:

1. page/role context;
2. period controls;
3. urgent atau action-needed section;
4. KPI/summary dengan scope dan period jelas;
5. work list;
6. guidance/reporting guide;
7. announcements;
8. contextual initial/empty/error state.

Actionable work mendahului analytics. KPI bukan hero dekoratif dan tidak boleh menyiratkan scope yang lebih luas daripada data server.

| Variant | Primary emphasis | Secondary emphasis | Must remain absent/restricted |
|---|---|---|---|
| D-01 Pure Super Admin | Administrative orientation, allowed admin/report destinations, account/system context. | Announcements dan generic role context existing. | Tidak ada queue/agent workspace hanya karena role admin. |
| D-02 Pemohon-only | Buat tiket, action-needed own tickets, own progress/status. | Guidance, announcements, recent own context. | Tidak ada data tiket orang lain/internal/agent action. |
| D-03 Agen Tier 1 | Queue urgency, SLA risk, assigned/waiting operational work. | Summary/report/announcement destinations yang existing. | Tidak otomatis memperoleh admin resource access. |
| D-04 Agen Tier 2 | Own assigned workload, SLA/next work, waiting/completed context. | Personal operational summary. | Tidak ada unassigned/global queue atau T1 controls. |
| D-05 Current Approver | **Perlu Tindakan Saya** dan oldest pending approvals paling atas. | Summary/report yang memang authorized. | Role Approver tanpa active assignment bukan variant ini; Team Chair override tetap denied. |
| D-06 Ketua Tim Kerja | Safe team monitoring: status, SLA, assignee, latest public reply/solution. | Team/member context. | Tidak ada overall/full-model data, internal notes, attachments, approval/report action. |

Multi-role actors tidak dipaksa masuk satu persona visual. ACT-08, ACT-09, ACT-10, dan ACT-11 mempertahankan branch composition existing.

Coverage: **D-01 through D-06 = 6/6**.

## Ticket Detail

Ticket detail adalah pusat workbench dan area redesign paling sensitif. Desktop dan mobile boleh memiliki hierarchy berbeda, tetapi tidak boleh memakai satu universal data model atau client-side capability map.

### Desktop direction

1. **Ticket identity/status** — nomor, subject, canonical status/priority, requester context, SLA sesuai visibility.
2. **Action cluster** — hanya controller/policy booleans yang true; frequent action visible, lower-frequency di overflow.
3. **Primary work area** — conversation/timeline sebagai pusat chronology.
4. **Composer** — public/requester dan internal tetap berbeda.
5. **Metadata/workflow rail** — ownership, service, SLA, approval/waiting/service-specific context dalam sections.
6. **Attachments/history** — authorized links dan lower-frequency evidence.

Split workspace adalah direction, bukan final dimension. Independent scroll, sticky region, atau rail width harus menunggu manual baseline.

### Mobile direction

1. ticket identity dan canonical status;
2. critical authorized actions;
3. primary information/next step;
4. conversation/timeline;
5. relevant composer;
6. metadata/workflow disclosures;
7. lower-frequency history.

Mobile dapat memakai dedicated hierarchy, stacked sections, disclosures, atau overflow actions. Semua authorized actions tetap reachable, long content tetap recoverable, dan modal tidak keluar viewport.

### Six detail variants

| Variant | Visual emphasis | Actions and data | Hard boundary |
|---|---|---|---|
| T-01 Super Admin | Read-only full-context inspection; identity, chronology, metadata, attachments/history sesuai policy. | Pure Super Admin tidak memperoleh operational action cluster. | Full visibility tidak sama dengan write capability. |
| T-02 Pemohon | Current state/next requester action, public conversation, requester-visible data/attachments, solution. | Cancel, reply, confirm, not-satisfied, reopen hanya pada state/policy existing. | Internal comments/fields/attachments/approval context tidak dirender. |
| T-03 Agen Tier 1 | Queue/work context, state actions, conversation, internal/public work, SLA/service controls. | Setiap action tetap separate policy/form/modal. | Missing openers, restricted triage choices, dan no-trigger endpoints tetap baseline. |
| T-04 Agen Tier 2 assigned | Assignment identity, work/return/wait/approval/completion controls, conversation, service evidence. | Hanya saat assigned dan state mengizinkan. | Tidak ada T1 queue/triage/priority/reject; access hilang saat assignment hilang. |
| T-05 Current pending Approver | Pending-decision context dan consequence, approve/reject, relevant chronology/context. | Active assignment dan pending request untuk actor wajib. | Approval validation auto-open/old gap tetap frozen; role chip saja tidak cukup. |
| T-06 Ketua Tim Kerja | Safe read-only identity/status/SLA, public safe timeline, solution. | Tidak ada action/composer/download. | Separate safe view model; full Ticket, internal/private/sensitive data dilarang masuk render tree. |

DEC-020 sampai DEC-027 menjadi reference utama; DEC-028 tetap `IGNORE`. Action cluster tidak menjadi generic status dropdown dan timeline tidak menambah event types yang tidak ada.

Coverage: **T-01 through T-06 = 6/6**.

## Communication

Communication surfaces harus menjawab tiga pertanyaan sebelum content dibaca: **siapa menulis, siapa dapat melihat, dan peristiwa apa ini**.

| Type | Surface | Label/audience cue | Author metadata | Attachments | Composer identity |
|---|---|---|---|---|---|
| PUBLIC / REQUESTER COMMUNICATION | Primary/neutral conversation surface, border-led. | Explicit “Balasan ke Pemohon” atau requester/public audience label. | Author, role/context yang existing, timestamp. | Hanya requester/public-compatible policies dan authorized download route. | Separate public/requester form dan submit action. |
| INTERNAL NOTE | Muted but clearly distinct work surface. | Persistent “Catatan Internal” + private audience explanation. | Author dan timestamp; no requester exposure. | Internal policy set only. | Separate internal form/modal; tidak menjadi toggle public/internal. |
| SYSTEM EVENT | Compact event row pada operational spine. | Event label + resulting state/context. | System/actor and timestamp as provided. | Tidak mengarang attachment/action. | No composer. |
| APPROVAL EVENT | Decision-focused event dengan approver/outcome context. | Request/approved/not-approved label dan consequence. | Requester/approver/timestamp per projection. | Hanya bila existing authorized data menyediakan. | Decision forms tetap terpisah dari message composer. |
| WORKFLOW EVENT | Compact chronology untuk assignment, status, waiting, completion, reopen, SVC controls. | Verb/result + before/after context yang safe. | Actor/timestamp. | Evidence links hanya sesuai policy. | No generic workflow mutation. |

Public dan internal surfaces boleh berbagi low-level typography, attachment chip, dan editor anatomy. Mereka tidak boleh berbagi one-form audience toggle, route, hidden context, policy, or attachment visibility.

## Modal and Dialog

### Anatomy

1. title;
2. optional concise context/description;
3. body;
4. fields or consequence;
5. inline error;
6. footer;
7. secondary/cancel;
8. primary atau destructive action;
9. explicit close path;
10. local loading state.

| Variant | Intended use | Direction |
|---|---|---|
| Narrow | Confirmation atau satu short field/action. | Focused copy, simple footer, mobile actions may stack. |
| Standard | Routine create/edit/action form. | Stable header/body/footer, body scroll only when needed. |
| Wide | Existing high-density editor/detail form. | Viewport gutters, internal scroll, preserved context; bukan default. |
| Mobile full/near-full | Existing modal task pada narrow viewport. | Header/close/action tetap reachable; content tidak keluar viewport. |

Visual anatomy boleh distandardisasi. Behavior berikut tetap frozen: modal ID, opener, `data-*`, action, method, CSRF/spoofing, `_action_modal`, auto-open, old/error routing, reset/clear semantics, focus behavior baseline, and redirect/flash.

Fase ini **tidak memperbaiki** VIS-001, VIS-002, VIS-003, VIS-004, VIS-005, VIS-015, atau VIS-016. Empat missing opener tetap missing; approval reject auto-open/old gap tetap; ticket modal retained-state behavior tetap; request-information tidak memperoleh attachment; triage tidak memperoleh option baru; SVC-03 reachability/readiness tidak berubah.

## Contextual States

| State family | Message intent | Recovery rule |
|---|---|---|
| Initial | Menjelaskan apa yang belum diminta/dipilih, misalnya report belum digenerate atau service belum dipilih. | Tawarkan existing next action only. |
| Loading | Menjelaskan request/navigation/export/action sedang berlangsung dan mencegah duplicate action. | Pertahankan page context; jangan membuat fake client data state. |
| Empty | Collection valid tetapi belum mempunyai record. | Existing create/action link hanya bila actor authorized. |
| Filtered empty | Query valid tetapi tidak menemukan result. | Reset existing filters/query; jangan menyatakan collection global kosong. |
| Validation error | Menjelaskan field/action yang harus diperbaiki sambil mempertahankan old input/context. | Kembali ke exact field/modal/form. |
| Action failure | Menjelaskan operation gagal, termasuk concurrency/domain failure, tanpa berpura-pura sukses. | Retry hanya bila safe/existing; keep current state authoritative. |
| Forbidden | Akses ditolak. | Jangan membocorkan object/data; gunakan existing 403 destination. |
| Not found | Resource/path tidak ditemukan atau disamarkan sesuai server response. | Jangan diganti generic empty. |
| Success feedback | Menyebut apa yang berhasil dan next state/destination jika relevan. | Gunakan existing flash/redirect semantics. |

Recovery action tidak diciptakan hanya agar empty/error panel tampak lengkap. 403 dan 404 tidak boleh disamarkan sebagai empty data.

## Admin Collections

Shared design language:

- resource-aware page header;
- existing search/filter controls;
- desktop comparison table atau structured list;
- mobile record card dengan equivalent essential metadata/actions;
- record state/relationship metadata;
- authorized row actions;
- existing modal atau standalone form anatomy;
- contextual empty/filtered-empty/error/loading.

| Screen | Shared direction | Resource-specific contract that must remain |
|---|---|---|
| UI-012 Users | High-density identity, role/team/skill/status metadata; create/details/edit/reset actions clearly separated. | `q`, `per_page`, per-user modal/error targeting, self restrictions, array payloads, standalone pages. |
| UI-016 Locations | Building hierarchy with nested floors and status context. | Alias/query, building/floor modal IDs, `_location_form`, dependency errors; room UI stays absent. |
| UI-017 Teams | Team identity, current chair/member summary, edit/status context. | Bespoke create/edit markers; actual status/delete trigger availability must not be invented. |
| UI-018 Skills | Searchable skill collection with usage counts and state. | `q`, `per_page`, modal targeting, mapping/deactivation constraints. |
| UI-021 Services | High-density catalog with service identity, SLA/skills, ordered versioned fields, preview/editor contexts. | Nested payload, field versioning, `service`/`service_tab`, modal IDs/error routing; field-status/dormant UI stays absent. |
| UI-022 Announcements | Create plus scheduled/current record disclosures and status actions. | Super Admin/T1 policy, `_announcement_id`, ordering, no invented search/pagination. |

UI-012 and UI-021 require special density and focus-order validation. Presentation may avoid preserving their measured DOM byte weight, but every form/modal/error/query contract remains.

## Responsive

Core principle: **REPRIORITIZE, NOT SHRINK**.

| Responsive classification | Meaning | Examples |
|---|---|---|
| SAME CONTENT / REFLOWED | Same information, new column/stack arrangement. | Forms, KPI groups, metadata lists. |
| SAME CONTRACT / DIFFERENT PRESENTATION | Same server data/forms/actions represented differently. | Desktop table → mobile record cards; desktop ticket rail → mobile sections. |
| COLLAPSED | Secondary content placed in disclosure without losing availability or context. | Advanced filters, metadata/history sections. |
| OVERFLOW ACTIONS | Lower-frequency authorized actions moved to labeled overflow. | Detail/admin row actions; primary/critical action stays evident. |
| DEDICATED MOBILE HIERARCHY | Page order changes to put identity/state/next action first. | UI-008 T-01 through T-06. |

Rules:

- authorized action tidak boleh hilang dan unauthorized action tidak boleh muncul;
- long text, labels, filenames, errors, and identifiers remain recoverable/readable;
- touch targets adequate and separated from destructive neighbors;
- modal/drawer/popover stays within viewport and supports internal scroll;
- visual/focus order remains logical after reflow;
- no horizontal page overflow by default; intentional contained table overflow must preserve key identity/actions;
- exact breakpoint menunggu manual 1440×900 dan 390×844 evidence.

## Accessibility

### Presentation-safe improvement direction

- clear heading hierarchy;
- text labels for status/priority;
- sufficient contrast target for text, border, state, and focus;
- visible focus styling where existing focus behavior remains unchanged;
- readable wrapping and non-color cues;
- accessible names for icon-only actions using existing semantics;
- helper/error placement that does not alter validation or focus lifecycle;
- reduced-motion visual alternative.

### Interaction change requiring acceptance test

- adding or changing focus movement/order;
- custom tabs, dropdowns, disclosures, or menus;
- modal focus trap, initial focus, Escape/backdrop close, and focus return;
- mobile navigation focus containment/return;
- keyboard submit shortcuts or composer collapse/reset behavior;
- live regions/error summaries that change announcement order;
- adopting a skip link and target.

VIS-011 through VIS-014 remain baseline references. Skip-link adoption is **OPEN-022**, not an automatic addition in this phase. Any change must distinguish improvement from a baseline defect repair and must be tested at keyboard, zoom, contrast, reduced-motion, and narrow viewport.

## Role Consistency Rules

### May be shared across roles

- shell visual grammar, provided existing navigation branch remains;
- typography hierarchy;
- button and icon-button appearance;
- status/priority badge grammar;
- table/mobile-card anatomy;
- modal visual anatomy;
- contextual state panels;
- form-field visual primitives;
- timeline visual vocabulary when input events are already sanitized.

### Must not be forced shared

- navigation destinations;
- dashboard sections/data feeds;
- ticket list query/projection;
- ticket detail data model;
- action cluster;
- communication visibility/composer availability;
- attachments/downloads;
- internal sections/history;
- approval and report capability;
- admin resource access.

| Role | Non-negotiable visual rule |
|---|---|
| Super Admin | Administrative identity cannot imply operational ticket action. |
| Pemohon | Own/requester context dominates; internal/private surfaces absent. |
| Agen Tier 1 | Queue/triage/work actions remain state- and assignment-dependent. |
| Agen Tier 2 | Assigned-only scope remains visible in page context; no T1 affordance. |
| Approver | Pending/current-Approver context is explicit; stale role does not look actionable. |
| Ketua Tim Kerja | Read-only monitoring and safe projection are unmistakable; no hidden full-model residue. |

Multi-role presentation composes actual branches; it does not calculate “most powerful role”.

## Prohibited Patterns

| ID | Prohibited pattern | Why prohibited |
|---|---|---|
| ANT-001 | Card soup | Mengaburkan group hierarchy dan menurunkan data density. |
| ANT-002 | Giant dashboard hero | Mengambil ruang dari urgent work dan memberi impression consumer/marketing. |
| ANT-003 | Color-only status/priority | Menghilangkan meaning bagi sebagian pengguna/contexts. |
| ANT-004 | Ambiguous icon-only action | Meningkatkan salah tindakan dan mengurangi accessibility. |
| ANT-005 | Client-only filters/sort/pagination | Melanggar server query/paginator contract. |
| ANT-006 | Hidden unauthorized data | CSS/JS hiding bukan authorization atau safe projection. |
| ANT-007 | Team Chair full-model rendering | Melanggar safe allow-list dan private-data boundary. |
| ANT-008 | Capability inferred from role label | Mengabaikan current assignment, status, record scope, dan multi-role precedence. |
| ANT-009 | Inline ticket mutation | Melompati form, validation, audit, workflow, dan modal contracts. |
| ANT-010 | Bulk workflow/action | Menambah mutation/API/policy behavior di luar baseline. |
| ANT-011 | Saved views/persisted columns/multi-sort | Menambah client/persistence feature baru. |
| ANT-012 | Command palette | Menambah global command/search/shortcut behavior. |
| ANT-013 | Realtime presence/typing | Memerlukan backend/socket/state dan menambah exposure. |
| ANT-014 | Settings as SPA modal | Meratakan named admin routes, policies, history, dan error contexts. |
| ANT-015 | Dark mode | Menambah theme state/token/QA scope yang tidak disetujui. |
| ANT-016 | Frappe clone styling/source/token/assets | Bertentangan dengan identity, stack, dan licensing boundary. |
| ANT-017 | Decorative gradient-heavy operational UI | Decoration bersaing dengan status dan action. |
| ANT-018 | Universal ticket data model | Meratakan T-01 sampai T-06 dan dapat membocorkan data. |
| ANT-019 | One public/internal audience toggle | Menggabungkan form/policy/attachment semantics yang harus terpisah. |
| ANT-020 | Dormant/no-trigger feature activation | Mengubah product behavior melalui redesign. |

## Design Direction by Wave

| Wave | Design goals | Pattern families | Highest contract risks | Manual baseline prerequisite | Do-not-cross boundary |
|---|---|---|---|---|---|
| W1 Foundation | Relative typography, surfaces, semantic color roles, spacing rhythm, shape, focus, badges, states. | DSP-001–004/009/010; SIG-005–013, SIG-034–038. | Form errors, enum labels, flash/loading semantics. | Core baseline plus status/priority matrix and validation-old pair. | No final implementation before hold lifted; no exact tokens in FASE 3.2. |
| W2 Shell/Common | Stable desktop/mobile shell, page header, notification/common pages. | SIG-001–006, SIG-031/033/037/038. | Role navigation, logout/read POST, focus/menu/sidebar state, query/back. | Six dashboards/shell pairs, ACT-08–11, mobile drawer/focus captures. | No role capability regrouping or selector migration without parity tests. |
| W3 Admin Collections | Consistent list/card/filter/modal language for straightforward resources. | SIG-014–019, SIG-030–039. | Modal markers, methods, dependency errors, audit sensitivity. | Relevant admin list/filter/modal/empty/error captures. | No room/attachment-policy/operational-policy/dormant endpoint activation. |
| W4 Role/Data-heavy | Six dashboards, report/export, users, services/form configuration. | SIG-020/021, SIG-039/040 plus form/list/modal families. | D variants, data scopes, binary export, nested/versioned fields, UI-012/UI-021 density. | All D variants, report states, users/services desktop/mobile and modal evidence. | No dashboard personalization, settings SPA, client data calculation, or field-history mutation. |
| W5 Ticket Entry/List/Approval | Three list variants, queue/all, dynamic create, approvals. | SIG-007–019, SIG-030/034–038. | Query normalization, paginator, dynamic/nested inputs, attachments, current Approver. | L variants, create services/errors/files, queue tabs, approval profiles. | No saved views, bulk actions, load-more, claim/handle trigger, or new triage/create behavior. |
| W6 Critical Ticket Detail/Workflow | Six detail variants, communication, timeline, metadata, attachments, 14 modal families. | SIG-022–033 plus state/feedback patterns. | Policy flags, safe projection, public/internal, modal/error routing, SLA/approval/SVC/waiting. | T-01–T-06, all reachable modal pairs/errors, workflow/SVC/attachment/long-content evidence. | No baseline defect repair, universal model, new opener, inline mutation, or client workflow. |

### Active-screen direction coverage

| UI | Direction destination | Primary SIG | Wave |
|---|---|---|---|
| UI-001 Login | Calm guest form, explicit validation and submit feedback. | SIG-001/005/007/035/036/038 | W2 |
| UI-002 Dashboard | Role-specific actionable composition D-01–D-06. | SIG-001/004/012/013/020/021/034–038 | W4 |
| UI-003 Change password | Focused authenticated form in guest-style shell. | SIG-001/004/005/007/035/036/038 | W2 |
| UI-004 Ticket list | L-01/L-02/L-03 responsive list grammar. | SIG-004/012–019/034–036 | W5 |
| UI-005 Queue | Authorized tabs, compact work list, contextual empty. | SIG-004/012–014/017–019/034–036 | W5 |
| UI-006 All tickets | Tier 1 search/list/pagination. | SIG-004/012/013/015–019/034–036 | W5 |
| UI-007 Create ticket | Service context, grouped dynamic form, authorized files. | SIG-004/005/007–011/030/034–036 | W5 |
| UI-008 Ticket detail | T-01–T-06 conversation-first workbench. | SIG-022–033/034–038 | W6 |
| UI-009 Notifications | User-owned chronological list and read actions. | SIG-004/005/018/019/034–036/038 | W2 |
| UI-010 Approvals | Oldest-first decision queue with explicit consequence. | SIG-004/005/018/023/034–036/038 | W5 |
| UI-011 Reports | Period context, summary/data, export state. | SIG-004/005/015/019/020/034–036/038/040 | W4 |
| UI-012 Users | High-density admin collection and targeted forms. | SIG-004/005/007/008/010/016–019/032–039 | W4 |
| UI-013 Create user | Grouped standalone admin form. | SIG-004/005/007/008/010/034–036/038 | W4 |
| UI-014 Edit user | Record context, relations, guarded actions. | SIG-004/005/007/008/010/033/036/038 | W4 |
| UI-015 Reset password | Target/consequence-focused form. | SIG-004/005/007/010/036/038 | W4 |
| UI-016 Locations | Hierarchical admin collection. | SIG-004/005/007/015–019/032–036/038/039 | W3 |
| UI-017 Teams | Team metadata collection and forms. | SIG-004/005/007/009/018/032/034–036/038/039 | W3 |
| UI-018 Skills | Searchable usage-aware collection. | SIG-004/005/007/009/015–019/032–036/038/039 | W3 |
| UI-019 Audit | Read-only filterable chronology/details. | SIG-004/014/015/017–019/024/027/031/034–037/039 | W3 |
| UI-020 Branding | Editor, authorized file control, preview/history. | SIG-004/005/007/009–011/030/031/034–036/038 | W3 |
| UI-021 Services | High-density versioned service/form workbench. | SIG-004/005/007–010/014–019/031–036/038/039 | W4 |
| UI-022 Announcements | Create plus scheduled record collection. | SIG-004/005/007/009/010/017/018/027/031/034–036/038/039 | W3 |
| UI-023 403 | Truthful forbidden state with safe navigation. | SIG-037 | W2 |

Coverage: **UI-001 through UI-023 = 23/23**.

### DEC destination traceability

| DEC | FASE 3.2 destination or explicit ignore |
|---|---|
| DEC-001 | **IGNORE** → ANT-020/architecture guard; no SPA root. |
| DEC-002 | Application Shell; SIG-002; W2. |
| DEC-003 | Application Shell/Responsive; SIG-003; W2. |
| DEC-004 | Role Consistency Rules; SIG-001/002/003; W2. |
| DEC-005 | Page Header; SIG-004; W2–W6. |
| DEC-006 | North Star/Surface and Elevation; SIG-001/039; W1. |
| DEC-007 | Typography Direction; all text-bearing SIG; W1. |
| DEC-008 | Actions and Buttons; SIG-005/006; W1 onward. |
| DEC-009 | Forms; SIG-007–011; W1 onward. |
| DEC-010 | Status and Priority; SIG-012/013; W1. |
| DEC-011 | Application Shell/UI-009; SIG-001/003/018/038; W2. |
| DEC-012 | **IGNORE** → ANT-012; no command palette. |
| DEC-013 | Page Header/Lists; SIG-004/014; W3/W5. |
| DEC-014 | Filters; SIG-014–016; W3–W5. |
| DEC-015 | **IGNORE** → ANT-011; no multi-sort/configurable columns. |
| DEC-016 | **IGNORE** → ANT-005; no click-cell filter. |
| DEC-017 | Lists and Tables; SIG-017/018; W3/W5. |
| DEC-018 | **IGNORE** → ANT-010; no bulk workflow. |
| DEC-019 | **IGNORE** → ANT-005; numbered server pagination retained. |
| DEC-020 | Ticket Detail; SIG-022–031; W6. |
| DEC-021 | Ticket Detail/Responsive; SIG-022–031; W6. |
| DEC-022 | Ticket Detail action cluster; SIG-022/023/033; W6. |
| DEC-023 | Communication/timeline; SIG-024/027/028; W6. |
| DEC-024 | Communication; SIG-025/026/029; W6. |
| DEC-025 | Communication composer; SIG-029; W6. |
| DEC-026 | Forms/Communication attachments; SIG-011/030; W4–W6. |
| DEC-027 | Ticket Detail metadata; SIG-031; W4/W6. |
| DEC-028 | **IGNORE** → ANT-009; no inline ticket mutation. |
| DEC-029 | Forms; SIG-007–011; W4/W5. |
| DEC-030 | **IGNORE** → product out-of-scope; no knowledge suggestion. |
| DEC-031 | Modal and Dialog; SIG-032; W3–W6 after foundation. |
| DEC-032 | Dashboard; SIG-020/021; W4. |
| DEC-033 | **IGNORE** → ANT-011; no customizable dashboard. |
| DEC-034 | **IGNORE** → ANT-014; standalone admin routes retained. |
| DEC-035 | Admin Collections; SIG-039; W3/W4. |
| DEC-036 | Contextual States; SIG-034–037; every wave. |
| DEC-037 | **IGNORE** → ANT-013; no realtime presence. |
| DEC-038 | **IGNORE** → ANT-015; no dark mode. |
| DEC-039 | Accessibility; all interactive SIG; every wave. |
| DEC-040 | Contextual States/feedback; SIG-035/036/038/040; every wave. |

Coverage: **DEC-001 through DEC-040 = 40/40**, termasuk 12 explicit `IGNORE` destinations.

## Open Questions

Exact font, scale, color, dimension, breakpoint, and interaction choices yang belum mempunyai evidence tidak diputuskan di sini. Register authoritative berada di `19-DESIGN-OPEN-QUESTIONS.md` dengan tiga decision stages:

- `CAN DECIDE BEFORE SCREENSHOT`;
- `MUST WAIT FOR MANUAL BASELINE`;
- `MUST WAIT FOR DESIGN SYSTEM PHASE`.

### VIS compliance

| VIS | FASE 3.2 treatment |
|---|---|
| VIS-001 | Preserve four missing openers; separate defect decision required. |
| VIS-002 | Preserve approval reject auto-open/old gap; separate defect decision required. |
| VIS-003 | Preserve ticket modal retained/reset semantics; separate decision required. |
| VIS-004 | No request-information attachment input added. |
| VIS-005 | No extra triage outcome/field exposed. |
| VIS-006 | Preserve ACT-08 admin-dominant navigation/dashboard. |
| VIS-007 | Preserve ACT-09 mixed requester/current-Approver presentation. |
| VIS-008 | Preserve Team Chair ticket/report override and resource-specific additional-role access. |
| VIS-009 | UI-021 contract preserved; DOM weight is not a design requirement. |
| VIS-010 | UI-012 targeting/contracts preserved; DOM weight is not a design requirement. |
| VIS-011 | Skip-link remains explicit OPEN-022; not added implicitly. |
| VIS-012 | Manual responsive captures remain prerequisite. |
| VIS-013 | Existing focus/nav/modal behavior requires browser acceptance evidence before change. |
| VIS-014 | Contrast/zoom/forced-color evidence remains required before final tokens. |
| VIS-015 | SVC-03 modal remains without opener unless separate defect scope approved. |
| VIS-016 | SVC-03 incomplete/pre-execution action/readiness semantics unchanged. |
| VIS-017 | Long-content fixtures remain mandatory for list/detail manual validation. |

Coverage: **VIS-001 through VIS-017 = 17/17**. FASE 3.2 does not repair baseline defects.

Direction stability: the qualitative UI grammar is ready for review. Terdapat **23 open questions**, termasuk **10 yang wajib menunggu manual baseline**; sisanya berada pada explicit interaction decision atau design-system phase. Redesign implementation remains `HOLD — MANUAL VISUAL BASELINE REQUIRED`.

**FASE 3.2 PASS WITH OPEN QUESTIONS** — arah dan grammar cukup stabil untuk masuk review serta FASE 3.3 specification, tetapi exact tokens/dimensions dan screenshot-dependent layout decisions tetap terbuka. Gate ini tidak menghapus implementation hold.
