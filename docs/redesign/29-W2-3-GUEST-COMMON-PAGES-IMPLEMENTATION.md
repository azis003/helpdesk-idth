# W2.3 Guest Shell & Common Pages Implementation Report

## Conformance Metrics
*   **Starting SHA**: `ce9e8018320ca9147c5a13614c7ea0510e02125d`
*   **W2.2 Closure Commit SHA**: `ce9e8018320ca9147c5a13614c7ea0510e02125d`
*   **Production Files Changed**:
    *   `resources/views/layouts/guest.blade.php`
    *   `resources/views/layouts/app.blade.php`
    *   `resources/views/auth/login.blade.php`
    *   `resources/views/auth/change-password.blade.php`
    *   `resources/views/notifications/index.blade.php`
    *   `resources/views/errors/403.blade.php`
    *   `resources/views/vendor/pagination/tailwind.blade.php`
    *   `resources/css/theme-modern.css`

---

## 1. Guest Shell Layout & Aesthetic
*   **Centered Card Architecture**: Refactored `resources/views/layouts/guest.blade.php` to center the authentication card on desktop with borders and soft shadow styling.
*   **Responsive Gutters**: Mobile screens use single-column fluid widths with comfortable margins (`p-4 sm:p-8`).
*   **Branding Consistency**: Renders proportional logo URLs when configured or monogram fallbacks otherwise. Logo aspect ratios are strictly preserved without distortion.

---

## 2. Authentication Contracts
*   **Login Form (`resources/views/auth/login.blade.php`)**:
    *   Preserved `POST login.store`, `@csrf`, and exact fields `username` and `password`.
    *   Autofocus and autocomplete attributes are fully intact.
    *   Omitted the `remember` checkbox UI per guidelines.
    *   Maintained the `aria-label="Masuk ke {{ $branding['application_name'] }}"` to satisfy test suite assertions.
*   **Change Password Form (`resources/views/auth/change-password.blade.php`)**:
    *   Preserved `PUT password.update`, `@csrf`, `@method('PUT')`, and exact fields `current_password`, `password`, and `password_confirmation`.
    *   Maintained helpful security guidance notes and minimum password complexity labels.

---

## 3. Keyboard Accessibility & Skip-Link
*   **Accessible Skip Links**: Added visually hidden link (`class="ui-skip-link"`) at the start of `<body>` in `resources/views/layouts/app.blade.php`, `resources/views/layouts/guest.blade.php`, and `resources/views/errors/403.blade.php`.
*   **Target Focusing**: Primary content containers use `id="main-content"` and `tabindex="-1"`.
*   **Sticky Topbar Overlap Prevention**: Defined `scroll-margin-top: 5rem;` on `#main-content` in `resources/css/theme-modern.css` to ensure focused targets are not scrolled underneath the sticky topbar.

---

## 4. Notifications & Pagination
*   **Accessibility Text Badge**: Added an inline text badge `Baru` next to unread titles in `resources/views/notifications/index.blade.php` to ensure unread status is identifiable without relying solely on background color.
*   **Paginator Focus States**: Replaced default slate/cyan styling in `resources/views/vendor/pagination/tailwind.blade.php` with design system variables (`focus:ring-[color:var(--tm-brand-500)]`), while retaining native page numbering and disabled tags.

---

## 5. Standalone 403 Page
*   **Redirection Routing**: Dynamically links back to `dashboard` (for authenticated sessions) or `login` (for guests) using standard Laravel checks.
*   **Generic Wording**: Replaced policy detail checking messages with generic user-facing wording: *"Akun Anda tidak memiliki izin untuk mengakses halaman atau menjalankan tindakan ini."*

---

## 6. Static-Audit Corrections
*   **W2.3-AUDIT-001 (Invalid Design Token)**: Corrected the undefined `--tm-sunken` token usage to `--tm-surface-sunken` in `resources/views/notifications/index.blade.php`.
*   **W2.3-AUDIT-002 (403 Detail Wording)**: Replaced technical descriptions containing references to server checks, roles, or domains with a generic access-denied message.
*   **Guest Branding Hierarchy**: Refactored the guest header block to render the `application_name` and `organization_name` clearly alongside both the logo image and the monogram fallback.
*   **Action-Oriented Login Heading**: Changed the visual visual H1 to say "Masuk" clearly (concise and action-oriented) while preserving the required `aria-label` for test assertions.
*   **Neutral Password Security Copy**: Substituted the institutional policy statements in the change-password guidance box with neutral guidance copy: *"Jaga kerahasiaan kata sandi dan jangan membagikannya kepada pihak lain."*
*   **Corrected Evidence SHA**: Verified and updated both starting SHA and W2.2 closure SHA references to point to `ce9e8018320ca9147c5a13614c7ea0510e02125d`.

---

## 7. Regression Gate Results
*   **Automated Tests**: `139 passed, 1 skipped, 1780 assertions` (all tests passed).
*   **Non-Vendor Routes**: Exactly `112` non-vendor routes.
*   **Blade Views**: Cached successfully.
*   **Vite Build**: Compiled production assets without errors.
*   **Git Check**: `git diff --check` passed cleanly.

*   *Browser Note*: No controllable browser was used for this verification pass.

---

## 8. Conformance Status
**W2.3 STATIC CORRECTIVE COMPLETE — HUMAN BROWSER VERIFICATION REQUIRED**
