# RTMF Spec → Frontend File Mapping — DSSB-LZS-NAS-RTMF-PRF-AS-QS_QB

**Companion to:** `RTMF-PRF-AS-QS_QB-TAB-MAPPING.md`
**Target repo:** `/Users/macbook/Documents/NAS/nas-frontend`
**Method:** Static crawl — no IDs exist in code, matches inferred from folder names, page titles, tab labels (Malay), form field names, and business context.
**Confidence legend:** High = explicit label match · Medium = structural/contextual match · Low = best guess only.

---

## Section 1 — Pendaftaran Pantas Perseorangan (QS)

### Group A — Carian & Paparan Profil

| Spec ID | Frontend File | Evidence | Confidence |
|---|---|---|---|
| `PRF-AS-QS-01_01` | [carian-profil-ppp/index.vue](../nas-frontend/pages/profiling/asnaf/carian-profil-ppp/index.vue) (search form section) | Title "Carian Profil"; fields Jenis Pengenalan ID + No Pengenalan | High |
| `PRF-AS-QS-01_02` | [carian-profil-ppp/index.vue](../nas-frontend/pages/profiling/asnaf/carian-profil-ppp/index.vue) (results table section, ~L140+) | "Hasil Carian…" computed; RsTable with Nama/Status/Tindakan columns; paginated | High |

> Both 01_01 and 01_02 live in the **same page file** — search form + results table combined.

### Group B — Isi Borang Permohonan (QS-02 Multi-Tab Form)

**Container:** [carian-profil-ppp/pendaftaran-pantas-perseorangan/index.vue](../nas-frontend/pages/profiling/asnaf/carian-profil-ppp/pendaftaran-pantas-perseorangan/index.vue)

This is a **single-file 5-tab container** (`MultiSectionFormContainer`) with internal step-based subtabs inside Tab 1. Spec tabs 02_01_01 – 02_01_06 map to steps/sub-sections inside this one container, **not** separate files.

| Spec ID | Tab / Step inside container | Confidence |
|---|---|---|
| `PRF-AS-QS-02_01_01` Maklumat Peribadi | Tab 1 "Maklumat Pemohon" → Step: Peribadi | High |
| `PRF-AS-QS-02_01_02` Maklumat Alamat | Tab 1 → Step: Alamat (combined with Peribadi as Step 1 in some configs) | High |
| `PRF-AS-QS-02_01_03A` Had Kifayah (data entry variant A) | Tab 2 "Jadual Had Kifayah" — `JadualPengiraanHadKifayah` component | Medium |
| `PRF-AS-QS-02_01_03` Perakuan Pemohon | Internal step (profilPerakuanId state) | Medium |
| `PRF-AS-QS-02_01_04` Pengesahan Pendapatan | Tab 1 → Step: Pekerjaan & Pendapatan | Medium |
| `PRF-AS-QS-02_01_05` Pengesahan Permastautin | Tab 5 "Pengesahan" → Step: Bermastautin | Medium |
| `PRF-AS-QS-02_01_06` Maklumat Pendaftar | Tab 5 "Pengesahan" → Step: Akhir (registrar info) | Low–Medium |

### Group C — Semakan, Notifikasi & Pengiraan

| Spec ID | Frontend File | Evidence | Confidence |
|---|---|---|---|
| `PRF-AS-QS-03` | [semakan-maklumat/index.vue](../nas-frontend/pages/profiling/asnaf/semakan-maklumat/index.vue) · detail: [semakan-data-lengkap/index.vue](../nas-frontend/pages/profiling/asnaf/semakan-maklumat/semakan-data-lengkap/index.vue) | "Senarai Permohonan untuk Disemak"; Perubahan comparison section | High |
| `PRF-AS-QS-04` | — | System-driven notifications; no user screen (confirmed by spec: no EX tab) | N/A |
| `PRF-AS-QS_05` | Tab 2 `JadualPengiraanHadKifayah` inside pendaftaran-pantas-perseorangan + [keputusan-pengiraan/[id].vue](../nas-frontend/pages/profiling/asnaf/semakan-maklumat/keputusan-pengiraan/[id].vue) | Auto-calc output displayed | High |
| `PRF-AS-QS-06` | — (no standalone page) | Integrated into kelulusan/siasatan workflows | Low |

### Group D — Syor Kategori & Pengesahan Status

