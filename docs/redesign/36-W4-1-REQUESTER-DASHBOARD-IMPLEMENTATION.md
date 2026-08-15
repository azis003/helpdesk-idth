# 36 — W4.1 Requester Dashboard Implementation Report

## Summary & Objectives

Wave 4.1 marks the start of the accelerated user-facing track. The primary goal is delivering a polished, modern, and confident service portal for **Pemohon (pure Requester)** employees.

- **Starting HEAD SHA:** `8fb5e23af9b9f8f682af848a4030e70308234b85`
- **Target Commit Message:** `fix(redesign): polish W4.1 requester dashboard visuals`
- **Current Status:** `W4.1 IMPLEMENTED — HUMAN QA REQUIRED`

---

## Human QA Corrective Record — W4.1-HQA-001

- **Trigger:** Initial visual layout review by product owner / Human QA rejected the pale, basic visual treatment.
- **Accepted Foundations:** Dashboard information architecture and functional content remain accepted.
- **Corrective Implemented:**
  1. **Top Requester Area:** Strengthened typography, removed decorative/pale eyebrow ("PORTAL LAYANAN TI"), strengthened primary CTA (`Buat tiket`), refined secondary outline CTA (`Lihat tiket saya`), and added clean tertiary link trigger (`Tata cara pelaporan &rarr;`).
  2. **Announcements:** Compacted into crisp service notices with warm alert indicators and clear timestamps without giant empty zones.
  3. **4 Metric Tiles:** Maintained exact 4 metrics (`total_ticket_count`, `total_active_ticket_count`, `total_completed_ticket_count`, `total_closed_ticket_count`) in a compact, single-row desktop layout (2x2 on mobile) with disciplined semantic color accents.
  4. **Cara Mendapatkan Bantuan:** Surfaced existing reporting guide steps as a horizontal 3-step help section directly below the summary while preserving the `<template data-reporting-guide-template>` modal trigger.
  5. **Visual Styling:** Applied crisp 1px borders (`#d8e0e6`), high-contrast slate text (`#17212b`, `#5d6975`), confident brand teal (`#0f7384`), and tightened page spacing (20–24px).
  6. **Zero Backend/Data Change:** 0 changes to controllers, routes, models, queries, or workflow contracts.

---

## Production Changes

### Files Modified

1. `resources/views/dashboard.blade.php`
   - Polished pure-requester view (`$isRequesterOnly`) with approved desktop and mobile visual composition.
   - Preserved pure requester role boundary (`$isRequesterOnly`).
   - Cleanly separated pure requester flow from multi-role / administrative personas.
   - Preserved Helpdesk dashboard delegation (`@include('dashboard.helpdesk')`).

---

## Role Isolation & Semantic Boundary

The pure requester branch strictly relies on:

```php
$isRequesterOnly = $user->hasRole(\App\Enums\Role::Pemohon)
    && ! $user->hasAnyRole([
        \App\Enums\Role::SuperAdmin,
        \App\Enums\Role::AgenTier1,
        \App\Enums\Role::AgenTier2,
        \App\Enums\Role::Approver,
        \App\Enums\Role::KetuaTimKerja,
    ]);
```

- Users with any additional operational, approval, chair, or administrative roles do **NOT** receive the pure requester presentation.
- `resources/views/dashboard/helpdesk.blade.php` remains frozen and unmodified.

---

## Requester Information Architecture

### A. Service Action Area (Top Panel)
- **Heading:** `Butuh bantuan TI?` (`<h1>` semantic anchor for requester content).
- **Subtext:** `Laporkan kendala atau ajukan kebutuhan layanan TI.`
- **Primary CTA:** `[Buat tiket]` (`route('tickets.create')`) — prominent saturated teal button.
- **Secondary CTA:** `[Lihat tiket saya]` (`route('tickets.index')`) — crisp neutral outline button.
- **Tertiary / Help CTA:** `[Tata cara pelaporan &rarr;]` (`data-reporting-guide-trigger aria-haspopup="dialog"`).

### B. Important Announcements (Conditional)
- Displayed when active announcements exist (`$announcements->isNotEmpty()`).
- Compact notice cards with warm warning accent and clear timestamps.
- Preserves exact scheduling query, active window, title, and body.

### C. Ticket Status Summary (4 Service Status Indicators)
Strictly adheres to existing backend data contract:
1. `total_ticket_count`: **Total tiket** (*Semua permintaan*)
2. `total_active_ticket_count`: **Tiket aktif** (*Sedang ditangani*)
3. `total_completed_ticket_count`: **Selesai** (*Menunggu konfirmasi*)
4. `total_closed_ticket_count`: **Ditutup** (*Proses selesai*)

### D. "Cara Mendapatkan Bantuan" & Reporting Guide Dialog
- Visible 3-step summary on page:
  1. *01 Pilih layanan* (Pilih kategori yang sesuai dengan kebutuhan Anda.)
  2. *02 Jelaskan kebutuhan* (Ceritakan kendala dengan jelas.)
  3. *03 Pantau & konfirmasi* (Ikuti progres hingga selesai.)
- Dialog modal contract preserved via `<template data-reporting-guide-template>`.

---

## Responsive Design Specification

- **Desktop (1440–1700px):** Action panel and 4-column summary grid fit above the fold; breathable spacing (20-24px vertical rhythm) with high-contrast surfaces.
- **Tablet / Small Desktop (1024px):** Summary displays in 4 columns or 2x2 grid with clear typography and no cramped content.
- **Mobile (390px):** Zero horizontal overflow; CTAs stack naturally with thumb-friendly tap targets; 2x2 summary grid with legible numbers and concise labels.

---

## Verification & Test Gate Results

| Verification Item | Expected | Actual Result |
| :--- | :--- | :--- |
| **PHPUnit Test Suite** | 139 passed, 1 skipped, 0 failures | **139 passed, 1 skipped, 1780 assertions** |
| **View Compilation** | Views clear & cache cleanly | **Blade templates cached successfully** |
| **Frontend Production Build** | Vite build successful | **vite build completed in 2.21s** |
| **Non-vendor Route Count** | 112 non-vendor routes | **112 non-vendor routes** |
| **Git Diff Quality** | No whitespace / trailing errors | **git diff --check clean** |

---

## Human QA Readiness Checklist

To be tested using a pure **Pemohon** account (`act01` / Pemohon):

- [ ] Desktop Requester Dashboard layout and hierarchy
- [ ] Top Action Panel: `Buat tiket`, `Lihat tiket saya`, and `Tata cara pelaporan`
- [ ] Announcements rendering when present in compact notice format
- [ ] 4-card Ticket summary counts accurately reflect user's tickets
- [ ] "Cara mendapatkan bantuan" 3-step section and modal popup trigger
- [ ] 390px mobile viewport responsiveness (no overflow, 2x2 summary grid, natural stacking)
- [ ] Role isolation sanity check (Multi-role, Tier 1, Tier 2, Super Admin, Approver, Team Chair dashboards retain original views)

---

## Wave Status

**Status:** `W4.1 IMPLEMENTED — HUMAN QA REQUIRED`
*(Awaiting Human QA review before proceeding to W5.1)*
