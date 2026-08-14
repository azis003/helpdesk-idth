# Redesign Guardrails

Dokumen ini adalah pagar kerja untuk redesign presentation layer aplikasi helpdesk. Baseline disusun dari source code repository per 13 Agustus 2026: route, controller, Form Request, policy, service/domain workflow, Blade, CSS, JavaScript, model/enum, serta PRD `docs/PRD-helpdesk-internal.md`.

## Status dokumen

- Fase saat ini: **FASE 2 — REDESIGN BASELINE & CONTRACT FREEZE**.
- Perubahan repository yang diperbolehkan pada fase ini hanya dokumentasi di `docs/redesign/`.
- Dokumen ini tidak memberi izin untuk mengubah implementasi.
- Bila dokumentasi dan implementasi berbeda, behavior server yang sedang berjalan tetap menjadi baseline sampai perbedaan tersebut dianalisis dan disetujui secara eksplisit.

## A. Tujuan redesign

Redesign bertujuan untuk:

1. mengganti presentation layer dan meningkatkan UI/UX;
2. mempertahankan seluruh behavior aplikasi yang sekarang;
3. mempertahankan backend, database, API/HTTP contract, authentication, authorization, routing, dan business logic;
4. tidak melakukan perubahan arsitektur aplikasi;
5. tetap menggunakan arsitektur Laravel monolith, server-rendered Blade, dan progressive enhancement JavaScript yang ada;
6. menjaga Bahasa Indonesia sebagai bahasa antarmuka;
7. meningkatkan konsistensi, responsivitas, aksesibilitas, dan kejelasan visual tanpa memperluas hak akses atau mengubah workflow.

Redesign **bukan** kesempatan untuk mengubah state machine tiket, menyederhanakan policy di sisi client, mengganti route, mengubah payload, atau memindahkan kewenangan server ke Blade/JavaScript.

## Baseline arsitektur yang dipertahankan

| Area | Baseline |
|---|---|
| Aplikasi | Laravel 12 monolith |
| Rendering | Multi-page server-rendered Blade |
| Build asset | Vite 6 |
| Styling | Tailwind CSS 4, `resources/css/app.css`, dan `resources/css/theme-modern.css` |
| Interaksi client | Vanilla JavaScript di `resources/js/app.js` |
| Navigasi | Livewire 3 navigation hook pada layout; tidak ada PHP Livewire component atau `wire:*` aktif |
| Feedback/confirmation | SweetAlert2 |
| HTTP data | Form submit dan page navigation; `fetch()` hanya untuk unduhan report; Axios di-bootstrap tetapi tidak digunakan oleh layar aktif |
| Authentication | Laravel session authentication kustom |
| Authorization | Middleware, Gate/Policy, dan `DomainAuthorization` di server |
| API | Tidak ada `routes/api.php`; route web adalah contract HTTP aktif |

## Invariant domain yang tidak boleh berubah

### Role kanonis

- Super Admin (`super_admin`)
- Pemohon (`pemohon`)
- Agen Tier 1 (`agen_tier_1`)
- Agen Tier 2 (`agen_tier_2`)
- Approver (`approver`)
- Ketua Tim Kerja (`ketua_tim_kerja`)

Nama role tidak boleh dipakai sebagai asumsi kewenangan. Policy, middleware, current approver assignment, team scope, dan domain service adalah sumber keputusan.

### Status tiket kanonis

- Baru
- Diproses
- Dikerjakan
- Menunggu Persetujuan
- Menunggu Pemohon
- Menunggu Pihak Ketiga
- Menunggu Konfirmasi
- Ditutup
- Ditolak
- Tidak Disetujui
- Dibatalkan

### Prioritas kanonis

- Kritis
- Tinggi
- Sedang
- Rendah

Label, warna, urutan visual, atau grouping dapat didesain ulang kelak, tetapi value, transisi, dan makna domain tidak boleh berubah.

## B. Area yang boleh diubah pada fase implementasi redesign

Setelah contract freeze disetujui, perubahan harus tetap berfokus pada presentation source:

