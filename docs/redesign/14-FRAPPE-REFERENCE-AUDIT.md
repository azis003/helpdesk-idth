# Frappe Helpdesk Reference Audit

Dokumen ini adalah audit referensi desain, bukan spesifikasi design system dan bukan izin implementasi. Seluruh temuan Frappe diterjemahkan sebagai prinsip visual atau interaksi. Source, asset, komponen, arsitektur SPA, dan behavior produk Frappe tidak disalin ke SIHATI.

## Reference Snapshot

| Item | Nilai authoritative |
|---|---|
| Repository | Official `frappe/helpdesk`: <https://github.com/frappe/helpdesk> |
| Branch yang diaudit | `develop` |
| Exact commit | `34e0e8f41a35342b3066da8ae5681ecfa7730d38` |
| Commit timestamp | `2026-08-14T00:07:06+05:30` |
| Commit subject | `Merge pull request #3696 from RitvikSardana/fix/hide-bulk-actions-for-customers` |
| Tanggal audit | 2026-08-14 (Asia/Jakarta) |
| License repository | Teks `LICENSE` adalah GNU Affero General Public License, Version 3 (AGPLv3) |
| Frontend root | `desk/`; implementation utama di `desk/src/` |
| UI submodule yang dirujuk commit | `frappe-ui` pada `a3d141c63b098d7dfbeb7a511d98fc988a950048` |
| Cara inspeksi | Shallow clone official branch di direktori temporer di luar repository SIHATI; submodule UI di-checkout hanya untuk inspeksi source |

Snapshot dapat diverifikasi melalui tree permanen <https://github.com/frappe/helpdesk/tree/34e0e8f41a35342b3066da8ae5681ecfa7730d38>. Audit ini tidak mengikuti perubahan Frappe setelah SHA tersebut.

### Source areas reviewed

| Area | Source yang dibaca |
|---|---|
| Metadata/build/style | `LICENSE`, `desk/package.json`, `desk/vite.config.js`, `desk/tailwind.config.js`, `desk/src/index.css` |
| Bootstrap/router/roots | `desk/src/main.js`, `App.vue`, `router/index.ts`, `roots/AgentRoot.vue`, `roots/CustomerPortalRoot.vue`, `roots/PortalRoot.vue` |
| Shell/navigation | `components/layouts/{DesktopLayout,MobileLayout,AppHeader,MobileAppHeader,Sidebar,AppSidebar,MobileSidebar}.vue`, `layoutSettings.ts`, `LayoutHeader.vue`, `ViewBreadcrumbs.vue` |
| Ticket list/view controls | `pages/ticket/Tickets.vue`, `ListViewBuilder.vue`, `ListRows.vue`, `view-controls/**`, `EmptyState.vue`, `SkeletonLoader.vue` |
| Ticket detail | `pages/ticket/{TicketAgent,MobileTicketAgent,TicketCustomer,TicketConversation,TicketCommunication}.vue`, `components/ticket/**`, `components/ticket-agent/**` |
| Create/form | `pages/ticket/TicketNew.vue`, `TicketField.vue`, `UniInput.vue`, `FieldLabel.vue`, `Questionnaire.vue`, `composables/formCustomisation.ts` |
| Communication/attachment | `CommunicationArea.vue`, `EmailArea.vue`, `CommentBox.vue`, `TicketTextEditor.vue`, `AttachmentList.vue`, `AttachmentItem.vue`, `HistoryBox.vue` |
| Dialog | `components/dialogs.jsx`, `ConfirmDialog.vue`, `ViewModal.vue`, ticket and bulk action dialogs, `pages/ticket/modalStates.ts` |
| Home/dashboard | `pages/home/Home.vue`, `pages/home/components/**`, `pages/dashboard/Dashboard.vue`, chart card components |
| Management/settings | `SettingsModal.vue`, `SettingsLayoutBase.vue`, `Settings/Agents.vue`, `pages/customer/Customers.vue`, `pages/contact/Contacts.vue` |
| State/realtime | `stores/{auth,sidebar,globalStore,ticketStatus,user}.ts`, `composables/{useTicket,realtime,screen,mobile}.ts`, `socket.ts` |
| Actual Frappe UI dependencies | Frappe UI `Sidebar`, `SidebarItem`, `Dialog`, `Tabs`, `ListView`, `ListFooter`, Inter font source, Tailwind typography/radius/color definitions |

## Frontend Architecture

Frappe Helpdesk adalah Vue 3 SPA. Ini berbeda secara mendasar dari SIHATI yang tetap Laravel/Blade server-rendered. Nilai referensi berada pada hierarchy dan interaction concept, bukan pada implementasi arsitekturnya.

| Layer | Frappe implementation pada commit audit | Implikasi untuk SIHATI |
|---|---|---|
| Bootstrap | `main.js` memasang Vue, Pinia, Vue Router, Frappe UI, translation, telemetry, global dialog, dan Socket.IO | Tidak dipindahkan. SIHATI mempertahankan boot Laravel/Vite dan JavaScript existing. |
| Routing | Vue Router memakai history base `/helpdesk/`, lazy-loaded pages, route meta, dan navigation guard | Route/URL/method/name SIHATI tetap authoritative dari `routes/web.php`. |
| Portal root | `PortalRoot.vue` memilih agent atau customer root dari session; keduanya memilih layout desktop/mobile | Audience-aware shell dapat menjadi referensi visual, tetapi SIHATI memakai server role/policy branch existing. |
| State | Pinia stores untuk auth, user, status, sidebar, notification, dan domain state; local state memakai refs/computed | Jangan menambahkan Pinia atau client state layer. Server/session/Blade tetap sumber kebenaran SIHATI. |
| Data | `createResource`, `createListResource`, dan `createDocumentResource` mengakses Frappe APIs dan menyimpan cache client | Tidak dipindahkan. Form action, query string, pagination, redirect, flash, dan old/error contract SIHATI tetap server-side. |
| Realtime | Socket.IO memperbarui ticket, comments, viewers, typing, notification, dan progress | Diabaikan karena akan menjadi feature/backend expansion. |
| Styling | Tailwind dengan preset Frappe UI dan semantic variables `surface`, `ink`, `outline`; app CSS tipis di atas library | Prinsip semantic hierarchy boleh dipelajari. Token/class/source tidak disalin. |
| Components | Frappe UI primitives + Vue SFC domain components; Reka UI/Headless UI untuk focus/dialog/menu behavior | UI SIHATI nanti harus diimplementasikan ulang dengan Blade/Tailwind/JS existing, bukan Vue/Frappe UI. |
| Packaging | Vite + PWA plugin membangun ke Frappe public desk | PWA/client router tidak relevan dengan redesign SIHATI. |

