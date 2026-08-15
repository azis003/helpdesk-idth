# 36 — W4.1 Requester Dashboard Implementation Report

## Summary & Objectives

Wave 4.1 marks the start of the accelerated user-facing track. The primary goal is delivering a calm, institutional, and welcoming service portal for **Pemohon (pure Requester)** employees.

- **Starting HEAD SHA:** `f689e045d775b2853cceaa404fc8b7f5003c2db9`
- **Target Commit Message:** `feat(redesign): implement W4.1 requester dashboard`
- **Current Status:** `W4.1 IMPLEMENTED — HUMAN QA REQUIRED`

---

## Production Changes

### Files Modified

1. `resources/views/dashboard.blade.php`
   - Redesigned pure-requester view into a **Calm Service Portal**.
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

### A. Service Action Area (Hero)
- **Heading:** `Butuh bantuan TI?` (`<h1>` semantic anchor for requester content).
- **Subtext:** `Laporkan kendala atau ajukan kebutuhan layanan TI Anda di sini.`
- **Primary CTA:** `[Buat tiket]` (`route('tickets.create')`) — visually prominent action.
- **Secondary CTA:** `[Lihat tiket saya]` (`route('tickets.index')`) — neutral outline button.
- **Tertiary / Help CTA:** `[Tata cara pelaporan]` (`data-reporting-guide-trigger aria-haspopup="dialog"`) — ghost button with help circle icon.

### B. Important Announcements (Conditional)
- Displayed when active announcements exist (`$announcements->isNotEmpty()`).
- Styled as institutional notices without overshadowing primary actions.
- Preserves exact scheduling query, active window, title, and body.

### C. Ticket Status Summary (4 Service Status Indicators)
Strictly adheres to existing backend data contract:
1. `total_ticket_count`: **Total tiket** (*Semua tiket yang Anda buat.*)
2. `total_active_ticket_count`: **Tiket aktif** (*Sedang diproses atau menunggu tindak lanjut.*)
3. `total_completed_ticket_count`: **Tiket selesai** (*Solusi tersedia, menunggu konfirmasi Anda.*)
4. `total_closed_ticket_count`: **Tiket ditutup** (*Tiket sudah berstatus Ditutup.*)

### D. Reporting Guide Dialog
- Contract preserved via `<template data-reporting-guide-template>`.
- Preserved 3 semantic steps:
  1. *Pilih layanan* (Pilih kategori yang paling mendekati kebutuhan Anda.)
  2. *Jelaskan kebutuhan* (Tuliskan kendala, dampak, lokasi, dan hasil yang diharapkan.)
  3. *Pantau dan konfirmasi* (Balas jika ada pertanyaan dan konfirmasi setelah solusi tersedia.)

---

## Responsive Design Specification

- **Desktop (1440–1700px):** Action bar and 4-column summary grid fit above the fold; breathable spacing with calm service colors.
- **Tablet / Small Desktop (1024px):** Summary gracefully displays in a 2x2 grid with clear typography and no cramped content.
- **Mobile (390px):** Zero horizontal overflow; CTAs stack naturally with thumb-friendly tap targets (minimum 44px height); clear status cards with legible typography.

---

## Verification & Test Gate Results

| Verification Item | Expected | Actual Result |
| :--- | :--- | :--- |
| **PHPUnit Test Suite** | 139 passed, 1 skipped, 0 failures | **139 passed, 1 skipped, 1780 assertions** |
| **View Compilation** | Views clear & cache cleanly | **Blade templates cached successfully** |
| **Frontend Production Build** | Vite build successful | **vite build completed in 1.67s** |
| **Non-vendor Route Count** | 112 non-vendor routes | **112 non-vendor routes** |
| **Git Diff Quality** | No whitespace / trailing errors | **git diff --check clean** |

---

## Human QA Readiness Checklist

To be tested using a pure **Pemohon** account (`act01` / Pemohon):

- [ ] Desktop Requester Dashboard layout and hierarchy
- [ ] Primary CTA (`Buat tiket`) navigation to `route('tickets.create')`
- [ ] Secondary CTA (`Lihat tiket saya`) navigation to `route('tickets.index')`
- [ ] Reporting guide popup trigger (`Tata cara pelaporan`) and 3-step modal
- [ ] Ticket summary counts accurately reflect user's tickets
- [ ] Announcements rendering when present
- [ ] 390px mobile viewport responsiveness (no overflow, natural stacking)
- [ ] Role isolation sanity check (Multi-role, Tier 1, Tier 2, Super Admin, Approver, Team Chair dashboards retain original views)

---

## Wave Status

**Status:** `W4.1 IMPLEMENTED — HUMAN QA REQUIRED`
*(Awaiting Human QA review before proceeding to W5.1)*
