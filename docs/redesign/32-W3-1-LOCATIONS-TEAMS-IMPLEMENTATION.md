# W3.1 — Locations & Teams Redesign Implementation Document

## Metadata
- **Wave**: W3.1 (Locations and Teams)
- **Status**: W3.1 IMPLEMENTED — HUMAN QA REQUIRED
- **Starting HEAD SHA**: `66cf2965029a7b4ca743e1144623d83b4a8a4926`

## Changed Files
The following production files were modified to achieve the visual redesign:
1. [`resources/views/admin/locations/index.blade.php`](../../resources/views/admin/locations/index.blade.php)
2. [`resources/views/admin/locations/_building-form.blade.php`](../../resources/views/admin/locations/_building-form.blade.php)
3. [`resources/views/admin/locations/_floor-form.blade.php`](../../resources/views/admin/locations/_floor-form.blade.php)
4. [`resources/views/admin/teams/index.blade.php`](../../resources/views/admin/teams/index.blade.php)

---

## Content Architecture & Visual Strategy

### 1. Locations Redesign
- **Transition**: Replaced the wide traditional 62rem-wide table and the redundant duplicate mobile listing with a single unified card-based operational workbench.
- **Desktop Strategy**: Grouped each building inside a simplified vertical card structure:
  - **Header**: Building name, floor count, and building status badge.
  - **Body**: Daftar Lantai title and floors collection (arranged as a 2-column grid on desktop).
  - **Footer**: Building-level action footer containing Tambah lantai, Edit, and Status toggle buttons.
- **Mobile/Responsive Strategy**:
  - Leveraged Tailwind flex/grid styling to ensure a clean visual priority flow on mobile screens:
    1. **Building name/status** first (in the card header).
    2. **Floor lists** second (body).
    3. **Building actions** last (footer).
  - All actions wrap elegantly and buttons are padded for easy touch-targets (390px safe).

### 2. Teams Redesign
- **Transition**: Restructured the team index layout from a database spreadsheet-like table into a clean, modern card grid.
- **Desktop/Tablet Strategy**:
  - Each card represents an operational team record showcasing the structural overview.
  - **Header**: Displays the team name and status badge, alongside the edit modal trigger and screen-reader action labels.
  - **Description**: Displays the team description, with an italicized fallback if empty.
  - **Ketua Tim (Chair)**: Clear dedicated slot displaying the chair's name, `@username`, and a circular initials avatar block.
  - **Anggota (Members)**: Compact summary slot indicating the count. Clicking opens a scrollable, read-only list modal.
- **Mobile Strategy**: Grid columns collapse to a single column list stack. All information (chair avatar, @username, description text, and member count) wrap naturally with no horizontal scroll.

---

## Technical Contracts Preserved (Frozen Baseline)

We preserved all literal endpoints and HTML selectors to ensure zero functional disruption:
- Query parameters: `q` (search) and `per_page` (size options: 10, 25, 50).
- Location modals:
  - Create Building: `location-building-create-modal` (POST `admin.catalog.buildings.store`)
  - Edit Building: `location-building-edit-modal-{id}` (PUT `admin.catalog.buildings.update`)
  - Create Floor: `location-floor-create-modal-{buildingId}` (POST `admin.catalog.floors.store`)
  - Edit Floor: `location-floor-edit-modal-{id}` (PUT `admin.catalog.floors.update`)
  - Status updates: CSRF protected forms invoking building and floor status endpoints.
- Team modals:
  - Create Team: `team-create-modal` with validation error indicators (`_team_create`) and JS bindings (`data-team-create-open`, `data-team-create-modal`, `data-team-create-form`).
  - Edit Team: `team-edit-modal-{id}` with error tracking (`_team_edit`).
- SweetAlert confirmation handlers: `data-swal-confirm` attributes intact.

### Dormant & Denied Capabilities Intentionally Excluded
- **No Room UI** was added (remains dormant).
- **No Team delete or activate/deactivate toggles** were exposed on the index UI.

---

## Human QA Corrections

### `W3.1-HQA-001` — Locations desktop content hierarchy correction
- **Ambiguous floor action labels corrected**:
  - Restored clear, explicit text labels: **"Nonaktifkan"** for deactivating active floors, and **"Aktifkan"** for activating inactive floors (replacing ambiguous "Nonaktif"/"Aktif" labels).