Dependency penting di `desk/package.json` mencakup Vue 3.5, Vue Router 4, Pinia 2, Frappe UI beta, Tailwind 3.4, Headless UI, Tiptap, Socket.IO, VueUse, dan Vite PWA. Dependency tersebut menjelaskan behavior referensi, tetapi tidak menjadi dependency proposal.

## Visual Language

Bahasa visual Frappe Helpdesk bersifat tenang, padat, dan berorientasi pekerjaan:

- hierarchy dibangun terutama dari typography, whitespace kecil yang konsisten, garis pemisah, dan muted gray surfaces;
- shadow dipakai selektif untuk active navigation, popover, floating selection bar, dialog, dan elevation sementara—bukan untuk setiap section;
- primary actions tidak berteriak dengan brand color besar; solid gray/neutral action tetap kontras terhadap secondary/ghost controls;
- status dan priority menggabungkan simbol dengan teks, sehingga warna bukan satu-satunya pembeda;
- ticket workspace memprioritaskan activity/conversation, sedangkan metadata ditempatkan di rail atau tab sekunder;
- list dan settings sengaja compact, dengan kontrol lanjutan muncul saat dibutuhkan;
- empty/loading/error state berada di konteks area yang kosong, bukan hanya global page message.

Yang layak dibawa ke SIHATI adalah ketenangan visual, hierarchy, dan progressive disclosure. Identitas Frappe, exact palette, exact dimensions, icon assets, dan client behavior bukan target adopsi.

## Application Shell

| Concern | What it does | Why it works | Visual vs behavior | Yang dapat dipetakan ke SIHATI |
|---|---|---|---|---|
| Desktop layout | Sidebar tetap di kiri/inline-start; header dan page content berada di flex column yang mengisi viewport | Navigation stabil tanpa mengurangi ruang scroll page secara tak terkontrol | Visual: rail, border, compact header. Behavior: Vue root, client navigation, notification overlay | Adapt hierarchy shell; jangan pindahkan routing atau session logic. |
| Sidebar width/collapse | Frappe UI default 15rem dan rail 3rem; state collapse disimpan; item aktif memakai surface elevation + subtle shadow | Label mudah dipindai saat expanded dan icon tetap usable saat collapsed | Visual: width contrast, item height, active treatment. Behavior: local storage dan Pinia | Adapt visual states hanya setelah branch navigation multi-role SIHATI teruji. Exact width bukan keputusan fase ini. |
| Workspace hierarchy | User menu di atas, search/notifications, primary navigation, public/private saved views, onboarding/help di bawah | Utility, destination, dan contextual views terpisah jelas | Saved views/onboarding adalah feature behavior | Adapt grouping; ignore saved-view/onboarding capability yang tidak ada. |
| Page header | `LayoutHeader` men-teleport konten page ke slot shell; left = title/breadcrumb, right = actions | Setiap screen konsisten tanpa header berlapis | Visual pattern sangat berguna; Teleport adalah Vue behavior | Adapt sebagai Blade header region; jangan menyalin Teleport. |
| Mobile shell | Dedicated header 48px dan drawer overlay; route change menutup drawer | Navigasi tidak memakan viewport; overlay memberi konteks | Different component untuk shell; Headless UI mengelola dialog transition | Adapt sebagai existing mobile nav behavior; preserve focus/close/nav contracts. |
| Sticky/fixed surfaces | Header, notification panel, ticket composer, section header, dan settings labels dapat sticky/fixed | Action utama tetap tersedia saat konten panjang | Visual dan behavior saling terkait; stacking/focus penting | Gunakan selektif; baseline responsive/manual screenshots wajib sebelum markup change. |
| Border vs shadow | Shell/header/list/section memakai border; shadow terutama elevation sementara | Mengurangi “card soup” dan menjaga density | Murni presentation kecuali overlay stacking | Adopt principle, bukan exact class/token. |

## Navigation

Frappe memisahkan agent portal dan customer portal. Agent navigation mencakup Home, Dashboard, Tickets, Knowledge Base, Customers, Contacts, dan Call Logs; customer navigation hanya Tickets dan Knowledge Base. `AppSidebar` juga menyisipkan search, notifications, public/private views, profile options, theme, settings, dan logout.

Hal yang kuat:

- active state diturunkan dari route/view dan diperbarui optimistically;
- section saved views dapat collapse tanpa mencampurnya dengan primary destinations;
- collapsed rail mempertahankan tooltip dan icon;
- mobile menggunakan drawer yang menutup setelah navigation;
- unread notification memakai dot/badge, bukan mengubah keseluruhan item menjadi warna kuat;
- RTL diperhitungkan dengan logical direction classes.

Kontrak yang tidak dapat dipetakan langsung:

- Frappe route guard membedakan agent/customer menggunakan meta route dan auth store;
- SIHATI memiliki enam role, current approver, dan multi-role precedence yang tidak memiliki analog langsung;
- pure Super Admin SIHATI tidak otomatis operasional, sementara Frappe `admin`/`manager` flags berasal dari model berbeda;
- Ketua Tim SIHATI harus tetap safe projection/read-only walau mempunyai additional role.

Karena itu navigation grouping adalah `ADAPT`, bukan salinan. Server-side authorization dan branch Blade tetap authoritative; navigation tidak boleh dianggap enforcement.

## Typography

Frappe UI memuat Inter variable dan menyediakan hierarchy compact. Angka berikut adalah evidence source, bukan token proposal SIHATI: body control umum berada sekitar 14px, metadata 12–13px, page/section title umumnya 16–18px, dan prominent dashboard/customer activity heading dapat naik ke 20–24px. Paragraph variants mempunyai line-height lebih longgar daripada label/control text.

| Role typography | Frappe behavior | Nilai hierarchy untuk SIHATI |
|---|---|---|
| Application/page title | Medium/semibold, dark ink, compact line height | Satu anchor jelas per page; jangan memakai hero besar di layar kerja padat. |
| Section heading | Medium/semibold, sering sticky di metadata rail | Section dapat dipindai tanpa card berat. |
| Body/content | Regular, darker than metadata; rich content memakai prose line-height | Prioritaskan readability description/message panjang. |
| Metadata/timestamp/helper | Ukuran lebih kecil dan muted ink | Bedakan konteks sekunder tanpa menyembunyikannya. |
| Table/list | Compact regular; subject unread menjadi semibold | Weight dapat membawa state tanpa menambah badge. |
| Field label | Muted, fixed label column pada detail; required marker merah | Adapt ke layout SIHATI; mobile tidak boleh memaksakan label width desktop. |
| Badge/status/button | Compact medium; icon + label | Action/status tetap terbaca dan tidak bergantung warna/icon saja. |
| Empty state | Title medium, description muted dan dibatasi lebar | Gunakan copy kontekstual dan satu arah tindakan bila memang ada. |