- `resources/views/` — struktur visual Blade, dengan semua contract form, visibility, route, dan data tetap terjaga;
- `resources/css/` — token, typography, spacing, layout, responsive rules, focus states, dan visual treatment;
- `resources/js/` — hanya behavior presentasional/progressive enhancement yang diperlukan oleh UI baru;
- asset visual — logo presentation, icon, ilustrasi, gambar, dan font yang tidak mengubah contract branding/storage.

Catatan penting:

- Daftar di atas adalah batas umum untuk fase implementasi mendatang, **bukan** izin perubahan pada Fase 2.
- Refactor markup hanya aman setelah setiap contract pada `02-UI-CONTRACT-MATRIX.md` dipertahankan atau dimigrasikan secara terkontrol.
- Perubahan JavaScript harus tetap progressive enhancement; policy dan validasi tidak boleh bergantung pada client.
- Asset visual tidak boleh mengubah endpoint `branding.logo`, storage semantics, file authorization, atau branding version history.

## C. Area locked

Semua area berikut berada di luar scope UI redesign dan tidak boleh diubah sebagai bagian redesign.

| Area locked | Contract yang harus dipertahankan |
|---|---|
| `routes/web.php` | Seluruh URI, HTTP method, route name, route parameter, middleware, order, constraint, dan binding. |
| Route name | Semua nama yang dipanggil oleh Blade/controller/tests, termasuk compatibility aliases. |
| URL | Path dan query semantics, termasuk `from`, `q`, `per_page`, `page`, `tab`, `month`, `start_date`, dan `end_date`. |
| HTTP method | GET/POST/PUT/DELETE serta method spoofing pada form. |
| Route aliases | `tickets.complete`/`tickets.resolve`, `tickets.confirm`/`tickets.requester-confirm`, `tickets.not-satisfied`/`tickets.confirmation.not-satisfied`, dan compatibility GET pada katalog/report. |
| Controller contract | Action, parameter binding, view selection, data yang dikirim ke view, response type, redirect, dan flash. |
| Form Request | Field contract, normalization, fallback alias, validation rule, error bag/key, dan authorization. |
| Validation | Required/nullable/array/file/date/enum/exists/unique rules, cross-field validation, dan pesan error. |
| Policy | Semua ability, record scope, owner/assignee/current-approver check, read/write rule, dan deny behavior. |
| Middleware | `guest`, `auth`, `active`, `role:*`, CSRF, session, binding, dan middleware framework lain. |
| Gate | Mapping ability ke policy dan hasil pemeriksaan authoritative di server. |
| `DomainAuthorization` | Pemeriksaan Gate, audit denied action, dan exception 403. |
| Model | Attribute, cast, relation, scope, accessor, event, soft delete, dan persistence behavior. |
| Enum | Value dan state kanonis untuk role, status, priority, comment visibility, approval, dan domain lain. |
| Migration/database | Migration, table, column, index, constraint, foreign key, seed semantics, dan database schema. |
| Service/domain workflow | Seluruh transition, orchestration, transaction, side effect, idempotency/concurrency behavior, dan invariant. |
| SLA | Kalender kerja, target, pause/resume, segment, near-limit/overdue/compliance calculation, dan snapshot. |
| Approval | Current active approver, pending assignment, previous state restoration, approve/reject semantics, dan decision note. |
| Waiting state | Menunggu Pemohon, Menunggu Pihak Ketiga, Menunggu Persetujuan, Menunggu Konfirmasi, serta pause/resume SLA. |
| Attachment authorization | Policy, visibility public/internal, requester-accessible flag, MIME/size/count policy, storage, soft-deleted lookup, dan download response. |
| Reporting/export | Filter semantics, data scope, file content, filename, MIME/headers, Excel/PDF response, dan export audit record. |
| Authentication | Credential check, rate limit, session regeneration, logout/invalidation, active-user enforcement, dan intended redirect. |
| Session behavior | Session driver/lifetime, CSRF, old input, error bag, flash, remember semantics, dan navigation state. |
| Safe projection Ketua Tim | `TeamChairTicketProjection`, `TeamScopeService`, explicit allow-list, public-comment-only projection, and attachment denial. |
| Notification | Recipient selection, payload, read/read-all behavior, unread count, link destination, and delivery persistence. |
| Audit logging | Successful/denied action logging, actor/subject/context, report export audit, and history snapshots. |
| Job/queue/scheduler | Job payload, queue behavior, retries, schedule, SLA/notification processing, and side effects. |
| Backend configuration | Service provider, config, environment semantics, storage, mail, queue, cache, and logging. |
| Tests as behavior evidence | Existing feature/unit tests must not be weakened, removed, or rewritten merely to accommodate a redesign regression. |

