# W2.2 Mobile Shell / Navigation Drawer Implementation Report

## Conformance Metrics
*   **Starting Implementation Commit**: `2c8f5dd494f2b0eea6d00eca15021fbdfdd33f1e`
    *   *Note*: The initial W2.2 implementation commit used the message `"w2.2"` rather than the planned descriptive message.
*   **W2.1 Closure Commit**: `c1efb2a727999ca64b9f0058b86a48343bed2c1e`
*   **Files Changed in Initial W2.2**:
    *   `resources/views/layouts/app.blade.php`
    *   `resources/css/theme-modern.css`
    *   `resources/js/app.js`

---

## 1. Drawer DOM Structure & Geometry
The mobile menu navigation drawer is structured entirely inside the main applications layout:
*   **Parent Location**: Embedded inside the `<header>` element (`//header//nav[@aria-label='Navigasi mobile']`) to conform with existing test selectors.
*   **Backdrop Layer**: `<div data-mobile-menu-backdrop>` with class `fixed inset-0 bg-[#0f172a]/40 backdrop-blur-[2px] opacity-0 transition-opacity duration-300`.
*   **Drawer Panel Layer**: `<div data-mobile-menu-panel>` with class `fixed inset-y-0 left-0 flex flex-col bg-white shadow-2xl border-r border-[#dfe8ec] transition-transform duration-300 -translate-x-full`.
*   **Geometry Width**: Sized dynamically via CSS using `width: min(18rem, calc(100vw - 2rem))`.
*   **Responsive Breakpoint**: Restricted to `lg` viewports via Tailwind utility class `lg:hidden` (1024px).

---

## 2. Layering Hierarchy
The application UI layers are strictly defined with the following standard relative/fixed z-indices:
1.  **Page Body/Sidebar**: Base layers
2.  **Sticky Topbar**: `z-30`
3.  **Mobile Drawer**: `z-40`
4.  **Ticket Action Modal**: `z-50` (injected via `@stack('modals')` at the body root)
5.  **Global Loading Overlay**: `z-[1000]`

---

## 3. Interactive Mechanics & Lifecycle Safeguards
*   **Focus Trap**: Interactive Tab and Shift+Tab keystroke listeners are bound on `keydown` within the open menu drawer, keeping keyboard focus within active elements (`button`, `a`, etc.) inside the drawer.
*   **Escape Close**: Keydown listener for the `Escape` key immediately closes the drawer and restores focus.
*   **Backdrop Click**: Clicking the backdrop immediately closes the drawer.
*   **Focus Return**: Upon closing the drawer normally, focus is programmatically returned to the toggle hamburger button (`data-mobile-menu-trigger`).
*   **Body Scroll Lock Ownership**: The scroll lock (`.overflow-hidden` on the body) is toggled by the drawer. When closing the drawer, the lock is only released if no other modal/dialog is open (such as the password reset modal `[data-password-reset-modal]` or `[data-ui-modal]`).
*   **Pageshow Reset**: A deterministic pageshow listener resets the mobile menu state immediately to closed on BFCache restore.
*   **Livewire Navigation Reset**: Listens to the `livewire:navigate` event and resets the drawer state immediately (without transition animations) before page navigation proceeds, ensuring no stale scroll lock is left behind.
*   **Resize Reset**: Viewport resize >= 1024px resets the drawer state immediately and safely to closed without restoring focus.
*   **Notification Mutual Exclusion**: Opening the notifications details menu (`data-notification-menu`) closes the mobile menu drawer immediately using the centralized close logic, preserving focus on the notifications trigger.

---

## 4. Branding, Authentication & Security Preservation
*   **Role Navigation**: Navigation menu items are fully server-side rendered (Blade) based on user credentials.
*   **Logout Endpoint**: The logout trigger utilizes a secure POST form with CSRF validation.
*   **Topbar Branding**: Uses undistorted proportional logos (`height: 2.25rem; width: auto; max-width: 4.5rem; object-fit: contain;`) in `theme-modern.css`.

---

## 5. Automated Gate Results
All verification suites execute cleanly with zero regressions:
*   **Command**: `php artisan test`
*   **Tests Run**: 140 total (139 passed, 1 skipped)
*   **Assertions**: 1780 assertions

---

## 6. Human QA Corrections & Hardening

### W2.2-HQA-001: Mobile Menu Trigger Leaked into Desktop
*   **Defect**: On desktop viewports (>=1024px), both the mobile menu hamburger and the desktop sidebar-collapse hamburger were visible simultaneously.
*   **Root Cause**: Custom `.ui-mobile-menu-button { display: inline-flex }` in `theme-modern.css` overrode the Tailwind responsive `lg:hidden` utility.
*   **Correction**:
    *   Removed `display: inline-flex;` from `.ui-mobile-menu-button` in [theme-modern.css](file:///c:/Users/Personal/Herd/helpdesk-idth/resources/css/theme-modern.css).
    *   Added the `inline-flex` utility class directly to the button element in [app.blade.php](file:///c:/Users/Personal/Herd/helpdesk-idth/resources/views/layouts/app.blade.php) so that responsive visibility is handled entirely by Tailwind (`inline-flex lg:hidden`).
*   **Post-Correction Gate Results**:
    *   `php artisan test` passed with `139 passed, 1 skipped, 1780 assertions`.
    *   Vite assets compiled successfully with zero console/build warnings.
    *   `git diff --check` reported zero trailing white spaces or syntax style discrepancies.

---

## 7. Final Status
W2.2 HUMAN QA CORRECTIVE COMPLETE — HUMAN RE-VERIFICATION REQUIRED