Hierarchy ini layak diadaptasi; exact font family, size, tracking, dan weight belum ditetapkan pada fase 3.1.

## Color and Surface

Frappe UI memakai semantic variables yang mendukung light/dark mode. Light surface base dan elevation umumnya putih, sidebar memakai very light gray, ink bergerak dari muted gray ke near-black, dan outline memakai gray steps. App source memakai warna status tertentu hanya untuk meaning.

| Kategori | Penggunaan aktual | Keputusan referensi |
|---|---|---|
| Neutral page/surface | Base page, section, editor, list; sidebar sedikit dibedakan | `ADOPT` principle border-led neutral hierarchy. |
| Muted surface | Hover, comment body, collapsed composer, empty icon disc | Gunakan sebagai grouping/interaction feedback, bukan dekorasi acak. |
| Selected/active | Sidebar item elevation + subtle shadow; tab ink + indicator; selected form pills | Active state harus mempunyai bentuk/weight/label selain warna. |
| Primary action | Solid neutral button; destructive action memakai red semantic theme | Adapt action hierarchy, bukan exact color. |
| Status | Dot berwarna + agent/customer label; SLA memakai labeled badge | Semantic color; label tetap wajib. |
| Priority | Monochrome bar/urgent icon + text | Sangat relevan karena tidak bergantung pada hue. |
| Success/warning/danger | Toast, alert, badge, destructive dialog | Semantic only dan terikat outcome/action. |
| Dashboard charts | Multi-color series | Decorative + semantic campuran; jangan dibawa tanpa data/legend/contrast review. |
| Dark theme | Full theme state dan shadow overrides | `IGNORE` untuk fase redesign ini karena akan menambah theme behavior. |

Decorative color tidak boleh menggantikan semantic status/priority SIHATI yang sudah dibekukan. Contrast, forced-colors, dan mobile rendering masih memerlukan visual/manual testing.

## Spacing and Density

Rhythm Frappe berasal dari kelipatan kecil dan konsisten. Source menunjukkan sidebar item dan controls sekitar 28px, header shell sekitar 42–48px, default list row sekitar 40px, page gutters sekitar 20px pada desktop dengan penyesuaian 12px pada mobile, form gap 16–20px, dan dialog body horizontal padding yang meningkat pada `sm` viewport. Nilai ini hanya evidence untuk menilai density.

Prinsip yang bekerja:

- gap internal control kecil; gap antar-group lebih besar;
- list memakai row height konsisten dan divider tipis, bukan card per row;
- ticket detail memisahkan scroll conversation dan metadata rail;
- form create membatasi content width dan memberi gap antar-section lebih besar daripada antar-field;
- dialog mempunyai header/body/footer rhythm yang stabil;
- mobile mengurangi gutter dan menyembunyikan control sekunder, bukan mengecilkan semua touch target;
- long content memakai truncation hanya di list/header, sedangkan detail menyediakan wrapping/scroll.

SIHATI dapat mengadopsi rhythm tersebut secara konseptual. Exact spacing, breakpoint, dan density akan diputuskan pada design-system phase setelah baseline screenshot manual tersedia.

## Ticket List

`Tickets.vue` menggabungkan `LayoutHeader`, `ViewBreadcrumbs`, `ListViewBuilder`, export, saved view, dan bulk action dialogs. Hal penting dari implementation aktual:

- header kiri menunjukkan Tickets/current view; header kanan mempunyai Create;
- tidak ada search field sederhana khusus di page header: search global berada di command palette dan quick text fields berasal dari server metadata;
- desktop toolbar menampilkan horizontal quick filters, reload, filter, multi-sort, dan column settings;
- mobile menyembunyikan quick filters dan column settings, menyisakan Filter, Reload, dan icon-only Sort;
- list columns dan rows berasal dari API; subject unread memakai semibold;
- status memakai colored indicator + audience-specific label; priority memakai icon level + text;
- cell non-text tertentu dapat diklik untuk menambah filter;
- selection banner menampilkan Reply/Assign secara inline dan Export/Edit/Delete di overflow sesuai role;
- footer memakai selectable page length, `Load More`, dan `shown of total`; page length dan scroll position disimpan lokal;
- loading pertama memakai centered indicator; empty state copy berbeda untuk saved view, active filter, atau no data;
- underlying Frappe UI list menggunakan horizontal overflow, bukan card conversion penuh pada mobile.

### Translation to UI-004/UI-005/UI-006

| Frappe concept | SIHATI treatment |
|---|---|
| Compact header + toolbar | Adapt visual grouping. Existing `q`, tab, class, status, date, and `per_page` query contracts tetap. |
| Quick/advanced filter anatomy | Adapt sebagai server-submitted GET controls; jangan memakai client resource state. |
| Status/priority redundant cue | Adopt concept; pertahankan canonical SIHATI labels/classes. |
| Dense list row | Adapt untuk existing desktop table + mobile card behavior. |
| Saved views, configurable columns, click-to-filter | Ignore karena menambah persisted feature/client behavior. |
| Selection/bulk actions | Ignore; SIHATI tidak mempunyai frozen bulk ticket workflow. |
| Load more/local page length | Ignore; pertahankan paginator dan query preservation existing. |
| Contextual empty/loading state | Adopt/adapt presentation tanpa mengubah result semantics. |

## Ticket Detail

### Agent desktop

`TicketAgent.vue` memakai compact action header, conversation/activity sebagai area utama, dan resizable metadata sidebar. `TicketActivityPanel` menyediakan tabs Activity, Emails, Comments, optional Calls, dan Analytics. Header mengelompokkan active viewers, custom actions, status dropdown, dan overflow actions. Metadata rail menempatkan contact/SLA di atas, lalu collapsible Overview, More Details, feedback, recent/similar tickets.

### Agent mobile

`MobileTicketAgent.vue` adalah **different component**, bukan sekadar CSS reflow. Status ditempatkan di compact header, team/assignee dan custom actions dipadatkan, sedangkan Details/Activity/Emails/Comments/Calls menjadi tabs. Composer sticky di bawah. Ini efektif untuk ruang sempit, tetapi implementation/client state tidak dapat disalin ke Blade.

### Customer portal