### Locked role semantics

- **Super Admin tidak otomatis operasional.** Admin route diperoleh dari `role:super_admin`, tetapi operasi tiket tetap ditentukan oleh `TicketPolicy`. Pure Super Admin tidak lolos `TicketPolicy::viewAny`, meskipun `TicketPolicy::view` mengizinkan melihat tiket tertentu dan attachment policy memberi visibility tertentu.
- **Ketua Tim Kerja selalu read-only.** Kehadiran role ini mengaktifkan read-only override pada ticket/approval/report behavior, termasuk pada akun multi-role.
- **Approver harus aktif dan merupakan current active approver.** Label role saja tidak cukup.
- **Agen Tier 2 hanya melihat/mengubah tiket yang ditugaskan kepadanya**, kecuali rule server menyatakan lain.
- **Internal dan public communication tidak boleh dicampur.** Visibility komentar dan attachment tetap domain contract.

### Locked service-specific workflow

- SVC-01 dan SVC-05 mempertahankan kewajiban lokasi sesuai service workflow.
- SVC-02 mempertahankan kewajiban `data_export_result` sebelum completion.
- SVC-03 mempertahankan evidence/execute/verify workflow dan error key terkait.
- SVC-07 mempertahankan internal-field version contract: `internal_fields[...]` dan `internal_field_versions[...]`.

## D. HTML/form contract

Redesign tidak boleh secara tidak sengaja mengubah atau menghilangkan:

- `action` form;
- `method` form;
- token CSRF (`@csrf`);
- method spoofing (`@method('PUT')`, `@method('DELETE')`);
- setiap `name` input, termasuk nested-array syntax;
- hidden input;
- error key dan error bag;
- dependency `old()` dan repopulation setelah validation error;
- query parameter dan nilai default/allow-list-nya;
- route name dan route parameter;
- `data-*` attribute yang dibaca JavaScript;
- modal `id`, `aria-labelledby`, opener target, dan auto-open condition;
- pagination query preservation (`withQueryString()` atau equivalent output);
- download link, filename, dan response headers;
- visibility condition di Blade;
- policy/Gate condition dan server authorization;
- `enctype="multipart/form-data"`, multiple file syntax, `accept`, serta attachment policy grouping;
- submit button value/name bila menjadi bagian payload;
- redirect target, back behavior, fragment/query preservation, dan flash message;
- accessible name, focus restoration, focus trap, dan keyboard close untuk dialog.

### Pengecualian terkontrol

`data-*`, modal markup, atau selector markup boleh berubah hanya jika:

1. seluruh JavaScript terkait diperbarui dalam perubahan yang sama;
2. behavior sebelum dan sesudah terbukti identik;
3. non-JavaScript/server fallback tetap bekerja bila sebelumnya tersedia;
4. auto-open saat validation error, focus management, submit locking, loading feedback, confirmation, dan error clearing tetap teruji;
5. tidak ada perubahan pada payload, authorization, redirect, flash, state transition, atau audit side effect;
6. contract lama tidak masih digunakan oleh partial/layout lain.

Perubahan tersebut harus dicatat sebagai migrasi contract, bukan dianggap perubahan kosmetik bebas risiko.

## Contract routing yang wajib dipertahankan

### Compatibility aliases aktif