| Spec ID | Frontend File | Evidence | Confidence |
|---|---|---|---|
| `PRF-AS-QS-07` Pengesahan Status Asnaf | [kelulusan/index.vue](../nas-frontend/pages/profiling/asnaf/kelulusan/index.vue) | "Senarai Kelulusan"; status tabs Menunggu Kelulusan / Selesai | Medium |
| `PRF-AS-QS-07_01` Siasatan Lapangan — Laporan | [siasatan/[id].vue](../nas-frontend/pages/profiling/asnaf/siasatan/[id].vue) | "Semak dan siasat maklumat profil asnaf"; SiasatanTindakanSidebar | High |
| `PRF-AS-QS-09` Siasatan Lapangan — Tindakan | [siasatan/[id].vue](../nas-frontend/pages/profiling/asnaf/siasatan/[id].vue) (same page, sidebar actions) | Tindakan sidebar integrated into same investigation page | High |
| `PRF-AS-QS-10` Kelulusan Status Asnaf (Pelulus) | [kelulusan/index.vue](../nas-frontend/pages/profiling/asnaf/kelulusan/index.vue) | Same list; role-filtered for Pelulus | Medium–High |

> **Shared-file alert:** QS-07 and QS-10 both land on `kelulusan/index.vue`. Tagging must distinguish role/action (Pegawai LZS vs Pelulus). QS-07_01 and QS-09 both land on `siasatan/[id].vue` (report view vs action sidebar).

---

## Section 2 — Pendaftaran Lengkap (FT)

| Spec ID | Frontend File(s) | Evidence | Confidence |
|---|---|---|---|
| `PRF-AS-FT` | Form: [carian-profil/pendaftaran-lengkap/index.vue](../nas-frontend/pages/profiling/asnaf/carian-profil/pendaftaran-lengkap/index.vue) · Visualization: [family-tree/index.vue](../nas-frontend/pages/profiling/family-tree/index.vue) | Form has Tab 3 "Tanggungan"; family-tree page title "Salasilah Keluarga" | High |

**FT form tab structure** (in `carian-profil/pendaftaran-lengkap/index.vue`):
1. Aduan · 2. Maklumat Pemohon (steps: Peribadi & Kesihatan / Pendidikan / Pinjaman & Aset / Pekerjaan & Pendapatan) · 3. Tanggungan · 4. Bantuan · 5. Pengesahan (steps: Bermastautin / Akhir)

---

## Section 3 — Pendaftaran Pantas Pukal (QB)

### Group A — Entry Point

| Spec ID | Frontend File | Evidence | Confidence |
|---|---|---|---|
| `PRF-AS-QB-01` | [pendaftaran-pantas-pukal/index.vue](../nas-frontend/pages/profiling/asnaf/pendaftaran-pantas-pukal/index.vue) | Title "Pendaftaran Pantas"; table columns Nama Bencana / Lokasi / Tarikh / Status | High |

### Group B — Isi Borang Permohonan Pukal

| Spec ID | Frontend File | Evidence | Confidence |
|---|---|---|---|
| `PRF-AS-QB-01_01` Manual | [permohonan/tambah.vue](../nas-frontend/pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/tambah.vue) | "Permohonan" + breadcrumb "Bantuan Bencana"; manual per-applicant entry | High |
| `PRF-AS-QB-02_01` Import | [permohonan/index.vue](../nas-frontend/pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/index.vue) | `parseExcelFile` / `parseCSVFile` imports; Excel template download; staging pool | High |

### Group C — Semakan & Paparan Maklumat Pukal

| Spec ID | Frontend File | Evidence | Confidence |
|---|---|---|---|
| `PRF-AS-QB-03_01` Semakan by kategori | [permohonan/index.vue](../nas-frontend/pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/index.vue) | `kategoriAsnafIdMapping: Fakir / Miskin / Non-F&M`; selectedKategoriCard filter | High |
| `PRF-AS-QB-03_02` Lihat Maklumat | [permohonan/[batchId]/index.vue](../nas-frontend/pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/[batchId]/index.vue) | Unified tabular batch layout; mode "lihat" vs "permohonan" | High |
| `PRF-AS-QB-03_03` Paparan Read-Only | [permohonan/[batchId]/index.vue](../nas-frontend/pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/[batchId]/index.vue) (isReadOnly mode) | Reuses QS form tabs in read-only mode | High |

### Group D — Notifikasi & Syor Asnaf

| Spec ID | Frontend File | Evidence | Confidence |
|---|---|---|---|
| `PRF-AS-QB-04` | — | System-driven, no EX tab in spec | N/A |
| `PRF-AS-QB-05` Syor Asnaf (EOAD, Non-FM) | [permohonan/index.vue](../nas-frontend/pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/index.vue) (LULUS status tab) | Approved batch state where EOAD sends notification | Medium |

