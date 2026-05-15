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

class RtmfBantuanPhase4Seeder extends Seeder
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

        $htn = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'HTN'],
            ['name' => 'Senarai Hartanah', 'sort_order' => 140],
        );
        $vrb = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'VRB'],
            ['name' => 'Verifikasi Bantuan', 'sort_order' => 150],
        );
        $klk = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'KLK'],
            ['name' => 'Kelulusan Khas', 'sort_order' => 160],
        );
        $kpd = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'KPD'],
            ['name' => 'Kuota Pendidikan', 'sort_order' => 170],
        );

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        $staff = [$pegawai->id, $penyelia->id];
        $all   = [$pegawai->id, $penyelia->id, $pelulus->id];

        $mid = $module->id;

        // ══════════════════════════════════════════════════════════════════
        // SENARAI HARTANAH (HTN) — 10 pages
        // ══════════════════════════════════════════════════════════════════

        $htn01 = $this->seed($mid, $htn->id, 'BNT-HTN-01', 'Konfigurasi Senarai Hartanah',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-hartanah/konfigurasi/index.vue', 10, $all,
            [
                ['screen_name' => 'Konfigurasi Hartanah', 'label' => 'Senarai Konfigurasi',       'type' => 'Table',  'condition' => 'Columns: Jenis Hartanah, Had Nilai, Status Aktif, Tarikh Berkuat Kuasa'],
                ['screen_name' => 'Konfigurasi Hartanah', 'label' => 'Butang Tambah',             'type' => 'Button', 'condition' => 'Navigate to tambah/index.vue to create new property record'],
                ['screen_name' => 'Konfigurasi Hartanah', 'label' => 'Butang Lihat',              'type' => 'Button', 'condition' => 'Navigate to view/[id].vue for read-only detail'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/senarai-hartanah/konfigurasi', 'description' => 'Fetch property configuration list'],
            ]);

        $htn02 = $this->seed($mid, $htn->id, 'BNT-HTN-02', 'Tambah Hartanah',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-hartanah/tambah/index.vue', 20, $staff,
            [
                ['screen_name' => 'Tambah Hartanah', 'label' => 'Jenis Hartanah',                 'type' => 'Select', 'condition' => 'Tanah, Bangunan, Kenderaan, etc.', 'mandatory' => true],
                ['screen_name' => 'Tambah Hartanah', 'label' => 'Nombor Lot / Pendaftaran',       'type' => 'Text',   'condition' => 'Property registration number', 'mandatory' => true],
                ['screen_name' => 'Tambah Hartanah', 'label' => 'Alamat / Lokasi',                'type' => 'Textarea', 'condition' => 'Property address or location description', 'mandatory' => true],
                ['screen_name' => 'Tambah Hartanah', 'label' => 'Nilai Anggaran (RM)',            'type' => 'Number', 'condition' => 'Estimated property value'],
                ['screen_name' => 'Tambah Hartanah', 'label' => 'Butang Tambah Terperinci',       'type' => 'Button', 'condition' => 'Navigate to tambah/terperinci/[id] for detailed property data'],
                ['screen_name' => 'Tambah Hartanah', 'label' => 'Butang Simpan',                  'type' => 'Button', 'condition' => 'Save new hartanah record'],
            ],
            [
                ['method' => 'POST', 'endpoint' => '/bantuan/senarai-hartanah',            'description' => 'Create new hartanah record'],
                ['method' => 'GET',  'endpoint' => '/kod/jenis-hartanah',                  'description' => 'Fetch hartanah type codes for dropdown'],
            ]);

        $htn03 = $this->seed($mid, $htn->id, 'BNT-HTN-03', 'Tambah Terperinci Hartanah',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-hartanah/tambah/terperinci/[id].vue', 30, $staff,
            [
                ['screen_name' => 'Tambah Terperinci Hartanah', 'label' => 'Maklumat Asas',        'type' => 'Display', 'condition' => 'Read-only: Jenis, Nombor Lot, Alamat from tambah/index'],
                ['screen_name' => 'Tambah Terperinci Hartanah', 'label' => 'Luas / Keluasan',      'type' => 'Number',  'condition' => 'Property area in sq. ft or acres'],
                ['screen_name' => 'Tambah Terperinci Hartanah', 'label' => 'Status Pegangan',      'type' => 'Select',  'condition' => 'Freehold / Leasehold / Malay Reserve'],
                ['screen_name' => 'Tambah Terperinci Hartanah', 'label' => 'Dokumen Hakmilik',     'type' => 'Upload',  'condition' => 'Upload title deed or geran tanah'],
                ['screen_name' => 'Tambah Terperinci Hartanah', 'label' => 'Catatan',              'type' => 'Textarea', 'condition' => 'Additional remarks'],
                ['screen_name' => 'Tambah Terperinci Hartanah', 'label' => 'Butang Simpan',        'type' => 'Button',  'condition' => 'Save detailed property information'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/senarai-hartanah/{id}',                  'description' => 'Fetch hartanah record for terperinci step'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/senarai-hartanah/{id}/terperinci',       'description' => 'Save detailed property information'],
            ]);

        $htn04 = $this->seed($mid, $htn->id, 'BNT-HTN-04', 'Lihat Hartanah',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-hartanah/view/[id].vue', 40, $all,
            [
                ['screen_name' => 'Lihat Hartanah', 'label' => 'Maklumat Hartanah',               'type' => 'Display', 'condition' => 'Read-only summary: Jenis, Nombor, Alamat, Nilai'],
                ['screen_name' => 'Lihat Hartanah', 'label' => 'Tab Maklumat Utama',              'type' => 'Button',  'condition' => 'Navigate to view/main/[id] for full detail'],
                ['screen_name' => 'Lihat Hartanah', 'label' => 'Tab Terperinci',                  'type' => 'Button',  'condition' => 'Navigate to view/terperinci/[id] for detailed specs'],
                ['screen_name' => 'Lihat Hartanah', 'label' => 'Tab Kelulusan',                   'type' => 'Button',  'condition' => 'Navigate to view/kelulusan/[id] for approval history'],
                ['screen_name' => 'Lihat Hartanah', 'label' => 'Butang Edit',                     'type' => 'Button',  'condition' => 'Navigate to edit/[id] for modification'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/senarai-hartanah/{id}', 'description' => 'Fetch hartanah record for view'],
            ]);

        $htn05 = $this->seed($mid, $htn->id, 'BNT-HTN-05', 'Lihat Hartanah Utama',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-hartanah/view/main/[id].vue', 50, $all,
            [
                ['screen_name' => 'Lihat Hartanah Utama', 'label' => 'Maklumat Lengkap',          'type' => 'Display', 'condition' => 'All main fields: Jenis, Nombor Lot, Alamat, Nilai, Status Pegangan'],
                ['screen_name' => 'Lihat Hartanah Utama', 'label' => 'Dokumen Terlampir',         'type' => 'Display', 'condition' => 'List of uploaded documents with download links'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/senarai-hartanah/{id}/main', 'description' => 'Fetch hartanah main detail tab'],
            ]);

        $htn06 = $this->seed($mid, $htn->id, 'BNT-HTN-06', 'Lihat Terperinci Hartanah',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-hartanah/view/terperinci/[id].vue', 60, $all,
            [
                ['screen_name' => 'Lihat Terperinci Hartanah', 'label' => 'Data Terperinci',      'type' => 'Display', 'condition' => 'Luas, Status Pegangan, Kategori Guna Tanah, additional specs'],
                ['screen_name' => 'Lihat Terperinci Hartanah', 'label' => 'Penilaian Semasa',     'type' => 'Display', 'condition' => 'Current valuation date and amount'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/senarai-hartanah/{id}/terperinci', 'description' => 'Fetch hartanah detailed specification tab'],
            ]);

        $htn07 = $this->seed($mid, $htn->id, 'BNT-HTN-07', 'Lihat Kelulusan Hartanah',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-hartanah/view/kelulusan/[id].vue', 70, $all,
            [
                ['screen_name' => 'Lihat Kelulusan Hartanah', 'label' => 'Status Kelulusan',      'type' => 'Display', 'condition' => 'Current approval status badge'],
                ['screen_name' => 'Lihat Kelulusan Hartanah', 'label' => 'Sejarah Kelulusan',     'type' => 'Table',   'condition' => 'Approval history: Tarikh, Pelulus, Keputusan, Catatan'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/senarai-hartanah/{id}/kelulusan', 'description' => 'Fetch approval history for this hartanah record'],
            ]);

        $htn08 = $this->seed($mid, $htn->id, 'BNT-HTN-08', 'Edit Hartanah',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-hartanah/edit/[id].vue', 80, $staff,
            [
                ['screen_name' => 'Edit Hartanah', 'label' => 'Jenis Hartanah',                   'type' => 'Select', 'condition' => 'Editable type dropdown', 'mandatory' => true],
                ['screen_name' => 'Edit Hartanah', 'label' => 'Nombor Lot / Pendaftaran',         'type' => 'Text',   'condition' => 'Editable registration number', 'mandatory' => true],
                ['screen_name' => 'Edit Hartanah', 'label' => 'Alamat / Lokasi',                  'type' => 'Textarea', 'condition' => 'Editable address'],
                ['screen_name' => 'Edit Hartanah', 'label' => 'Nilai Anggaran (RM)',              'type' => 'Number', 'condition' => 'Editable estimated value'],
                ['screen_name' => 'Edit Hartanah', 'label' => 'Butang Terperinci',                'type' => 'Button', 'condition' => 'Navigate to edit/terperinci/[id] for detailed edit'],
                ['screen_name' => 'Edit Hartanah', 'label' => 'Butang Simpan',                    'type' => 'Button', 'condition' => 'Save hartanah edits (triggers kelulusan workflow)'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/senarai-hartanah/{id}',         'description' => 'Fetch hartanah for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/senarai-hartanah/{id}',         'description' => 'Update hartanah record and submit for approval'],
            ]);

        $htn09 = $this->seed($mid, $htn->id, 'BNT-HTN-09', 'Edit Terperinci Hartanah',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-hartanah/edit/terperinci/[id].vue', 90, $staff,
            [
                ['screen_name' => 'Edit Terperinci Hartanah', 'label' => 'Luas / Keluasan',       'type' => 'Number',  'condition' => 'Editable property area'],
                ['screen_name' => 'Edit Terperinci Hartanah', 'label' => 'Status Pegangan',       'type' => 'Select',  'condition' => 'Editable tenure status'],
                ['screen_name' => 'Edit Terperinci Hartanah', 'label' => 'Dokumen Hakmilik',      'type' => 'Upload',  'condition' => 'Replace or add document upload'],
                ['screen_name' => 'Edit Terperinci Hartanah', 'label' => 'Catatan',               'type' => 'Textarea', 'condition' => 'Additional notes'],
                ['screen_name' => 'Edit Terperinci Hartanah', 'label' => 'Butang Simpan',         'type' => 'Button',  'condition' => 'Save detailed property edits'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/senarai-hartanah/{id}/terperinci',  'description' => 'Fetch detailed fields for edit'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/senarai-hartanah/{id}/terperinci',  'description' => 'Save terperinci edits'],
            ]);

        $htn10 = $this->seed($mid, $htn->id, 'BNT-HTN-10', 'Senarai Kelulusan Hartanah',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/senarai-hartanah/kelulusan/index.vue', 100, [$pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Jadual Menunggu',             'type' => 'Table',  'condition' => 'Columns: Jenis Hartanah, Nombor, Pemohon, Tarikh Hantar'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Butang Lulus',                'type' => 'Button', 'condition' => 'Navigate to view/kelulusan/[id] for decision'],
                ['screen_name' => 'Selesai',            'label' => 'Jadual Selesai',              'type' => 'Table',  'condition' => 'Approved/rejected hartanah records'],
                ['screen_name' => 'Semua',              'label' => 'Jadual Semua',                'type' => 'Table',  'condition' => 'All kelulusan records'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/senarai-hartanah/kelulusan', 'description' => 'Fetch hartanah records pending pelulus approval'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // VERIFIKASI BANTUAN (VRB) — 5 pages
        // ══════════════════════════════════════════════════════════════════

        $vrb01 = $this->seed($mid, $vrb->id, 'BNT-VRB-01', 'Konfigurasi Verifikasi Bantuan',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/verifikasi-bantuan/konfigurasi/index.vue', 10, $all,
            [
                ['screen_name' => 'Konfigurasi Verifikasi Bantuan', 'label' => 'Senarai Peraturan Verifikasi', 'type' => 'Table',  'condition' => 'Rules/criteria that trigger verification: Threshold, Jenis Bantuan, Frekuensi'],
                ['screen_name' => 'Konfigurasi Verifikasi Bantuan', 'label' => 'Butang Kemaskini',           'type' => 'Button', 'condition' => 'Navigate to senarai/kemaskini/[id] to edit a rule'],
                ['screen_name' => 'Konfigurasi Verifikasi Bantuan', 'label' => 'Butang Lihat',              'type' => 'Button', 'condition' => 'Navigate to senarai/lihat/[id] for read-only rule view'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/verifikasi-bantuan/konfigurasi', 'description' => 'Fetch verification rules configuration list'],
            ]);

        $vrb02 = $this->seed($mid, $vrb->id, 'BNT-VRB-02', 'Kemaskini Verifikasi Bantuan',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/verifikasi-bantuan/senarai/kemaskini/[id].vue', 20, $staff,
            [
                ['screen_name' => 'Kemaskini Verifikasi Bantuan', 'label' => 'Jenis Bantuan',     'type' => 'Display', 'condition' => 'Read-only: aid type this rule applies to'],
                ['screen_name' => 'Kemaskini Verifikasi Bantuan', 'label' => 'Had Jumlah (RM)',   'type' => 'Number',  'condition' => 'Amount threshold that triggers verification', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Verifikasi Bantuan', 'label' => 'Frekuensi',         'type' => 'Select',  'condition' => 'Setiap Permohonan / Tahunan / Setiap 2 Tahun'],
                ['screen_name' => 'Kemaskini Verifikasi Bantuan', 'label' => 'Status Aktif',      'type' => 'Toggle',  'condition' => 'Enable or disable this verification rule'],
                ['screen_name' => 'Kemaskini Verifikasi Bantuan', 'label' => 'Butang Simpan',     'type' => 'Button',  'condition' => 'Save verification rule changes'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/verifikasi-bantuan/senarai/{id}',   'description' => 'Fetch verification rule for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/verifikasi-bantuan/senarai/{id}',   'description' => 'Update verification rule configuration'],
            ]);

        $vrb03 = $this->seed($mid, $vrb->id, 'BNT-VRB-03', 'Lihat Verifikasi Bantuan',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/verifikasi-bantuan/senarai/lihat/[id].vue', 30, $all,
            [
                ['screen_name' => 'Lihat Verifikasi Bantuan', 'label' => 'Maklumat Peraturan',    'type' => 'Display', 'condition' => 'Read-only: Jenis Bantuan, Had, Frekuensi, Status'],
                ['screen_name' => 'Lihat Verifikasi Bantuan', 'label' => 'Sejarah Perubahan',     'type' => 'Table',   'condition' => 'Audit trail: Tarikh, Diubah Oleh, Perubahan'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/verifikasi-bantuan/senarai/{id}', 'description' => 'Fetch verification rule read-only detail'],
            ]);

        $vrb04 = $this->seed($mid, $vrb->id, 'BNT-VRB-04', 'Senarai Kelulusan Verifikasi',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/verifikasi-bantuan/kelulusan/index.vue', 40, [$pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Jadual Menunggu',             'type' => 'Table',  'condition' => 'Columns: Jenis Bantuan, Had Baru, Pemohon, Tarikh'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Butang Lulus',                'type' => 'Button', 'condition' => 'Navigate to kelulusan/lihat/[id] for approval decision'],
                ['screen_name' => 'Selesai',            'label' => 'Jadual Selesai',              'type' => 'Table',  'condition' => 'Approved/rejected verification rules'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/verifikasi-bantuan/kelulusan', 'description' => 'Fetch verification rules pending pelulus approval'],
            ]);

        $vrb05 = $this->seed($mid, $vrb->id, 'BNT-VRB-05', 'Lihat Kelulusan Verifikasi',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/verifikasi-bantuan/kelulusan/lihat/[id].vue', 50, [$pelulus->id],
            [
                ['screen_name' => 'Lihat Kelulusan Verifikasi', 'label' => 'Perbandingan Peraturan', 'type' => 'Display', 'condition' => 'Side-by-side: existing vs proposed rule values'],
                ['screen_name' => 'Lihat Kelulusan Verifikasi', 'label' => 'Keputusan Pelulus',    'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Lihat Kelulusan Verifikasi', 'label' => 'Catatan',              'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Lihat Kelulusan Verifikasi', 'label' => 'Butang Simpan',        'type' => 'Button',  'condition' => 'Submit approval decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/verifikasi-bantuan/kelulusan/{id}',  'description' => 'Fetch verification rule kelulusan detail'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/verifikasi-bantuan/kelulusan/{id}',  'description' => 'Submit pelulus decision for verification rule change'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // KELULUSAN KHAS (KLK) — 5 pages
        // ══════════════════════════════════════════════════════════════════

        $klk01 = $this->seed($mid, $klk->id, 'BNT-KLK-01', 'Konfigurasi Kelulusan Khas',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/kelulusan-khas/konfigurasi/index.vue', 10, $all,
            [
                ['screen_name' => 'Konfigurasi Kelulusan Khas', 'label' => 'Senarai Konfigurasi', 'type' => 'Table',  'condition' => 'Special approval thresholds: Jenis, Had Nilai, Pelulus Khas, Status'],
                ['screen_name' => 'Konfigurasi Kelulusan Khas', 'label' => 'Butang Kemaskini',    'type' => 'Button', 'condition' => 'Navigate to konfigurasi/[id] to edit a special approval rule'],
                ['screen_name' => 'Konfigurasi Kelulusan Khas', 'label' => 'Butang Lihat',        'type' => 'Button', 'condition' => 'Navigate to konfigurasi/lihat/[id] for read-only view'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/kelulusan-khas/konfigurasi', 'description' => 'Fetch special approval configuration list'],
            ]);

        $klk02 = $this->seed($mid, $klk->id, 'BNT-KLK-02', 'Kemaskini Konfigurasi Kelulusan Khas',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/kelulusan-khas/konfigurasi/[id].vue', 20, $staff,
            [
                ['screen_name' => 'Kemaskini Kelulusan Khas', 'label' => 'Jenis Bantuan',         'type' => 'Select', 'condition' => 'Aid type this special rule applies to', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Kelulusan Khas', 'label' => 'Had Nilai (RM)',        'type' => 'Number', 'condition' => 'Value threshold above which special approval is required', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Kelulusan Khas', 'label' => 'Pelulus Khas',          'type' => 'Select', 'condition' => 'Officer/role designated as special approver'],
                ['screen_name' => 'Kemaskini Kelulusan Khas', 'label' => 'Status Aktif',          'type' => 'Toggle', 'condition' => 'Enable/disable this special approval rule'],
                ['screen_name' => 'Kemaskini Kelulusan Khas', 'label' => 'Butang Simpan',         'type' => 'Button', 'condition' => 'Save special approval configuration'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/kelulusan-khas/konfigurasi/{id}',   'description' => 'Fetch special approval config for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/kelulusan-khas/konfigurasi/{id}',   'description' => 'Update special approval configuration'],
            ]);

        $klk03 = $this->seed($mid, $klk->id, 'BNT-KLK-03', 'Lihat Konfigurasi Kelulusan Khas',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/kelulusan-khas/konfigurasi/lihat/[id].vue', 30, $all,
            [
                ['screen_name' => 'Lihat Konfigurasi Kelulusan Khas', 'label' => 'Maklumat Peraturan', 'type' => 'Display', 'condition' => 'Read-only: Jenis Bantuan, Had, Pelulus Khas, Status'],
                ['screen_name' => 'Lihat Konfigurasi Kelulusan Khas', 'label' => 'Sejarah Perubahan',  'type' => 'Table',   'condition' => 'Audit trail for this special approval rule'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/kelulusan-khas/konfigurasi/{id}', 'description' => 'Fetch special approval rule detail (read-only)'],
            ]);

        $klk04 = $this->seed($mid, $klk->id, 'BNT-KLK-04', 'Senarai Kelulusan Khas',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/kelulusan-khas/kelulusan/index.vue', 40, [$pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan Khas', 'label' => 'Jadual Menunggu',        'type' => 'Table',  'condition' => 'Columns: Rujukan Bantuan, Nama Pemohon, Jumlah, Sebab Kelulusan Khas, Tarikh'],
                ['screen_name' => 'Menunggu Kelulusan Khas', 'label' => 'Butang Lulus',           'type' => 'Button', 'condition' => 'Navigate to kelulusan/[id] for decision'],
                ['screen_name' => 'Selesai',                 'label' => 'Jadual Selesai',         'type' => 'Table',  'condition' => 'Completed special approval decisions'],
                ['screen_name' => 'Semua',                   'label' => 'Jadual Semua',           'type' => 'Table',  'condition' => 'All kelulusan khas records'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/kelulusan-khas/kelulusan', 'description' => 'Fetch applications requiring special approval'],
            ]);

        $klk05 = $this->seed($mid, $klk->id, 'BNT-KLK-05', 'Butiran Kelulusan Khas',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/kelulusan-khas/kelulusan/[id].vue', 50, [$pelulus->id],
            [
                ['screen_name' => 'Butiran Kelulusan Khas', 'label' => 'Ringkasan Permohonan',    'type' => 'Display', 'condition' => 'Rujukan, Nama Pemohon, Jenis Bantuan, Jumlah Dipohon, Sebab Khas'],
                ['screen_name' => 'Butiran Kelulusan Khas', 'label' => 'Keputusan Pelulus Khas', 'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Butiran Kelulusan Khas', 'label' => 'Jumlah Diluluskan (RM)', 'type' => 'Number',  'condition' => 'Pelulus may reduce the approved amount'],
                ['screen_name' => 'Butiran Kelulusan Khas', 'label' => 'Catatan Pelulus Khas',   'type' => 'Textarea', 'condition' => 'Mandatory remarks for special approval decisions'],
                ['screen_name' => 'Butiran Kelulusan Khas', 'label' => 'Butang Simpan',          'type' => 'Button',  'condition' => 'Submit special approval decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/kelulusan-khas/kelulusan/{id}',   'description' => 'Fetch special approval request for pelulus khas'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/kelulusan-khas/kelulusan/{id}',   'description' => 'Submit special approval decision'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // KUOTA PENDIDIKAN (KPD) — 5 pages
        // ══════════════════════════════════════════════════════════════════

        $kpd01 = $this->seed($mid, $kpd->id, 'BNT-KPD-01', 'Konfigurasi Kuota Pendidikan',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/kuota-pendidikan/konfigurasi/index.vue', 10, $all,
            [
                ['screen_name' => 'Konfigurasi Kuota Pendidikan', 'label' => 'Senarai Kuota',     'type' => 'Table',  'condition' => 'Columns: Tahun, Institusi/Program, Kuota, Digunakan, Baki'],
                ['screen_name' => 'Konfigurasi Kuota Pendidikan', 'label' => 'Butang Kemaskini',  'type' => 'Button', 'condition' => 'Navigate to konfigurasi/[id] to update quota'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/kuota-pendidikan/konfigurasi', 'description' => 'Fetch education quota configuration list'],
            ]);

        $kpd02 = $this->seed($mid, $kpd->id, 'BNT-KPD-02', 'Kemaskini Kuota Pendidikan',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/kuota-pendidikan/konfigurasi/[id].vue', 20, $staff,
            [
                ['screen_name' => 'Kemaskini Kuota Pendidikan', 'label' => 'Tahun',               'type' => 'Display', 'condition' => 'Read-only budget year'],
                ['screen_name' => 'Kemaskini Kuota Pendidikan', 'label' => 'Institusi / Program', 'type' => 'Select',  'condition' => 'Education institution or program', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Kuota Pendidikan', 'label' => 'Kuota Baru',          'type' => 'Number',  'condition' => 'New quota count', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Kuota Pendidikan', 'label' => 'Catatan',             'type' => 'Textarea', 'condition' => 'Reason for quota update'],
                ['screen_name' => 'Kemaskini Kuota Pendidikan', 'label' => 'Butang Simpan',       'type' => 'Button',  'condition' => 'Submit quota changes for approval'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/kuota-pendidikan/konfigurasi/{id}',  'description' => 'Fetch education quota record for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/kuota-pendidikan/konfigurasi/{id}',  'description' => 'Update education quota and submit for approval'],
            ]);

        $kpd03 = $this->seed($mid, $kpd->id, 'BNT-KPD-03', 'Senarai Kelulusan Kuota Pendidikan',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/kuota-pendidikan/kelulusan/index.vue', 30, [$pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Jadual Menunggu',             'type' => 'Table',  'condition' => 'Columns: Institusi/Program, Kuota Semasa, Kuota Cadangan, Pemohon'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Butang Lulus',                'type' => 'Button', 'condition' => 'Navigate to kelulusan/[id] for decision'],
                ['screen_name' => 'Selesai',            'label' => 'Jadual Selesai',              'type' => 'Table',  'condition' => 'Approved/rejected quota changes'],
                ['screen_name' => 'Semua',              'label' => 'Jadual Semua',                'type' => 'Table',  'condition' => 'All kelulusan records for education quota'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/kuota-pendidikan/kelulusan', 'description' => 'Fetch education quota changes pending approval'],
            ]);

        $kpd04 = $this->seed($mid, $kpd->id, 'BNT-KPD-04', 'Butiran Kelulusan Kuota Pendidikan',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/kuota-pendidikan/kelulusan/[id].vue', 40, [$pelulus->id],
            [
                ['screen_name' => 'Butiran Kelulusan Kuota', 'label' => 'Ringkasan Permohonan',   'type' => 'Display', 'condition' => 'Institusi/Program, Kuota Semasa vs Cadangan'],
                ['screen_name' => 'Butiran Kelulusan Kuota', 'label' => 'Keputusan Pelulus',      'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Butiran Kelulusan Kuota', 'label' => 'Kuota Diluluskan',       'type' => 'Number',  'condition' => 'Pelulus may adjust the approved quota value'],
                ['screen_name' => 'Butiran Kelulusan Kuota', 'label' => 'Catatan Pelulus',        'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Butiran Kelulusan Kuota', 'label' => 'Butang Simpan',          'type' => 'Button',  'condition' => 'Submit kelulusan decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/kuota-pendidikan/kelulusan/{id}',  'description' => 'Fetch quota kelulusan detail for pelulus'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/kuota-pendidikan/kelulusan/{id}',  'description' => 'Submit pelulus decision for quota change'],
            ]);

        $kpd05 = $this->seed($mid, $kpd->id, 'BNT-KPD-05', 'Lihat Kelulusan Kuota Pendidikan',
            'pages/pengurusan-bantuan/konfigurasi-bantuan/kuota-pendidikan/kelulusan/view/[id].vue', 50, $all,
            [
                ['screen_name' => 'Lihat Kelulusan Kuota', 'label' => 'Maklumat Keputusan',       'type' => 'Display', 'condition' => 'Read-only: Keputusan, Kuota Diluluskan, Pelulus, Tarikh, Catatan'],
                ['screen_name' => 'Lihat Kelulusan Kuota', 'label' => 'Sejarah Quota',            'type' => 'Table',   'condition' => 'All past quota changes for this institution/program'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/kuota-pendidikan/kelulusan/view/{id}', 'description' => 'Fetch quota kelulusan result detail (read-only)'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // Page Links
        // ══════════════════════════════════════════════════════════════════

        $links = [
            // Senarai Hartanah
            ['BNT-HTN-01', 'BNT-HTN-02'],
            ['BNT-HTN-01', 'BNT-HTN-04'],
            ['BNT-HTN-02', 'BNT-HTN-03'],
            ['BNT-HTN-04', 'BNT-HTN-05'],
            ['BNT-HTN-04', 'BNT-HTN-06'],
            ['BNT-HTN-04', 'BNT-HTN-07'],
            ['BNT-HTN-04', 'BNT-HTN-08'],
            ['BNT-HTN-08', 'BNT-HTN-09'],
            ['BNT-HTN-10', 'BNT-HTN-07'],
            // Verifikasi Bantuan
            ['BNT-VRB-01', 'BNT-VRB-02'],
            ['BNT-VRB-01', 'BNT-VRB-03'],
            ['BNT-VRB-04', 'BNT-VRB-05'],
            // Kelulusan Khas
            ['BNT-KLK-01', 'BNT-KLK-02'],
            ['BNT-KLK-01', 'BNT-KLK-03'],
            ['BNT-KLK-04', 'BNT-KLK-05'],
            // Kuota Pendidikan
            ['BNT-KPD-01', 'BNT-KPD-02'],
            ['BNT-KPD-03', 'BNT-KPD-04'],
            ['BNT-KPD-03', 'BNT-KPD-05'],
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