`TicketCustomer.vue` memakai conversation utama, Close action, optional out-of-hours alert, reply editor, dan detail sidebar desktop. Pada mobile, Activity dan Details menjadi tabs. Customer hanya melihat field template yang tidak `hide_from_customer`.

### Hierarchy finding

1. Ticket identity dan current status selalu terlihat.
2. Primary work surface adalah activity/conversation.
3. Metadata dan lower-frequency information dipindah ke rail/tabs/sections.
4. Actions ditampilkan normal, grouped, atau overflow sesuai frequency/space.
5. Email, internal comment, system history, call, feedback, dan attachment mempunyai renderer berbeda.
6. Missing/not-authorized ticket mempunyai dedicated error state dan route back.

### Constraint for UI-008

SIHATI tidak boleh mengadopsi satu universal full-model ticket view. Enam variants T-01 sampai T-06 tetap berbeda. Khusus T-06, Team Chair harus menerima `TeamChairTicketView` dan public safe projection saja; CSS hiding atas full Ticket dilarang. Status/action cluster hanya merender action booleans yang sudah dihitung controller/policy. Frappe inline edit, custom action execution, status `setValue`, realtime, merge/split, analytics, telephony, and similar-ticket feature tidak boleh masuk sebagai expansion.

## Ticket Create / Forms

`TicketNew.vue` membatasi content ke centered maximum width, memakai one-column mobile dan three-column field grid mulai small viewport, kemudian Subject, optional knowledge suggestions, rich description, attachment uploader, dan Submit di editor footer.

Form behavior aktual:

- field template berasal dari API;
- `parseField` mengevaluasi visibility, mandatory, readonly, dan link filters;
- `UniInput` memilih Link, Select, Check, Date/Datetime, autocomplete, atau generic control dari metadata;
- customer fields disaring dengan `hide_from_customer`;
- required marker ditempel pada label;
- dependent on-change dapat mengubah option/filter field lain;
- subject minimal memicu progressive disclosure pada customer portal;
- upload dibuat private dan diikat ke ticket;
- validation resource mengembalikan pesan required umum, bukan per-field `old()`/error bag seperti SIHATI;
- `formCustomisation.ts` dapat mengeksekusi form script dinamis dengan `new Function`, yang sama sekali tidak layak dipindahkan.

Untuk UI-007, hanya hierarchy, max-width concept, grouping, label/required/helper rhythm, dan responsive field grid yang layak diadaptasi. `name`, hidden values, multipart action, dynamic field keys, `old()`, nested error keys, service selection GET, authorization, attachment policy, dan validation server SIHATI tetap frozen. Knowledge suggestion dan executable form scripting diabaikan.

## Communication

| Message type di Frappe | Visual/interaction | Relevansi SIHATI |
|---|---|---|
| Customer/agent email | Bordered white communication surface; sender/recipient metadata, delivery badge, timestamp, reply/reply-all, attachments | Reference untuk public conversation hierarchy, bukan email feature expansion. |
| Internal comment | Muted filled surface; commenter/avatar/timestamp; edit/delete untuk owner; optional reaction | Reference kuat untuk membedakan internal note. Edit/delete/reaction tidak diadopsi bila tidak ada contract SIHATI. |
| Customer portal message | Timeline avatar + white message surface; reply editor di bawah | Adapt untuk Pemohon public thread dengan existing route/policy. |
| System history | Compact text event, related changes dapat collapse, timestamp di sisi lain | Adapt untuk existing server-built timeline. |
| Attachment | Labeled chip dengan file-type icon; image/video/text dapat preview dialog; file lain membuka URL | Adapt visual only; SIHATI download route/authorization/visibility tetap authoritative. |
| Composer | Reply dan Comment adalah dua toggle eksklusif; keyboard submit; Escape/click-outside close | Adopt separation principle, adapt exact lifecycle to existing forms/modal state. |

Frappe “Reply” secara konseptual mengarah ke external/customer email, sedangkan “Comment” adalah internal agent note. SIHATI memiliki public message, requester reply, dan internal note dengan route, policy, visibility, and attachment contract masing-masing. Mereka boleh berbagi visual primitive, tetapi tidak boleh disatukan menjadi satu form/payload/endpoint.

## Filters / View Controls

Frappe memisahkan visual controls dan data behavior dengan cukup jelas:

- `QuickFilters` adalah horizontal strip; text input di-debounce 500ms;
- advanced `Filter` adalah three-step popover: overview, field selection, operator/value editor;
- active filter rows dapat edit, replace, remove, atau clear all;
- keyboard shortcut dan listbox semantics tersedia pada field/value picker;
- `SortBy` dapat menyusun beberapa field/direction dan drag reorder;
- `ColumnSettings` dapat menambah, menghapus, reorder, rename, dan resize columns;
- filter/sort/column state diterapkan melalui injected list actions dan Frappe APIs;
- URL dapat menyimpan JSON filters dan saved view ID.

Untuk SIHATI:

- ambil hierarchy toolbar, active filter summary, clear affordance, dan mobile collapse;
- submit tetap GET server-side;
- query parameter existing tetap exact dan paginator menjaga query;
- jangan menambah JSON filter protocol, saved view, multi-sort, client metadata fetch, local page state, atau user-configurable column behavior;
- filter/action visibility tetap mengikuti server role/policy, bukan client flag.

## Modal / Dialog

Frappe UI `Dialog` yang benar-benar digunakan Helpdesk menyediakan overlay, centered responsive max-width, rounded elevated content, semantic title/description, close button, Escape/outside-click rules, focus scope, focus return, optional autofocus, body padding, action footer, and per-action loading. Helpdesk memakai tiga bentuk:

1. declarative `Dialog` component untuk form/export/bulk/ticket actions;
2. imperative `createDialog` untuk confirmation/message actions;
3. bare large dialog untuk Settings split-pane.

Visual anatomy yang layak:

- title dan close affordance konsisten;
- body dan footer terpisah jelas;
- one primary action dapat full-width pada small dialog;
- destructive action diberi semantic red dan loading;
- large editor dialog membatasi max width/height dan membuat content scrollable;
- mobile tetap mempunyai viewport gutter.

Risiko terhadap SIHATI sangat tinggi. Generic modal SIHATI mempunyai modal IDs, `data-*`, auto-open berdasarkan error context, preserved form state, and 14 ticket action modal contracts. Empat missing opener dan approval auto-open gap adalah frozen defects. Standardisasi visual tidak boleh menambah opener, mengubah reset/clear, memindahkan form action, atau memperbaiki validation routing secara diam-diam.

## Dashboard / Home

