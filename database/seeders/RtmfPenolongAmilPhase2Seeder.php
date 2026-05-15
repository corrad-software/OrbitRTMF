<?php

namespace Database\Seeders;

use App\Models\RtmfActor;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendApiEndpoint;
use App\Models\RtmfFrontendItem;
use App\Models\RtmfModule;
use App\Models\RtmfProject;
use App\Models\RtmfSubModule;
use Illuminate\Database\Seeder;

class RtmfPenolongAmilPhase2Seeder extends Seeder
{
    public function run(): void
    {
        $project = RtmfProject::firstOrFail();

        $module = RtmfModule::firstOrCreate(
            ['code' => 'PAM'],
            ['name' => 'Penolong Amil', 'sort_order' => 30, 'project_id' => $project->id],
        );
        if (! $module->project_id) {
            $module->update(['project_id' => $project->id]);
        }

        $dok = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'DOK'],
            ['name' => 'Konfigurasi Dokumen', 'sort_order' => 70],
        );
        $kat = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'KAT'],
            ['name' => 'Konfigurasi Kategori', 'sort_order' => 80],
        );

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        $staff = [$pegawai->id, $penyelia->id];
        $all   = [$pegawai->id, $penyelia->id, $pelulus->id];

        $mid = $module->id;

        // ══════════════════════════════════════════════════════════════════
        // KONFIGURASI DOKUMEN (DOK) — 5 pages
        // ══════════════════════════════════════════════════════════════════

        $dok01 = $this->seed($mid, $dok->id, 'PAM-DOK-01', 'Senarai Konfigurasi Dokumen',
            'pages/penolong-amil/konfigurasi/dokumen/index.vue', 10, $all,
            [
                ['screen_name' => 'Senarai Dokumen', 'label' => 'Jadual Konfigurasi Dokumen',  'type' => 'Table',  'condition' => 'Columns: Nama Dokumen, Jenis, Wajib/Tidak, Status Aktif'],
                ['screen_name' => 'Senarai Dokumen', 'label' => 'Butang Tambah',               'type' => 'Button', 'condition' => 'Navigate to dokumen/create.vue'],
                ['screen_name' => 'Senarai Dokumen', 'label' => 'Butang Lihat',                'type' => 'Button', 'condition' => 'Navigate to dokumen/[id]/index.vue for detail'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/konfigurasi/dokumen', 'description' => 'Fetch list of document configuration records for PA registration'],
            ]);

        $dok02 = $this->seed($mid, $dok->id, 'PAM-DOK-02', 'Cipta Konfigurasi Dokumen',
            'pages/penolong-amil/konfigurasi/dokumen/create.vue', 20, $staff,
            [
                ['screen_name' => 'Cipta Dokumen', 'label' => 'Nama Dokumen',                  'type' => 'Text',   'condition' => 'Document name (e.g. "Salinan IC", "Surat Sokongan")', 'mandatory' => true],
                ['screen_name' => 'Cipta Dokumen', 'label' => 'Jenis Fail',                    'type' => 'Select', 'condition' => 'Accepted format: PDF / Imej / Semua'],
                ['screen_name' => 'Cipta Dokumen', 'label' => 'Wajib',                         'type' => 'Toggle', 'condition' => 'Mark as mandatory document for PA registration'],
                ['screen_name' => 'Cipta Dokumen', 'label' => 'Keterangan',                    'type' => 'Textarea', 'condition' => 'Optional instructions for the applicant'],
                ['screen_name' => 'Cipta Dokumen', 'label' => 'Butang Simpan',                 'type' => 'Button', 'condition' => 'Create document configuration (triggers kelulusan workflow)'],
            ],
            [
                ['method' => 'POST', 'endpoint' => '/penolong-amil/konfigurasi/dokumen', 'description' => 'Create new document requirement configuration'],
            ]);

        $dok03 = $this->seed($mid, $dok->id, 'PAM-DOK-03', 'Butiran Konfigurasi Dokumen',
            'pages/penolong-amil/konfigurasi/dokumen/[id]/index.vue', 30, $all,
            [
                ['screen_name' => 'Butiran Dokumen', 'label' => 'Maklumat Dokumen',            'type' => 'Display', 'condition' => 'Read-only: Nama, Jenis Fail, Wajib, Status Aktif, Keterangan'],
                ['screen_name' => 'Butiran Dokumen', 'label' => 'Status Kelulusan',            'type' => 'Display', 'condition' => 'Current approval status for this config'],
                ['screen_name' => 'Butiran Dokumen', 'label' => 'Butang Edit',                 'type' => 'Button',  'condition' => 'Navigate to [id]/edit.vue'],
                ['screen_name' => 'Butiran Dokumen', 'label' => 'Butang Kelulusan',            'type' => 'Button',  'condition' => 'Navigate to [id]/kelulusan.vue (pelulus only)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/konfigurasi/dokumen/{id}', 'description' => 'Fetch document config detail'],
            ]);

        $dok04 = $this->seed($mid, $dok->id, 'PAM-DOK-04', 'Edit Konfigurasi Dokumen',
            'pages/penolong-amil/konfigurasi/dokumen/[id]/edit.vue', 40, $staff,
            [
                ['screen_name' => 'Edit Dokumen', 'label' => 'Nama Dokumen',                   'type' => 'Text',    'condition' => 'Editable document name', 'mandatory' => true],
                ['screen_name' => 'Edit Dokumen', 'label' => 'Jenis Fail',                     'type' => 'Select',  'condition' => 'Editable accepted format'],
                ['screen_name' => 'Edit Dokumen', 'label' => 'Wajib',                          'type' => 'Toggle',  'condition' => 'Toggle mandatory status'],
                ['screen_name' => 'Edit Dokumen', 'label' => 'Status Aktif',                   'type' => 'Toggle',  'condition' => 'Enable/disable this document requirement'],
                ['screen_name' => 'Edit Dokumen', 'label' => 'Keterangan',                     'type' => 'Textarea', 'condition' => 'Editable instructions'],
                ['screen_name' => 'Edit Dokumen', 'label' => 'Butang Simpan',                  'type' => 'Button',  'condition' => 'Save edits (triggers kelulusan workflow)'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/konfigurasi/dokumen/{id}', 'description' => 'Fetch doc config for editing'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/konfigurasi/dokumen/{id}', 'description' => 'Update document configuration'],
            ]);

        $dok05 = $this->seed($mid, $dok->id, 'PAM-DOK-05', 'Kelulusan Konfigurasi Dokumen',
            'pages/penolong-amil/konfigurasi/dokumen/[id]/kelulusan.vue', 50, [$pelulus->id],
            [
                ['screen_name' => 'Kelulusan Dokumen', 'label' => 'Butiran Konfigurasi',       'type' => 'Display', 'condition' => 'Read-only: proposed document config values'],
                ['screen_name' => 'Kelulusan Dokumen', 'label' => 'Keputusan Pelulus',         'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Kelulusan Dokumen', 'label' => 'Catatan Pelulus',           'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Kelulusan Dokumen', 'label' => 'Butang Simpan',             'type' => 'Button',  'condition' => 'Submit kelulusan decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/konfigurasi/dokumen/{id}',            'description' => 'Fetch dokumen config for pelulus review'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/konfigurasi/dokumen/{id}/kelulusan',  'description' => 'Submit approval decision for document config'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // KONFIGURASI KATEGORI (KAT) — 15 pages
        // Covers: Kategori (4) + Elaun (5) + Jawatan (3) + Sesi (3)
        // ══════════════════════════════════════════════════════════════════

        // ── Kategori root (4 pages) ──────────────────────────────────────

        $kat01 = $this->seed($mid, $kat->id, 'PAM-KAT-01', 'Senarai Kategori PA',
            'pages/penolong-amil/konfigurasi/kategori/index.vue', 10, $all,
            [
                ['screen_name' => 'Senarai Kategori', 'label' => 'Jadual Kategori',            'type' => 'Table',  'condition' => 'Columns: Nama Kategori, Bilangan Jawatan, Bilangan Sesi, Status Aktif'],
                ['screen_name' => 'Senarai Kategori', 'label' => 'Butang Tambah',              'type' => 'Button', 'condition' => 'Navigate to kategori/create.vue'],
                ['screen_name' => 'Senarai Kategori', 'label' => 'Butang Lihat',               'type' => 'Button', 'condition' => 'Navigate to kategori/[id]/index.vue for detail with sub-tabs'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/konfigurasi/kategori', 'description' => 'Fetch PA category configuration list'],
            ]);

        $kat02 = $this->seed($mid, $kat->id, 'PAM-KAT-02', 'Cipta Kategori PA',
            'pages/penolong-amil/konfigurasi/kategori/create.vue', 20, $staff,
            [
                ['screen_name' => 'Cipta Kategori', 'label' => 'Nama Kategori',                'type' => 'Text',    'condition' => 'Category name (e.g. "Kategori A", "Kategori Baru")', 'mandatory' => true],
                ['screen_name' => 'Cipta Kategori', 'label' => 'Keterangan',                   'type' => 'Textarea', 'condition' => 'Optional description for this PA category'],
                ['screen_name' => 'Cipta Kategori', 'label' => 'Status Aktif',                 'type' => 'Toggle',  'condition' => 'Activate/deactivate category'],
                ['screen_name' => 'Cipta Kategori', 'label' => 'Butang Simpan',                'type' => 'Button',  'condition' => 'Create category (triggers kelulusan workflow)'],
            ],
            [
                ['method' => 'POST', 'endpoint' => '/penolong-amil/konfigurasi/kategori', 'description' => 'Create new PA category configuration'],
            ]);

        $kat03 = $this->seed($mid, $kat->id, 'PAM-KAT-03', 'Butiran Kategori PA',
            'pages/penolong-amil/konfigurasi/kategori/[id]/index.vue', 30, $all,
            [
                ['screen_name' => 'Butiran Kategori', 'label' => 'Maklumat Kategori',          'type' => 'Display', 'condition' => 'Read-only: Nama Kategori, Keterangan, Status'],
                ['screen_name' => 'Butiran Kategori', 'label' => 'Tab Elaun',                  'type' => 'Button',  'condition' => 'Navigate to kategori/[id]/elaun/index.vue'],
                ['screen_name' => 'Butiran Kategori', 'label' => 'Tab Jawatan',                'type' => 'Button',  'condition' => 'Navigate to kategori/[id]/jawatan/index.vue'],
                ['screen_name' => 'Butiran Kategori', 'label' => 'Tab Sesi',                   'type' => 'Button',  'condition' => 'Navigate to kategori/[id]/sesi/index.vue'],
                ['screen_name' => 'Butiran Kategori', 'label' => 'Butang Kelulusan',           'type' => 'Button',  'condition' => 'Navigate to [id]/kelulusan.vue (pelulus only)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}', 'description' => 'Fetch category detail with elaun, jawatan, and sesi sub-data'],
            ]);

        $kat04 = $this->seed($mid, $kat->id, 'PAM-KAT-04', 'Kelulusan Kategori PA',
            'pages/penolong-amil/konfigurasi/kategori/[id]/kelulusan.vue', 40, [$pelulus->id],
            [
                ['screen_name' => 'Kelulusan Kategori', 'label' => 'Butiran Konfigurasi',      'type' => 'Display', 'condition' => 'Proposed category values for review'],
                ['screen_name' => 'Kelulusan Kategori', 'label' => 'Keputusan Pelulus',        'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Kelulusan Kategori', 'label' => 'Catatan Pelulus',          'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Kelulusan Kategori', 'label' => 'Butang Simpan',            'type' => 'Button',  'condition' => 'Submit category kelulusan decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}',            'description' => 'Fetch category for pelulus review'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/kelulusan',  'description' => 'Submit approval decision for category config'],
            ]);

        // ── Elaun (5 pages) ─────────────────────────────────────────────

        $kat05 = $this->seed($mid, $kat->id, 'PAM-KAT-05', 'Senarai Elaun Kategori',
            'pages/penolong-amil/konfigurasi/kategori/[id]/elaun/index.vue', 50, $all,
            [
                ['screen_name' => 'Senarai Elaun', 'label' => 'Jadual Elaun',                  'type' => 'Table',  'condition' => 'Columns: Jenis Elaun, Kadar (RM), Unit, Status Aktif'],
                ['screen_name' => 'Senarai Elaun', 'label' => 'Butang Tambah Elaun',           'type' => 'Button', 'condition' => 'Navigate to elaun/create.vue'],
                ['screen_name' => 'Senarai Elaun', 'label' => 'Butang Lihat',                  'type' => 'Button', 'condition' => 'Navigate to elaun/[elaunId]/index.vue'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/elaun', 'description' => 'Fetch elaun rates configured for this PA category'],
            ]);

        $kat06 = $this->seed($mid, $kat->id, 'PAM-KAT-06', 'Cipta Elaun Kategori',
            'pages/penolong-amil/konfigurasi/kategori/[id]/elaun/create.vue', 60, $staff,
            [
                ['screen_name' => 'Cipta Elaun', 'label' => 'Jenis Elaun',                     'type' => 'Select', 'condition' => 'Mesyuarat / Tugasan Biasa / Tugasan Khas / etc.', 'mandatory' => true],
                ['screen_name' => 'Cipta Elaun', 'label' => 'Kadar Elaun (RM)',                'type' => 'Number', 'condition' => 'Rate per unit', 'mandatory' => true],
                ['screen_name' => 'Cipta Elaun', 'label' => 'Unit',                            'type' => 'Select', 'condition' => 'Per Hari / Per Sesi / Per Mesyuarat'],
                ['screen_name' => 'Cipta Elaun', 'label' => 'Had Maksimum (RM)',               'type' => 'Number', 'condition' => 'Monthly or annual cap (optional)'],
                ['screen_name' => 'Cipta Elaun', 'label' => 'Tarikh Berkuat Kuasa',            'type' => 'Date',   'condition' => 'Effective date for this elaun rate'],
                ['screen_name' => 'Cipta Elaun', 'label' => 'Butang Simpan',                   'type' => 'Button', 'condition' => 'Create elaun rate (triggers kelulusan workflow)'],
            ],
            [
                ['method' => 'POST', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/elaun', 'description' => 'Create new elaun rate for this PA category'],
            ]);

        $kat07 = $this->seed($mid, $kat->id, 'PAM-KAT-07', 'Butiran Elaun Kategori',
            'pages/penolong-amil/konfigurasi/kategori/[id]/elaun/[elaunId]/index.vue', 70, $all,
            [
                ['screen_name' => 'Butiran Elaun', 'label' => 'Maklumat Elaun',                'type' => 'Display', 'condition' => 'Read-only: Jenis, Kadar, Unit, Had, Tarikh Berkuat Kuasa, Status'],
                ['screen_name' => 'Butiran Elaun', 'label' => 'Status Kelulusan',              'type' => 'Display', 'condition' => 'Approval status for this elaun config'],
                ['screen_name' => 'Butiran Elaun', 'label' => 'Butang Edit',                   'type' => 'Button',  'condition' => 'Navigate to elaun/[elaunId]/edit.vue'],
                ['screen_name' => 'Butiran Elaun', 'label' => 'Butang Kelulusan',              'type' => 'Button',  'condition' => 'Navigate to elaun/[elaunId]/kelulusan.vue (pelulus)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/elaun/{elaunId}', 'description' => 'Fetch elaun rate detail'],
            ]);

        $kat08 = $this->seed($mid, $kat->id, 'PAM-KAT-08', 'Edit Elaun Kategori',
            'pages/penolong-amil/konfigurasi/kategori/[id]/elaun/[elaunId]/edit.vue', 80, $staff,
            [
                ['screen_name' => 'Edit Elaun', 'label' => 'Kadar Elaun (RM)',                 'type' => 'Number', 'condition' => 'Editable elaun rate', 'mandatory' => true],
                ['screen_name' => 'Edit Elaun', 'label' => 'Unit',                             'type' => 'Select', 'condition' => 'Editable unit type'],
                ['screen_name' => 'Edit Elaun', 'label' => 'Had Maksimum (RM)',                'type' => 'Number', 'condition' => 'Editable monthly/annual cap'],
                ['screen_name' => 'Edit Elaun', 'label' => 'Tarikh Berkuat Kuasa',             'type' => 'Date',   'condition' => 'Updated effective date'],
                ['screen_name' => 'Edit Elaun', 'label' => 'Butang Simpan',                    'type' => 'Button', 'condition' => 'Save elaun rate changes (triggers kelulusan)'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/elaun/{elaunId}', 'description' => 'Fetch elaun for editing'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/elaun/{elaunId}', 'description' => 'Update elaun rate configuration'],
            ]);

        $kat09 = $this->seed($mid, $kat->id, 'PAM-KAT-09', 'Kelulusan Elaun Kategori',
            'pages/penolong-amil/konfigurasi/kategori/[id]/elaun/[elaunId]/kelulusan.vue', 90, [$pelulus->id],
            [
                ['screen_name' => 'Kelulusan Elaun', 'label' => 'Butiran Kadar Elaun',         'type' => 'Display', 'condition' => 'Read-only: Jenis, Kadar Baru, Unit, Had, Tarikh Berkuat Kuasa'],
                ['screen_name' => 'Kelulusan Elaun', 'label' => 'Keputusan Pelulus',           'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Kelulusan Elaun', 'label' => 'Catatan',                     'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Kelulusan Elaun', 'label' => 'Butang Simpan',               'type' => 'Button',  'condition' => 'Submit elaun kelulusan decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/elaun/{elaunId}',            'description' => 'Fetch elaun config for pelulus review'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/elaun/{elaunId}/kelulusan',  'description' => 'Submit approval decision for elaun rate'],
            ]);

        // ── Jawatan (3 pages) ────────────────────────────────────────────

        $kat10 = $this->seed($mid, $kat->id, 'PAM-KAT-10', 'Senarai Jawatan Kategori',
            'pages/penolong-amil/konfigurasi/kategori/[id]/jawatan/index.vue', 100, $all,
            [
                ['screen_name' => 'Senarai Jawatan', 'label' => 'Jadual Jawatan',              'type' => 'Table',  'condition' => 'Columns: Nama Jawatan, Tanggungjawab, Status Aktif'],
                ['screen_name' => 'Senarai Jawatan', 'label' => 'Butang Lihat Jawatan',        'type' => 'Button', 'condition' => 'Navigate to jawatan/[jawatanId]/index.vue'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/jawatan', 'description' => 'Fetch position/jawatan list for this PA category'],
            ]);

        $kat11 = $this->seed($mid, $kat->id, 'PAM-KAT-11', 'Butiran Jawatan Kategori',
            'pages/penolong-amil/konfigurasi/kategori/[id]/jawatan/[jawatanId]/index.vue', 110, $all,
            [
                ['screen_name' => 'Butiran Jawatan', 'label' => 'Nama Jawatan',                'type' => 'Display', 'condition' => 'Read-only position name'],
                ['screen_name' => 'Butiran Jawatan', 'label' => 'Tanggungjawab',               'type' => 'Display', 'condition' => 'Responsibilities description'],
                ['screen_name' => 'Butiran Jawatan', 'label' => 'Status Aktif',                'type' => 'Display', 'condition' => 'Active/inactive badge'],
                ['screen_name' => 'Butiran Jawatan', 'label' => 'Status Kelulusan',            'type' => 'Display', 'condition' => 'Approval status for this jawatan config'],
                ['screen_name' => 'Butiran Jawatan', 'label' => 'Butang Kelulusan',            'type' => 'Button',  'condition' => 'Navigate to jawatan/[jawatanId]/kelulusan.vue (pelulus)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/jawatan/{jawatanId}', 'description' => 'Fetch jawatan detail'],
            ]);

        $kat12 = $this->seed($mid, $kat->id, 'PAM-KAT-12', 'Kelulusan Jawatan Kategori',
            'pages/penolong-amil/konfigurasi/kategori/[id]/jawatan/[jawatanId]/kelulusan.vue', 120, [$pelulus->id],
            [
                ['screen_name' => 'Kelulusan Jawatan', 'label' => 'Butiran Jawatan',           'type' => 'Display', 'condition' => 'Proposed jawatan config for review'],
                ['screen_name' => 'Kelulusan Jawatan', 'label' => 'Keputusan Pelulus',         'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Kelulusan Jawatan', 'label' => 'Catatan',                   'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Kelulusan Jawatan', 'label' => 'Butang Simpan',             'type' => 'Button',  'condition' => 'Submit jawatan kelulusan decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/jawatan/{jawatanId}',            'description' => 'Fetch jawatan for pelulus review'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/jawatan/{jawatanId}/kelulusan',  'description' => 'Submit approval for jawatan config'],
            ]);

        // ── Sesi (3 pages) ───────────────────────────────────────────────

        $kat13 = $this->seed($mid, $kat->id, 'PAM-KAT-13', 'Senarai Sesi Kategori',
            'pages/penolong-amil/konfigurasi/kategori/[id]/sesi/index.vue', 130, $all,
            [
                ['screen_name' => 'Senarai Sesi', 'label' => 'Jadual Sesi',                    'type' => 'Table',  'condition' => 'Columns: Nama Sesi, Tempoh (bulan), Tarikh Mula, Status Aktif'],
                ['screen_name' => 'Senarai Sesi', 'label' => 'Butang Lihat Sesi',              'type' => 'Button', 'condition' => 'Navigate to sesi/[sesiId]/index.vue'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/sesi', 'description' => 'Fetch appointment session periods for this PA category'],
            ]);

        $kat14 = $this->seed($mid, $kat->id, 'PAM-KAT-14', 'Butiran Sesi Kategori',
            'pages/penolong-amil/konfigurasi/kategori/[id]/sesi/[sesiId]/index.vue', 140, $all,
            [
                ['screen_name' => 'Butiran Sesi', 'label' => 'Nama Sesi',                      'type' => 'Display', 'condition' => 'Read-only session name'],
                ['screen_name' => 'Butiran Sesi', 'label' => 'Tempoh (Bulan)',                 'type' => 'Display', 'condition' => 'Duration of this appointment session'],
                ['screen_name' => 'Butiran Sesi', 'label' => 'Tarikh Mula Berkuat Kuasa',      'type' => 'Display', 'condition' => 'Effective start date for this session config'],
                ['screen_name' => 'Butiran Sesi', 'label' => 'Status Aktif',                   'type' => 'Display', 'condition' => 'Active/inactive badge'],
                ['screen_name' => 'Butiran Sesi', 'label' => 'Status Kelulusan',               'type' => 'Display', 'condition' => 'Approval status for this sesi config'],
                ['screen_name' => 'Butiran Sesi', 'label' => 'Butang Kelulusan',               'type' => 'Button',  'condition' => 'Navigate to sesi/[sesiId]/kelulusan.vue (pelulus)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/sesi/{sesiId}', 'description' => 'Fetch sesi detail'],
            ]);

        $kat15 = $this->seed($mid, $kat->id, 'PAM-KAT-15', 'Kelulusan Sesi Kategori',
            'pages/penolong-amil/konfigurasi/kategori/[id]/sesi/[sesiId]/kelulusan.vue', 150, [$pelulus->id],
            [
                ['screen_name' => 'Kelulusan Sesi', 'label' => 'Butiran Sesi',                 'type' => 'Display', 'condition' => 'Proposed sesi config for review'],
                ['screen_name' => 'Kelulusan Sesi', 'label' => 'Keputusan Pelulus',            'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Kelulusan Sesi', 'label' => 'Catatan',                      'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Kelulusan Sesi', 'label' => 'Butang Simpan',                'type' => 'Button',  'condition' => 'Submit sesi kelulusan decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/sesi/{sesiId}',            'description' => 'Fetch sesi for pelulus review'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/konfigurasi/kategori/{id}/sesi/{sesiId}/kelulusan',  'description' => 'Submit approval for sesi config'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // Page Links
        // ══════════════════════════════════════════════════════════════════

        $links = [
            // Dokumen
            ['PAM-DOK-01', 'PAM-DOK-02'],
            ['PAM-DOK-01', 'PAM-DOK-03'],
            ['PAM-DOK-03', 'PAM-DOK-04'],
            ['PAM-DOK-03', 'PAM-DOK-05'],
            // Kategori
            ['PAM-KAT-01', 'PAM-KAT-02'],
            ['PAM-KAT-01', 'PAM-KAT-03'],
            ['PAM-KAT-03', 'PAM-KAT-04'],
            ['PAM-KAT-03', 'PAM-KAT-05'],
            ['PAM-KAT-03', 'PAM-KAT-10'],
            ['PAM-KAT-03', 'PAM-KAT-13'],
            // Elaun
            ['PAM-KAT-05', 'PAM-KAT-06'],
            ['PAM-KAT-05', 'PAM-KAT-07'],
            ['PAM-KAT-07', 'PAM-KAT-08'],
            ['PAM-KAT-07', 'PAM-KAT-09'],
            // Jawatan
            ['PAM-KAT-10', 'PAM-KAT-11'],
            ['PAM-KAT-11', 'PAM-KAT-12'],
            // Sesi
            ['PAM-KAT-13', 'PAM-KAT-14'],
            ['PAM-KAT-14', 'PAM-KAT-15'],
        ];

        $this->seedLinks($links);
    }

    private function seedLinks(array $links): void
    {
        foreach ($links as [$fromSpec, $toSpec]) {
            $from = RtmfFrontend::where('spec_id', $fromSpec)->first();
            $to   = RtmfFrontend::where('spec_id', $toSpec)->first();
            if ($from && $to) {
                $from->linksTo()->syncWithoutDetaching([$to->id]);
            }
        }
    }

    private function seed(
        int $moduleId,
        int $subModuleId,
        string $specId,
        string $title,
        string $vuePath,
        int $sortOrder,
        array $actorIds,
        array $items,
        array $endpoints,
    ): RtmfFrontend {
        $fe = RtmfFrontend::updateOrCreate(
            ['spec_id' => $specId],
            [
                'module_id'     => $moduleId,
                'sub_module_id' => $subModuleId,
                'title'         => $title,
                'vue_path'      => $vuePath,
                'sort_order'    => $sortOrder,
            ],
        );

        $fe->actors()->sync($actorIds);

        RtmfFrontendItem::where('rtmf_frontend_id', $fe->id)->delete();
        foreach ($items as $i => $item) {
            RtmfFrontendItem::create([
                'rtmf_frontend_id' => $fe->id,
                'sort_order'       => $i,
                'screen_name'      => $item['screen_name'] ?? $title,
                'id_fr'            => $fe->spec_id . '-FR-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'label'            => $item['label'],
                'type'             => $item['type'] ?? 'Text',
                'condition'        => $item['condition'] ?? null,
                'mandatory'        => $item['mandatory'] ?? false,
                'table_fieldname'  => $item['table_fieldname'] ?? null,
                'validation'       => $item['validation'] ?? null,
                'status'           => $item['status'] ?? 'missing',
            ]);
        }

        RtmfFrontendApiEndpoint::where('rtmf_frontend_id', $fe->id)->delete();
        foreach ($endpoints as $k => $ep) {
            RtmfFrontendApiEndpoint::create([
                'rtmf_frontend_id' => $fe->id,
                'sort_order'       => $k,
                'method'           => $ep['method'],
                'endpoint'         => $ep['endpoint'],
                'description'      => $ep['description'] ?? null,
            ]);
        }

        return $fe;
    }
}
