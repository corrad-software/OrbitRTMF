# RTMF Tab Mapping — DSSB-LZS-NAS-RTMF-PRF-AS-QS_QB

**Project:** New Agihan System (NAS)  
**Module:** [BF-PRF] Profiling  
**Sub Module:** Pendaftaran Asnaf  
**File:** DSSB-LZS-NAS-RTMF-PRF-AS-QS_QB.xlsx

---

## Conventions

| Prefix | Meaning |
|---|---|
| `PRF-AS-QS-xx` | Spec sheet — Functional Requirement |
| `EX-PRF-AS-QS-xx` | Frontend mockup/example for the corresponding spec |
| `PRF-AS-QB-xx` | Spec sheet — Pukal (Bulk) variant |
| `EX-PRF-AS-QB-xx` | Frontend mockup/example for the corresponding Pukal spec |
| `PRF-AS-FT` | Spec sheet — Pendaftaran Lengkap (Full Registration) |
| `EX-PRF-AS-FT` | Frontend mockup for Pendaftaran Lengkap |

---

## Section 1 — Pendaftaran Pantas Perseorangan (QS)

> Individual Quick Registration flow — from profile search through to status approval.

### Group A: Carian & Paparan Profil

| Spec Tab | Frontend Tab | Business Requirement | Actor | Key Screen / Component |
|---|---|---|---|---|
| `PRF-AS-QS-01_01` | `EX-PRF-AS-QS-01_01` | Carian Profil | Pendaftar | Skrin carian profil asnaf/bukan asnaf menggunakan Jenis Pengenalan + ID |
| `PRF-AS-QS-01_02` | `EX-PRF-AS-QS-01_02` | Paparan Hasil Carian | Pendaftar | Senarai hasil carian — sortable, bilangan data per skrin configurable |

**Data Dictionary (01_01, 01_02):** `rekod_profil`, `profil_asnaf`, `rekod_individu`, `profil_individu`

---

### Group B: Isi Borang Permohonan (Tab-by-Tab)

> Multi-tab form filled by Pemohon/Pendaftar. Each spec represents one tab inside the registration form.

| Spec Tab | Frontend Tab | Tab Label | Business Requirement | Actor | Notes |
|---|---|---|---|---|---|
| `PRF-AS-QS-02_01_01` | `EX-PRF-AS-QS-02_01_01` | Maklumat Peribadi | Isi Borang Permohonan | Pemohon/Pendaftar | Personal info — Jenis Pengenalan, IC, etc. |
| `PRF-AS-QS-02_01_02` | `EX-PRF-AS-QS-02_01_02` | Maklumat Alamat | Isi Borang Permohonan | Pemohon/Pendaftar | Address details |
| `PRF-AS-QS-02_01_03A` | _(no EX tab)_ | Maklumat Had Kifayah Pemohon | Isi Borang Permohonan | Pemohon/Pendaftar | Had Kifayah info for head of household (variant A — data entry state) |
| `PRF-AS-QS-02_01_03` | `EX-PRF-AS-QS-02_01_03` | Maklumat Perakuan Pemohon | Isi Borang Permohonan | Pemohon/Pendaftar | Applicant declaration/attestation |
| `PRF-AS-QS-02_01_04` | `EX-PRF-AS-QS-02_01_04` | Pengesahan Pendapatan | Isi Borang Permohonan | Pemohon/Pendaftar | Income verification fields |
| `PRF-AS-QS-02_01_05` | `EX-PRF-AS-QS-02_01_05` | Maklumat Pengesahan Permastautin | Isi Borang Permohonan | Pemohon/Pendaftar | Residency verification — signed by Wakil Rakyat / Penghulu / Ketua Kampung / Nazir Masjid / LZS officers etc. |
| `PRF-AS-QS-02_01_06` | `EX-PRF-AS-QS-02_01_06` | Maklumat Pendaftar | Isi Borang Permohonan | Pemohon/Pendaftar | Registrar info |

**Data Dictionary (02_01_01, 02_01_02):** `rekod_profil`, `profil_asnaf`, `rekod_individu`, `profil_individu`

---

### Group C: Semakan, Notifikasi & Pengiraan