Frappe mempunyai dua pola:

- Home agen adalah user-customizable grid yang dapat edit, drag, resize, add/remove, reset, dan persist charts.
- Dashboard organisasi/agen adalah fixed responsive composition: period/team/agent filters, manager-only organization/my-stat toggle, number cards, trend/master/tag charts, skeletons, per-chart empty state, dan whole-dashboard empty state.

Visual strengths:

- page header menampung filter mode/action, bukan mencampurnya ke chart;
- KPI cards memakai border dan clear label/value hierarchy;
- filters membentuk satu horizontal strip yang dapat overflow;
- grid turun dari lima ke dua ke satu column sesuai viewport;
- chart empty state mempertahankan card geometry sehingga layout tidak melompat;
- organization vs personal scope ditandai dengan segmented control dan title berubah.

Untuk UI-002, FRP dashboard hanya dapat menjadi reference composition. Enam variants D-01–D-06 dan data sections SIHATI tetap server-defined. Drag/resize/add chart, client persistence, Frappe chart APIs, manager scope toggle, and feature set baru diabaikan.

## Responsive Patterns

| Classification | Actual Frappe examples | Mapping consequence |
|---|---|---|
| **Different component** | Desktop/Mobile layout; `TicketAgent.vue` vs `MobileTicketAgent.vue`; notifications side panel vs mobile page | Berguna ketika hierarchy benar-benar berbeda, tetapi SIHATI boleh tetap memakai Blade branch/partials existing. |
| **Same content / different layout** | Customer detail sidebar menjadi tabs; create grid 3→1; dashboard 5→2→1; toolbar labels menjadi icons | Adapt melalui responsive presentation tanpa mengubah data/action availability. |
| **Hidden/collapsed** | Quick filters dan column settings hilang di mobile; sidebar menjadi drawer; custom actions masuk overflow; detail metadata pindah tab | Hidden hanya untuk secondary controls. Authorized action penting tidak boleh hilang tanpa alternative affordance. |
| **Scrollable** | Tabs, filter strip, list horizontal overflow, modal/settings body, activity feed | Dapat mencegah clipping, tetapi long-content fixtures dan focus order wajib diuji. |

Breakpoint mobile internal Helpdesk adalah kurang dari 640px. Exact breakpoint tidak otomatis menjadi keputusan SIHATI. Existing target baseline tetap 1440×900 dan 390×844, dengan screenshot manual sebelum implementation.

## Empty / Loading / Error States

Frappe tidak memakai satu generic copy untuk seluruh app:

- list empty state membedakan no data, no filter result, dan saved view result;
- activity tabs membedakan no emails/comments/calls/activity;
- dashboard membedakan loading skeleton, per-chart no data, filtered no data, dan full empty;
- Home membedakan dashboard loading dan “No charts added”;
- ticket detail membedakan initial loader dan no access/not found;
- invalid route memiliki icon, title, explanation, dan back action;
- network offline/online serta API errors muncul sebagai toast;
- progress/submit controls menunjukkan loading dan mencegah duplicate action pada beberapa flows.

Contextual state anatomy adalah high-value reference. Copy, authorization meaning, redirect, flash, and whether a page is 403/404/empty must tetap mengikuti SIHATI.

## Accessibility Patterns

### Strengths found in source

- Frappe UI Dialog menggunakan Reka UI focus scope, semantic title/description, Escape/outside close controls, and focus return;
- tabs/listbox/combobox memakai roles dan selected/expanded/active descendant states;
- command palette mempunyai `aria-live` result announcement dan complete keyboard model;
- filter field/value lists mendukung arrow/Enter/Escape dan reduced-motion override;
- icon-only controls sering mempunyai tooltip/label; decorative icons memakai `aria-hidden` pada sejumlah components;
- focus-visible rings tersedia pada inputs, breadcrumb, and action chips;
- RTL logical classes dan document direction didukung;
- reduced-motion handling ditemukan pada filter, tags, dan command palette;
- status dan priority memiliki text labels selain visual cue.

### Gaps/cautions found in source

- implementation tidak konsisten: beberapa raw icon buttons/links bergantung pada library defaults atau tooltip tanpa explicit label di local source;
- sejumlah focus-visible styles sengaja dihilangkan pada nav/settings/tabs, sehingga keyboard visibility harus diverifikasi, bukan diasumsikan;
- activity rows diberi `tabindex="0"` lalu outline dihapus;
- no runtime contrast/zoom/forced-colors audit dilakukan dalam fase ini;
- table/list mobile tetap berpotensi horizontal scroll;
- large Settings dialog tampak desktop-oriented;
- several strings bypass translation helper;
- accessibility behavior library tidak dapat disalin hanya dengan meniru markup visual.

Untuk SIHATI, accessibility concept termasuk `ADOPT`, tetapi perubahan terhadap existing focus/modal behavior tetap memerlukan regression and manual capture. VIS-011 sampai VIS-014 tetap menjadi evidence/decision gate.

## Component Pattern Catalog

Definisi suitability:

- **ADOPT**: konsep visual dapat dibawa hampir langsung, tetapi tetap diimplementasikan ulang secara native Blade/Tailwind/JS SIHATI.
- **ADAPT**: konsep bernilai namun harus diterjemahkan karena workflow, role, server rendering, responsive behavior, atau frozen contract.
- **IGNORE**: akan menambah feature/architecture atau bertentangan dengan baseline.

