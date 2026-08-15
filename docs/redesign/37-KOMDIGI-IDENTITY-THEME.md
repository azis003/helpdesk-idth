# 37 — Komdigi Institutional Identity Theme

## 1. Status & Governance

- **Status:** APPROVED GLOBAL VISUAL DIRECTION
- **Effective Wave:** Wave 4.1+ (Supersedes generic teal brand direction)
- **Applies to:** All current and future SIHATI frontend redesign work across all user roles, layouts, components, and workflows.
- **Primary Principle:** *"Ubah wajahnya, jangan bongkar mesinnya."*

### Authority & Boundaries

This visual identity specification establishes the single visual source of truth for the entire SIHATI frontend redesign.

This document **supersedes**:
- The generic teal brand direction (`#0f7384`, `#0d6270`, `#e6f4f6`) previously referenced in initial wave documents.
- Ad-hoc or screen-specific brand color choices.

This document **does NOT supersede**:
- Backend contracts, Eloquent queries, models, or services
- Workflow, lifecycle, or state machine transitions
- Authorization policies, gates, or role boundaries
- Route names, URL structures, or controller actions
- Database schema, migrations, or data contracts
- Accepted information architecture (IA) and page layouts
- Status semantics and domain logic
- Priority semantics and SLA calculations

---

## 2. Brand Positioning & Identity Character

### Institutional Character: KOMDIGI-INSPIRED INSTITUTIONAL IDENTITY

SIHATI is an enterprise government operational support system for the **Kementerian Komunikasi dan Digital (Komdigi)**. The visual presentation must embody the prestige, technological authority, and public-service trust of an advanced digital ministry.

#### Desired Qualities
- **Institutional & Authoritative:** Conveys official government authority and stability without feeling bureaucratic or archaic.
- **Modern & Digital:** Reflects forward-thinking digital governance, cutting-edge technology, and crisp execution.
- **Confident & Professional:** Strong contrast, decisive interaction states, and structured layout rhythm.
- **Trustworthy & Clean:** High legibility, uncluttered surfaces, and transparent visual hierarchy.
- **Operational & Work-Focused:** Designed for high-frequency daily task completion, rapid triage, and effortless scanning.
- **Accessible & Inclusive:** Strict adherence to high-contrast WCAG 2.1 AA standards for all typography, icons, and interactive elements.

#### Explicit Anti-Patterns (What to Avoid)
- **Generic Teal SaaS:** Avoid turquoise/cyan-tinted consumer SaaS aesthetics.
- **Pale / Washed-Out Interfaces:** No low-contrast gray text on off-white backgrounds or invisible borders.
- **Pastel-Heavy Dashboards:** No washed-out pastel buttons or decorative pastel backgrounds.
- **Default Tailwind Out-of-the-Box Look:** No generic indigo/violet default templates.
- **Rainbow Dashboards:** Never assign arbitrary colorful backgrounds to operational widgets.
- **Excessive Gradients & Glassmorphism:** No glossy translucent cards, backdrop-blur gimmicks, or novelty skeuomorphism.
- **Heavy Elevation & Deep Shadows:** Do not use massive floating shadows (e.g. `shadow-2xl`); use crisp 1px borders for structural separation.
- **Excessively Rounded Shapes:** Avoid pill cards or large bubble borders (`rounded-3xl` / `rounded-full` on cards); retain restrained enterprise radii.
- **Decorative Visual Noise:** No purely decorative background illustrations, floating blobs, or distracting empty graphics.

> [!IMPORTANT]
> **Design Token Disclaimer:**
> The working HEX values defined in this document are **APPLICATION DESIGN TOKENS** tailored for the SIHATI web application interface. They must **NOT** be described as official *Kementerian Komunikasi dan Digital* statutory brand guidelines unless an authoritative ministry-wide visual identity manual explicitly confirms them.

---

## 3. Color Architecture & Token Specification

### 3.1 Primary Identity Palette (Brand Blue Family)

The brand color family represents institutional authority, digital innovation, and primary interactivity.