| Spec Tab | Frontend Tab | Business Requirement | Actor | Key Screen / Component |
|---|---|---|---|---|
| `PRF-AS-QS-03` | `EX-PRF-AS-QS-03` | Semakan Maklumat Permohonan | Pegawai LZS | Tab Semakan — verify docs & attachments; ICU JPN integration for IC verification (fallback: 'Not Verified') |
| `PRF-AS-QS-04` | _(no EX tab)_ | Terima Notifikasi | NAS / Pemohon / Pengesah Status Asnaf | System sends notifications to Pemohon and Pengesah Status Asnaf |
| `PRF-AS-QS_05` | _(no EX tab)_ | Pengiraan Had Kifayah | NAS (System) | Auto-calculation of had kifayah per household using SQL logic across residential status, age, schooling, working categories |

---

### Group D: Syor Kategori & Pengesahan Status

| Spec Tab | Frontend Tab | Business Requirement | Actor | Key Screen / Component |
|---|---|---|---|---|
| `PRF-AS-QS-06` | _(no EX tab)_ | Syor Kategori | NAS (System) | Tab Syor Kategori — display had kifayah calculation result; recommend family status |
| `PRF-AS-QS-07` | `EX-PRF-AS-QS-07` | Pengesahan Status Asnaf | Pegawai LZS | Tab Syor Kategori — Had Kifayah + Multidimensi result; officer confirms/overrides |
| `PRF-AS-QS-07_01` | `EX-PRF-AS-QS-07_01` | Siasatan di Lapangan (Laporan) | Pengesah Status | Tab Syor Kategori — field investigation report by PAK/PAK+/Pegawai LZS |
| `PRF-AS-QS-09` | `EX-PRF-AS-QS-09` | Siasatan di Lapangan (Tindakan) | PAK / PAK+ / Pegawai LZS | Tab Syor Kategori — field investigation action screen |
| `PRF-AS-QS-10` | `EX-PRF-AS-FR-10` | Kelulusan Status Asnaf | Pelulus | Tab Syor Kategori — Had Kifayah + Multidimensi; officer performs final approval of asnaf status |

> **Note:** `EX-PRF-AS-FR-10` is the frontend tab for `PRF-AS-QS-10` (naming discrepancy — FR vs QS).

---

## Section 2 — Pendaftaran Lengkap (FT)

> Full registration flow (web application only).

| Spec Tab | Frontend Tab | Business Requirement | Actor | Key Screen / Component |
|---|---|---|---|---|
| `PRF-AS-FT` | `EX-PRF-AS-FT` | Pendaftaran Lengkap | Pemohon/Pendaftar | Tab Family Tree — full household tree for complete asnaf registration |

---

## Section 3 — Pendaftaran Pantas Pukal / Bulk (QB)

> Bulk/group Quick Registration flow — disaster relief / batch registration scenarios.

### Group A: Entry Point

| Spec Tab | Frontend Tab | Business Requirement | Actor | Key Screen / Component |
|---|---|---|---|---|
| `PRF-AS-QB` | _(index/cover tab)_ | — | — | Cover/index sheet for QB section |
| `PRF-AS-QB-01` | `EX-PRF-AS-QB-01` | Senarai Pendaftaran Pantas Pukal | Pendaftar | Senarai Bencana — dropdown Nama Bencana, date filter; entry point for bulk registration |

---

### Group B: Isi Borang Permohonan Pukal

| Spec Tab | Frontend Tab | Tab Label | Business Requirement | Actor | Notes |
|---|---|---|---|---|---|
| `PRF-AS-QB-01_01` | `EX-PRF-AS-QB-01_01` | Maklumat Peribadi | Isi Borang Permohonan (web) | Pendaftar | Same field structure as QS-02_01_01 — manual entry per applicant |
| `PRF-AS-QB-02_01` | `EX-PRF-AS-QB-02_01` | Maklumat Peribadi (Import) | Isi Borang Permohonan (web) | Pendaftar | Import via file — applicant data loaded from file into list |

**Data Dictionary (QB-01_01, QB-02_01):** `rekod_profil`, `profil_asnaf`, `rekod_individu`, `profil_individu`

---

### Group C: Semakan & Paparan Maklumat Pukal

| Spec Tab | Frontend Tab | Business Requirement | Actor | Key Screen / Component |
|---|---|---|---|---|
| `PRF-AS-QB-03_01` | `EX-PRF-AS-QB-03_01` | Semakan Maklumat | Pendaftar | Search/filter by kategori (Fakir/Miskin/Non-FM); fill bantuan info per category |
| `PRF-AS-QB-03_02` | `EX-PRF-AS-QB-03_02` | Lihat Maklumat | Pendaftar | View applicant details — tabular list (No, Nama, etc.) |
| `PRF-AS-QB-03_03` | `EX-PRF-AS-QB-03_03` | Paparan Maklumat Pemohon | Pendaftar | Read-only view of applicant + tanggungan details (same tabs as QS form but ReadOnly) |