| ID | Name | Frappe source files | Purpose | Visual / interaction / responsive behavior | Vue/Frappe dependency | Suitability dan alasan |
|---|---|---|---|---|---|---|
| FRP-001 | Session-selected dual portal SPA root | `roots/PortalRoot.vue`, `AgentRoot.vue`, `CustomerPortalRoot.vue`, `router/index.ts` | Memilih agent/customer shell dari session | Visual shell serupa; client guard mengganti portal/root dan redirect | Vue Router, Pinia auth | **IGNORE** — SIHATI memakai Laravel routes, middleware, policy, dan enam role; menyalin pola ini akan mengganti arsitektur. |
| FRP-002 | Collapsible desktop sidebar | `layouts/AppSidebar.vue`, Frappe UI `Sidebar*.vue`, `stores/sidebar.ts` | Navigation rail stabil dan hemat ruang | 15rem→3rem, active elevation, tooltip, state persisted; desktop only | Vue state, local storage, Frappe UI | **ADAPT** — hierarchy berguna; exact collapse behavior hanya boleh diterapkan setelah multi-role navigation regression. |
| FRP-003 | Mobile drawer + compact header | `MobileLayout.vue`, `MobileAppHeader.vue`, `MobileSidebar.vue` | Mengganti sidebar desktop pada layar kecil | 48px header, menu button, animated overlay, close on route change | Vue transition, Headless UI, router | **ADAPT** — reimplement dengan existing JS/Blade dan preserve focus/close behavior. |
| FRP-004 | Audience-specific navigation groups | `layoutSettings.ts`, `Sidebar.vue`, `AppSidebar.vue` | Memisahkan agent/customer destinations dan contextual views | Grouped labels, active row, badges, footer utilities; drawer di mobile | Auth store, route names, view resources | **ADAPT** — map dari policy flags SIHATI, bukan nama role atau Frappe portal model. |
| FRP-005 | Compact page header / breadcrumb-action bar | `LayoutHeader.vue`, `PageTitle.vue`, `ViewBreadcrumbs.vue`, ticket headers | Memberi anchor page dan action utama konsisten | Border-bottom, compact title/breadcrumb left, actions right; labels truncate/collapse mobile | Vue Teleport, router | **ADAPT** — visual candidate kuat untuk shared Blade header; route/back/query contract tetap existing. |
| FRP-006 | Neutral border-led surface hierarchy | `index.css`, semantic Tailwind classes, Frappe UI color tokens | Mengelompokkan work surfaces tanpa card berlebih | White/light-gray surfaces, thin borders, shadows hanya elevation sementara; theme-aware | Frappe UI tokens/Tailwind | **ADOPT** — principle dapat direimplementasi tanpa menyalin exact token/palette. |
| FRP-007 | Compact muted typographic hierarchy | Frappe UI Inter/typography tokens; headers, metadata, list, forms | Membuat dense application tetap mudah dipindai | Medium titles, regular body, smaller muted metadata, paragraph line-height lebih lega | Inter files, Frappe Tailwind typography | **ADAPT** — hierarchy dipakai, exact font/size/weight diputuskan fase design system. |
| FRP-008 | Button hierarchy | Frappe UI `Button.vue`; usages in headers, toolbar, dialogs | Membedakan primary, secondary, ghost, icon, destructive actions | Solid/subtle/outline/ghost; icon+label; loading/disabled; mobile dapat icon-only | Frappe UI Button | **ADOPT** — action hierarchy bernilai; reimplement dan tetap gunakan Bahasa Indonesia serta existing submit contracts. |
| FRP-009 | Form control anatomy | `TicketField.vue`, `UniInput.vue`, Frappe UI FormControl/TextInput/Select/Checkbox | Konsistensi input, labels, helper/error, readonly states | Compact border/surface, focus ring, required marker, responsive grid; type-specific controls | Vue dynamic component, Frappe metadata/UI | **ADAPT** — visual anatomy saja; `name`, old/error, validation, hidden fields, and dynamic rules tetap SIHATI. |
| FRP-010 | Status dot + text / priority icon + text | `Tickets.vue`, `IndicatorIcon.vue`, `TicketPriority.vue`, status/priority stores | Redundant semantic cue untuk state dan urgency | Small dot or level bars alongside label; compact in row/header; no hue-only priority | Vue stores and configured status metadata | **ADOPT** — concept aman; canonical SIHATI statuses/priorities/colors remain authoritative. |
| FRP-011 | Contextual notification panel/full mobile page | `notifications/Notifications.vue`, `MobileNotifications.vue`, `AppSidebar.vue` | Membaca notification tanpa meninggalkan work context | Desktop side panel anchored to sidebar; mobile page; unread dot; empty state | Pinia notification, router, API | **ADAPT** — UI-009 dapat memakai density/hierarchy; existing notification ownership/routes/forms tetap. |
| FRP-012 | Command palette + shortcut system | `command-palette/**`, `AppSidebar.vue` | Global search and command discovery | Cmd/Ctrl+K dialog, grouped results, listbox keyboard, context chip, live announcements | Vue, Reka UI, client commands/search | **IGNORE** — feature dan global behavior baru, bukan presentation-only redesign. |
| FRP-013 | View breadcrumb / saved-view selector | `ViewBreadcrumbs.vue`, `Tickets.vue`, `composables/useView.ts` | Menampilkan current list view dan switcher | Breadcrumb + dropdown, public/private/standard markers, edit menu; truncates mobile | Router query, persisted view API | **ADAPT** — breadcrumb/header anatomy berguna; saved views sendiri tidak ditambahkan. |
| FRP-014 | Quick filters + advanced filter flow | `view-controls/QuickFilters.vue`, `QuickFilterField.vue`, `filter/**` | Progressive disclosure untuk simple dan compound filters | Horizontal quick controls; Filter count; overview→field→value popover; mobile hides quick strip | Injected list resource/actions, Vue, Frappe UI | **ADAPT** — translate to server GET forms/existing query names, not client filters. |
| FRP-015 | Multi-sort + user-configurable columns | `SortBy.vue`, `ColumnSettings.vue`, `ListViewBuilder.vue` | Personalize order and visible columns | Drag reorder, multiple directions, add/remove/rename/resize columns | Frappe metadata, local/client state, draggable | **IGNORE** — adds persisted behavior and may alter report/list contracts. |
| FRP-016 | Click-cell-to-filter behavior | `ListViewBuilder.vue` | Mempercepat narrowing dari cell value | Link/avatar/status cell stops row navigation and injects filter | Client list params, metadata | **IGNORE** — changes interaction/query behavior and conflicts with existing row links. |
| FRP-017 | Dense list scaffold with loading/empty state | `ListViewBuilder.vue`, `ListRows.vue`, Frappe UI `ListView/**` | Reusable list header/rows/footer/state | 40px-style rows, dividers, horizontal overflow, hover/select, spinner/empty, responsive toolbar | Frappe list API/components | **ADAPT** — hierarchy/density useful; keep SIHATI Blade tables/mobile cards/server pagination. |
| FRP-018 | Selection banner and bulk ticket actions | `ListViewBuilder.vue`, `Tickets.vue`, `Bulk*Modal.vue` | Operasi pada multiple records | Floating selection actions, inline frequent actions, overflow rest | Client selections, bulk APIs/socket progress | **IGNORE** — SIHATI has no frozen bulk workflow; would expand behavior/authorization. |
| FRP-019 | Load-more + locally persisted page length | `ListViewBuilder.vue`, Frappe UI `ListFooter.vue` | Progressive list fetch dan user preference | Page-size pills, Load More, shown/total, stored count and scroll | Local storage, resource reload | **IGNORE** — preserve existing numbered paginator/query preservation. |
| FRP-020 | Desktop split ticket workspace | `TicketAgent.vue`, `TicketActivityPanel.vue`, `TicketSidebar.vue`, `Resizer.vue` | Conversation-first work area dengan metadata rail | Independent scroll, tabs main, resizable bordered side panel | Vue provide/inject, ticket resources | **ADAPT** — strong hierarchy for UI-008, but six variants/data projections/actions remain separate. |
| FRP-021 | Dedicated mobile ticket-detail component/tabs | `MobileTicketAgent.vue`, `TicketCustomer.vue` | Reprioritize detail for small viewport | Separate component; status/team/actions compact; Details/Activity tabs; sticky composer | Vue route-time component selection/state | **ADAPT** — layout concept useful; server-rendered alternatives and all authorized actions must remain reachable. |
| FRP-022 | Status/action cluster in ticket header | `ticket-agent/TicketHeader.vue`, mobile ticket header | Menempatkan current state dan frequent/overflow actions | Status dropdown, normal/grouped/ellipsis actions, breadcrumbs, viewers; compresses mobile | Customizations, status API, Vue | **ADAPT** — render only existing controller policy booleans; no universal status dropdown or custom actions. |
| FRP-023 | Filterable multi-type activity timeline | `TicketActivityPanel.vue`, `TicketAgentActivities.vue`, `EmailArea.vue`, `CommentBox.vue`, `HistoryBox.vue` | Menyatukan chronology sambil mempertahankan event type | Activity/email/comment/call/analytics tabs; distinct renderers; timeline rail; type empty states | Resource data, tabs, telephony | **ADAPT** — map only existing SIHATI public/internal/system events; no call/analytics/reaction features. |
| FRP-024 | Explicit public reply vs internal comment composers | `CommunicationArea.vue`, `EmailEditor.vue`, `CommentTextEditor.vue` | Mencegah ambiguity audience | Two exclusive toggles, distinct icon/label/editor, separate submit functions | Vue refs/state, separate APIs/editors | **ADOPT** — concept matches SIHATI public/internal separation; endpoints, fields, visibility, attachment policy remain separate. |
| FRP-025 | Collapsible composer + keyboard submit/discard | `CommunicationArea.vue`, `TicketTextEditor.vue`, `CompactEditor.vue` | Menghemat ruang sampai user siap menulis | Collapsed prompt expands; Ctrl/Cmd+Enter submit; Escape/click-outside close; loading/disabled | Vue state, Tiptap, VueUse | **ADAPT** — lifecycle/reset/old/error semantics differ and must preserve SIHATI baseline. |
| FRP-026 | Attachment chip and preview dialog | `AttachmentItem.vue`, `AttachmentList.vue`, ticket editors/messages | Menampilkan file secara compact dan recognizable | File-type icon + filename; preview image/video/text; other files open URL; wraps mobile | MIME library, fetch, Frappe Dialog | **ADAPT** — use existing authorized download URL and visibility condition; no client fetch bypass. |
| FRP-027 | Collapsible metadata sections in side panel | `Section.vue`, `TicketDetailsTab.vue` | Mengelompokkan overview/custom/recent data dengan progressive disclosure | Sticky small heading, chevron, divider, stored open state; scrollable rail | Vue state/local storage | **ADOPT** — grouping concept useful, but section visibility/data source must be server-authorized. |
| FRP-028 | Inline editable ticket metadata | `TicketField.vue`, `TicketDetailsTab.vue`, `AssignTo.vue`, status dropdown | Mempercepat field update tanpa form page | Ghost inputs/dropdowns inside read layout; save on blur/selection | Direct `setValue`, metadata API, client auth | **IGNORE** — changes SIHATI form/action/validation/audit workflow and can bypass frozen contracts. |
| FRP-029 | Dynamic conditional field grid | `TicketNew.vue`, `UniInput.vue`, `formCustomisation.ts` | Render template-defined fields dan dependencies | 1→3 columns, type-specific controls, conditional visibility/required/readonly | API metadata, dynamic Vue component, executable scripts | **ADAPT** — layout/anatomy only; existing SIHATI dynamic field names/options/old/errors/backend logic remain. |
| FRP-030 | Subject-driven knowledge suggestion | `TicketNew.vue`, `SearchArticles.vue` | Deflect ticket creation dengan recommended articles | Suggestions appear after subject threshold; customer-only | Knowledge base APIs/content | **IGNORE** — knowledge base is out of SIHATI scope and would be feature expansion. |
| FRP-031 | Standard dialog anatomy + focus/loading | Frappe UI `Dialog.vue`, `dialogs.jsx`, `ConfirmDialog.vue`, ticket dialogs | Consistent modal structure and safe submission feedback | Responsive max-width, title/close/body/footer, focus scope/return, Escape, loading, destructive theme | Reka/Frappe UI | **ADOPT** — anatomy/a11y principle; existing modal IDs/data/form/auto-open/reset behavior must remain exact. |
| FRP-032 | Responsive KPI/chart dashboard composition | `dashboard/Dashboard.vue`, `SkeletonLoader.vue`, chart components | Ringkas health/work metrics dan filters | KPI grid, responsive charts, horizontal filters, per-chart skeleton/empty state | Frappe chart/resource APIs | **ADAPT** — composition only; SIHATI six dashboard variants/data/filter/export remain server-defined. |
| FRP-033 | Drag/resize/user-customizable dashboard | `home/Home.vue`, home chart components | Personalisasi chart layout per agent | Edit/save/reset/add/remove, grid drag/resize, local/server persistence | Frappe GridLayout, resource APIs | **IGNORE** — new product capability and persistence contract. |
| FRP-034 | Frappe settings as large split-pane modal | `SettingsModal.vue`, `SettingsLayoutBase.vue`, Settings components | Menyatukan account/system configuration dalam one overlay | Bare 5xl dialog, fixed-height sidebar/content split, sticky groups | Vue dynamic tabs, modal state, Frappe settings APIs | **IGNORE** — SIHATI admin routes, URLs, error contexts, and permission screens must remain independent. |
| FRP-035 | Search/filter/list CRUD management pattern | `Settings/Agents.vue`, `Customers.vue`, `Contacts.vue`, `ListViewBuilder.vue` | Consistent admin collection management | Header Create, search/filter row, compact records, badges, kebab, empty/loading; dialog create | Vue resources/dialogs | **ADAPT** — useful for UI-012/016–022; keep named routes, forms, pagination, modal targeting, policy. |
| FRP-036 | Explicit contextual empty/loading/not-found states | `EmptyState.vue`, `SkeletonLoader.vue`, `InvalidPage.vue`, ticket/list/dashboard states | Menjelaskan state dan next action | Icon/title/description/action; contextual copy; skeleton preserves layout; responsive centering | Vue conditional rendering/resources | **ADOPT** — anatomy and contextuality carry well; exact HTTP/error/empty meaning stays SIHATI. |
| FRP-037 | Real-time viewers, typing, live update toast | `composables/realtime.ts`, `socket.ts`, ticket pages, `TypingIndicator.vue` | Collaboration awareness dan live refresh | Viewer avatars, typing label, update toast/socket reload | Socket.IO/backend events | **IGNORE** — requires backend/API/realtime feature changes. |
| FRP-038 | Dark theme toggle and persistence | `App.vue`, `Sidebar.vue`, `MobileSidebar.vue`, `index.css` | Theme preference | Light/dark semantic tokens, toggle, stored preference, shadow overrides | Frappe UI theme/local storage | **IGNORE** — new cross-app behavior; not part of current presentation-only reference decision. |
| FRP-039 | Accessible keyboard/listbox/reduced-motion patterns | Frappe UI Dialog/Tabs; `command-palette/**`; `view-controls/filter/**`; `index.css` | Keyboard/focus/announcement resilience | Semantic roles, focus return, shortcuts, live region, reduced motion; varies by component/mobile | Reka/Frappe UI and Vue handlers | **ADOPT** — principles required; reimplement and test against SIHATI generic modal/nav/forms, not copy library markup. |
| FRP-040 | Toast and action loading feedback | `main.js`, resources throughout tickets/lists/dialogs/dashboard | Menjelaskan success/error/progress dan mencegah repeated action | Semantic toast, button spinner/disabled, online/offline feedback, some background progress | Frappe UI resource lifecycle/toast/socket | **ADAPT** — align with existing flash/SweetAlert/global submit locks; no new resource lifecycle. |

