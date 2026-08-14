# W2.1 Authenticated Shell Structure and Branding Implementation Report

## Current Status
* **W2.1 Authenticated Shell & Branding**: `W2.1 CORRECTIVE IMPLEMENTATION COMPLETE — HUMAN BROWSER VERIFICATION REQUIRED`
* **W2.2 Mobile Shell & Drawer Redesign**: `NOT STARTED`

---

## 1. Objective and Scope
This report documents the implementation of the authenticated desktop shell structure and brand identity system (Task W2.1) on the `redesign-frontend` branch.

In accordance with the principle of **"Ubah wajahnya, jangan bongkar mesinnya" (Change the face, keep the engine)**, all backend logic, routing, role-based authorization predicates, and baseline test assertions have been preserved intact. No mobile drawer redesign, ticket details redesign, or W3-W6 features have been introduced.

---

## 2. Project Context & Scope Boundaries
* **W1 baseline SHA**: `b1e8045878c386c758821d978a5e52aa52bfc5fb`
* **Initial W2.1 commit**: `0c68ebd0c05f35897fc0cc866f80e09cc51be72c`
* **Files in W2.1 production scope**:
  - [layouts/app.blade.php](file:///c:/Users/Personal/Herd/helpdesk-idth/resources/views/layouts/app.blade.php)
  - [app.css](file:///c:/Users/Personal/Herd/helpdesk-idth/resources/css/app.css)
  - [theme-modern.css](file:///c:/Users/Personal/Herd/helpdesk-idth/resources/css/theme-modern.css)
* **JS Behavior Confirmation**:
  - `resources/js/app.js` is confirmed **unchanged** to preserve the frozen baseline JS interface contract.

---

## 3. Structural & Layout Changes

### 3.1 Desktop Sidebar Branding Header
The brand header has been relocated directly into the desktop sidebar as the primary header block:
* **Brand Container**: `<div class="ui-sidebar-header">` sits at the top of the sidebar.
* **Proportional Branding (Expanded)**: 
  - If a rectangular logo URL is provided, it is rendered with height-constrained proportional sizing (`height: 2.75rem`, `width: auto`, `max-width: 6.5rem`, `object-fit: contain`) preventing distortion or forcing it into a square box.
  - Fallback square monogram/logo uses a sleek, custom styled `.ui-brand-mark` with `height: 2.75rem`, `width: 2.75rem`, and correct centered text alignment.
* **Collapsed Monogram Branding**:
  - To prevent forcing a wide institutional logo into a small collapsed square, the monogram is rendered next to the logo with the class `.ui-sidebar-brand-collapsed-monogram` and `aria-hidden="true"`.
  - In expanded state: `.ui-sidebar-brand-collapsed-monogram` is hidden (`display: none`), showing the proportional logo.
  - In collapsed state: `.ui-sidebar-brand-logo` is hidden (`display: none`), showing the centered monogram.
  - If no logo exists, the monogram remains the identity in both states.
* **Identity Block**: Shows the application name (`application_name`) and organization name (`organization_name`) in a stacked typography block.

### 3.2 Shell Geometry and Flex Layout
The shell layout has been updated to remove legacy conflicting geometry (`padding-top: 4.75rem` on `.ui-content-shell` and `15rem` sidebar width):
* **Sidebar Sizing**:
  - Expanded width: exactly `16rem` / `256px`.
  - Collapsed width: exactly `4.5rem` / `72px`.
* **Workspace Consumption**:
  - Sized via standard flex growth: `flex: 1`, `min-width: 0` inside `.ui-shell.lg:flex`.
  - Consumes remaining viewport width naturally without using margins, absolute offsets, transform hacks, or window calculations.
* **Topbar**:
  - Moved from a viewport-spanning `fixed inset-x-0` position to a local `sticky top-0 z-30` header within the workspace column (`.ui-content-shell`).
  - Height is constrained to `4.5rem` (approximately `72px`).
  - Base class `.ui-topbar` has been normalized in `app.css` to `min-height: 4.5rem;`.
  - Hides the large duplicate application branding on desktop (`lg:hidden` on `.ui-topbar-brand`).
  - Layout: `[Toggle Button] [Page Title]` on the left; utilities on the right.

### 3.3 Sidebar Collapse and State Transitions
* **Persistence Contract**: Preserved the contract utilizing the `sihati.sidebar.collapsed` key in `localStorage` parsed by the existing `resources/js/app.js` module.
* **Transitions**: Left and right paddings transition smoothly.
* **Collapsed Mode Layout**:
  - Collapses navigations into centered icon grids using `margin-inline: auto` and `width: 2.75rem`.
  - Direct child `<nav>` selector styles are applied cleanly via `.ui-sidebar.is-collapsed > nav`.
  - Labels and subtitles (`.ui-sidebar-brand-text`, `.ui-sidebar-label`, `.ui-nav-label`) are hidden (`display: none` or `max-width: 0`) without overflowing or clipping the workspace.

---

## 4. Conformance & Test Verification

### 4.1 Automated Test Execution
* **Pre-correction php artisan test**:
  - Total: `140`, Passed: `139`, Skipped: `1` (concurrency test), Failed: `0`, Errors: `0`, Assertions: `1780`.
  - Status: PASS
* **Post-correction php artisan test**:
  - Total: `140`, Passed: `139`, Skipped: `1` (concurrency test), Failed: `0`, Errors: `0`, Assertions: `1780`.
  - Status: PASS

### 4.2 Build and Cache Compilation
* **php artisan view:cache**:
  - Command output: `Blade templates cached successfully.`
  - Exit code: `0`
* **npm run build**:
  - Output: Compiled successfully. CSS files (`app-*.css`, `theme-modern-*.css`) and JS bundle (`app-*.js`) generated without errors.
  - Exit code: `0`
* **git diff --check**:
  - Exit code: `0` (no whitespace anomalies or check conflicts).

---

## 5. Human Browser Verification Checklist & QA Requirements
> [!WARNING]
> Actual human browser QA has **NOT** yet been completed because a controllable browser is unavailable for runtime validation.

Manual QA verification must be conducted on the following items:
* [ ] **Expanded Sidebar (>= 1024px)**: Sidebar width is exactly `256px`. Brand logo has height `44px` (`2.75rem`) and auto width. Topbar sticky header contains only the toggle and page title.
* [ ] **Collapsed Sidebar (>= 1024px)**: Toggle collapses sidebar to exactly `72px` (`4.5rem`). Monogram/logo is perfectly centered. Sidebar labels and titles are completely hidden.
* [ ] **Responsive Transition**: Content column adapts naturally without visual shift, overlap, or scrollbars when resizing the browser between `1024px` and wider screen dimensions.
* [ ] **Keyboard Focus-Visible**: Tab navigation displays a visible ring on interactive elements.
* [ ] **Mobile Layout Smoke Test (768px & 390px)**: Because W2.1 relocated topbar positioning globally from `fixed` to `sticky` within the workspace container, verify that topbar layout, headers, and buttons are fully aligned and function correctly on smaller viewports.
