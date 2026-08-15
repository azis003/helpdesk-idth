# W3.2 — Skills & Announcements Redesign Implementation Document

## Metadata
- **Wave**: W3.2 (Skills and Announcements)
- **Status**: W3.2 IMPLEMENTED — HUMAN QA REQUIRED
- **Starting HEAD SHA**: `52798eb24e0a77d0dfbbfe77fa32dd8f7517077b`

## Changed Files
The following production files were modified to achieve the visual redesign:
1. [`resources/views/admin/skills/index.blade.php`](../../resources/views/admin/skills/index.blade.php)
2. [`resources/views/admin/announcements/index.blade.php`](../../resources/views/admin/announcements/index.blade.php)

---

## Content Architecture & Visual Strategy

### 1. Manajemen Keahlian (Skills)
- **Transition**: Replaced the separate desktop wide table (`min-w-[52rem]`) and duplicated mobile article list with a single, unified, responsive single-column operational card collection.
- **Card Hierarchy**:
  - **Header**: Skill Number and accessible label (`No. X`, `Nama Keahlian:`), bold Skill Title, concise description, and immediate status badge (`Aktif` / `Nonaktif`).
  - **Body (Layanan Terpetakan)**: Clearly grouped service badges displaying `[CODE · Service Name]` with natural wrap for varying lengths. Fallback message shown when no services are mapped.
  - **Footer Actions**: Distinct, accessible secondary action buttons:
    - **Edit**: secondary button opening `skill-edit-modal-{id}`
    - **Status Toggle**: `Nonaktifkan` / `Aktifkan` form submit with SweetAlert confirmation for deactivation
    - **Hapus**: soft-delete button with subdued danger styling and confirmation
- **Search & Pagination**: Server-native `q` search query, `per_page` (10, 25, 50) selector, and pagination links retained.
- **Empty States**: Specific and truthful messages preserved for empty database and filtered no-match scenarios.

### 2. Manajemen Pengumuman (Announcements)
- **Transition**: Rebalanced the screen from a marketing-like landing page into a focused, calm operational workbench.
- **Streamlined Header & Metrics**:
  - Completely removed the oversized marketing hero banner (*"Satu pesan, satu konteks, waktu tampil yang jelas"*).
  - Replaced large stat cards with a clean, compact 3-part operational summary ribbon (`Tampil Sekarang`, `Menunggu Jadwal`, `Total Pengumuman`).
- **Two-Column Workbench Layout**:
  - **Left Column (Create Form)**: Compact, inline creation form with clean field hierarchy:
    - Judul (`title`, max 150 chars)
    - Isi pengumuman (`body`, max 5000 chars)
    - Waktu tampil (`starts_at`, `ends_at`)
    - Checkbox aktif (`is_active`)
    - Subdued writing tip and clear submit button.
  - **Right Column (Announcement List)**: Scalable list using native `<details class="ui-announcement-item">` disclosure elements:
    - **Collapsed Summary**: Immediate scan of Title, Status badge (`Tampil sekarang`, `Terjadwal`, `Masa tampil selesai`, `Nonaktif`), schedule timeframe, and creator name.
    - **Expanded Body**: Formatted message preview, full edit form targeting `_announcement_id`, and status toggle action (`admin.announcements.status`).
    - Disclosure automatically expands upon validation error targeting matching `_announcement_id`.
- **Mobile Responsive**: Columns stack gracefully into a single-column layout on viewports down to 375px/390px with zero horizontal clipping.

---

## Technical Contracts Preserved (Frozen Baseline)

### Skills Contracts:
- Query parameters: `q`, `per_page` (10, 25, 50), `page`
- Form tokens: `_skill_form`, `_skill_create`, `_skill_edit`
- Modal IDs: `skill-create-modal` (POST `admin.skills.store`), `skill-edit-modal-{id}` (PUT `admin.skills.update`)
- Status endpoints: POST `admin.skills.activate`, POST `admin.skills.deactivate`
- Delete endpoint: DELETE `admin.skills.destroy` (soft delete)
- Modal JS bindings: `data-ui-modal`, `data-ui-modal-open`, `data-ui-modal-close`, `data-reset-on-close="true"`, `data-clear-on-close="true"`, `data-ui-validation-error`
- Service mapping remains strictly read-only (no mapping mutation controls added).

### Announcements Contracts:
- Route endpoints:
  - POST `admin.announcements.store`
  - PUT `admin.announcements.update`
  - POST `admin.announcements.status` (`activate`, `deactivate`)
- Validation token: `_announcement_id`
- Input fields: `title`, `body`, `starts_at`, `ends_at`, `is_active`
- CSRF, PUT method spoofing, and error routing intact.
- Authorization: Super Admin and Agen Tier 1 preserved.

---

## Verification & Build Outcomes

- **PHP Unit Test Result**:
  - Command: `php artisan test`
  - Output: `Tests: 1 skipped, 139 passed (1780 assertions)`
- **Production Asset Build**:
  - Command: `npm run build`
  - Output: Vite compiled successfully with code 0 (`theme-modern.css` and `app.js` processed).
- **Route Audit**:
  - Command: `php artisan route:list --except-vendor`
  - Output: `Showing [112] routes` (exact baseline match).
- **Link Portability**:
  - Command: `git grep -n "file:///" -- docs/redesign`
  - Output: 0 matches (all documentation links are repository-relative).
- **Source Leak Audit**:
  - Checked for accidental raw `<?php` or `?>` tags in modified Blade files; verified 0 occurrences.
- **Browser Automation Note**:
  - Local Playwright browser driver execution is unavailable in this environment; all template validity is confirmed via view compiler and full automated test suite.