| Role | Token Name | HEX Value | Intended Usage & Interaction |
| :--- | :--- | :--- | :--- |
| **Brand Navy** | `--tm-brand-900` | `#073B6F` | Strong institutional emphasis, top headings, active navigation text, restrained identity accents |
| **Brand Blue** | `--tm-brand-600` | `#0869C7` | Primary action buttons, primary text links, selected controls, active interactive states |
| **Digital Blue** | `--tm-brand-500` | `#1686D9` | Secondary brand accents, focus rings/indication, compact icon accents, informative emphasis |
| **Brand Hover** | `--tm-brand-700` | `#075AAE` | Hover / pressed / active states for primary interactive controls |

### 3.2 Full Brand Scale (10-Step Operational Scale)

```css
--tm-brand-50:  #EFF6FC; /* Soft backgrounds / light selection fills */
--tm-brand-100: #DCEEFE; /* Subtle hover surfaces / secondary badge backgrounds */
--tm-brand-200: #BCDDF7; /* Muted brand borders / highlighted row containers */
--tm-brand-300: #8FC5EF; /* Active border highlights / secondary accents */
--tm-brand-400: #4EA4E4; /* Interactive indicators / supporting graphics */
--tm-brand-500: #1686D9; /* Digital Blue: focus indication / compact icon accents */
--tm-brand-600: #0869C7; /* Brand Blue: primary buttons / active links / dominant brand */
--tm-brand-700: #075AAE; /* Brand Hover: hover & pressed primary interaction */
--tm-brand-800: #074A87; /* Deep institutional blue / secondary headings */
--tm-brand-900: #073B6F; /* Brand Navy: dominant institutional mastheads & titles */
```

#### Scale Tier Distribution:
- **`50–200` (Light Tiers):** Soft container backgrounds, selected navigation rows, active table row highlights, and light chip backgrounds.
- **`300–500` (Mid Tiers):** Accent borders, visible focus rings, compact status/icon accents, and informative indicators.
- **`600` (Action Tier):** Dominant brand interactive color (primary CTA buttons, active tabs, primary links).
- **`700` (Interactive Tier):** Hover, pressed, and focus-active states for primary actions.
- **`800–900` (Deep Tiers):** Authoritative typographic headings, masthead titles, active sidebar text, and high-contrast institutional anchors.

---

### 3.3 Neutral Surface & Typographic Palette

The neutral palette provides a calm, high-contrast operational workbench that lets data and workflows remain the primary focus.

| Neutral Token | CSS Variable | HEX Value | Structural Purpose |
| :--- | :--- | :--- | :--- |
| **Canvas** | `--tm-neutral-canvas` | `#F4F6F9` | Global page background; cool, subtle gray providing contrast for white cards |
| **Surface** | `--tm-neutral-surface` | `#FFFFFF` | Primary content panels, tables, cards, modal dialogs, and form sheets |
| **Muted Surface** | `--tm-neutral-muted-surface` | `#EEF2F6` | Secondary panels, table header rows, input addons, summary callouts |
| **Border** | `--tm-neutral-border` | `#D7E0E8` | Standard 1px container borders, table dividers, card boundaries |
| **Strong Border** | `--tm-neutral-strong-border` | `#AAB8C5` | Input borders on hover/focus, table header dividers, structural separators |
| **Primary Text** | `--tm-neutral-text-primary` | `#17212B` | Dominant text color for titles, labels, table cells, and form inputs (high contrast) |
| **Secondary Text** | `--tm-neutral-text-secondary` | `#566574` | Descriptive copy, helper text, timestamps, table column headers |
| **Muted Text** | `--tm-neutral-text-muted` | `#72808D` | Placeholder text, disabled labels, de-emphasized metadata |

