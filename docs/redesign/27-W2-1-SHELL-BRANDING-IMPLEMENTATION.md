# W2.1 Authenticated Shell Structure and Branding Implementation Report

## Current Status
* **W2.1 Authenticated Shell & Branding**: `W2.1 IMPLEMENTATION COMPLETE — HUMAN BROWSER VERIFICATION REQUIRED`
* **W2.2 Mobile Shell & Drawer Redesign**: `NOT STARTED`

---

## 1. Objective and Scope
This report documents the implementation of the authenticated desktop shell structure and brand identity system (Task W2.1) on the `redesign-frontend` branch.

In accordance with the principle of **"Ubah wajahnya, jangan bongkar mesinnya" (Change the face, keep the engine)**, all backend logic, routing, role-based authorization predicates, and baseline test assertions have been preserved intact. No mobile drawer redesign, ticket details redesign, or W3-W6 features have been introduced.

---

## 2. Structural & Layout Changes

### 2.1 Desktop Sidebar Branding Header
The brand header has been relocated directly into the desktop sidebar as the primary header block:
* **Brand Container**: `<div class="ui-sidebar-header">` sits at the top of the sidebar.
* **Proportional Branding**: 
  - If a rectangular logo URL is provided, it is rendered with height-constrained proportional sizing (`height: 2.75rem`, `width: auto`, `max-width: 6.5rem`, `object-fit: contain`) preventing distortion or forcing it into a square box.
  - Fallback square monogram/logo uses a sleek, custom styled `.ui-brand-mark` with `height: 2.75rem`, `width: 2.75rem`, and correct centered text alignment.
* **Identity Block**: Shows the application name (`application_name`) and organization name (`organization_name`) in a stacked typography block.

### 2.2 Shell Geometry and Flex Layout
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
  - Hides the large duplicate application branding on desktop (`lg:hidden` on `.ui-topbar-brand`).
  - Layout: `[Toggle Button] [Page Title]` on the left; utilities on the right.

### 2.3 Sidebar Collapse and State Transitions
* **Persistence Contract**: Preserved the contract utilizing the `sihati.sidebar.collapsed` key in `localStorage` parsed by the existing `resources/js/app.js` module.
* **Transitions**: Left and right paddings transition smoothly.
* **Collapsed Mode Layout**:
  - Restricts brand logo to a square monogram boundary (`max-width: 2.75rem`).
  - Collapses navigations into centered icon grids using `margin-inline: auto` and `width: 2.75rem`.
  - Labels and subtitles (`.ui-sidebar-brand-text`, `.ui-sidebar-label`, `.ui-nav-label`) are hidden (`display: none` or `max-width: 0`) without overflowing or clipping the workspace.

---

## 3. Conformance & Test Verification

### 3.1 XPath Conformance & Test Suite
To ensure total conformance with existing regression tests, `<nav>` elements have been kept as direct children of the `<aside>` element. The brand header is implemented as a non-anchor `div` element to prevent duplicate dashboard links in the sidebar query:
* **All Tests Passed**: Verified running `$env:XDEBUG_MODE="off"; php artisan test`.
  - **Total**: 139 passed, 1 skipped, 1780 assertions.
  - **Status**: Clean baseline green.

### 3.2 Production Build Compilation
* Asset compilation was verified via `npm run build` and succeeded without errors:
  - `public/build/assets/app-*.css` and `theme-modern-*.css` generated successfully.

---

## 4. Human Browser Verification Checklist
Because a sample browser is unavailable for runtime validation, the project owner should perform manual QA following this checklist:

* [ ] **Expanded Sidebar (>= 1024px)**: Sidebar width is exactly `256px`. Brand logo has height `44px` (`2.75rem`) and auto width. Topbar sticky header contains only the toggle and page title.
* [ ] **Collapsed Sidebar (>= 1024px)**: Toggle collapses sidebar to exactly `72px` (`4.5rem`). Monogram/logo is perfectly centered. Sidebar labels and titles are completely hidden.
* [ ] **Responsive Transition**: Content column adapts naturally without visual shift, overlap, or scrollbars when resizing the browser between `1024px` and wider screen dimensions.
* [ ] **Keyboard Focus-Visible**: Tab navigation displays a visible ring on interactive elements.
