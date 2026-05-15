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

class RtmfBantuanPhase3Seeder extends Seeder
{
    public function run(): void
    {
        $project = RtmfProject::firstOrFail();

        $module = RtmfModule::firstOrCreate(
            ['code' => 'BNT'],
            ['name' => 'Pengurusan Bantuan', 'sort_order' => 20, 'project_id' => $project->id],
        );
        if (! $module->project_id) {
            $module->update(['project_id' => $project->id]);
        }

        // KFB — Konfigurasi Bantuan sections (sort_order 100–130)
        $seb = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'SEB'],
            ['name' => 'Selenggara Bantuan', 'sort_order' => 100],
        );
        $prg = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'PRG'],
            ['name' => 'Program', 'sort_order' => 110],
        );
        $smb = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'SMB'],
            ['name' => 'Semakan Bajet', 'sort_order' => 120],
        );
        $snb = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'SNB'],
            ['name' => 'Senarai Bajet', 'sort_order' => 130],
        );

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        $staff = [$pegawai->id, $penyelia->id];
        $all   = [$pegawai->id, $penyelia->id, $pelulus->id];

        $mid = $module->id;

        // ══════════════════════════════════════════════════════════════════
        // SELENGGARA BANTUAN (SEB) — 16 pages
        // ══════════════════════════════════════════════════════════════════

        $seb01 = $this->seed($mid, $seb->id, 'BNT-SEB-01', 'Konfigurasi Selenggara Bantuan',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/konfigurasi/index.vue', 10, $all,
            [
                ['screen_name' => 'Konfigurasi Selenggara Bantuan', 'label' => 'Senarai Konfigurasi Aktif',        'type' => 'Table',   'condition' => 'List of active aid config sets: Nama, Jenis, Status, Tarikh Berkuat Kuasa'],
                ['screen_name' => 'Konfigurasi Selenggara Bantuan', 'label' => 'Butang Cipta',                    'type' => 'Button',  'condition' => 'Create new selenggara bantuan configuration set'],
                ['screen_name' => 'Konfigurasi Selenggara Bantuan', 'label' => 'Butang Lihat Aid',               'type' => 'Button',  'condition' => 'Navigate to selenggara-bantuan/aid/lihat/[id]'],
                ['screen_name' => 'Konfigurasi Selenggara Bantuan', 'label' => 'Butang Lihat Aid Product',       'type' => 'Button',  'condition' => 'Navigate to selenggara-bantuan/aid-product/lihat/[id]'],
                ['screen_name' => 'Konfigurasi Selenggara Bantuan', 'label' => 'Butang Lihat Product Package',   'type' => 'Button',  'condition' => 'Navigate to selenggara-bantuan/product-package/lihat/[id]'],
                ['screen_name' => 'Konfigurasi Selenggara Bantuan', 'label' => 'Butang Lihat Entitlement',       'type' => 'Button',  'condition' => 'Navigate to selenggara-bantuan/entitlement-product/lihat/[id]'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/selenggara-bantuan/konfigurasi', 'description' => 'Fetch list of active selenggara bantuan configurations'],
            ]);

        $seb02 = $this->seed($mid, $seb->id, 'BNT-SEB-02', 'Lihat Aid',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/aid/lihat/[id].vue', 20, $all,
            [
                ['screen_name' => 'Lihat Aid', 'label' => 'Maklumat Aid',              'type' => 'Display', 'condition' => 'Read-only: Kod Aid, Nama, Kategori, Status Aktif'],
                ['screen_name' => 'Lihat Aid', 'label' => 'Senarai Aid Product',       'type' => 'Table',   'condition' => 'Products linked to this aid with their entitlement details'],
                ['screen_name' => 'Lihat Aid', 'label' => 'Sejarah Perubahan',         'type' => 'Display', 'condition' => 'Audit trail of changes to this aid configuration'],
                ['screen_name' => 'Lihat Aid', 'label' => 'Butang Kemaskini',          'type' => 'Button',  'condition' => 'Navigate to aid/kemaskini/[id]'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/selenggara-bantuan/aid/{id}', 'description' => 'Fetch aid configuration detail'],
            ]);

        $seb03 = $this->seed($mid, $seb->id, 'BNT-SEB-03', 'Kemaskini Aid',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/aid/kemaskini/[id].vue', 30, $staff,
            [
                ['screen_name' => 'Kemaskini Aid', 'label' => 'Kod Aid',               'type' => 'Text',   'condition' => 'Aid code (may be read-only after initial creation)', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Aid', 'label' => 'Nama Aid',              'type' => 'Text',   'condition' => 'Display name for this aid type', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Aid', 'label' => 'Kategori',              'type' => 'Select', 'condition' => 'Aid category classification'],
                ['screen_name' => 'Kemaskini Aid', 'label' => 'Status Aktif',          'type' => 'Toggle', 'condition' => 'Enable/disable this aid configuration'],
                ['screen_name' => 'Kemaskini Aid', 'label' => 'Butang Simpan Draf',    'type' => 'Button', 'condition' => 'Save as draft for kelulusan'],
                ['screen_name' => 'Kemaskini Aid', 'label' => 'Butang Hantar',         'type' => 'Button', 'condition' => 'Submit for approval workflow'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/selenggara-bantuan/aid/{id}',    'description' => 'Fetch aid for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/selenggara-bantuan/aid/{id}',    'description' => 'Update aid configuration and trigger kelulusan workflow'],
            ]);

        $seb04 = $this->seed($mid, $seb->id, 'BNT-SEB-04', 'Lihat Aid Product',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/aid-product/lihat/[id].vue', 40, $all,
            [
                ['screen_name' => 'Lihat Aid Product', 'label' => 'Maklumat Product',         'type' => 'Display', 'condition' => 'Read-only: Kod, Nama, Aid Induk, Had Jumlah, Unit'],
                ['screen_name' => 'Lihat Aid Product', 'label' => 'Entitlement Terkait',      'type' => 'Table',   'condition' => 'Entitlement-products linked to this aid-product'],
                ['screen_name' => 'Lihat Aid Product', 'label' => 'Butang Kemaskini',         'type' => 'Button',  'condition' => 'Navigate to aid-product/kemaskini/[id]'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/selenggara-bantuan/aid-product/{id}', 'description' => 'Fetch aid-product configuration detail'],
            ]);

        $seb05 = $this->seed($mid, $seb->id, 'BNT-SEB-05', 'Kemaskini Aid Product',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/aid-product/kemaskini/[id].vue', 50, $staff,
            [
                ['screen_name' => 'Kemaskini Aid Product', 'label' => 'Nama Product',        'type' => 'Text',   'condition' => 'Product name', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Aid Product', 'label' => 'Aid Induk',           'type' => 'Select', 'condition' => 'Parent aid this product belongs to', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Aid Product', 'label' => 'Had Jumlah (RM)',     'type' => 'Number', 'condition' => 'Maximum amount per application'],
                ['screen_name' => 'Kemaskini Aid Product', 'label' => 'Unit',                'type' => 'Select', 'condition' => 'Unit of measure: RM, Bulan, Unit'],
                ['screen_name' => 'Kemaskini Aid Product', 'label' => 'Butang Simpan',       'type' => 'Button', 'condition' => 'Save changes with kelulusan workflow'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/selenggara-bantuan/aid-product/{id}', 'description' => 'Fetch aid-product for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/selenggara-bantuan/aid-product/{id}', 'description' => 'Update aid-product and trigger approval workflow'],
            ]);

        $seb06 = $this->seed($mid, $seb->id, 'BNT-SEB-06', 'Lihat Entitlement Product',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/entitlement-product/lihat/[id].vue', 60, $all,
            [
                ['screen_name' => 'Lihat Entitlement Product', 'label' => 'Maklumat Entitlement',    'type' => 'Display', 'condition' => 'Read-only: Kod, Nama, Had, Tempoh, Kategori Asnaf'],
                ['screen_name' => 'Lihat Entitlement Product', 'label' => 'Syarat Kelayakan',        'type' => 'Display', 'condition' => 'Eligibility criteria for this entitlement'],
                ['screen_name' => 'Lihat Entitlement Product', 'label' => 'Butang Kemaskini',        'type' => 'Button',  'condition' => 'Navigate to entitlement-product/kemaskini/[id]'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/selenggara-bantuan/entitlement-product/{id}', 'description' => 'Fetch entitlement-product detail'],
            ]);

        $seb07 = $this->seed($mid, $seb->id, 'BNT-SEB-07', 'Tambah Entitlement Product',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/entitlement-product/tambah/[id].vue', 70, $staff,
            [
                ['screen_name' => 'Tambah Entitlement Product', 'label' => 'Nama Entitlement',       'type' => 'Text',   'condition' => 'Entitlement product name', 'mandatory' => true],
                ['screen_name' => 'Tambah Entitlement Product', 'label' => 'Aid Product Induk',      'type' => 'Select', 'condition' => 'Parent aid-product this entitlement belongs to', 'mandatory' => true],
                ['screen_name' => 'Tambah Entitlement Product', 'label' => 'Kategori Asnaf',         'type' => 'Select', 'condition' => 'Asnaf category eligibility (Fakir, Miskin, etc.)'],
                ['screen_name' => 'Tambah Entitlement Product', 'label' => 'Had Jumlah',             'type' => 'Number', 'condition' => 'Maximum grant amount for this entitlement'],
                ['screen_name' => 'Tambah Entitlement Product', 'label' => 'Tempoh (Bulan)',         'type' => 'Number', 'condition' => 'Duration of entitlement in months'],
                ['screen_name' => 'Tambah Entitlement Product', 'label' => 'Butang Simpan',          'type' => 'Button', 'condition' => 'Create entitlement product'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/selenggara-bantuan/aid-product',                    'description' => 'Fetch aid products for parent selection'],
                ['method' => 'POST', 'endpoint' => '/bantuan/selenggara-bantuan/entitlement-product/{aidId}',    'description' => 'Create new entitlement product under an aid product'],
            ]);

        $seb08 = $this->seed($mid, $seb->id, 'BNT-SEB-08', 'Kemaskini Entitlement Product',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/entitlement-product/kemaskini/[id].vue', 80, $staff,
            [
                ['screen_name' => 'Kemaskini Entitlement Product', 'label' => 'Nama Entitlement',    'type' => 'Text',   'condition' => 'Entitlement name (editable)', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Entitlement Product', 'label' => 'Kategori Asnaf',      'type' => 'Select', 'condition' => 'Asnaf category eligibility'],
                ['screen_name' => 'Kemaskini Entitlement Product', 'label' => 'Had Jumlah',          'type' => 'Number', 'condition' => 'Max grant amount'],
                ['screen_name' => 'Kemaskini Entitlement Product', 'label' => 'Tempoh (Bulan)',      'type' => 'Number', 'condition' => 'Duration in months'],
                ['screen_name' => 'Kemaskini Entitlement Product', 'label' => 'Butang Simpan',       'type' => 'Button', 'condition' => 'Save changes and trigger kelulusan workflow'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/selenggara-bantuan/entitlement-product/{id}', 'description' => 'Fetch entitlement product for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/selenggara-bantuan/entitlement-product/{id}', 'description' => 'Update entitlement product'],
            ]);

        $seb09 = $this->seed($mid, $seb->id, 'BNT-SEB-09', 'Lihat Product Package',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/product-package/lihat/[id].vue', 90, $all,
            [
                ['screen_name' => 'Lihat Product Package', 'label' => 'Maklumat Package',           'type' => 'Display', 'condition' => 'Read-only: Kod, Nama, Senarai Aid Products dalam package'],
                ['screen_name' => 'Lihat Product Package', 'label' => 'Aid Products Terkait',       'type' => 'Table',   'condition' => 'List of aid-products bundled in this package'],
                ['screen_name' => 'Lihat Product Package', 'label' => 'Butang Kemaskini',           'type' => 'Button',  'condition' => 'Navigate to product-package/kemaskini/[id]'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/selenggara-bantuan/product-package/{id}', 'description' => 'Fetch product package detail'],
            ]);

        $seb10 = $this->seed($mid, $seb->id, 'BNT-SEB-10', 'Tambah Product Package',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/product-package/tambah/[id].vue', 100, $staff,
            [
                ['screen_name' => 'Tambah Product Package', 'label' => 'Nama Package',              'type' => 'Text',   'condition' => 'Package name', 'mandatory' => true],
                ['screen_name' => 'Tambah Product Package', 'label' => 'Pilih Aid Products',        'type' => 'MultiSelect', 'condition' => 'Select one or more aid products to bundle'],
                ['screen_name' => 'Tambah Product Package', 'label' => 'Keterangan',                'type' => 'Textarea', 'condition' => 'Optional description'],
                ['screen_name' => 'Tambah Product Package', 'label' => 'Butang Simpan',             'type' => 'Button',  'condition' => 'Create product package'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/selenggara-bantuan/aid-product',          'description' => 'Fetch aid products for selection'],
                ['method' => 'POST', 'endpoint' => '/bantuan/selenggara-bantuan/product-package/{id}', 'description' => 'Create new product package'],
            ]);

        $seb11 = $this->seed($mid, $seb->id, 'BNT-SEB-11', 'Kemaskini Product Package',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/product-package/kemaskini/[id].vue', 110, $staff,
            [
                ['screen_name' => 'Kemaskini Product Package', 'label' => 'Nama Package',           'type' => 'Text',    'condition' => 'Package name (editable)', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Product Package', 'label' => 'Pilih Aid Products',     'type' => 'MultiSelect', 'condition' => 'Update the aid products bundled in this package'],
                ['screen_name' => 'Kemaskini Product Package', 'label' => 'Keterangan',             'type' => 'Textarea', 'condition' => 'Optional description'],
                ['screen_name' => 'Kemaskini Product Package', 'label' => 'Butang Simpan',          'type' => 'Button',  'condition' => 'Save changes'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/selenggara-bantuan/product-package/{id}', 'description' => 'Fetch product package for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/selenggara-bantuan/product-package/{id}', 'description' => 'Update product package'],
            ]);

        $seb12 = $this->seed($mid, $seb->id, 'BNT-SEB-12', 'Kelulusan Selenggara Bantuan',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/kelulusan/index.vue', 120, [$pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Senarai Menunggu',              'type' => 'Table',   'condition' => 'Columns: Jenis Konfigurasi, Nama, Pemohon, Tarikh Hantar'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Butang Semak',                  'type' => 'Button',  'condition' => 'Navigate to semakan/kemaskini/[keterangan]/[id] for review'],
                ['screen_name' => 'Selesai',            'label' => 'Senarai Selesai',               'type' => 'Table',   'condition' => 'Approved/rejected configurations'],
                ['screen_name' => 'Semua',              'label' => 'Senarai Semua',                 'type' => 'Table',   'condition' => 'All kelulusan records'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/selenggara-bantuan/kelulusan', 'description' => 'Fetch list of selenggara bantuan configurations pending approval'],
            ]);

        $seb13 = $this->seed($mid, $seb->id, 'BNT-SEB-13', 'Semakan Kemaskini Konfigurasi',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/kelulusan/semakan/kemaskini/[keterangan]/[id]/index.vue', 130, [$pelulus->id],
            [
                ['screen_name' => 'Semakan Kemaskini', 'label' => 'Butiran Konfigurasi',            'type' => 'Display', 'condition' => 'Read-only: current and proposed config values side-by-side for review'],
                ['screen_name' => 'Semakan Kemaskini', 'label' => 'Keputusan Pelulus',              'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus / Tangguh', 'mandatory' => true],
                ['screen_name' => 'Semakan Kemaskini', 'label' => 'Catatan',                        'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Semakan Kemaskini', 'label' => 'Butang Simpan',                  'type' => 'Button',  'condition' => 'Submit approval decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/selenggara-bantuan/kelulusan/{keterangan}/{id}',           'description' => 'Fetch config change proposal for pelulus review'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/selenggara-bantuan/kelulusan/semakan/{keterangan}/{id}',   'description' => 'Submit pelulus approval decision for config change'],
            ]);

        $seb14 = $this->seed($mid, $seb->id, 'BNT-SEB-14', 'Lihat Keputusan Konfigurasi',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/kelulusan/keputusan/lihat/[keterangan]/[id]/index.vue', 140, $all,
            [
                ['screen_name' => 'Lihat Keputusan', 'label' => 'Maklumat Keputusan',              'type' => 'Display', 'condition' => 'Read-only: approval decision, approver name, date, catatan'],
                ['screen_name' => 'Lihat Keputusan', 'label' => 'Perbandingan Konfigurasi',        'type' => 'Display', 'condition' => 'Side-by-side before/after config values'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/selenggara-bantuan/kelulusan/keputusan/{keterangan}/{id}', 'description' => 'Fetch approval keputusan for a config change'],
            ]);

        $seb15 = $this->seed($mid, $seb->id, 'BNT-SEB-15', 'Kemaskini Draf Konfigurasi',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/draf/kemaskini/[keterangan]/[id]/index.vue', 150, $staff,
            [
                ['screen_name' => 'Kemaskini Draf', 'label' => 'Jenis Konfigurasi',                'type' => 'Display', 'condition' => 'Read-only: config type (aid/aid-product/entitlement/package)'],
                ['screen_name' => 'Kemaskini Draf', 'label' => 'Maklumat Draf',                    'type' => 'Text',    'condition' => 'Editable draft fields for the selected konfigurasi type'],
                ['screen_name' => 'Kemaskini Draf', 'label' => 'Butang Simpan Draf',               'type' => 'Button',  'condition' => 'Update draft without submitting'],
                ['screen_name' => 'Kemaskini Draf', 'label' => 'Butang Hantar',                    'type' => 'Button',  'condition' => 'Submit draft for kelulusan'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/selenggara-bantuan/draf/{keterangan}/{id}',    'description' => 'Fetch draft configuration for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/selenggara-bantuan/draf/{keterangan}/{id}',    'description' => 'Update draft and optionally submit for approval'],
            ]);

        $seb16 = $this->seed($mid, $seb->id, 'BNT-SEB-16', 'Lihat Keputusan Konfigurasi (Umum)',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/selenggara-bantuan/keputusan/lihat/[keterangan]/[id]/index.vue', 160, $all,
            [
                ['screen_name' => 'Lihat Keputusan Konfigurasi', 'label' => 'Maklumat Keputusan', 'type' => 'Display', 'condition' => 'General keputusan view: decision, approver, date, status badge'],
                ['screen_name' => 'Lihat Keputusan Konfigurasi', 'label' => 'Audit Trail',       'type' => 'Display', 'condition' => 'Full change history for this config item'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/selenggara-bantuan/keputusan/{keterangan}/{id}', 'description' => 'Fetch general keputusan detail for a configuration item'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // PROGRAM (PRG) — 9 pages
        // ══════════════════════════════════════════════════════════════════

        $prg01 = $this->seed($mid, $prg->id, 'BNT-PRG-01', 'Senarai Program',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/program/index.vue', 10, $all,
            [
                ['screen_name' => 'Senarai Program', 'label' => 'Filter Status',                  'type' => 'Select',  'condition' => 'Filter: Aktif / Tidak Aktif / Semua'],
                ['screen_name' => 'Senarai Program', 'label' => 'Jadual Program',                 'type' => 'Table',   'condition' => 'Columns: Nama Program, Tarikh Mula, Tarikh Tamat, Bilangan Peserta, Status'],
                ['screen_name' => 'Senarai Program', 'label' => 'Butang Tambah',                  'type' => 'Button',  'condition' => 'Navigate to program/tambah.vue'],
                ['screen_name' => 'Senarai Program', 'label' => 'Butang Lihat',                   'type' => 'Button',  'condition' => 'Navigate to program/[id]/index.vue'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/program', 'description' => 'Fetch paginated list of bantuan programs'],
            ]);

        $prg02 = $this->seed($mid, $prg->id, 'BNT-PRG-02', 'Tambah Program',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/program/tambah.vue', 20, $staff,
            [
                ['screen_name' => 'Tambah Program', 'label' => 'Nama Program',                    'type' => 'Text',    'condition' => 'Program name', 'mandatory' => true],
                ['screen_name' => 'Tambah Program', 'label' => 'Tarikh Mula',                     'type' => 'Date',    'condition' => 'Program start date', 'mandatory' => true],
                ['screen_name' => 'Tambah Program', 'label' => 'Tarikh Tamat',                    'type' => 'Date',    'condition' => 'Program end date'],
                ['screen_name' => 'Tambah Program', 'label' => 'Jenis Bantuan',                   'type' => 'Select',  'condition' => 'Aid type linked to this program', 'mandatory' => true],
                ['screen_name' => 'Tambah Program', 'label' => 'Kapasiti',                        'type' => 'Number',  'condition' => 'Maximum number of participants'],
                ['screen_name' => 'Tambah Program', 'label' => 'Keterangan',                      'type' => 'Textarea', 'condition' => 'Program description'],
                ['screen_name' => 'Tambah Program', 'label' => 'Butang Simpan',                   'type' => 'Button',  'condition' => 'Create program record'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/jenis-bantuan',   'description' => 'Fetch aid types for dropdown'],
                ['method' => 'POST', 'endpoint' => '/bantuan/program',          'description' => 'Create new bantuan program'],
            ]);

        $prg03 = $this->seed($mid, $prg->id, 'BNT-PRG-03', 'Butiran Program',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/program/[id]/index.vue', 30, $all,
            [
                ['screen_name' => 'Butiran Program', 'label' => 'Maklumat Program',               'type' => 'Display', 'condition' => 'Read-only summary: Nama, Tarikh, Jenis Bantuan, Status'],
                ['screen_name' => 'Butiran Program', 'label' => 'Senarai Peserta',                'type' => 'Table',   'condition' => 'Enrolled participants with status; linkable to profiling'],
                ['screen_name' => 'Butiran Program', 'label' => 'Tab Kehadiran',                  'type' => 'Button',  'condition' => 'Navigate to [id]/kehadiran.vue for attendance'],
                ['screen_name' => 'Butiran Program', 'label' => 'Tab Tuntutan',                   'type' => 'Button',  'condition' => 'Navigate to [id]/tuntutan.vue for claims'],
                ['screen_name' => 'Butiran Program', 'label' => 'Butang Kemaskini',               'type' => 'Button',  'condition' => 'Navigate to [id]/kemaskini.vue for editing'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/program/{id}',             'description' => 'Fetch program detail with participant list'],
                ['method' => 'GET', 'endpoint' => '/bantuan/program/{id}/peserta',     'description' => 'Fetch paginated participant list for the program'],
            ]);

        $prg04 = $this->seed($mid, $prg->id, 'BNT-PRG-04', 'Kemaskini Program',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/program/[id]/kemaskini.vue', 40, $staff,
            [
                ['screen_name' => 'Kemaskini Program', 'label' => 'Nama Program',                 'type' => 'Text',    'condition' => 'Editable program name', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Program', 'label' => 'Tarikh Mula / Tamat',          'type' => 'Date',    'condition' => 'Date range picker'],
                ['screen_name' => 'Kemaskini Program', 'label' => 'Kapasiti',                     'type' => 'Number',  'condition' => 'Max participants (editable)'],
                ['screen_name' => 'Kemaskini Program', 'label' => 'Keterangan',                   'type' => 'Textarea', 'condition' => 'Program description'],
                ['screen_name' => 'Kemaskini Program', 'label' => 'Butang Simpan',                'type' => 'Button',  'condition' => 'Save program updates'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/program/{id}',   'description' => 'Fetch program for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/program/{id}',   'description' => 'Update program details'],
            ]);

        $prg05 = $this->seed($mid, $prg->id, 'BNT-PRG-05', 'Kehadiran Program',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/program/[id]/kehadiran.vue', 50, $staff,
            [
                ['screen_name' => 'Kehadiran Program', 'label' => 'Filter Tarikh Sesi',           'type' => 'Date',    'condition' => 'Filter attendance by session date'],
                ['screen_name' => 'Kehadiran Program', 'label' => 'Senarai Peserta + Kehadiran',  'type' => 'Table',   'condition' => 'Rows: Nama Peserta, Status Kehadiran (Hadir/Tidak Hadir), Catatan'],
                ['screen_name' => 'Kehadiran Program', 'label' => 'Toggle Kehadiran',             'type' => 'Toggle',  'condition' => 'Mark present/absent per participant'],
                ['screen_name' => 'Kehadiran Program', 'label' => 'Butang Simpan',                'type' => 'Button',  'condition' => 'Save attendance records for the session'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/program/{id}/kehadiran',  'description' => 'Fetch attendance records for program sessions'],
                ['method' => 'POST',  'endpoint' => '/bantuan/program/{id}/kehadiran',  'description' => 'Save attendance records for a session'],
            ]);

        $prg06 = $this->seed($mid, $prg->id, 'BNT-PRG-06', 'Tuntutan Program',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/program/[id]/tuntutan.vue', 60, $staff,
            [
                ['screen_name' => 'Tuntutan Program', 'label' => 'Senarai Tuntutan',              'type' => 'Table',   'condition' => 'Tuntutan linked to this program: Rujukan, Peserta, Jumlah, Status'],
                ['screen_name' => 'Tuntutan Program', 'label' => 'Butang Tambah Tuntutan',        'type' => 'Button',  'condition' => 'Create new claim for a participant in this program'],
                ['screen_name' => 'Tuntutan Program', 'label' => 'Butang Lihat Tuntutan',         'type' => 'Button',  'condition' => 'Navigate to tuntutan detail page'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/program/{id}/tuntutan',   'description' => 'Fetch tuntutan records linked to this program'],
                ['method' => 'POST', 'endpoint' => '/bantuan/program/{id}/tuntutan',   'description' => 'Create new tuntutan for a program participant'],
            ]);

        $prg07 = $this->seed($mid, $prg->id, 'BNT-PRG-07', 'Jemputan Program',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/program/jemputan.vue', 70, $staff,
            [
                ['screen_name' => 'Jemputan Program', 'label' => 'Pilih Program',                 'type' => 'Select',  'condition' => 'Select which program to send invitations for', 'mandatory' => true],
                ['screen_name' => 'Jemputan Program', 'label' => 'Senarai Jemputan',              'type' => 'Table',   'condition' => 'Invited asnaf: Nama, IC, Status Jemputan (Dijemput/Diterima/Ditolak)'],
                ['screen_name' => 'Jemputan Program', 'label' => 'Butang Hantar Jemputan',        'type' => 'Button',  'condition' => 'Send invitations to selected asnaf records'],
                ['screen_name' => 'Jemputan Program', 'label' => 'Butang Import',                 'type' => 'Button',  'condition' => 'Import invitation list from Excel'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/program',                          'description' => 'Fetch program list for selection'],
                ['method' => 'POST', 'endpoint' => '/bantuan/program/{id}/jemputan',            'description' => 'Send invitations to selected asnaf for the program'],
                ['method' => 'POST', 'endpoint' => '/bantuan/program/{id}/jemputan/import',     'description' => 'Bulk import invitation list from Excel'],
            ]);

        $prg08 = $this->seed($mid, $prg->id, 'BNT-PRG-08', 'Senarai Kelulusan Program',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/program/kelulusan/index.vue', 80, [$pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Jadual Menunggu',             'type' => 'Table',   'condition' => 'Columns: Nama Program, Tarikh Mula, Kapasiti, Pemohon, Tarikh Hantar'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Butang Lulus',                'type' => 'Button',  'condition' => 'Navigate to kelulusan/[id] for approval'],
                ['screen_name' => 'Selesai',            'label' => 'Jadual Selesai',              'type' => 'Table',   'condition' => 'Approved/rejected programs'],
                ['screen_name' => 'Semua',              'label' => 'Jadual Semua',                'type' => 'Table',   'condition' => 'All program kelulusan records'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/program/kelulusan', 'description' => 'Fetch programs pending pelulus approval'],
            ]);

        $prg09 = $this->seed($mid, $prg->id, 'BNT-PRG-09', 'Butiran Kelulusan Program',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/program/kelulusan/[id].vue', 90, [$pelulus->id],
            [
                ['screen_name' => 'Butiran Kelulusan Program', 'label' => 'Ringkasan Program',    'type' => 'Display', 'condition' => 'Read-only: Nama Program, Tarikh, Jenis Bantuan, Kapasiti'],
                ['screen_name' => 'Butiran Kelulusan Program', 'label' => 'Keputusan Pelulus',    'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Butiran Kelulusan Program', 'label' => 'Catatan Pelulus',      'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Butiran Kelulusan Program', 'label' => 'Butang Simpan',        'type' => 'Button',  'condition' => 'Submit kelulusan decision for program'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/program/kelulusan/{id}',   'description' => 'Fetch program kelulusan detail for pelulus'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/program/kelulusan/{id}',   'description' => 'Submit pelulus decision for program'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // SEMAKAN BAJET (SMB) — 5 pages
        // ══════════════════════════════════════════════════════════════════

        $smb01 = $this->seed($mid, $smb->id, 'BNT-SMB-01', 'Konfigurasi Semakan Bajet',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/semakan-bajet/konfigurasi/index.vue', 10, $all,
            [
                ['screen_name' => 'Konfigurasi Semakan Bajet', 'label' => 'Senarai Konfigurasi',  'type' => 'Table',   'condition' => 'Columns: Tahun, Kategori Bantuan, Had Bajet, Status'],
                ['screen_name' => 'Konfigurasi Semakan Bajet', 'label' => 'Butang Edit',          'type' => 'Button',  'condition' => 'Navigate to konfigurasi/edit/[id]'],
                ['screen_name' => 'Konfigurasi Semakan Bajet', 'label' => 'Butang Lihat',         'type' => 'Button',  'condition' => 'Navigate to senarai/lihat/[id]'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/semakan-bajet/konfigurasi', 'description' => 'Fetch list of bajet configurations per year/category'],
            ]);

        $smb02 = $this->seed($mid, $smb->id, 'BNT-SMB-02', 'Edit Konfigurasi Semakan Bajet',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/semakan-bajet/konfigurasi/edit/[id].vue', 20, $staff,
            [
                ['screen_name' => 'Edit Konfigurasi Bajet', 'label' => 'Tahun Bajet',             'type' => 'Text',    'condition' => 'Budget year (read-only after creation)'],
                ['screen_name' => 'Edit Konfigurasi Bajet', 'label' => 'Kategori Bantuan',        'type' => 'Select',  'condition' => 'Aid category for this budget', 'mandatory' => true],
                ['screen_name' => 'Edit Konfigurasi Bajet', 'label' => 'Had Bajet (RM)',          'type' => 'Number',  'condition' => 'Maximum budget allocation', 'mandatory' => true],
                ['screen_name' => 'Edit Konfigurasi Bajet', 'label' => 'Butang Simpan',           'type' => 'Button',  'condition' => 'Save budget configuration'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/semakan-bajet/konfigurasi/{id}',   'description' => 'Fetch bajet config for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/semakan-bajet/konfigurasi/{id}',   'description' => 'Update bajet configuration'],
            ]);

        $smb03 = $this->seed($mid, $smb->id, 'BNT-SMB-03', 'Lihat Semakan Bajet',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/semakan-bajet/senarai/lihat/[id].vue', 30, $all,
            [
                ['screen_name' => 'Lihat Semakan Bajet', 'label' => 'Maklumat Bajet',             'type' => 'Display', 'condition' => 'Read-only: Tahun, Kategori, Had Bajet, Baki Semasa'],
                ['screen_name' => 'Lihat Semakan Bajet', 'label' => 'Sejarah Penggunaan',         'type' => 'Table',   'condition' => 'History of budget utilization: Tarikh, Rujukan, Jumlah Digunakan'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/semakan-bajet/senarai/{id}', 'description' => 'Fetch detailed bajet utilization for a specific budget record'],
            ]);

        $smb04 = $this->seed($mid, $smb->id, 'BNT-SMB-04', 'Senarai Kelulusan Bajet',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/semakan-bajet/kelulusan/index.vue', 40, [$pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Jadual Menunggu',             'type' => 'Table',   'condition' => 'Columns: Tahun Bajet, Kategori, Had Bajet, Pemohon, Tarikh'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Butang Lulus',                'type' => 'Button',  'condition' => 'Navigate to kelulusan/[id] for decision'],
                ['screen_name' => 'Selesai',            'label' => 'Jadual Selesai',              'type' => 'Table',   'condition' => 'Approved/rejected bajet configs'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/semakan-bajet/kelulusan', 'description' => 'Fetch bajet configurations pending approval'],
            ]);

        $smb05 = $this->seed($mid, $smb->id, 'BNT-SMB-05', 'Butiran Kelulusan Bajet',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/semakan-bajet/kelulusan/[id].vue', 50, [$pelulus->id],
            [
                ['screen_name' => 'Butiran Kelulusan Bajet', 'label' => 'Ringkasan Permohonan',   'type' => 'Display', 'condition' => 'Tahun, Kategori, Had Bajet requested'],
                ['screen_name' => 'Butiran Kelulusan Bajet', 'label' => 'Keputusan Pelulus',      'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Butiran Kelulusan Bajet', 'label' => 'Catatan',                'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Butiran Kelulusan Bajet', 'label' => 'Butang Simpan',          'type' => 'Button',  'condition' => 'Submit kelulusan decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/semakan-bajet/kelulusan/{id}',  'description' => 'Fetch bajet kelulusan detail'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/semakan-bajet/kelulusan/{id}',  'description' => 'Submit approval decision for bajet config'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // SENARAI BAJET (SNB) — 4 pages
        // ══════════════════════════════════════════════════════════════════

        $snb01 = $this->seed($mid, $snb->id, 'BNT-SNB-01', 'Senarai Bajet',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-bajet/index.vue', 10, $all,
            [
                ['screen_name' => 'Senarai Bajet', 'label' => 'Filter Tahun',                    'type' => 'Select',  'condition' => 'Filter by budget year'],
                ['screen_name' => 'Senarai Bajet', 'label' => 'Filter Kategori',                 'type' => 'Select',  'condition' => 'Filter by aid category'],
                ['screen_name' => 'Senarai Bajet', 'label' => 'Jadual Bajet',                    'type' => 'Table',   'condition' => 'Columns: Tahun, Kategori Bantuan, Had Bajet, Baki, % Penggunaan'],
                ['screen_name' => 'Senarai Bajet', 'label' => 'Butang Kemaskini',                'type' => 'Button',  'condition' => 'Navigate to senarai/kemaskini/[id]'],
                ['screen_name' => 'Senarai Bajet', 'label' => 'Butang Lihat',                    'type' => 'Button',  'condition' => 'Navigate to senarai/lihat/[id]'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/senarai-bajet', 'description' => 'Fetch paginated budget records with utilization summary'],
            ]);

        $snb02 = $this->seed($mid, $snb->id, 'BNT-SNB-02', 'Kemaskini Senarai Bajet',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-bajet/senarai/kemaskini/[id].vue', 20, $staff,
            [
                ['screen_name' => 'Kemaskini Senarai Bajet', 'label' => 'Tahun Bajet',            'type' => 'Display', 'condition' => 'Read-only budget year'],
                ['screen_name' => 'Kemaskini Senarai Bajet', 'label' => 'Kategori Bantuan',       'type' => 'Display', 'condition' => 'Read-only aid category'],
                ['screen_name' => 'Kemaskini Senarai Bajet', 'label' => 'Had Bajet Baru (RM)',    'type' => 'Number',  'condition' => 'New budget ceiling amount', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Senarai Bajet', 'label' => 'Import Maklumat',        'type' => 'Button',  'condition' => 'Open import modal to bulk-update bajet from Excel'],
                ['screen_name' => 'Kemaskini Senarai Bajet', 'label' => 'Sebab Kemaskini',        'type' => 'Textarea', 'condition' => 'Reason for budget revision'],
                ['screen_name' => 'Kemaskini Senarai Bajet', 'label' => 'Butang Hantar',          'type' => 'Button',  'condition' => 'Submit kemaskini for pelulus approval'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/senarai-bajet/{id}',                'description' => 'Fetch bajet record for kemaskini'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/senarai-bajet/{id}',                'description' => 'Update bajet ceiling and submit for approval'],
                ['method' => 'POST',  'endpoint' => '/bantuan/senarai-bajet/{id}/import',         'description' => 'Import bajet maklumat from Excel'],
            ]);

        $snb03 = $this->seed($mid, $snb->id, 'BNT-SNB-03', 'Lihat Senarai Bajet',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-bajet/senarai/lihat/[id].vue', 30, $all,
            [
                ['screen_name' => 'Lihat Senarai Bajet', 'label' => 'Maklumat Bajet',             'type' => 'Display', 'condition' => 'Tahun, Kategori, Had, Baki, % Penggunaan'],
                ['screen_name' => 'Lihat Senarai Bajet', 'label' => 'Sejarah Perubahan',          'type' => 'Table',   'condition' => 'Revision history: Tarikh, Had Lama, Had Baru, Diubah Oleh'],
                ['screen_name' => 'Lihat Senarai Bajet', 'label' => 'Sejarah Penggunaan',         'type' => 'Table',   'condition' => 'Usage history: Rujukan Bantuan, Jumlah, Tarikh'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/senarai-bajet/{id}',              'description' => 'Fetch detailed bajet record with history'],
                ['method' => 'GET', 'endpoint' => '/bantuan/senarai-bajet/{id}/sejarah',      'description' => 'Fetch revision history for the bajet record'],
            ]);

        $snb04 = $this->seed($mid, $snb->id, 'BNT-SNB-04', 'Kemaskini Bajet Pelulus',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-bajet/pelulus/kemaskini/[id].vue', 40, [$pelulus->id],
            [
                ['screen_name' => 'Kemaskini Bajet Pelulus', 'label' => 'Ringkasan Permohonan',   'type' => 'Display', 'condition' => 'Tahun, Kategori, Had Semasa, Had Cadangan'],
                ['screen_name' => 'Kemaskini Bajet Pelulus', 'label' => 'Keputusan Pelulus',      'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus / Ubah Suai', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Bajet Pelulus', 'label' => 'Had Diluluskan (RM)',    'type' => 'Number',  'condition' => 'Pelulus may adjust the approved budget amount'],
                ['screen_name' => 'Kemaskini Bajet Pelulus', 'label' => 'Catatan Pelulus',        'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Kemaskini Bajet Pelulus', 'label' => 'Butang Simpan',          'type' => 'Button',  'condition' => 'Submit pelulus decision for budget revision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/senarai-bajet/pelulus/{id}',   'description' => 'Fetch bajet kemaskini request for pelulus review'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/senarai-bajet/pelulus/{id}',   'description' => 'Submit pelulus approval decision for budget revision'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // Page Links
        // ══════════════════════════════════════════════════════════════════

        $links = [
            // Selenggara Bantuan
            ['BNT-SEB-01', 'BNT-SEB-02'],
            ['BNT-SEB-01', 'BNT-SEB-04'],
            ['BNT-SEB-01', 'BNT-SEB-06'],
            ['BNT-SEB-01', 'BNT-SEB-09'],
            ['BNT-SEB-02', 'BNT-SEB-03'],
            ['BNT-SEB-04', 'BNT-SEB-05'],
            ['BNT-SEB-06', 'BNT-SEB-07'],
            ['BNT-SEB-06', 'BNT-SEB-08'],
            ['BNT-SEB-09', 'BNT-SEB-10'],
            ['BNT-SEB-09', 'BNT-SEB-11'],
            ['BNT-SEB-12', 'BNT-SEB-13'],
            ['BNT-SEB-12', 'BNT-SEB-14'],
            ['BNT-SEB-15', 'BNT-SEB-12'],
            // Program
            ['BNT-PRG-01', 'BNT-PRG-02'],
            ['BNT-PRG-01', 'BNT-PRG-03'],
            ['BNT-PRG-03', 'BNT-PRG-04'],
            ['BNT-PRG-03', 'BNT-PRG-05'],
            ['BNT-PRG-03', 'BNT-PRG-06'],
            ['BNT-PRG-08', 'BNT-PRG-09'],
            // Semakan Bajet
            ['BNT-SMB-01', 'BNT-SMB-02'],
            ['BNT-SMB-01', 'BNT-SMB-03'],
            ['BNT-SMB-04', 'BNT-SMB-05'],
            // Senarai Bajet
            ['BNT-SNB-01', 'BNT-SNB-02'],
            ['BNT-SNB-01', 'BNT-SNB-03'],
            ['BNT-SNB-02', 'BNT-SNB-04'],
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