#### Structural Layering Hierarchy:
$$\text{Canvas (\#F4F6F9)} \longrightarrow \text{Surface (\#FFFFFF)} \longrightarrow \text{Muted Surface (\#EEF2F6)} \longrightarrow \text{Border (\#D7E0E8)}$$

---

### 3.4 Semantic Palette & State Separation Rule

> [!CAUTION]
> **CRITICAL ARCHITECTURAL RULE: BRAND COLOR $\neq$ STATUS COLOR**
>
> Under no circumstances may ticket statuses or domain workflow states be styled with brand blue solely for visual branding.
> - **Brand Blue communicates:** Identity, primary user action, navigation selection, and active input focus.
> - **Semantic Colors communicate:** Workflow state, operational health, urgency, validation errors, and lifecycle status.

#### Semantic State Mappings:

| Semantic State | Base Color Family | Typical Domain Tokens | Mandatory Usage in SIHATI |
| :--- | :--- | :--- | :--- |
| **SUCCESS** | Emerald / Green | `#059669` / `#ECFDF5` | Closed tickets (`Ditutup`), resolved items, successful approvals, positive confirmation |
| **WARNING** | Amber / Orange | `#D97706` / `#FFFBEB` | Waiting states (`Menunggu Pemohon`, `Menunggu Persetujuan`, `Menunggu Pihak Ketiga`, `Menunggu Konfirmasi`), approaching SLA, announcements |
| **DANGER** | Crimson / Red | `#DC2626` / `#FEF2F2` | Rejected requests (`Ditolak`, `Tidak Disetujui`), critical priority (`Kritis`), overdue SLA, destructive actions, validation errors |
| **INFO** | Slate / Neutral Blue | `#2563EB` / `#EFF6FF` | New tickets (`Baru`), in-progress operational triage (`Diproses`), informational notices |

#### Status Badge Semantic Examples:
- **`Ditutup`:** Success Green chip (e.g., green text on soft green background, green border).
- **`Menunggu Pemohon` / `Menunggu Persetujuan` / `Menunggu Pihak Ketiga` / `Menunggu Konfirmasi`:** Warning Amber chip.
- **`Ditolak` / `Tidak Disetujui`:** Danger Red chip (critical priority `Kritis` also uses Danger Red).
- **`Dibatalkan`:** Neutral / muted cancelled chip (`--sihati-color-text-muted` on `--sihati-color-surface-muted`).
- **`Baru` / `Diproses` / `Dikerjakan`:** Clear operational informative badge.
- **Never render all statuses in uniform blue.**

---

### 3.5 Global Color Balance Ratio (60-30-10 Principle)

To ensure an enterprise, mature, and readable interface:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        60% NEUTRAL FOUNDATION                          │
│   (Canvas #F4F6F9, Surface #FFFFFF, Muted #EEF2F6, Slate Text #17212B) │
├──────────────────────────────────────────┬─────────────────────────────┤
│         30% INSTITUTIONAL BLUE           │        10% SEMANTIC         │
│  (Brand Navy #073B6F, Brand Blue #0869C7)│ (Success / Warning / Danger)│
└──────────────────────────────────────────┴─────────────────────────────┘
```

- **60% Neutral:** Dominated by cool canvas backgrounds, clean white surfaces, subtle dividers, and dark slate typography.
- **30% Institutional Blue:** Establishes product identity via headers, mastheads, primary CTA buttons, active sidebar items, and focus indicators.
- **10% Semantic / Accent:** Strictly reserved for functional feedback, status chips, urgency indicators, and data highlights.

---

## 4. Component Visual System Specifications

### 4.1 Typography System

SIHATI is an operational government workbench, **not** a consumer marketing landing page. Typography must prioritize density, rapid scanning, and unambiguous reading.

- **Primary Font Family:** System Sans / Inter stack (`ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`).
- **Heading 1 (`h1`):** Dark confident slate (`#073B6F` or `#17212B`), bold/semibold, 20px–24px on desktop, 18px–20px on mobile. No oversized marketing headings.
- **Heading 2 / 3 (`h2`, `h3`):** Semibold `#17212B`, 14px–16px, clean vertical rhythm.
- **Body Text:** `#17212B`, 13px–14px, line-height 1.4–1.5 for compact data readability.
- **Metadata / Helper Text:** `#566574`, 11px–12px, high legibility.
- **Typography Anti-Patterns:**
  - No low-contrast pale gray text.
  - No decorative uppercase eyebrow labels (e.g. avoid tiny tracked-out `PORTAL LAYANAN TI` uppercase tags).
  - No stylized script or display fonts.

---

### 4.2 Surface & Elevation System

- **Canvas Background:** Cool gray (`#F4F6F9`).
- **Card Containers:** Solid white (`#FFFFFF`) with 1px border (`#D7E0E8`).
- **Corner Radius:** Restrained enterprise radius (`rounded-lg` / 6px–8px for cards and panels; `rounded-md` / 4px–6px for inputs and buttons). Avoid bubble or pill cards.
- **Elevation / Shadows:** Minimal shadows (`shadow-xs` or `shadow-sm`). Crisp 1px borders must perform all primary structural separation.
- **Padding:** Compact but breathable (12px–20px depending on table vs. summary card context).

---

### 4.3 Interactive Button System

| Button Variant | Background | Border | Text Color | Hover State | Focus State |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Primary CTA** | Brand Blue (`#0869C7`) | Transparent / `#0869C7` | Solid White (`#FFFFFF`) | Brand Hover (`#075AAE`) | 2px Digital Blue (`#1686D9`) offset ring |
| **Secondary CTA** | Neutral Surface (`#FFFFFF`) | Neutral Border (`#D7E0E8`) | Primary Text (`#17212B`) | Muted Surface (`#EEF2F6`), Stronger Border (`#AAB8C5`) | 2px Digital Blue ring |
| **Tertiary / Link**| Transparent | Transparent | Brand Blue (`#0869C7`) | Brand Navy (`#073B6F`), soft underline or bg | 2px Digital Blue ring |
| **Destructive / Danger** | Danger Red (`#DC2626`) | Transparent | Solid White (`#FFFFFF`) | Dark Red (`#B91C1C`) | 2px Red offset ring |

> [!WARNING]
> Destructive actions (e.g., delete, reject, revoke) must **always** use semantic red styling. Never recolor destructive actions to blue.

---

### 4.4 Form Controls & Input Fields

- **Default State:** White surface (`#FFFFFF`), 1px neutral border (`#D7E0E8`), primary text (`#17212B`), placeholder (`#72808D`).
- **Hover State:** Border transitions to Strong Border (`#AAB8C5`).
- **Focus State:** Border transitions to Brand Blue (`#0869C7`) with a 2px visible Digital Blue (`#1686D9`) focus ring and 1px offset.
- **Error State:** Border transitions to Semantic Red (`#DC2626`) with red validation error text below the field.
- **Disabled State:** Muted surface (`#EEF2F6`), muted border (`#D7E0E8`), muted text (`#72808D`), cursor `not-allowed`.

---

### 4.5 Navigation & Application Shell (Sidebar & Topbar)

- **Accepted Structure:** The two-tier sidebar navigation hierarchy accepted in Wave 2 remains completely structurally unchanged.
- **Sidebar Surface:** Neutral clean surface (`#FFFFFF` or subtle neutral `#F8FAFC`) with right 1px border (`#D7E0E8`).
- **Default Nav Item:** Secondary text (`#566574`), transparent background.
- **Hover Nav Item:** Soft brand background (`--tm-brand-50` / `#EFF6FC`), text `#073B6F`.
- **Active Nav Item:** Brand soft background (`--tm-brand-100` / `#DCEEFE`), active text Brand Navy (`#073B6F`), active icon/indicator Brand Blue (`#0869C7`).
- **Anti-Pattern:** Do not make the sidebar a full solid saturated blue or heavy dark navy block unless separately approved.

---

### 4.6 Iconography & Visual Assets

- **Style:** Compact, line-based, consistent 1.5px–2px stroke weight (Heroicons / Lucide style).
- **Color:** Neutral slate (`#566574`) for secondary icons; Brand Blue (`#0869C7`) or Brand Navy (`#073B6F`) for primary active state indicators; semantic colors for status icons.
- **Constraint:** No giant decorative illustrations, no novelty 3D icons, and **zero copied Frappe assets or AGPL code**.

---

### 4.7 Density, Spacing & Layout Rhythm ("Calm Operational Workbench")

- **Base Grid:** Strict 4px/8px modular spacing rhythm (`gap-2`, `gap-3`, `gap-4`, `gap-6`).
- **Card Padding:** 12px–16px for metric tiles and lists; 16px–20px for major forms and details panels.
- **Information Density:** Compact and scannable without visual claustrophobia; avoid excessive vertical dead space.

---

## 5. Dashboard & Screen Application Guidelines

### 5.1 Requester Dashboard (W4.1 Target)

The pure Requester Dashboard information architecture remains strictly SIHATI-specific:
1. **Header Action Panel:** Direct greeting (`Butuh bantuan TI?`), concise helper text, prominent `[Buat tiket]` Brand Blue primary CTA, crisp `[Lihat tiket saya]` secondary CTA, and accessible `[Tata cara pelaporan →]` link.
2. **Announcements Panel:** Compact service notices using the existing announcement content, visibility, and scheduling behavior.
3. **4 Metric Tiles:** Crisp 4-card status row (`Total tiket`, `Tiket aktif`, `Selesai`, `Ditutup`) with restrained semantic color indicators.
4. **Service Guidance:** 3-step structured cards (`Pilih layanan`, `Jelaskan kebutuhan`, `Pantau & konfirmasi`) with working reporting guide dialog template.

---

### 5.2 Reference Usage: Frappe Helpdesk

Frappe Helpdesk serves as an external **quality and density benchmark** for:
- Crisp 1px border structural containment
- High information density with clear typography
- Restrained, mature enterprise component radius
- Explicit interactive hover/focus states

**Strict Prohibition:**
- Do **NOT** copy Frappe source code, Vue components, styles, icons, or AGPL-licensed assets.
- SIHATI retains its native Laravel Blade architecture, Tailwind/custom CSS tokens, vanilla JavaScript ES modules, existing Livewire navigation/lifecycle hooks, and Indonesian government business logic.

---

## 6. Responsive & Accessibility Standards

### 6.1 Responsive Adaptations
- **Desktop ($\ge 1280\text{px}$):** Multi-column compact grid, sidebars expanded, all metrics and quick actions visible above the fold.
- **Tablet ($768\text{px} - 1024\text{px}$):** 2-column or collapsible grid layouts, preserving complete data visibility without horizontal scrolling.
- **Mobile ($\le 640\text{px}$):** Single-column natural flow, 2x2 metric grid, full-width thumb-friendly buttons (minimum 44px touch target), zero horizontal overflow.

### 6.2 Accessibility (WCAG 2.1 AA Compliance)
- **Contrast Ratios:** Minimum 4.5:1 for normal text against background; minimum 3:1 for large text and UI borders/controls.
- **Color Independence:** Status badges must always include clear text labels (never communicate status through color alone).
- **Keyboard & Focus:** All interactive elements must exhibit visible Digital Blue (`#1686D9`) focus rings when navigated via keyboard.
- **Disabled State Legibility:** Disabled elements must remain readable while clearly indicating inactive status.

---

## 7. Global Implementation Rules & Scope

### 7.1 Scope of Application
This theme applies universally across all SIHATI views:
- Application Shell (topbar, sidebar, footer, mobile drawer)
- Guest & Authentication screens (login, password reset)
- Dashboards (Requester, Helpdesk, Approver, Super Admin)
- Ticket Management (list, create, detail, timeline, activities)
- Approvals & Workflows
- Reports, Analytics & Master Data Administration
- Form controls, tables, modals, slide-overs, and flash notifications

### 7.2 Governance & Non-Proliferation Rule
1. **Single Source of Truth:** All future CSS/Blade redesign waves must consume global tokens defined here.
2. **Prohibition of Local Overrides:** No developer or subagent may introduce local teal, cyan, purple, or arbitrary hex codes in individual Blade templates without an approved amendment to this specification.
3. **Amendment Protocol:** If visual direction is updated in the future, this document (`37-KOMDIGI-IDENTITY-THEME.md`) must be amended first before modifying code.

---

## 8. Wave Context & Transition Record

- **W1 Visual Foundation:** PASS / previously accepted structurally, brand identity now amended by this specification.
- **W2 Shell & Navigation:** PASS / CLOSED. Existing accepted structure remains.
- **W3.1 Locations & Teams:** PASS / Human QA accepted.
- **W3.2 Skills & Announcements:** IMPLEMENTED — HUMAN QA INCOMPLETE — DEFERRED.
- **Remaining W3:** DEFERRED.
- **W4.1 Requester Dashboard:** IMPLEMENTED — HUMAN QA REQUIRED.
- **Current Milestone:** Identity theme defined and approved as specification.
- **Status:** **`IDENTITY TOKENS IMPLEMENTED — HUMAN QA REQUIRED`**
- **Next Phase:** Human QA verification across application shells and screens before dedicated view correctives.