---

## Orphan Screens — Exist in Frontend, Not in Spec

Candidates for undocumented features or follow-up clarification with stakeholders:

| Path | Purpose |
|---|---|
| [asnaf/penilaian-awal/aduan/index.vue](../nas-frontend/pages/profiling/asnaf/penilaian-awal/aduan/index.vue) | Complaint-triggered initial assessment intake |
| [asnaf/penilaian-awal/kaunter/index.vue](../nas-frontend/pages/profiling/asnaf/penilaian-awal/kaunter/index.vue) | Counter-based initial assessment intake |
| [asnaf/sokongan/index.vue](../nas-frontend/pages/profiling/asnaf/sokongan/index.vue) + [[id].vue](../nas-frontend/pages/profiling/asnaf/sokongan/[id].vue) | EOAD endorsement workflow |
| [asnaf/carian-profil/index.vue](../nas-frontend/pages/profiling/asnaf/carian-profil/index.vue) | General asnaf profile search (non-PPP variant) |
| [asnaf/carian-profil/lihat/[id].vue](../nas-frontend/pages/profiling/asnaf/carian-profil/lihat/[id].vue) | View existing asnaf profile |
| [asnaf/carian-profil/kemaskini/[id].vue](../nas-frontend/pages/profiling/asnaf/carian-profil/kemaskini/[id].vue) | Update existing asnaf profile |
| [asnaf/poc-multi-section-form/index.vue](../nas-frontend/pages/profiling/asnaf/poc-multi-section-form/index.vue) | POC / experimental — possibly deprecated |
| [pendaftaran-pantas-pukal/carian-pemohon/index.vue](../nas-frontend/pages/profiling/asnaf/pendaftaran-pantas-pukal/carian-pemohon/index.vue) | Bulk applicant search within batch |
| [pendaftaran-pantas-pukal/permohonan/draft/[draftId]/index.vue](../nas-frontend/pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/draft/[draftId]/index.vue) | Draft batch recovery/edit |
| [family-tree/individu/[id].vue](../nas-frontend/pages/profiling/family-tree/individu/[id].vue) | Individual family tree member detail |
| [family-tree/[id]/ahli/[memberId].vue](../nas-frontend/pages/profiling/family-tree/[id]/ahli/[memberId].vue) | Family member add/edit |
| [family-tree/saya/index.vue](../nas-frontend/pages/profiling/family-tree/saya/index.vue) | Applicant's own family tree view |

---

## Spec Tabs With No Frontend Match (System-Only or Not Yet Built)

| Spec ID | Reason |
|---|---|
| `PRF-AS-QS-04` | System notification only — expected, spec has no EX tab |
| `PRF-AS-QS-06` | Syor Kategori display — currently integrated into kelulusan/siasatan, no dedicated tab |
| `PRF-AS-QB-04` | System notification only — expected |

---

## Frontend Layout Overview

**QS (Individual)** — multi-tab single-file form. The entire applicant registration lives inside [pendaftaran-pantas-perseorangan/index.vue](../nas-frontend/pages/profiling/asnaf/carian-profil-ppp/pendaftaran-pantas-perseorangan/index.vue) as 5 tabs with internal steppers. Downstream workflow (semakan → siasatan → kelulusan) spreads across **separate pages**.

**QB (Bulk)** — multi-page workflow keyed on disaster (`Bencana`) and batch ID. Separate pages for list / manual-add / import / batch-review / draft recovery. No separate semakan/kelulusan pages — approval is integrated into the batch-review page.

**FT (Full)** — multi-page workflow. Form container at `carian-profil/pendaftaran-lengkap` plus a dedicated `family-tree` visualization tree.

**Shared components** (used across flows): `MultiSectionFormContainer`, `StepperTabs`, `FormSectionWrapper`, `FormActionSidebar`, `SiasatanTindakanSidebar`, `SokonganTindakanSidebar`, `JadualPengiraanHadKifayah`, `RsTable`.

---

## Recommended Tagging Strategy

Since no IDs exist in the code, add a **`data-rtmf-id`** attribute on the root container of each mapped screen/tab, matching the spec ID. For single-file multi-tab forms (QS-02 container, FT container), tag at the **tab-content root** and step-wrapper level so each 02_01_0x sub-tab is individually traceable.

Suggested convention:
```html
<div data-rtmf-id="PRF-AS-QS-02_01_01" data-rtmf-section="qs-b1"> … </div>
```

Pair with a top-of-file comment listing the spec ID(s) the file implements, so grep finds both the attribute and the provenance note.
