# Changelog

## [2026-05-15] — Full Page Catalog Seeding (521 pages), Actor System Overhaul

### Added
- **Page Catalog — complete NAS system coverage** across 8 modules, 521 frontend pages total:
  - `TUN` Pengurusan Tunai — Phases 2–4: Rekupmen (13), Laporan (9), Konfigurasi sub-modules KPG/KTB/KTP/KTU/KUP/KAT (26) = 48 pages
  - `ADU` Pengurusan Aduan — Phases 1–2: Dashboard, Senarai, Tugasan, Daftar, Butiran, Laporan, Konfigurasi sub-modules KFQ/KKM/KPN/KVI = 51 pages
  - `PNT` Pentadbir Sistem — Phases 1–2: Dashboard, Pengguna, Konfigurasi, Keselamatan, Kod, Modul Khusus, Notifikasi, Audit, Laporan, Utiliti = 46 pages
  - `PRF` Profiling catch-up — Workflow iframes (PRF-WKF: 3) + Simulasi & Kaunter (PRF-SIM: 4) = 7 pages
  - `PUB` Portal Awam (new module) — Daftar Aduan, FAQ, PA Kad Tauliah, Pendaftaran Bencana, Program, Terima Tawaran = 10 pages
  - `SYS` Sistem Global (new module) — Dashboard Utama, Notifikasi, Profil Saya, Tetapan Profil, Process Flow, Workflow = 6 pages
- **Actor system overhaul** — replaced 6 generic placeholder actors (Pegawai, Penyelia, Pelulus, Admin, Staff, Orang Awam) with 29 real NAS system role codes: SUPER_ADMIN, DEVELOPER, EOAD, EOAD_PRF, EOAD_BTN, EOAD_TNI, PINDAAN_EOAD, KOAD, KOAD_BTN, KOAD_TNI, EPOAD, EPOAD_TNI, PENTADBIR_SISTEM, KJ_TNI, KJ_DPA, KJ_PA, KC_TNI, ORANG_AWAM, SSU, SSU_TNI, PIC_DPA, PIC_PA, PIC_TNI, PENOLONG_AMIL, ASNAF, EKP, PKP, PENTADBIR, ORGANISASI
- New seeders: `RtmfPengurusanTunaiPhase2–4`, `RtmfPengurusanAduanPhase1–2`, `RtmfPentadbirSistemPhase1–2`, `RtmfMissedProfilingSeeder`, `RtmfPublicPortalSeeder`, `RtmfSistemGlobalSeeder`
- All seeders are idempotent (safe to re-run) — use `updateOrCreate` by `spec_id`, delete+re-insert FR items and API endpoints, `sync` actors

### Changed
- `DatabaseSeeder` updated to register all 11 new seeders in correct dependency order

---

## [2026-05-14] — Catalog Import, API Endpoints Tab, Sub-module Fix

### Added
- `POST /api/rtmf-frontends/import` — bulk catalog seeding endpoint; accepts structured JSON payload extracted from `nas-frontend` / `nas-backend` source files
- Import admin page (`/admin/rtmf/import`) with three tabs: Run Payload, Module Queue, User Manual
- API Endpoints tab in Page Catalog editor — document backend API calls per page (new `rtmf_frontend_api_endpoints` table)
- Seeded Daftar Aduan (ADN-DA-01) with 25 FR items and 19 API endpoints
- Max 8-tier depth limit on sub-module nesting

### Fixed
- Sub-module `parent_id` cross-module validation — previously accepted a parent from a different module
- Soft-delete orphan bug — deleting a parent sub-module now nullifies `parent_id` on its direct children

### Changed
- Scenario tab and Page Links section hidden in Page Catalog editor (pending redesign)
- Module Queue table in Import page now shows Last Seeded timestamp

### Tests
- Added `RtmfModuleTest` with 21 cases covering multi-tier hierarchy, both bugs, sort order, reorder, and auth guards

---

## [2026-05-12] — Scenario Editor, Feedback, Defect Reporting

### Added
- Flow Scenarios module with step editor and SVG diagram (horizontal layout with bezier curves)
- Frontend Feedback tab in Page Catalog editor (Business Analyst / QA / Technical review)
- Defect Reporting view (MantisBT proxy integration)

---

## [2026-05-07] — Scenario Groups

### Added
- Scenario groups and rows on frontend catalog entries

---

## [2026-04-20] — Page Catalog Enhancements

### Added
- Stakeholder requirement field on frontend entries
- Environment URLs (dev / staging / production)
- Frontend attachments
- FR line items table with drag-to-reorder

### Changed
- Moved `vue_path` back to `rtmf_frontends` table

---

## [2026-04-18] — Initial Release

### Added
- OrbitRTMF Laravel + Vue SPA with PostgreSQL
- Page Catalog (RTMF) module — modules, sub-modules, actors, frontends
- Snapshot capture for URL paths
- RBAC with Sanctum authentication
- Admin dashboard, posts, pages, media, users, roles, settings