- **Destructive floor actions visually subdued**:
  - Converted floor status toggle buttons to compact, subtle outline buttons (`bg-transparent hover:bg-50`) to reduce destructive visual dominance across floor rows.
  - Kept edit buttons adjacent and compact.
- **Simplified building card layout**:
  - Reorganized building cards into a clean vertical stack: **Header** (Building name, count, status badge), **Body** (Daftar Lantai header & floors grid list), and **Action Footer** (Building actions block).
  - Completely removed redundant duplicate "Status Gedung" text.
  - Completely removed unnecessary "Diperbarui" timestamp metadata to optimize screen real estate.

### `W3.1-HQA-002` — Teams desktop card density
- **3-column grid caused excessive title wrapping in real data**:
  - Restructured responsive grid density on desktop/large screens from 3 columns to 2 columns on `xl` viewports (`grid-cols-1 xl:grid-cols-2 gap-6`). This ensures long, real-world team names have breathing room.
- **Nested member scroll removed**:
  - Completely removed nested scroll constraints (`max-h-[7.5rem]` and `overflow-y-auto`) from the members container to allow natural wrapping and document flow.
- **Natural card heights retained**:
  - Configured the grid container with `items-start` to size cards naturally according to their content, preventing short cards from visually stretching to match the tallest card in the row.

### `W3.1-HQA-003` — scalable team member access
- **No inline member preview**:
  - The team card now only shows the label **ANGGOTA**, the total count of members, and a **"Lihat semua anggota"** secondary button/link (if count > 0).
  - Keeps cards extremely compact regardless of member count.
- **Scrollable read-only member modal**:
  - Clicking "Lihat semua anggota" opens a presentation-only modal `team-members-modal-{teamId}` showing all team members (excluding the chair).
  - Modal content: Title "Anggota Tim Kerja", context (team name), count (X orang), and all members as a vertical list.
  - Set modal dialog to have `max-h-[85vh]` and the body content to `overflow-y-auto` to handle large teams of 40+ members gracefully.
- **No backend/mutation scope changes**:
  - No new data queries or endpoint changes. Handled strictly as a read-only visual projection.

### `W3.1-HQA-004` — raw Blade/PHP source leak
- **Exposed template source fragments**:
  - Fixed a Blade compiling parse error where inline and block php directives within the second modal loop on the Teams page caused raw code fragments (like `$errors->any()`, `@php`, etc.) to leak visually at the bottom of the page.
- **Consolidated loop calculations**:
  - Consolidated all per-team computations (errors state, chair user object, and filtered members collection) into a single, clean `@php ... @endphp` block right at the start of the `@foreach ($teams as $team)` loop.
  - Eliminated the inline `@php($isEditError = ...)` shorthand and duplicate calculations.
  - Regenerated and verified compiled template syntax successfully.

### `W3.1-HQA-005` — mobile notification dropdown viewport containment
- **Issue found during 375/390px W3.1 responsive smoke**:
  - The notification dropdown in the authenticated topbar used `absolute right-0` positioning relative to the bell trigger. Because the avatar element renders to the right of the bell on mobile, the near-viewport-width panel extended outside the left edge of the viewport, clipping the "Notifikasi" title and content.
- **Mobile geometry made viewport-contained**:
  - Changed dropdown positioning on mobile (<640px) to `fixed left-3 right-3 top-16 w-auto` so the panel stays within ~12px gutters on both viewport edges regardless of trigger position.
  - On tablet/desktop (>=640px), restored `sm:absolute sm:left-auto sm:right-0 sm:top-12 sm:w-[22rem]` to preserve the compact anchored dropdown behavior under the bell icon.
- **No notification contract changes**:
  - All notification queries, routes, CSRF forms, outside-click/Escape behavior, and mutual exclusion logic remain completely untouched.

---

## Verification & Build Outcomes

- **PHP Unit Test Result**:
  - Command: `php artisan test`
  - Output: `Tests: 1 skipped, 139 passed (1780 assertions)`
  - Duration: ~65s
- **Production Asset Build**:
  - Command: `npm run build`
  - Output: Vite compiled successfully with code 0 (`theme-modern.css` and `app.js` processed).
- **Route Audit**:
  - Command: `php artisan route:list --except-vendor`
  - Output: `Showing [112] routes` (exactly matching the baseline).
- **Git Compliance**:
  - Command: `git diff --check`
  - Output: Exited with code 0 (all trailing whitespaces cleaned up).