| Behavior | Primary/current route | Compatibility alias |
|---|---|---|
| Menyelesaikan tiket | `POST tickets.complete` | `POST tickets.resolve` |
| Konfirmasi Pemohon | `POST tickets.confirm` | `POST tickets.requester-confirm` |
| Tidak puas | `POST tickets.not-satisfied` | `POST tickets.confirmation.not-satisfied` |
| Laporan bulanan | `GET reports.index` (`/reports`) | `GET reports.monthly` (`/reports/monthly`) |
| Layanan | `GET admin.services.index` | `GET admin.catalog.index?section=services` merender screen yang sama |
| Lokasi | `GET admin.locations.index` | `GET admin.catalog.index?section=locations` mendelegasikan ke controller yang sama |
| Form catalog | `GET admin.forms.index` | Redirect ke `admin.services.index`, query `service` dipertahankan |
| Attachment catalog | `GET admin.catalog.index?section=attachments` | Redirect ke `admin.services.index` |

`tickets.handle` tetap merupakan endpoint aktif di route/controller meskipun tidak ditemukan trigger pada UI aktif. Endpoint ini tidak boleh dihapus atau diaktifkan dari redesign tanpa keputusan produk/domain terpisah.

## Kondisi visibility yang harus tetap authoritative

Blade boleh menyembunyikan atau menampilkan control sebagai affordance, tetapi:

- keputusan final selalu terjadi di middleware/Gate/Policy/`DomainAuthorization`;
- control tersembunyi bukan mekanisme keamanan;
- control tidak boleh ditampilkan hanya karena label role terlihat cocok;
- daftar data harus tetap berasal dari query/projection server yang sudah terscope;
- redesign tidak boleh mengambil data sensitif lalu sekadar menyembunyikannya dengan CSS/JavaScript;
- 403/404/validation/concurrency response harus tetap ditangani sebagai state aplikasi yang nyata.

## Klasifikasi risiko redesign

| Level | Definisi |
|---|---|
| LOW | Presentasi statis/shared visual; sedikit atau tanpa payload/authorization branch. |
| MEDIUM | Screen umum dengan filter, pagination, CRUD sederhana, atau reusable interaction yang contract-nya stabil. |
| HIGH | Screen berisi mutation, role-specific visibility, versioned/dynamic form, export, attachment, atau multi-modal interaction. |
| CRITICAL | Ticket detail dan workflow dinamis yang menggabungkan policy, state machine, role, attachment, approval, SLA, service-specific rules, hidden input, old/error routing, dan banyak JavaScript handler. |

## Checklist wajib untuk setiap perubahan redesign mendatang

### Sebelum perubahan

- Tentukan UI ID dari `01-UI-INVENTORY.md`.
- Tandai contract ID terkait dari `02-UI-CONTRACT-MATRIX.md`.
- Tandai role/data projection dari `04-ROLE-VARIANT-MATRIX.md`.
- Pastikan file berada dalam presentation scope.
- Catat modal ID, `data-*`, input name, old/error dependency, query, dan aliases yang terdampak.

### Sesudah perubahan

- Route name/URI/method tidak berubah.
- Semua request payload sama.
- Semua validation error kembali ke field/modal yang benar.
- Semua policy-visible action cocok dengan hasil server.
- Pagination mempertahankan query.
- File upload/download dan internal/public visibility tetap benar.
- Semua role utama dan akun multi-role diuji.
- Desktop, mobile, keyboard, focus, error, empty, dan loading state diuji.
- Existing backend/feature tests tetap lulus; tambahkan regression coverage bila markup/JS contract dimigrasikan.

## Aturan keputusan bila ditemukan konflik

Urutan sumber kebenaran untuk behavior adalah:

1. policy/middleware/Gate dan `DomainAuthorization` untuk authorization;
2. domain service/state machine untuk workflow dan side effect;
3. Form Request untuk payload/validation/normalization;
4. controller untuk view data, redirect, flash, dan response;
5. route untuk URI/name/method/alias;
6. safe projection/query scope untuk data visibility;
7. Blade/JavaScript untuk presentation dan progressive enhancement;
8. PRD untuk intent produk yang harus diverifikasi terhadap implementasi aktual.

Jika ada ketidaksesuaian, jangan “memperbaiki” backend di dalam scope redesign. Catat sebagai temuan terpisah dan minta keputusan sebelum implementasi.