**Data Dictionary (QB-03_01 to QB-03_03):** `rekod_profil`, `profil_asnaf`, `rekod_individu`, `profil_individu`

---

### Group D: Notifikasi & Syor Asnaf

| Spec Tab | Frontend Tab | Business Requirement | Actor | Key Screen / Component |
|---|---|---|---|---|
| `PRF-AS-QB-04` | _(no EX tab)_ | Terima Notifikasi | NAS / Pemohon / Pengesah Status Asnaf | Same notification structure as QS-04 — bulk variant |
| `PRF-AS-QB-05` | `EX-PRF-AS-QB-05_01` | Syor Asnaf | EOAD | Senarai penerima bantuan pukal berkategori Non-FM; EOAD sends notification to eligible recipients to register as asnaf |

---

## Full Tab Index

| # | Tab Name | Type | Paired With | Section |
|---|---|---|---|---|
| 1 | `Pendaftaran Pantas` | Cover/Index | — | Overview |
| 2 | `PRF-AS-QS-01_01` | Spec | `EX-PRF-AS-QS-01_01` | QS: Carian Profil |
| 3 | `EX-PRF-AS-QS-01_01` | Frontend | `PRF-AS-QS-01_01` | QS: Carian Profil |
| 4 | `PRF-AS-QS-01_02` | Spec | `EX-PRF-AS-QS-01_02` | QS: Hasil Carian |
| 5 | `EX-PRF-AS-QS-01_02` | Frontend | `PRF-AS-QS-01_02` | QS: Hasil Carian |
| 6 | `PRF-AS-QS-02_01_01` | Spec | `EX-PRF-AS-QS-02_01_01` | QS: Tab Maklumat Peribadi |
| 7 | `EX-PRF-AS-QS-02_01_01` | Frontend | `PRF-AS-QS-02_01_01` | QS: Tab Maklumat Peribadi |
| 8 | `PRF-AS-QS-02_01_02` | Spec | `EX-PRF-AS-QS-02_01_02` | QS: Tab Maklumat Alamat |
| 9 | `EX-PRF-AS-QS-02_01_02` | Frontend | `PRF-AS-QS-02_01_02` | QS: Tab Maklumat Alamat |
| 10 | `PRF-AS-QS-02_01_03A` | Spec | _(none)_ | QS: Tab Had Kifayah (variant A) |
| 11 | `PRF-AS-QS-02_01_03` | Spec | `EX-PRF-AS-QS-02_01_03` | QS: Tab Perakuan Pemohon |
| 12 | `EX-PRF-AS-QS-02_01_03` | Frontend | `PRF-AS-QS-02_01_03` | QS: Tab Perakuan Pemohon |
| 13 | `PRF-AS-QS-02_01_04` | Spec | `EX-PRF-AS-QS-02_01_04` | QS: Tab Pengesahan Pendapatan |
| 14 | `EX-PRF-AS-QS-02_01_04` | Frontend | `PRF-AS-QS-02_01_04` | QS: Tab Pengesahan Pendapatan |
| 15 | `PRF-AS-QS-02_01_05` | Spec | `EX-PRF-AS-QS-02_01_05` | QS: Tab Pengesahan Permastautin |
| 16 | `EX-PRF-AS-QS-02_01_05` | Frontend | `PRF-AS-QS-02_01_05` | QS: Tab Pengesahan Permastautin |
| 17 | `PRF-AS-QS-02_01_06` | Spec | `EX-PRF-AS-QS-02_01_06` | QS: Tab Maklumat Pendaftar |
| 18 | `EX-PRF-AS-QS-02_01_06` | Frontend | `PRF-AS-QS-02_01_06` | QS: Tab Maklumat Pendaftar |
| 19 | `PRF-AS-FT` | Spec | `EX-PRF-AS-FT` | FT: Family Tree (Lengkap) |
| 20 | `EX-PRF-AS-FT` | Frontend | `PRF-AS-FT` | FT: Family Tree (Lengkap) |
| 21 | `PRF-AS-QS-03` | Spec | `EX-PRF-AS-QS-03` | QS: Semakan Dokumen |
| 22 | `EX-PRF-AS-QS-03` | Frontend | `PRF-AS-QS-03` | QS: Semakan Dokumen |
| 23 | `PRF-AS-QS-04` | Spec | _(none)_ | QS: Notifikasi |
| 24 | `PRF-AS-QS_05` | Spec | _(none)_ | QS: Pengiraan Had Kifayah |
| 25 | `PRF-AS-QS-06` | Spec | _(none)_ | QS: Syor Kategori |
| 26 | `PRF-AS-QS-07` | Spec | `EX-PRF-AS-QS-07` | QS: Pengesahan Status Asnaf |
| 27 | `EX-PRF-AS-QS-07` | Frontend | `PRF-AS-QS-07` | QS: Pengesahan Status Asnaf |
| 28 | `PRF-AS-QS-07_01` | Spec | `EX-PRF-AS-QS-07_01` | QS: Siasatan Lapangan (Laporan) |
| 29 | `EX-PRF-AS-QS-07_01` | Frontend | `PRF-AS-QS-07_01` | QS: Siasatan Lapangan (Laporan) |
| 30 | `PRF-AS-QS-09` | Spec | `EX-PRF-AS-QS-09` | QS: Siasatan Lapangan (Tindakan) |
| 31 | `PRF-AS-QS-10` | Spec | `EX-PRF-AS-FR-10` | QS: Kelulusan Status Asnaf |
| 32 | `EX-PRF-AS-QS-09` | Frontend | `PRF-AS-QS-09` | QS: Siasatan Lapangan (Tindakan) |
| 33 | `EX-PRF-AS-FR-10` | Frontend | `PRF-AS-QS-10` | QS: Kelulusan Status Asnaf |
| 34 | `PRF-AS-QB` | Cover/Index | — | QB Overview |
| 35 | `PRF-AS-QB-01` | Spec | `EX-PRF-AS-QB-01` | QB: Senarai Bencana |
| 36 | `EX-PRF-AS-QB-01` | Frontend | `PRF-AS-QB-01` | QB: Senarai Bencana |
| 37 | `PRF-AS-QB-01_01` | Spec | `EX-PRF-AS-QB-01_01` | QB: Tab Maklumat Peribadi (manual) |
| 38 | `EX-PRF-AS-QB-01_01` | Frontend | `PRF-AS-QB-01_01` | QB: Tab Maklumat Peribadi (manual) |
| 39 | `PRF-AS-QB-02_01` | Spec | `EX-PRF-AS-QB-02_01` | QB: Tab Maklumat Peribadi (import) |
| 40 | `EX-PRF-AS-QB-02_01` | Frontend | `PRF-AS-QB-02_01` | QB: Tab Maklumat Peribadi (import) |
| 41 | `PRF-AS-QB-03_01` | Spec | `EX-PRF-AS-QB-03_01` | QB: Semakan by Kategori |
| 42 | `EX-PRF-AS-QB-03_01` | Frontend | `PRF-AS-QB-03_01` | QB: Semakan by Kategori |
| 43 | `PRF-AS-QB-03_02` | Spec | `EX-PRF-AS-QB-03_02` | QB: Lihat Maklumat |
| 44 | `EX-PRF-AS-QB-03_02` | Frontend | `PRF-AS-QB-03_02` | QB: Lihat Maklumat |
| 45 | `PRF-AS-QB-03_03` | Spec | `EX-PRF-AS-QB-03_03` | QB: Paparan Maklumat Pemohon (ReadOnly) |
| 46 | `EX-PRF-AS-QB-03_03` | Frontend | `PRF-AS-QB-03_03` | QB: Paparan Maklumat Pemohon (ReadOnly) |
| 47 | `PRF-AS-QB-04` | Spec | _(none)_ | QB: Notifikasi |
| 48 | `PRF-AS-QB-05` | Spec | `EX-PRF-AS-QB-05_01` | QB: Syor Asnaf (Non-FM) |
| 49 | `EX-PRF-AS-QB-05_01` | Frontend | `PRF-AS-QB-05` | QB: Syor Asnaf (Non-FM) |

---

## Notes

- **`PRF-AS-QS-02_01_03A`** has no corresponding EX tab. It appears to be an intermediate/variant state of the Had Kifayah tab before `PRF-AS-QS-02_01_03` (Perakuan).
- **`PRF-AS-QS_05`** uses an underscore before `05` (not a hyphen) — naming inconsistency in the file.
- **`EX-PRF-AS-FR-10`** is named with `FR` instead of `QS` — corresponds to spec `PRF-AS-QS-10`.
- Sheets `PRF-AS-QS-04` and `PRF-AS-QB-04` (Notifikasi) have no frontend EX tabs — notification is system-driven.
- `PRF-AS-QS_05` and `PRF-AS-QS-06` (Pengiraan Had Kifayah / Syor Kategori) have no EX tabs — system-computed outputs, not user-input screens.
