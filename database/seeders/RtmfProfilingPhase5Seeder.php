<?php

namespace Database\Seeders;

use App\Models\RtmfActor;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendApiEndpoint;
use App\Models\RtmfFrontendItem;
use App\Models\RtmfModule;
use App\Models\RtmfSubModule;
use Illuminate\Database\Seeder;

class RtmfProfilingPhase5Seeder extends Seeder
{
    public function run(): void
    {
        $module = RtmfModule::where('code', 'PRF')->firstOrFail();

        $knf = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'KNF'],
            ['name' => 'Konfigurasi', 'sort_order' => 80],
        );

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        $admin    = [$pegawai->id, $penyelia->id];
        $approver = [$pelulus->id];
        $all      = [$pegawai->id, $penyelia->id, $pelulus->id];

        $mid = $module->id;
        $kid = $knf->id;

        // ══════════════════════════════════════════════════════════════════
        // HAD KIFAYAH (HK)  — 16 pages
        // ══════════════════════════════════════════════════════════════════

        $hk01 = $this->seed($mid, $kid, 'PRF-KNF-HK-01', 'Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/index.vue', 10, $all,
            $this->itemsStub('Had Kifayah'), []);

        $hk02 = $this->seed($mid, $kid, 'PRF-KNF-HK-02', 'Senarai Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/admin/index.vue', 20, $admin,
            $this->itemsAdminSenarai('Senarai Had Kifayah', ['Draf', 'Menunggu Kelulusan', 'Diluluskan', 'Tidak Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/had-kifayah', 'description' => 'Fetch had-kifayah list with status filter'],
            ]);

        $hk03 = $this->seed($mid, $kid, 'PRF-KNF-HK-03', 'Tambah Had Kifayah Baharu',
            'pages/profiling/konfigurasi/had-kifayah/admin/tambah/index.vue', 30, $admin,
            $this->itemsTambah('Had Kifayah', ['Maklumat Had Kifayah', 'Kategori Had Kifayah']),
            [
                ['method' => 'GET',  'endpoint' => '/profiling/konfigurasi/had-kifayah/kategori', 'description' => 'Fetch category list for had-kifayah'],
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/had-kifayah',          'description' => 'Create new had-kifayah record'],
            ]);

        $hk04 = $this->seed($mid, $kid, 'PRF-KNF-HK-04', 'Lihat Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/admin/lihat/[id].vue', 40, $all,
            $this->itemsLihat('Had Kifayah'),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/had-kifayah/{id}', 'description' => 'Fetch had-kifayah detail'],
            ]);

        $hk05 = $this->seed($mid, $kid, 'PRF-KNF-HK-05', 'Kemaskini Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/admin/kemaskini/[id].vue', 50, $admin,
            $this->itemsKemaskini('Had Kifayah'),
            [
                ['method' => 'GET',   'endpoint' => '/profiling/konfigurasi/had-kifayah/{id}', 'description' => 'Fetch had-kifayah for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/konfigurasi/had-kifayah/{id}', 'description' => 'Update had-kifayah record'],
            ]);

        $hk06 = $this->seed($mid, $kid, 'PRF-KNF-HK-06', 'Semak Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/admin/semak/[id].vue', 60, $admin,
            $this->itemsSemak('Had Kifayah'),
            [
                ['method' => 'GET',  'endpoint' => '/profiling/konfigurasi/had-kifayah/{id}',          'description' => 'Fetch had-kifayah for review'],
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/had-kifayah/{id}/semak',    'description' => 'Submit semak decision'],
            ]);

        $hk07 = $this->seed($mid, $kid, 'PRF-KNF-HK-07', 'Kategori Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/admin/kategori/[id].vue', 70, $admin,
            [
                ['screen_name' => 'Kategori Had Kifayah', 'label' => 'Senarai Kategori',  'type' => 'Table',  'condition' => 'Table listing categories for this had-kifayah record'],
                ['screen_name' => 'Kategori Had Kifayah', 'label' => 'Butang Tambah',     'type' => 'Button', 'condition' => 'Add new category entry'],
                ['screen_name' => 'Kategori Had Kifayah', 'label' => 'Butang Kembali',    'type' => 'Button', 'condition' => 'Back to senarai'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/had-kifayah/{id}/kategori', 'description' => 'Fetch categories for had-kifayah record'],
            ]);

        $hk08 = $this->seed($mid, $kid, 'PRF-KNF-HK-08', 'Senarai Kelulusan Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/pelulus/index.vue', 80, $approver,
            $this->itemsPelulusSenarai('Had Kifayah', ['Menunggu Kelulusan', 'Diluluskan', 'Tidak Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/had-kifayah/kelulusan', 'description' => 'Fetch had-kifayah approval list'],
            ]);

        $hk09 = $this->seed($mid, $kid, 'PRF-KNF-HK-09', 'Butiran Kelulusan Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/pelulus/[id].vue', 90, $approver,
            $this->itemsLulusButiran('Had Kifayah'),
            [
                ['method' => 'GET',  'endpoint' => '/profiling/konfigurasi/had-kifayah/{id}',                  'description' => 'Fetch had-kifayah approval detail'],
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/had-kifayah/{id}/keputusan',        'description' => 'Submit kelulusan decision'],
            ]);

        $hk10 = $this->seed($mid, $kid, 'PRF-KNF-HK-10', 'Kelulusan Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/pelulus/kelulusan/index.vue', 100, $approver,
            $this->itemsPelulusSenarai('Had Kifayah (Kelulusan)', ['Menunggu Kelulusan', 'Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/had-kifayah/kelulusan/pending', 'description' => 'Fetch pending kelulusan list'],
            ]);

        $hk11 = $this->seed($mid, $kid, 'PRF-KNF-HK-11', 'Senarai Medan Rujukan Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/medan-rujukan/index.vue', 110, $admin,
            $this->itemsAdminSenarai('Medan Rujukan Had Kifayah', ['Aktif', 'Tidak Aktif', 'Menunggu Kelulusan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/had-kifayah/medan-rujukan', 'description' => 'Fetch medan rujukan list for had-kifayah'],
            ]);

        $hk12 = $this->seed($mid, $kid, 'PRF-KNF-HK-12', 'Tambah Medan Rujukan Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/medan-rujukan/tambah/index.vue', 120, $admin,
            $this->itemsTambah('Medan Rujukan Had Kifayah', ['Maklumat Medan']),
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/had-kifayah/medan-rujukan', 'description' => 'Create new medan rujukan for had-kifayah'],
            ]);

        $hk13 = $this->seed($mid, $kid, 'PRF-KNF-HK-13', 'Kemaskini Medan Rujukan Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/medan-rujukan/kemaskini/[id].vue', 130, $admin,
            $this->itemsKemaskini('Medan Rujukan Had Kifayah'),
            [
                ['method' => 'GET',   'endpoint' => '/profiling/konfigurasi/had-kifayah/medan-rujukan/{id}', 'description' => 'Fetch medan rujukan for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/konfigurasi/had-kifayah/medan-rujukan/{id}', 'description' => 'Update medan rujukan'],
            ]);

        $hk14 = $this->seed($mid, $kid, 'PRF-KNF-HK-14', 'Pelulus Medan Rujukan Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/medan-rujukan/pelulus/index.vue', 140, $approver,
            $this->itemsPelulusSenarai('Medan Rujukan Had Kifayah', ['Menunggu Kelulusan', 'Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/had-kifayah/medan-rujukan/kelulusan', 'description' => 'Fetch medan rujukan pending approval'],
            ]);

        $hk15 = $this->seed($mid, $kid, 'PRF-KNF-HK-15', 'Simulasi Pengiraan Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/simulasi/index.vue', 150, $all,
            [
                ['screen_name' => 'Simulasi Pengiraan Had Kifayah', 'label' => 'Jenis Simulasi',       'type' => 'Select',  'condition' => 'Toggle — By Individu or By Konfigurasi'],
                ['screen_name' => 'Simulasi Pengiraan Had Kifayah', 'label' => 'Parameter Simulasi',   'type' => 'Text',    'condition' => 'Input fields for simulation parameters (IC, household composition, etc.)'],
                ['screen_name' => 'Simulasi Pengiraan Had Kifayah', 'label' => 'Butang Kira',          'type' => 'Button',  'condition' => 'Run simulation calculation'],
                ['screen_name' => 'Simulasi Pengiraan Had Kifayah', 'label' => 'Keputusan Pengiraan',  'type' => 'Display', 'condition' => 'Display calculated had kifayah result'],
                ['screen_name' => 'Simulasi Pengiraan Had Kifayah', 'label' => 'Butang Perbandingan',  'type' => 'Button',  'condition' => 'Navigate to Perbandingan Konfigurasi page'],
            ],
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/had-kifayah/simulasi',      'description' => 'Run had kifayah simulation calculation'],
                ['method' => 'GET',  'endpoint' => '/profiling/konfigurasi/had-kifayah/aktif',         'description' => 'Fetch active had-kifayah configuration'],
            ]);

        $hk16 = $this->seed($mid, $kid, 'PRF-KNF-HK-16', 'Perbandingan Konfigurasi Had Kifayah',
            'pages/profiling/konfigurasi/had-kifayah/simulasi/perbandingan/index.vue', 160, $all,
            [
                ['screen_name' => 'Perbandingan Konfigurasi', 'label' => 'Pilih Konfigurasi 1',      'type' => 'Select',  'condition' => 'Dropdown — select first had-kifayah configuration version'],
                ['screen_name' => 'Perbandingan Konfigurasi', 'label' => 'Pilih Konfigurasi 2',      'type' => 'Select',  'condition' => 'Dropdown — select second had-kifayah configuration version'],
                ['screen_name' => 'Perbandingan Konfigurasi', 'label' => 'Jadual Perbandingan',      'type' => 'Table',   'condition' => 'Side-by-side comparison table showing differences between two configurations'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/had-kifayah',              'description' => 'Fetch all had-kifayah configurations for comparison dropdown'],
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/had-kifayah/perbandingan', 'description' => 'Fetch comparison data between two configurations'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // MULTIDIMENSI (MD)  — 17 pages
        // ══════════════════════════════════════════════════════════════════

        $md01 = $this->seed($mid, $kid, 'PRF-KNF-MD-01', 'Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/index.vue', 170, $all,
            $this->itemsStub('Multidimensi'), []);

        $md02 = $this->seed($mid, $kid, 'PRF-KNF-MD-02', 'Senarai Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/admin/index.vue', 180, $admin,
            $this->itemsAdminSenarai('Senarai Multidimensi', ['Draf', 'Menunggu Kelulusan', 'Diluluskan', 'Tidak Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/multidimensi', 'description' => 'Fetch multidimensi list with status filter'],
            ]);

        $md03 = $this->seed($mid, $kid, 'PRF-KNF-MD-03', 'Tambah Multidimensi Baharu',
            'pages/profiling/konfigurasi/multidimensi/admin/tambah/index.vue', 190, $admin,
            $this->itemsTambah('Multidimensi', ['Maklumat Multidimensi', 'Nota: Formula dikonfigurasi melalui halaman Kemaskini selepas kategori ditambah']),
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/multidimensi', 'description' => 'Create new multidimensi record'],
            ]);

        $md04 = $this->seed($mid, $kid, 'PRF-KNF-MD-04', 'Lihat Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/admin/lihat/[id].vue', 200, $all,
            $this->itemsLihat('Multidimensi'),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/multidimensi/{id}', 'description' => 'Fetch multidimensi detail'],
            ]);

        $md05 = $this->seed($mid, $kid, 'PRF-KNF-MD-05', 'Kemaskini Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/admin/kemaskini/[id].vue', 210, $admin,
            $this->itemsKemaskini('Multidimensi'),
            [
                ['method' => 'GET',   'endpoint' => '/profiling/konfigurasi/multidimensi/{id}', 'description' => 'Fetch multidimensi for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/konfigurasi/multidimensi/{id}', 'description' => 'Update multidimensi record'],
            ]);

        $md06 = $this->seed($mid, $kid, 'PRF-KNF-MD-06', 'Semak Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/admin/semak/[id].vue', 220, $admin,
            $this->itemsSemak('Multidimensi'),
            [
                ['method' => 'GET',  'endpoint' => '/profiling/konfigurasi/multidimensi/{id}',       'description' => 'Fetch multidimensi for review'],
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/multidimensi/{id}/semak', 'description' => 'Submit semak decision'],
            ]);

        $md07 = $this->seed($mid, $kid, 'PRF-KNF-MD-07', 'Kategori Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/admin/kategori/[id].vue', 230, $admin,
            [
                ['screen_name' => 'Kategori Multidimensi', 'label' => 'Senarai Kategori',    'type' => 'Table',  'condition' => 'Table of categories for this multidimensi configuration'],
                ['screen_name' => 'Kategori Multidimensi', 'label' => 'Butang Tambah',       'type' => 'Button', 'condition' => 'Add new category'],
                ['screen_name' => 'Kategori Multidimensi', 'label' => 'Butang Kembali',      'type' => 'Button', 'condition' => 'Back to senarai'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/multidimensi/{id}/kategori', 'description' => 'Fetch categories for multidimensi record'],
            ]);

        $md08 = $this->seed($mid, $kid, 'PRF-KNF-MD-08', 'Tambah Kategori Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/admin/kategori/tambah/index.vue', 240, $admin,
            $this->itemsTambah('Kategori Multidimensi', ['Maklumat Kategori']),
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/multidimensi/{id}/kategori', 'description' => 'Add new category to multidimensi'],
            ]);

        $md09 = $this->seed($mid, $kid, 'PRF-KNF-MD-09', 'Kuadran Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/admin/kuadran/[id].vue', 250, $admin,
            [
                ['screen_name' => 'Kuadran Multidimensi', 'label' => 'Senarai Kuadran',  'type' => 'Table',  'condition' => 'Table of quadrants for multidimensi scoring matrix'],
                ['screen_name' => 'Kuadran Multidimensi', 'label' => 'Butang Tambah',    'type' => 'Button', 'condition' => 'Add new kuadran'],
                ['screen_name' => 'Kuadran Multidimensi', 'label' => 'Butang Kembali',   'type' => 'Button', 'condition' => 'Back to senarai'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/multidimensi/{id}/kuadran', 'description' => 'Fetch kuadran list for multidimensi record'],
            ]);

        $md10 = $this->seed($mid, $kid, 'PRF-KNF-MD-10', 'Tambah Kuadran Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/admin/kuadran/tambah/index.vue', 260, $admin,
            $this->itemsTambah('Kuadran Multidimensi', ['Maklumat Kuadran']),
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/multidimensi/{id}/kuadran', 'description' => 'Add new kuadran to multidimensi'],
            ]);

        $md11 = $this->seed($mid, $kid, 'PRF-KNF-MD-11', 'Senarai Kelulusan Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/pelulus/index.vue', 270, $approver,
            $this->itemsPelulusSenarai('Multidimensi', ['Menunggu Kelulusan', 'Diluluskan', 'Tidak Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/multidimensi/kelulusan', 'description' => 'Fetch multidimensi approval list'],
            ]);

        $md12 = $this->seed($mid, $kid, 'PRF-KNF-MD-12', 'Semak Kelulusan Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/pelulus/semak/[id].vue', 280, $approver,
            $this->itemsLulusButiran('Multidimensi'),
            [
                ['method' => 'GET',  'endpoint' => '/profiling/konfigurasi/multidimensi/{id}',            'description' => 'Fetch multidimensi approval detail'],
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/multidimensi/{id}/keputusan',  'description' => 'Submit kelulusan decision'],
            ]);

        $md13 = $this->seed($mid, $kid, 'PRF-KNF-MD-13', 'Senarai Medan Rujukan Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/medan-rujukan/index.vue', 290, $admin,
            $this->itemsAdminSenarai('Medan Rujukan Multidimensi', ['Aktif', 'Tidak Aktif', 'Menunggu Kelulusan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/multidimensi/medan-rujukan', 'description' => 'Fetch medan rujukan list for multidimensi'],
            ]);

        $md14 = $this->seed($mid, $kid, 'PRF-KNF-MD-14', 'Tambah Medan Rujukan Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/medan-rujukan/tambah/index.vue', 300, $admin,
            $this->itemsTambah('Medan Rujukan Multidimensi', ['Maklumat Medan']),
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/multidimensi/medan-rujukan', 'description' => 'Create new medan rujukan for multidimensi'],
            ]);

        $md15 = $this->seed($mid, $kid, 'PRF-KNF-MD-15', 'Kemaskini Medan Rujukan Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/medan-rujukan/kemaskini/[id].vue', 310, $admin,
            $this->itemsKemaskini('Medan Rujukan Multidimensi'),
            [
                ['method' => 'GET',   'endpoint' => '/profiling/konfigurasi/multidimensi/medan-rujukan/{id}', 'description' => 'Fetch medan rujukan for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/konfigurasi/multidimensi/medan-rujukan/{id}', 'description' => 'Update medan rujukan'],
            ]);

        $md16 = $this->seed($mid, $kid, 'PRF-KNF-MD-16', 'Pelulus Medan Rujukan Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/medan-rujukan/pelulus/index.vue', 320, $approver,
            $this->itemsPelulusSenarai('Medan Rujukan Multidimensi', ['Menunggu Kelulusan', 'Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/multidimensi/medan-rujukan/kelulusan', 'description' => 'Fetch medan rujukan pending approval'],
            ]);

        $md17 = $this->seed($mid, $kid, 'PRF-KNF-MD-17', 'Simulasi Pengiraan Multidimensi',
            'pages/profiling/konfigurasi/multidimensi/simulasi/index.vue', 330, $all,
            [
                ['screen_name' => 'Simulasi Pengiraan Multidimensi', 'label' => 'Parameter Simulasi',   'type' => 'Text',    'condition' => 'IC / ID number input for simulation subject'],
                ['screen_name' => 'Simulasi Pengiraan Multidimensi', 'label' => 'Butang Kira',          'type' => 'Button',  'condition' => 'Run skor merit multidimensi calculation'],
                ['screen_name' => 'Simulasi Pengiraan Multidimensi', 'label' => 'Keputusan Skor Merit', 'type' => 'Display', 'condition' => 'Display multidimensi skor merit result and breakdown per dimension'],
            ],
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/multidimensi/simulasi', 'description' => 'Run multidimensi simulation calculation'],
                ['method' => 'GET',  'endpoint' => '/profiling/konfigurasi/multidimensi/aktif',    'description' => 'Fetch active multidimensi configuration'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // HOUSEHOLD (HH)  — 7 pages
        // ══════════════════════════════════════════════════════════════════

        $hh01 = $this->seed($mid, $kid, 'PRF-KNF-HH-01', 'Isi Rumah / Individu',
            'pages/profiling/konfigurasi/household/index.vue', 340, $all,
            [
                ['screen_name' => 'Isi Rumah / Individu', 'label' => 'Navigasi ke Admin',   'type' => 'Button', 'condition' => 'Button — navigates to household admin listing page'],
                ['screen_name' => 'Isi Rumah / Individu', 'label' => 'Navigasi ke Pelulus', 'type' => 'Button', 'condition' => 'Button — navigates to household pelulus listing page'],
            ],
            []);

        $hh02 = $this->seed($mid, $kid, 'PRF-KNF-HH-02', 'Senarai Maklumat Status Isi Rumah / Individu',
            'pages/profiling/konfigurasi/household/admin/index.vue', 350, $admin,
            $this->itemsAdminSenarai('Status Isi Rumah / Individu', ['Baru', 'Menunggu Kelulusan', 'Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/household', 'description' => 'Fetch household status configuration list'],
            ]);

        $hh03 = $this->seed($mid, $kid, 'PRF-KNF-HH-03', 'Tambah Maklumat Status Isi Rumah / Individu',
            'pages/profiling/konfigurasi/household/admin/tambah/index.vue', 360, $admin,
            $this->itemsTambah('Status Isi Rumah / Individu', ['Maklumat Status']),
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/household', 'description' => 'Create new household status record'],
            ]);

        $hh04 = $this->seed($mid, $kid, 'PRF-KNF-HH-04', 'Lihat Maklumat Status Isi Rumah / Individu',
            'pages/profiling/konfigurasi/household/admin/lihat/[id].vue', 370, $all,
            $this->itemsLihat('Status Isi Rumah / Individu'),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/household/{id}', 'description' => 'Fetch household status detail'],
            ]);

        $hh05 = $this->seed($mid, $kid, 'PRF-KNF-HH-05', 'Kemaskini Maklumat Status Isi Rumah / Individu',
            'pages/profiling/konfigurasi/household/admin/kemaskini/index.vue', 380, $admin,
            $this->itemsKemaskini('Status Isi Rumah / Individu'),
            [
                ['method' => 'GET',   'endpoint' => '/profiling/konfigurasi/household/{id}', 'description' => 'Fetch household status for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/konfigurasi/household/{id}', 'description' => 'Update household status record'],
            ]);

        $hh06 = $this->seed($mid, $kid, 'PRF-KNF-HH-06', 'Senarai Kelulusan Isi Rumah',
            'pages/profiling/konfigurasi/household/pelulus/index.vue', 390, $approver,
            $this->itemsPelulusSenarai('Isi Rumah', ['Menunggu Kelulusan', 'Diluluskan', 'Tidak Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/household/kelulusan', 'description' => 'Fetch household approval list'],
            ]);

        $hh07 = $this->seed($mid, $kid, 'PRF-KNF-HH-07', 'Kelulusan Isi Rumah',
            'pages/profiling/konfigurasi/household/pelulus/kelulusan/index.vue', 400, $approver,
            $this->itemsPelulusSenarai('Isi Rumah (Kelulusan)', ['Menunggu Kelulusan', 'Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/household/kelulusan/pending', 'description' => 'Fetch pending kelulusan for household'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // KB — BENCANA  — 6 pages
        // ══════════════════════════════════════════════════════════════════

        $kb01 = $this->seed($mid, $kid, 'PRF-KNF-KB-01', 'Senarai Maklumat Bencana',
            'pages/profiling/konfigurasi/kb/admin/index.vue', 410, $admin,
            $this->itemsAdminSenarai('Maklumat Bencana', ['Baru', 'Menunggu Kelulusan', 'Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/kb', 'description' => 'Fetch bencana configuration list'],
            ]);

        $kb02 = $this->seed($mid, $kid, 'PRF-KNF-KB-02', 'Tambah Maklumat Bencana',
            'pages/profiling/konfigurasi/kb/admin/tambah/index.vue', 420, $admin,
            $this->itemsTambah('Maklumat Bencana', ['Maklumat Bencana']),
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/kb', 'description' => 'Create new bencana record'],
            ]);

        $kb03 = $this->seed($mid, $kid, 'PRF-KNF-KB-03', 'Kemaskini Maklumat Bencana',
            'pages/profiling/konfigurasi/kb/admin/kemaskini.vue', 430, $admin,
            $this->itemsKemaskini('Maklumat Bencana'),
            [
                ['method' => 'GET',   'endpoint' => '/profiling/konfigurasi/kb/{id}', 'description' => 'Fetch bencana for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/konfigurasi/kb/{id}', 'description' => 'Update bencana record'],
            ]);

        $kb04 = $this->seed($mid, $kid, 'PRF-KNF-KB-04', 'Lihat Maklumat Bencana',
            'pages/profiling/konfigurasi/kb/admin/lihat/[id].vue', 440, $all,
            $this->itemsLihat('Maklumat Bencana'),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/kb/{id}', 'description' => 'Fetch bencana detail'],
            ]);

        $kb05 = $this->seed($mid, $kid, 'PRF-KNF-KB-05', 'Senarai Kelulusan Bencana',
            'pages/profiling/konfigurasi/kb/pelulus/index.vue', 450, $approver,
            $this->itemsPelulusSenarai('Bencana', ['Menunggu Kelulusan', 'Diluluskan', 'Tidak Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/kb/kelulusan', 'description' => 'Fetch bencana approval list'],
            ]);

        $kb06 = $this->seed($mid, $kid, 'PRF-KNF-KB-06', 'Kelulusan Bencana',
            'pages/profiling/konfigurasi/kb/pelulus/kelulusan/index.vue', 460, $approver,
            $this->itemsPelulusSenarai('Bencana (Kelulusan)', ['Menunggu Kelulusan', 'Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/kb/kelulusan/pending', 'description' => 'Fetch pending kelulusan for bencana'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // KT — TEMPOH TARIKH  — 6 pages
        // ══════════════════════════════════════════════════════════════════

        $kt01 = $this->seed($mid, $kid, 'PRF-KNF-KT-01', 'Senarai Maklumat Tempoh Tarikh',
            'pages/profiling/konfigurasi/kt/admin/index.vue', 470, $admin,
            $this->itemsAdminSenarai('Maklumat Tempoh Tarikh', ['Draf', 'Menunggu Kelulusan', 'Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/kt', 'description' => 'Fetch tempoh tarikh configuration list'],
            ]);

        $kt02 = $this->seed($mid, $kid, 'PRF-KNF-KT-02', 'Tambah Maklumat Tempoh Tarikh',
            'pages/profiling/konfigurasi/kt/admin/tambah/index.vue', 480, $admin,
            $this->itemsTambah('Maklumat Tempoh Tarikh', ['Maklumat Tempoh']),
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/kt', 'description' => 'Create new tempoh tarikh record'],
            ]);

        $kt03 = $this->seed($mid, $kid, 'PRF-KNF-KT-03', 'Kemaskini Maklumat Tempoh Tarikh',
            'pages/profiling/konfigurasi/kt/admin/kemaskini/index.vue', 490, $admin,
            $this->itemsKemaskini('Maklumat Tempoh Tarikh'),
            [
                ['method' => 'GET',   'endpoint' => '/profiling/konfigurasi/kt/{id}', 'description' => 'Fetch tempoh tarikh for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/konfigurasi/kt/{id}', 'description' => 'Update tempoh tarikh record'],
            ]);

        $kt04 = $this->seed($mid, $kid, 'PRF-KNF-KT-04', 'Lihat Maklumat Tempoh Tarikh',
            'pages/profiling/konfigurasi/kt/admin/lihat/[id].vue', 500, $all,
            $this->itemsLihat('Maklumat Tempoh Tarikh'),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/kt/{id}', 'description' => 'Fetch tempoh tarikh detail'],
            ]);

        $kt05 = $this->seed($mid, $kid, 'PRF-KNF-KT-05', 'Senarai Kelulusan Tempoh Tarikh',
            'pages/profiling/konfigurasi/kt/pelulus/index.vue', 510, $approver,
            $this->itemsPelulusSenarai('Tempoh Tarikh', ['Menunggu Kelulusan', 'Diluluskan', 'Tidak Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/kt/kelulusan', 'description' => 'Fetch tempoh tarikh approval list'],
            ]);

        $kt06 = $this->seed($mid, $kid, 'PRF-KNF-KT-06', 'Kelulusan Tempoh Tarikh',
            'pages/profiling/konfigurasi/kt/pelulus/kelulusan/index.vue', 520, $approver,
            $this->itemsPelulusSenarai('Tempoh Tarikh (Kelulusan)', ['Menunggu Kelulusan', 'Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/kt/kelulusan/pending', 'description' => 'Fetch pending kelulusan for tempoh tarikh'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // LEVEL KELULUSAN  — 1 page
        // ══════════════════════════════════════════════════════════════════

        $lv01 = $this->seed($mid, $kid, 'PRF-KNF-LV-01', 'Level Kelulusan',
            'pages/profiling/konfigurasi/level/index.vue', 530, $all,
            $this->itemsStub('Level Kelulusan'), []);

        // ══════════════════════════════════════════════════════════════════
        // RUU — KELULUSAN DATA  — 8 pages
        // ══════════════════════════════════════════════════════════════════

        $ru01 = $this->seed($mid, $kid, 'PRF-KNF-RU-01', 'Senarai Maklumat Kelulusan Data (RUU)',
            'pages/profiling/konfigurasi/ruu/admin/index.vue', 540, $admin,
            $this->itemsAdminSenarai('Maklumat Kelulusan Data RUU', ['Baru', 'Menunggu Kelulusan', 'Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/ruu', 'description' => 'Fetch RUU kelulusan data configuration list'],
            ]);

        $ru02 = $this->seed($mid, $kid, 'PRF-KNF-RU-02', 'Tambah Kategori Maklumat RUU',
            'pages/profiling/konfigurasi/ruu/admin/tambah/index.vue', 550, $admin,
            $this->itemsTambah('Kategori Maklumat RUU', ['Maklumat Kategori']),
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/ruu/kategori', 'description' => 'Create new RUU kategori record'],
            ]);

        $ru03 = $this->seed($mid, $kid, 'PRF-KNF-RU-03', 'Lihat Kategori Maklumat RUU',
            'pages/profiling/konfigurasi/ruu/admin/lihat.vue', 560, $all,
            [
                ['screen_name' => 'Lihat Kategori Maklumat RUU', 'label' => 'Maklumat Kategori',          'type' => 'Display', 'condition' => 'Read-only display of RUU kategori details'],
                ['screen_name' => 'Lihat Kategori Maklumat RUU', 'label' => 'Butang Kemaskini Kategori',  'type' => 'Button',  'condition' => 'Opens modal to update category name/description'],
                ['screen_name' => 'Lihat Kategori Maklumat RUU', 'label' => 'Senarai Sub Kategori',       'type' => 'Table',   'condition' => 'Table of sub-categories under this RUU kategori'],
                ['screen_name' => 'Lihat Kategori Maklumat RUU', 'label' => 'Butang Tambah Sub Kategori', 'type' => 'Button',  'condition' => 'Navigate to tambah sub-kategori page'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/profiling/konfigurasi/ruu/kategori/{id}',         'description' => 'Fetch RUU kategori detail'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/konfigurasi/ruu/kategori/{id}',         'description' => 'Update RUU kategori via modal'],
            ]);

        $ru04 = $this->seed($mid, $kid, 'PRF-KNF-RU-04', 'Lihat Data Maklumat RUU',
            'pages/profiling/konfigurasi/ruu/admin/lihat-data.vue', 570, $all,
            [
                ['screen_name' => 'Lihat Data Maklumat RUU', 'label' => 'Maklumat Data RUU',       'type' => 'Display', 'condition' => 'Read-only display — Kaedah Kemaskini, metadata fields'],
                ['screen_name' => 'Lihat Data Maklumat RUU', 'label' => 'Butang Kemaskini',        'type' => 'Button',  'condition' => 'Toggle to edit mode for the data record'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/ruu/{id}/data', 'description' => 'Fetch RUU data record detail'],
            ]);

        $ru05 = $this->seed($mid, $kid, 'PRF-KNF-RU-05', 'Tambah Sub Kategori Maklumat RUU',
            'pages/profiling/konfigurasi/ruu/admin/tambah-sub-kategori.vue', 580, $admin,
            $this->itemsTambah('Sub Kategori Maklumat RUU', ['Maklumat Sub Kategori']),
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/ruu/kategori/{id}/sub', 'description' => 'Add sub-kategori to RUU kategori'],
            ]);

        $ru06 = $this->seed($mid, $kid, 'PRF-KNF-RU-06', 'Kemaskini Maklumat RUU',
            'pages/profiling/konfigurasi/ruu/admin/kemaskini.vue', 590, $admin,
            $this->itemsKemaskini('Maklumat RUU'),
            [
                ['method' => 'GET',   'endpoint' => '/profiling/konfigurasi/ruu/{id}', 'description' => 'Fetch RUU record for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/konfigurasi/ruu/{id}', 'description' => 'Update RUU record'],
            ]);

        $ru07 = $this->seed($mid, $kid, 'PRF-KNF-RU-07', 'Senarai Kelulusan RUU',
            'pages/profiling/konfigurasi/ruu/pelulus/index.vue', 600, $approver,
            $this->itemsPelulusSenarai('Maklumat RUU', ['Menunggu Kelulusan', 'Diluluskan', 'Tidak Diluluskan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/ruu/kelulusan', 'description' => 'Fetch RUU approval list'],
            ]);

        $ru08 = $this->seed($mid, $kid, 'PRF-KNF-RU-08', 'Kelulusan RUU',
            'pages/profiling/konfigurasi/ruu/pelulus/kelulusan.vue', 610, $approver,
            $this->itemsPelulusSenarai('RUU (Kelulusan)', ['Menunggu Kelulusan', 'Diluluskan']),
            [
                ['method' => 'GET',  'endpoint' => '/profiling/konfigurasi/ruu/kelulusan/pending',       'description' => 'Fetch pending RUU kelulusan'],
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/ruu/{id}/keputusan',          'description' => 'Submit kelulusan decision for RUU'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // SPA — SOALAN PENILAIAN AWAL  — 7 pages
        // ══════════════════════════════════════════════════════════════════

        $sp01 = $this->seed($mid, $kid, 'PRF-KNF-SP-01', 'Senarai Soalan Penilaian Awal',
            'pages/profiling/konfigurasi/spa/admin2/index.vue', 620, $admin,
            [
                ['screen_name' => 'Senarai Soalan Penilaian Awal', 'label' => 'Jadual Soalan',     'type' => 'Table',  'condition' => 'Table listing all penilaian awal questions with Lihat / Edit actions'],
                ['screen_name' => 'Senarai Soalan Penilaian Awal', 'label' => 'Butang Tambah',     'type' => 'Button', 'condition' => 'Navigate to Tambah Soalan page'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/spa/soalan', 'description' => 'Fetch list of penilaian awal soalan'],
            ]);

        $sp02 = $this->seed($mid, $kid, 'PRF-KNF-SP-02', 'Tambah Soalan Penilaian Awal',
            'pages/profiling/konfigurasi/spa/tambah/index.vue', 630, $admin,
            [
                ['screen_name' => 'Tambah Soalan Penilaian Awal', 'label' => 'Teks Soalan',          'type' => 'Text',    'condition' => 'Input — question text (mandatory)'],
                ['screen_name' => 'Tambah Soalan Penilaian Awal', 'label' => 'Senarai Jawapan',      'type' => 'Table',   'condition' => 'Dynamic rows to add answer options; must have at least one answer before submit'],
                ['screen_name' => 'Tambah Soalan Penilaian Awal', 'label' => 'Butang Tambah Jawapan','type' => 'Button',  'condition' => 'Add new answer row'],
                ['screen_name' => 'Tambah Soalan Penilaian Awal', 'label' => 'Butang Hantar',        'type' => 'Button',  'condition' => 'Submit soalan for approval (disabled if no answers added)'],
            ],
            [
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/spa/soalan', 'description' => 'Create new penilaian awal soalan with answers'],
            ]);

        $sp03 = $this->seed($mid, $kid, 'PRF-KNF-SP-03', 'Lihat Soalan Penilaian Awal',
            'pages/profiling/konfigurasi/spa/admin2/lihat/[id].vue', 640, $all,
            $this->itemsLihat('Soalan Penilaian Awal'),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/spa/soalan/{id}', 'description' => 'Fetch soalan penilaian awal detail with answers'],
            ]);

        $sp04 = $this->seed($mid, $kid, 'PRF-KNF-SP-04', 'Kemaskini Soalan Penilaian Awal',
            'pages/profiling/konfigurasi/spa/kemaskini/[id].vue', 650, $admin,
            $this->itemsKemaskini('Soalan Penilaian Awal'),
            [
                ['method' => 'GET',   'endpoint' => '/profiling/konfigurasi/spa/soalan/{id}', 'description' => 'Fetch soalan for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/konfigurasi/spa/soalan/{id}', 'description' => 'Update soalan penilaian awal'],
            ]);

        $sp05 = $this->seed($mid, $kid, 'PRF-KNF-SP-05', 'Hierarki Level 1 Soalan Penilaian Awal',
            'pages/profiling/konfigurasi/spa/hierarchy/level1/[id].vue', 660, $all,
            [
                ['screen_name' => 'Hierarki Level 1 Soalan', 'label' => 'Maklumat Soalan',      'type' => 'Display', 'condition' => 'Left panel (3/4) — soalan details and senarai jawapan (pelulus view: stacked; admin view: separate)'],
                ['screen_name' => 'Hierarki Level 1 Soalan', 'label' => 'Panel Tindakan',       'type' => 'Display', 'condition' => 'Right panel (1/4) — admin: Tindakan; pelulus: keputusan buttons'],
                ['screen_name' => 'Hierarki Level 1 Soalan', 'label' => 'Senarai Jawapan',      'type' => 'Table',   'condition' => 'Table of answers for the soalan (shown in different layout per role)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/spa/soalan/{id}/hierarki', 'description' => 'Fetch soalan hierarchy with level-1 breakdown'],
            ]);

        $sp06 = $this->seed($mid, $kid, 'PRF-KNF-SP-06', 'Senarai Kelulusan Soalan Penilaian Awal',
            'pages/profiling/konfigurasi/spa/pelulus/index.vue', 670, $approver,
            [
                ['screen_name' => 'Senarai Kelulusan Soalan Penilaian Awal', 'label' => 'Senarai Soalan & Jawapan',  'type' => 'Table',  'condition' => 'Table listing all soalan & jawapan pending approval'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/konfigurasi/spa/soalan/kelulusan', 'description' => 'Fetch soalan penilaian awal awaiting approval'],
            ]);

        $sp07 = $this->seed($mid, $kid, 'PRF-KNF-SP-07', 'Kelulusan Soalan Penilaian Awal',
            'pages/profiling/konfigurasi/spa/pelulus/kelulusan/index.vue', 680, $approver,
            $this->itemsLulusButiran('Soalan Penilaian Awal'),
            [
                ['method' => 'GET',  'endpoint' => '/profiling/konfigurasi/spa/soalan/{id}/kelulusan',        'description' => 'Fetch soalan kelulusan detail'],
                ['method' => 'POST', 'endpoint' => '/profiling/konfigurasi/spa/soalan/{id}/keputusan',        'description' => 'Submit kelulusan decision for soalan'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // PAGE LINKS
        // ══════════════════════════════════════════════════════════════════

        $links = [
            // Had Kifayah
            [$hk01->id, $hk02->id],
            [$hk02->id, $hk03->id],
            [$hk02->id, $hk04->id],
            [$hk02->id, $hk05->id],
            [$hk04->id, $hk07->id],
            [$hk05->id, $hk07->id],
            [$hk08->id, $hk09->id],
            [$hk11->id, $hk12->id],
            [$hk11->id, $hk13->id],
            [$hk15->id, $hk16->id],
            // Multidimensi
            [$md01->id, $md02->id],
            [$md02->id, $md03->id],
            [$md02->id, $md04->id],
            [$md02->id, $md05->id],
            [$md04->id, $md07->id],
            [$md05->id, $md07->id],
            [$md07->id, $md08->id],
            [$md05->id, $md09->id],
            [$md09->id, $md10->id],
            [$md11->id, $md12->id],
            [$md13->id, $md14->id],
            [$md13->id, $md15->id],
            // Household
            [$hh01->id, $hh02->id],
            [$hh02->id, $hh03->id],
            [$hh02->id, $hh04->id],
            [$hh02->id, $hh05->id],
            [$hh06->id, $hh07->id],
            // KB
            [$kb01->id, $kb02->id],
            [$kb01->id, $kb04->id],
            [$kb05->id, $kb06->id],
            // KT
            [$kt01->id, $kt02->id],
            [$kt01->id, $kt04->id],
            [$kt05->id, $kt06->id],
            // RUU
            [$ru01->id, $ru02->id],
            [$ru01->id, $ru03->id],
            [$ru03->id, $ru04->id],
            [$ru03->id, $ru05->id],
            [$ru07->id, $ru08->id],
            // SPA
            [$sp01->id, $sp02->id],
            [$sp01->id, $sp03->id],
            [$sp03->id, $sp04->id],
            [$sp03->id, $sp05->id],
            [$sp06->id, $sp07->id],
        ];

        foreach ($links as [$from, $to]) {
            \DB::table('rtmf_frontend_links')
                ->updateOrInsert(
                    ['from_frontend_id' => $from, 'to_frontend_id' => $to],
                    ['from_frontend_id' => $from, 'to_frontend_id' => $to],
                );
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    private function seed(
        int $moduleId,
        int $subModuleId,
        string $specId,
        string $title,
        string $vuePath,
        int $sortOrder,
        array $actorIds,
        array $items,
        array $endpoints
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
                'id_fr'            => $item['id_fr'] ?? ($fe->spec_id . '-FR-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT)),
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

    private function itemsStub(string $title): array
    {
        return [
            ['screen_name' => $title, 'label' => 'Halaman Stub', 'type' => 'Display',
             'condition' => 'Placeholder/stub page — no content yet'],
        ];
    }

    private function itemsAdminSenarai(string $screenName, array $tabs): array
    {
        $items = [];
        foreach ($tabs as $tab) {
            $items[] = ['screen_name' => $screenName, 'label' => "Tab: $tab", 'type' => 'Table',
                        'condition' => "Tab showing records with status: $tab"];
        }
        $items[] = ['screen_name' => $screenName, 'label' => 'Stat Kad', 'type' => 'Display',
                    'condition' => 'Statistics cards showing count per status'];
        $items[] = ['screen_name' => $screenName, 'label' => 'Butang Tambah', 'type' => 'Button',
                    'condition' => 'Navigate to tambah page'];
        return $items;
    }

    private function itemsTambah(string $entity, array $sections): array
    {
        $items = [];
        foreach ($sections as $section) {
            $items[] = ['screen_name' => "Tambah $entity", 'label' => $section, 'type' => 'Text',
                        'condition' => "Form section: $section"];
        }
        $items[] = ['screen_name' => "Tambah $entity", 'label' => 'Butang Simpan', 'type' => 'Button',
                    'condition' => 'Submit and save the new record'];
        $items[] = ['screen_name' => "Tambah $entity", 'label' => 'Butang Kembali', 'type' => 'Button',
                    'condition' => 'Return to senarai without saving'];
        return $items;
    }

    private function itemsLihat(string $entity): array
    {
        return [
            ['screen_name' => "Lihat $entity", 'label' => "Maklumat $entity",  'type' => 'Display',
             'condition' => 'Read-only display of all record fields'],
            ['screen_name' => "Lihat $entity", 'label' => 'Butang Kembali',    'type' => 'Button',
             'condition' => 'Return to senarai'],
        ];
    }

    private function itemsKemaskini(string $entity): array
    {
        return [
            ['screen_name' => "Kemaskini $entity", 'label' => "Maklumat $entity",  'type' => 'Text',
             'condition' => 'Editable form fields pre-filled with current record values'],
            ['screen_name' => "Kemaskini $entity", 'label' => 'Butang Simpan',     'type' => 'Button',
             'condition' => 'Submit changes'],
            ['screen_name' => "Kemaskini $entity", 'label' => 'Butang Kembali',    'type' => 'Button',
             'condition' => 'Return to senarai without saving'],
        ];
    }

    private function itemsSemak(string $entity): array
    {
        return [
            ['screen_name' => "Semak $entity", 'label' => "Maklumat $entity",      'type' => 'Display',
             'condition' => 'Read-only display for review before forwarding to pelulus'],
            ['screen_name' => "Semak $entity", 'label' => 'Butang Hantar ke Pelulus', 'type' => 'Button',
             'condition' => 'Forward record to pelulus for approval'],
            ['screen_name' => "Semak $entity", 'label' => 'Butang Tolak',          'type' => 'Button',
             'condition' => 'Reject and return to requester with remarks'],
        ];
    }

    private function itemsPelulusSenarai(string $entity, array $tabs): array
    {
        $items = [];
        foreach ($tabs as $tab) {
            $items[] = ['screen_name' => "Kelulusan $entity", 'label' => "Tab: $tab", 'type' => 'Table',
                        'condition' => "Tab showing records with status: $tab"];
        }
        $items[] = ['screen_name' => "Kelulusan $entity", 'label' => 'Stat Kad', 'type' => 'Display',
                    'condition' => 'Statistics cards showing count per status'];
        return $items;
    }

    private function itemsLulusButiran(string $entity): array
    {
        return [
            ['screen_name' => "Butiran Kelulusan $entity", 'label' => "Maklumat $entity",   'type' => 'Display',
             'condition' => 'Full read-only display of record for kelulusan review'],
            ['screen_name' => "Butiran Kelulusan $entity", 'label' => 'Butang Lulus',       'type' => 'Button',
             'condition' => 'Approve the record'],
            ['screen_name' => "Butiran Kelulusan $entity", 'label' => 'Butang Tolak',       'type' => 'Button',
             'condition' => 'Reject with remarks modal'],
            ['screen_name' => "Butiran Kelulusan $entity", 'label' => 'Butang Kembali',     'type' => 'Button',
             'condition' => 'Return to senarai kelulusan'],
        ];
    }
}
