# 38 — Komdigi Identity Token Migration

## Summary & Metadata

- **Starting HEAD SHA:** `acf9ffbc2b09d4aff21e8144241a2da0d46ef9fe`
- **Source of Truth:** [37-KOMDIGI-IDENTITY-THEME.md](37-KOMDIGI-IDENTITY-THEME.md)
- **Scope:** Global Design Token & CSS Theme Migration (Foundation Level Only)
- **Status:** `KOMDIGI IDENTITY TOKENS IMPLEMENTED — HUMAN QA REQUIRED`

---

## 1. Migration Objective

Following the approval of the **Komdigi-Inspired Institutional Identity** in `37-KOMDIGI-IDENTITY-THEME.md`, this migration updates the global design tokens in `resources/css/theme-modern.css` from the previous generic teal brand system to the authoritative institutional blue palette.

Primary Principle: *"Ubah wajahnya, jangan bongkar mesinnya."*

- **0 Blade changes** across the entire application.
- **0 JavaScript changes**.
- **0 Backend changes** (controllers, routes, models, queries, policies remain frozen).
- **0 Layout geometry modifications** (sidebar structure, card padding, modal layout preserved).

---

## 2. Token Architecture Changes

### 2.1 Brand Scale Transition

| Token | Legacy Generic Teal | Migrated Komdigi Institutional Blue | Functional Role |
| :--- | :--- | :--- | :--- |
| `--tm-brand-50` | `#eff9fb` | `#eff6fc` | Soft selected fills & light badges |
| `--tm-brand-100` | `#d7f0f5` | `#dceefe` | Active navigation & hover surfaces |
| `--tm-brand-200` | `#b0e1eb` | `#bcddf7` | Muted brand container borders |
| `--tm-brand-300` | `#7cccdc` | `#8fc5ef` | Active accent outlines |
| `--tm-brand-400` | `#43adc4` | `#4ea4e4` | Secondary interactive indicators |
| `--tm-brand-500` | `#1e8fa8` | `#1686d9` | **Digital Blue:** Focus rings & icon accents |
| `--tm-brand-600` | `#14738b` | `#0869c7` | **Brand Blue:** Primary CTA actions & links |
| `--tm-brand-700` | `#135d72` | `#075aae` | **Brand Hover:** Pressed & active states |
| `--tm-brand-800` | `#154d5e` | `#074a87` | Deep institutional blue headings |
| `--tm-brand-900` | `#16404f` | `#073b6f` | **Brand Navy:** Masthead & title anchors |

### 2.2 Neutral Palette Realignment

The `--tm-n-*` scale was aligned with the cool slate palette specified in Document 37:

| Token | Legacy Value | Migrated Value | Structural Usage |
| :--- | :--- | :--- | :--- |
| `--tm-n-0` | `#ffffff` | `#ffffff` | Primary card & panel surfaces |
| `--tm-n-25` | `#fbfcfd` | `#fafbfc` | Soft card header & subtle fills |
| `--tm-n-50` | `#f6f8fa` | `#f4f6f9` | Global page canvas background |
| `--tm-n-100` | `#eef1f4` | `#eef2f6` | Muted surfaces, table headers, hover |
| `--tm-n-200` | `#e2e7ec` | `#d7e0e8` | Standard 1px container dividers |
| `--tm-n-300` | `#cbd3db` | `#aab8c5` | Strong structural borders |
| `--tm-n-400` | `#9aa6b2` | `#8c9aa7` | De-emphasized metadata & faint labels |
| `--tm-n-500` | `#6b7885` | `#72808d` | Placeholders, muted text, disabled labels |
| `--tm-n-600` | `#4e5a66` | `#566574` | Secondary body text & timestamps |
| `--tm-n-700` | `#3a444e` | `#3b4855` | Secondary headings & dark controls |
| `--tm-n-800` | `#262e36` | `#27333e` | Dark high-contrast structural text |
| `--tm-n-900` | `#161c22` | `#17212b` | Primary headings, titles, form values |

### 2.3 SIHATI Foundation Aliases Remapping

Foundation aliases were remapped to point directly to global tokens rather than maintaining disconnected hardcoded hex literals:

```css
--sihati-color-canvas: var(--tm-n-50);
--sihati-color-surface-primary: var(--tm-n-0);
--sihati-color-surface-muted: var(--tm-n-100);
--sihati-color-surface-hover: var(--tm-n-100);
--sihati-color-surface-selected: var(--tm-brand-100);
--sihati-color-border-default: var(--tm-n-200);
--sihati-color-border-strong: var(--tm-n-300);
--sihati-color-text-default: var(--tm-n-900);
--sihati-color-text-muted: var(--tm-n-600);
--sihati-color-text-subtle: var(--tm-n-500);
--sihati-color-text-inverse: var(--tm-n-0);
--sihati-color-action-primary: var(--tm-brand-600);
--sihati-color-action-primary-hover: var(--tm-brand-700);
--sihati-color-action-primary-soft: var(--tm-brand-100);
--sihati-color-action-destructive: var(--tm-danger-700);
--sihati-color-action-disabled-surface: var(--tm-n-100);
--sihati-color-action-disabled-text: var(--tm-n-500);
--sihati-color-success-text: var(--tm-success-700);
--sihati-color-success-surface: var(--tm-success-50);
--sihati-color-warning-text: var(--tm-warning-700);
--sihati-color-warning-surface: var(--tm-warning-50);
--sihati-color-danger-text: var(--tm-danger-700);
--sihati-color-danger-surface: var(--tm-danger-50);
--sihati-color-info-text: var(--tm-info-700);
--sihati-color-info-surface: var(--tm-info-50);
--sihati-color-focus-ring: var(--tm-brand-500);
```

---

## 3. Brand & Status Decoupling

In accordance with Section 7 of `37-KOMDIGI-IDENTITY-THEME.md` (*BRAND COLOR $\neq$ STATUS COLOR*), in-progress operational statuses were decoupled from primary action tokens:

```css
/* Before (coupled to action-primary brand tokens): */
--sihati-status-diproses-text: var(--sihati-color-action-primary);
--sihati-status-diproses-surface: var(--sihati-color-action-primary-soft);
--sihati-status-dikerjakan-text: var(--sihati-color-action-primary);
--sihati-status-dikerjakan-surface: var(--sihati-color-action-primary-soft);

/* After (decoupled to INFO semantic tokens): */
--sihati-status-diproses-text: var(--sihati-color-info-text);
--sihati-status-diproses-surface: var(--sihati-color-info-surface);
--sihati-status-dikerjakan-text: var(--sihati-color-info-text);
--sihati-status-dikerjakan-surface: var(--sihati-color-info-surface);
```

All semantic status families remain strictly intact:
- **`Ditutup`:** Success Green (`#0c6249` / `#e9f8f2`)
- **`Menunggu *`:** Warning Amber (`#8a5700` / `#fff7e8`)
- **`Ditolak` / `Tidak Disetujui` / `Kritis`:** Danger Red (`#a21f37` / `#fdeef1`)
- **`Baru` / `Diproses` / `Dikerjakan`:** Info Blue (`#175a86` / `#eaf4fb`)

---

## 4. Compatibility Layer Preservation

Section 15 (*Compat layer — remap warna hardcode lama*) in `theme-modern.css` was fully audited and preserved:
- Existing legacy selectors (e.g. `.bg-[#7138e8]`, `.bg-[#147a79]`, `.bg-[#075998]`) continue to map directly into `var(--tm-brand-*)`.
- As a direct result of the root brand scale migration, these legacy elements automatically inherit the new Komdigi institutional blue palette without requiring markup modifications.
- Legacy semantic selectors (e.g. `.bg-[#e8faf4]`, `.bg-[#fff1f2]`) continue to map into semantic tokens (`var(--tm-success-*)`, `var(--tm-danger-*)`).

---

## 5. Scope & Indirectly Affected Surfaces

Because this change operates at the token root level, all styled application views automatically inherit the new identity:
- **App Shell & Topbar:** Clean white surfaces, subtle dividers (`#d7e0e8`), institutional blue branding.
- **Sidebar Navigation:** Soft brand hover (`#eff6fc`), brand active highlights (`#dceefe`), brand navy active text (`#073b6f`).
- **Guest / Login Shell:** Primary CTA and brand accent bar now render in `#0869c7` / `#075aae`.
- **W3 Management Tables (Locations, Teams, Skills, Announcements):** Primary action buttons and badges inherit the crisp blue and neutral slate hierarchy.
- **W4.1 Requester Dashboard:** Inherits global tokens while its specific visual template remains isolated for dedicated Human QA review.

---

## 6. Verification & Automated Test Gate

| Check / Gate | Target Baseline | Actual Result | Status |
| :--- | :--- | :--- | :--- |
| **PHPUnit Test Suite** | 139 passed, 1 skipped | **139 passed, 1 skipped (1780 assertions)** | **PASS** |
| **Blade Cache Clear & Cache** | Zero compilation errors | **Blade templates cached successfully** | **PASS** |
| **Frontend Production Build** | Vite build successful | **vite build completed in 2.15s (0 errors)** | **PASS** |
| **Route List Contract** | 112 non-vendor routes | **112 non-vendor routes** | **PASS** |
| **Git Diff Cleanliness** | Clean, no whitespace issues | **git diff --check clean (exit code 0)** | **PASS** |

---

## 7. Next Steps & Quality Gate

- **Status:** `KOMDIGI IDENTITY TOKENS IMPLEMENTED — HUMAN QA REQUIRED`
- **Next Wave:** Human QA visual inspection of global shell, login, and administrative screens under the Komdigi institutional blue theme before proceeding to dedicated W4.1 requester dashboard corrective.