Catalog total: **40 patterns — 8 ADOPT, 20 ADAPT, 12 IGNORE**.

## Architecture Differences

| Concern | Frappe | SIHATI frozen direction | Audit decision |
|---|---|---|---|
| Rendering | Vue SPA | Laravel Blade server-rendered | Never port component/runtime architecture. |
| Routing | Vue Router/history and route meta | Existing Laravel routes, names, URLs, methods, aliases | Preserve all routing contracts. |
| Data/state | Client resources, Pinia, cache, local storage | Controllers/services/session/query string/Blade | Translate visual state only. |
| Authorization presentation | Client flags such as `isAgent`, `isManager`, `isAdmin`, portal meta | Policy/Gate/middleware/DomainAuthorization/controller booleans, six roles | Never infer from Frappe or role label. |
| Forms | `v-model`, JSON params, direct resource calls | HTML form action/method/CSRF/spoofing/name/old/error/redirect/flash | Preserve exact HTML/form contract. |
| Dynamic behavior | Metadata-driven components and executable form script | Existing service field definitions and backend validation | Layout may change; logic may not. |
| List behavior | API columns, client filters/sorts, saved views, load more | Server query/filter/sort/paginator | Do not add client list architecture. |
| Ticket mutation | Direct `setValue`, custom actions, bulk APIs | Existing workflow services and modal/action routes | No inline or bulk expansion. |
| Communication | Email/comment APIs + realtime | Public/internal/requester routes and policy visibility | Visual primitive may share; endpoints/data never merge. |
| Responsive | Route-selected components plus CSS | Existing Blade markup/partials and responsive CSS/JS | Different hierarchy is possible later, but contract and actions remain. |
| Settings | Large client-side split modal | Independent named admin routes/screens | Ignore settings architecture. |

