# 35 — User-Facing Priority Acceleration & W3/W4 Deferral

## Context & Rationale

The product owner has intentionally reprioritized the frontend redesign sequence.
**Immediate Goal:** Deliver a polished, redesigned experience to real end users (**Pemohon / Requester**) as quickly as possible.

Consequently, remaining administration-only and back-office redesign work is **PAUSED** and deferred without altering original wave definitions in `05-IMPLEMENTATION-WAVES.md`.

---

## Wave Classification & Status Tracking

| Wave / Milestone | Scope | Status | Notes |
| :--- | :--- | :--- | :--- |
| **W1** | Visual Foundation & Design Tokens | **PASS** | Design tokens, typography, and color primitives accepted. |
| **W2** | Shell, Navigation & Guest Pages | **PASS / CLOSED** | Desktop sidebar, mobile drawer, topbar, and auth flows accepted. |
| **W3.1** | Locations & Work Teams | **PASS** | Human QA accepted. |
| **W3.2** | Skills & Announcements | **IMPLEMENTED — HUMAN QA INCOMPLETE — DEFERRED** | Skills table corrective was NOT completed; Announcements HQA was NOT completed. Neither screen is silently accepted. |
| **Remaining W3** | Audit Logs, Branding | **DEFERRED** | Unfinished straightforward administration collections paused in favor of user-facing track. |
| **Original W4** | Helpdesk/Multi-role Dashboards, Reports, Users, Services | **DEFERRED / REPRIORITIZED** | Complex administration, analytics, and operational back-office dashboards paused in favor of pure Pemohon track. |

---

## Accelerated Requester-Facing Sequence

The active engineering sequence is now:

1. **W4.1**: Pure Pemohon Dashboard (`resources/views/dashboard.blade.php` for `$isRequesterOnly`)
2. **W5.1**: Pure Pemohon "Tiket Saya" (`resources/views/tickets/requester-index.blade.php`) & Create Ticket (`resources/views/tickets/create.blade.php`)
3. **W6 (Requester-facing)**: Requester-facing presentation within the **ACTIVE** `resources/views/tickets/show.blade.php` and its existing workflow contracts.

### Critical Note on Ticket Detail Views

- **Active Detail View:** `TicketController::show()` renders `resources/views/tickets/show.blade.php` for all authorized users (including pure Pemohon) with server-side authorization/projection.
- **Dormant View:** `resources/views/tickets/requester-show.blade.php` is **dormant / excluded from redesign / do NOT activate**.
- **Active List View:** `TicketController::index()` uses `resources/views/tickets/requester-index.blade.php` for pure Pemohon (`isRequesterOnlyList`).

---

## Wave Status

- **W4.1:** `W4.1 IMPLEMENTED — HUMAN QA REQUIRED`
- **W3.2:** `IMPLEMENTED — HUMAN QA INCOMPLETE — DEFERRED`

---

## Governance & Integrity Rules

- Do NOT rewrite authoritative wave ownership in `05-IMPLEMENTATION-WAVES.md`.
- Do NOT silently mark W3.2 or remaining W3/W4 screens as PASS.
- Do NOT perform opportunistic changes on admin modules during requester waves.
- Uphold "Ubah wajahnya, jangan bongkar mesinnya": Presentation redesign only; strict preservation of backend models, policies, controllers, database contracts, and Livewire lifecycle.
