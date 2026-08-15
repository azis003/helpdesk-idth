# 35 — User-Facing Priority Acceleration & W3 Deferral

## Context & Rationale

The product owner has intentionally reprioritized the frontend redesign sequence.
**Immediate Goal:** Deliver a polished, redesigned experience to real end users (**Pemohon / Requester**) as quickly as possible.

Consequently, remaining administration-only redesign work is **PAUSED** and deferred to a later milestone.

---

## Wave Status Tracking

| Wave / Milestone | Scope | Status | Notes |
| :--- | :--- | :--- | :--- |
| **W1** | Visual Foundation & Design Tokens | **PASS** | Design tokens, typography, and color primitives accepted. |
| **W2** | Shell, Navigation & Guest Pages | **PASS / CLOSED** | Desktop sidebar, mobile drawer, topbar, and auth flows accepted. |
| **W3.1** | Locations & Work Teams | **PASS** | Human QA accepted. |
| **W3.2** | Skills & Announcements | **IMPLEMENTED — HUMAN QA INCOMPLETE — DEFERRED** | Skills table corrective was NOT completed; Announcements HQA was NOT completed. Neither screen is silently accepted. |
| **W3.3+** | Audit Logs, Branding, Users, Services, Reports | **DEFERRED** | Paused prior to development in favor of requester-facing track. |

---

## Accelerated Requester-Facing Sequence

The active engineering sequence is now:

1. **W4.1**: Requester Dashboard (`dashboard.blade.php` for pure Pemohon)
2. **W5.1**: Requester Tickets & Create Ticket (`tickets/requester-index.blade.php`, `tickets/create.blade.php`)
3. **W6**: Requester-facing Ticket Detail (`tickets/requester-show.blade.php`)

---

## Governance & Integrity Rules

- Do NOT silently mark W3.2 or remaining W3 screens as PASS.
- Do NOT perform opportunistic changes on admin modules during requester waves.
- Uphold "Ubah wajahnya, jangan bongkar mesinnya": Presentation redesign only; strict preservation of backend models, policies, controllers, database contracts, and Livewire lifecycle.