## Licensing Boundary

Frappe Helpdesk adalah external open-source reference dengan AGPLv3 license text pada inspected repository. Frappe UI juga merupakan external submodule/dependency. Pada fase ini SIHATI hanya mempelajari:

- visual language;
- layout and density principles;
- interaction concepts;
- information hierarchy;
- accessibility patterns.

Boundary yang wajib:

1. Tidak ada Frappe/Vue component, source code, CSS token, Tailwind preset, icon, font file, screenshot, atau asset yang disalin/vendor ke SIHATI.
2. Tidak ada Frappe UI dependency atau SPA runtime yang ditambahkan.
3. Semua future implementation harus ditulis ulang native untuk stack SIHATI dan tunduk pada contract freeze.
4. Jika kelak ada keinginan menggunakan source Frappe/Frappe UI secara langsung, itu adalah keputusan licensing/compliance terpisah yang memerlukan review hukum/teknis; audit ini bukan persetujuannya.
5. Repository URL, branch, SHA, date, license, dan source paths dicatat agar provenance audit transparan.

## Findings

1. Nilai terbesar Frappe bukan “look” tertentu, melainkan hierarchy workbench: compact header, conversation-first detail, metadata rail, dan contextual states.
2. Neutral, border-led surfaces mengurangi visual noise pada aplikasi helpdesk padat dan dapat diadopsi tanpa menyalin palette.
3. Status/priority dengan icon + label adalah pola aman dan accessible; canonical SIHATI values tetap dipertahankan.
4. Advanced filter UI berguna sebagai reference, tetapi SIHATI harus tetap server-side dengan query/pagination existing.
5. Dedicated mobile hierarchy pada ticket detail sangat relevan, tetapi semua action yang authorized harus tetap reachable dan enam variants tidak boleh flatten.
6. Pemisahan Reply vs Comment memperkuat kebutuhan SIHATI untuk menjaga public/internal communication sebagai contract berbeda.
7. Dialog anatomy Frappe kuat, tetapi merupakan area berisiko karena modal IDs, auto-open, error context, dan frozen defects SIHATI.
8. Dynamic form layout dapat diadaptasi; executable client scripts, direct `setValue`, inline edit, and metadata APIs harus diabaikan.
9. Dashboard fixed composition berguna; customizable agent home, client persistence, saved views, bulk actions, realtime, KB, telephony, and settings modal adalah feature expansion dan harus diabaikan.
10. Source memperlihatkan a11y strengths sekaligus inconsistencies; visual similarity tidak menjamin focus/keyboard semantics. Manual baseline dan regression tetap prerequisite.
11. Tidak ada satu role model Frappe yang dapat menggantikan policy-driven six-role SIHATI; Super Admin, current Approver, dan Team Chair adalah caveat desain utama.
12. Audit siap menjadi input mapping dan decision register, tetapi bukan design-system specification. Redesign implementation masih `HOLD` sampai manual visual baseline gate pada `13-BASELINE-READINESS-GATE.md` dipenuhi.
