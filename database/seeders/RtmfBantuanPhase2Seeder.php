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

class RtmfBantuanPhase2Seeder extends Seeder
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

        $blk = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'BLK'],
            ['name' => 'Bulk Processing', 'sort_order' => 40],
        );
        $btu = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'BTU'],
            ['name' => 'Bantuan Utama', 'sort_order' => 50],
        );
        $stp = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'STP'],
            ['name' => 'Study Profile', 'sort_order' => 60],
        );
        $bds = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'BDS'],
            ['name' => 'Bantuan Dengan Siasatan', 'sort_order' => 70],
        );
        $lap = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'LAP'],
            ['name' => 'Laporan', 'sort_order' => 80],
        );
        $plh = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'PLH'],
            ['name' => 'Profiling Lihat', 'sort_order' => 90],
        );

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        $staff = [$pegawai->id, $penyelia->id];
        $all   = [$pegawai->id, $penyelia->id, $pelulus->id];

        $mid = $module->id;

        // ══════════════════════════════════════════════════════════════════
        // BULK PROCESSING (BLK) — 3 pages
        // ══════════════════════════════════════════════════════════════════

        $this->seed($mid, $blk->id, 'BNT-BLK-01', 'Senarai Bulk Processing',
            'pages/pengurusan-bantuan/bulk-processing/index.vue', 10, $staff,
            [
                ['screen_name' => 'Senarai Bulk Processing', 'label' => 'Carian / Filter',    'type' => 'Text',   'condition' => 'Filter by status, date range, bantuan type'],
                ['screen_name' => 'Senarai Bulk Processing', 'label' => 'Jadual Senarai',     'type' => 'Table',  'condition' => 'Columns: Rujukan, Jenis Bantuan, Bilangan Penerima, Status, Tarikh Cipta'],
                ['screen_name' => 'Senarai Bulk Processing', 'label' => 'Butang Tambah',      'type' => 'Button', 'condition' => 'Navigate to form.vue to create new bulk processing'],
                ['screen_name' => 'Senarai Bulk Processing', 'label' => 'Butang Lihat',       'type' => 'Button', 'condition' => 'Navigate to [id]/index.vue for details'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/bulk-processing', 'description' => 'Fetch paginated list of bulk processing records'],
            ]);

        $this->seed($mid, $blk->id, 'BNT-BLK-02', 'Tambah Bulk Processing',
            'pages/pengurusan-bantuan/bulk-processing/form.vue', 20, $staff,
            [
                ['screen_name' => 'Tambah Bulk Processing', 'label' => 'Jenis Bantuan',       'type' => 'Select',   'condition' => 'Dropdown — select bantuan type for bulk grant', 'mandatory' => true],
                ['screen_name' => 'Tambah Bulk Processing', 'label' => 'Tempoh Agihan',       'type' => 'Date',     'condition' => 'Date range picker for disbursement period', 'mandatory' => true],
                ['screen_name' => 'Tambah Bulk Processing', 'label' => 'Senarai Penerima',    'type' => 'Table',    'condition' => 'Searchable table to select/add recipients from profiling'],
                ['screen_name' => 'Tambah Bulk Processing', 'label' => 'Import Excel',        'type' => 'Upload',   'condition' => 'Upload Excel file of recipients as alternative to manual selection'],
                ['screen_name' => 'Tambah Bulk Processing', 'label' => 'Butang Simpan Draf',  'type' => 'Button',   'condition' => 'Save as draft without submitting'],
                ['screen_name' => 'Tambah Bulk Processing', 'label' => 'Butang Hantar',       'type' => 'Button',   'condition' => 'Submit bulk processing for approval workflow'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/jenis-bantuan',          'description' => 'List of available bantuan types for selection'],
                ['method' => 'POST', 'endpoint' => '/bantuan/bulk-processing',         'description' => 'Create new bulk processing record'],
                ['method' => 'POST', 'endpoint' => '/bantuan/bulk-processing/import',  'description' => 'Import recipients from Excel file'],
            ]);

        $blk03 = $this->seed($mid, $blk->id, 'BNT-BLK-03', 'Butiran Bulk Processing',
            'pages/pengurusan-bantuan/bulk-processing/[id]/index.vue', 30, $all,
            [
                ['screen_name' => 'Butiran Bulk Processing', 'label' => 'Maklumat Rekod',     'type' => 'Display', 'condition' => 'Summary: Rujukan, Jenis Bantuan, Status, Tarikh'],
                ['screen_name' => 'Butiran Bulk Processing', 'label' => 'Senarai Penerima',   'type' => 'Table',   'condition' => 'List of all recipients in this batch with per-row status'],
                ['screen_name' => 'Butiran Bulk Processing', 'label' => 'Kemajuan Pemprosesan', 'type' => 'Display', 'condition' => 'Progress bar / count of processed vs total recipients'],
                ['screen_name' => 'Butiran Bulk Processing', 'label' => 'Butang Proses Semula', 'type' => 'Button', 'condition' => 'Re-trigger processing for failed entries (Pegawai only)'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/bulk-processing/{id}',          'description' => 'Fetch bulk processing record detail with recipient list'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/bulk-processing/{id}/reprocess', 'description' => 'Re-process failed recipients in this bulk batch'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // BANTUAN UTAMA (BTU) — 2 pages
        // ══════════════════════════════════════════════════════════════════

        $this->seed($mid, $btu->id, 'BNT-BTU-01', 'Senarai Bantuan Utama',
            'pages/pengurusan-bantuan/bantuan-utama/senarai-bantuan-utama/index.vue', 10, $staff,
            [
                ['screen_name' => 'Senarai Bantuan Utama', 'label' => 'Carian',               'type' => 'Text',   'condition' => 'Search by nama, no. kad pengenalan, atau rujukan'],
                ['screen_name' => 'Senarai Bantuan Utama', 'label' => 'Jadual Senarai',       'type' => 'Table',  'condition' => 'Columns: Rujukan, Nama Pemohon, Jenis Bantuan, Jumlah, Status'],
                ['screen_name' => 'Senarai Bantuan Utama', 'label' => 'Butang Cipta',         'type' => 'Button', 'condition' => 'Navigate to cipta-bantuan-utama/form.vue'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/bantuan-utama', 'description' => 'Fetch paginated list of bantuan utama records'],
            ]);

        $btu02 = $this->seed($mid, $btu->id, 'BNT-BTU-02', 'Cipta Bantuan Utama',
            'pages/pengurusan-bantuan/bantuan-utama/cipta-bantuan-utama/form.vue', 20, $staff,
            [
                ['screen_name' => 'Cipta Bantuan Utama', 'label' => 'Nama Pemohon',           'type' => 'Text',   'condition' => 'Full name of applicant', 'mandatory' => true],
                ['screen_name' => 'Cipta Bantuan Utama', 'label' => 'No. Kad Pengenalan',     'type' => 'Text',   'condition' => 'IC number for applicant lookup', 'mandatory' => true],
                ['screen_name' => 'Cipta Bantuan Utama', 'label' => 'Jenis Bantuan',          'type' => 'Select', 'condition' => 'Dropdown to select aid type', 'mandatory' => true],
                ['screen_name' => 'Cipta Bantuan Utama', 'label' => 'Kariah',                 'type' => 'MultiSelect', 'condition' => 'Multi-select kariah (uses MultiSelectKariah component)'],
                ['screen_name' => 'Cipta Bantuan Utama', 'label' => 'Jumlah Bantuan',         'type' => 'Number', 'condition' => 'Amount to be granted', 'mandatory' => true],
                ['screen_name' => 'Cipta Bantuan Utama', 'label' => 'Catatan',                'type' => 'Textarea', 'condition' => 'Optional remarks'],
                ['screen_name' => 'Cipta Bantuan Utama', 'label' => 'Butang Simpan',          'type' => 'Button', 'condition' => 'Submit new bantuan utama record'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/organisasi/kariah',            'description' => 'Fetch list of kariah for multi-select'],
                ['method' => 'GET',  'endpoint' => '/bantuan/jenis-bantuan',        'description' => 'Fetch list of bantuan types'],
                ['method' => 'POST', 'endpoint' => '/bantuan/bantuan-utama',        'description' => 'Create new bantuan utama record'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // STUDY PROFILE (STP) — 12 pages
        // ══════════════════════════════════════════════════════════════════

        $stp01 = $this->seed($mid, $stp->id, 'BNT-STP-01', 'Senarai Study Profile Asnaf',
            'pages/pengurusan-bantuan/bantuan/study-profile/asnaf/index.vue', 10, $staff,
            [
                ['screen_name' => 'Menunggu Semakan',  'label' => 'Jadual Permohonan',        'type' => 'Table',  'condition' => 'Columns: Rujukan, Nama, Institusi, Semester, Status Permohonan'],
                ['screen_name' => 'Menunggu Semakan',  'label' => 'Butang Semak',             'type' => 'Button', 'condition' => 'Navigate to kemaskini/[id].vue for officer review'],
                ['screen_name' => 'Dalam Proses',      'label' => 'Jadual Dalam Proses',      'type' => 'Table',  'condition' => 'Applications currently under processing by officer'],
                ['screen_name' => 'Selesai',           'label' => 'Jadual Selesai',           'type' => 'Table',  'condition' => 'Completed study profile applications'],
                ['screen_name' => 'Semua',             'label' => 'Jadual Semua',             'type' => 'Table',  'condition' => 'All applications regardless of status'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/study-profile/asnaf', 'description' => 'Fetch paginated study profile applications for asnaf'],
            ]);

        $stp02 = $this->seed($mid, $stp->id, 'BNT-STP-02', 'Kemaskini Study Profile Asnaf',
            'pages/pengurusan-bantuan/bantuan/study-profile/asnaf/kemaskini/[id].vue', 20, $staff,
            [
                ['screen_name' => 'Kemaskini Study Profile Asnaf', 'label' => 'Maklumat Peribadi',        'type' => 'Display', 'condition' => 'Read-only section: Nama, IC, Alamat from profiling'],
                ['screen_name' => 'Kemaskini Study Profile Asnaf', 'label' => 'Maklumat Institusi',       'type' => 'Text',    'condition' => 'Editable: Nama Institusi, Program, Semester'],
                ['screen_name' => 'Kemaskini Study Profile Asnaf', 'label' => 'Maklumat Bantuan Semasa',  'type' => 'Display', 'condition' => 'Current aid amount and type'],
                ['screen_name' => 'Kemaskini Study Profile Asnaf', 'label' => 'Keputusan Penilaian',     'type' => 'Select',  'condition' => 'Officer sets Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Study Profile Asnaf', 'label' => 'Tahap Akademik',          'type' => 'Button',  'condition' => 'Navigate to tahap/[tahapId].vue for per-semester record'],
                ['screen_name' => 'Kemaskini Study Profile Asnaf', 'label' => 'Butang Simpan',           'type' => 'Button',  'condition' => 'Save updated study profile'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/study-profile/asnaf/{id}',    'description' => 'Fetch asnaf study profile detail for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/study-profile/asnaf/{id}',    'description' => 'Update asnaf study profile assessment'],
            ]);

        $this->seed($mid, $stp->id, 'BNT-STP-03', 'Kemaskini Tahap Study Profile Asnaf',
            'pages/pengurusan-bantuan/bantuan/study-profile/asnaf/kemaskini/[id]/tahap/[tahapId].vue', 30, $staff,
            [
                ['screen_name' => 'Kemaskini Tahap Asnaf', 'label' => 'Semester / Tahun',               'type' => 'Display', 'condition' => 'Read-only: current semester and academic year'],
                ['screen_name' => 'Kemaskini Tahap Asnaf', 'label' => 'Markah / Keputusan Semester',    'type' => 'Number',  'condition' => 'GPA or equivalent score input'],
                ['screen_name' => 'Kemaskini Tahap Asnaf', 'label' => 'Dokumen Sokongan',              'type' => 'Upload',  'condition' => 'Upload transcript or semester result slip'],
                ['screen_name' => 'Kemaskini Tahap Asnaf', 'label' => 'Catatan Pegawai',               'type' => 'Textarea', 'condition' => 'Officer notes for this tahap'],
                ['screen_name' => 'Kemaskini Tahap Asnaf', 'label' => 'Butang Simpan',                 'type' => 'Button',  'condition' => 'Save tahap record'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/study-profile/asnaf/{id}/tahap/{tahapId}', 'description' => 'Fetch specific semester record for asnaf study profile'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/study-profile/asnaf/{id}/tahap/{tahapId}', 'description' => 'Update semester/tahap record'],
            ]);

        $this->seed($mid, $stp->id, 'BNT-STP-04', 'Study Profile Diri',
            'pages/pengurusan-bantuan/bantuan/study-profile/diri/index.vue', 40, [$pegawai->id],
            [
                ['screen_name' => 'Study Profile Diri', 'label' => 'Maklumat Profil Diri',             'type' => 'Display', 'condition' => 'Personal profiling data auto-filled from profiling module'],
                ['screen_name' => 'Study Profile Diri', 'label' => 'Senarai Permohonan Saya',         'type' => 'Table',   'condition' => 'Columns: Rujukan, Tarikh, Jenis Bantuan, Status'],
                ['screen_name' => 'Study Profile Diri', 'label' => 'Butang Mohon Baru',               'type' => 'Button',  'condition' => 'Start new study profile application'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/study-profile/diri', 'description' => 'Fetch self study profile and own applications'],
            ]);

        $stp05 = $this->seed($mid, $stp->id, 'BNT-STP-05', 'Senarai Kelulusan Study Profile',
            'pages/pengurusan-bantuan/bantuan/study-profile/kelulusan/index.vue', 50, [$pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan',  'label' => 'Jadual Menunggu Kelulusan',       'type' => 'Table',  'condition' => 'Columns: Rujukan, Nama, Institusi, Cadangan Pegawai, Tarikh'],
                ['screen_name' => 'Menunggu Kelulusan',  'label' => 'Butang Luluskan',                 'type' => 'Button', 'condition' => 'Navigate to kemaskini/[id].vue for approval'],
                ['screen_name' => 'Selesai',             'label' => 'Jadual Selesai',                  'type' => 'Table',  'condition' => 'Approved/rejected applications'],
                ['screen_name' => 'Semua',               'label' => 'Jadual Semua',                    'type' => 'Table',  'condition' => 'All applications'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/study-profile/kelulusan', 'description' => 'Fetch study profile applications pending pelulus approval'],
            ]);

        $stp06 = $this->seed($mid, $stp->id, 'BNT-STP-06', 'Kemaskini Kelulusan Study Profile',
            'pages/pengurusan-bantuan/bantuan/study-profile/kelulusan/kemaskini/[id].vue', 60, [$pelulus->id],
            [
                ['screen_name' => 'Kemaskini Kelulusan Study Profile', 'label' => 'Ringkasan Permohonan',       'type' => 'Display', 'condition' => 'Read-only summary: applicant name, institution, program, recommendation'],
                ['screen_name' => 'Kemaskini Kelulusan Study Profile', 'label' => 'Keputusan Pelulus',         'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus / Tangguh', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Kelulusan Study Profile', 'label' => 'Jumlah Diluluskan',         'type' => 'Number',  'condition' => 'Approved aid amount (editable by pelulus)'],
                ['screen_name' => 'Kemaskini Kelulusan Study Profile', 'label' => 'Catatan Kelulusan',         'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Kemaskini Kelulusan Study Profile', 'label' => 'Butang Simpan',             'type' => 'Button',  'condition' => 'Submit approval decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/study-profile/kelulusan/{id}',  'description' => 'Fetch study profile application for pelulus review'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/study-profile/kelulusan/{id}',  'description' => 'Submit pelulus approval decision'],
            ]);

        $stp07 = $this->seed($mid, $stp->id, 'BNT-STP-07', 'Senarai Study Profile Pegawai',
            'pages/pengurusan-bantuan/bantuan/study-profile/pegawai/index.vue', 70, $staff,
            [
                ['screen_name' => 'Menunggu Semakan',  'label' => 'Jadual Permohonan Pegawai',         'type' => 'Table',  'condition' => 'Applications assigned to logged-in officer for review'],
                ['screen_name' => 'Menunggu Semakan',  'label' => 'Butang Semak',                      'type' => 'Button', 'condition' => 'Navigate to kemaskini/[id].vue'],
                ['screen_name' => 'Selesai',           'label' => 'Jadual Selesai',                    'type' => 'Table',  'condition' => 'Completed officer reviews'],
                ['screen_name' => 'Semua',             'label' => 'Jadual Semua',                      'type' => 'Table',  'condition' => 'All applications in officer queue'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/study-profile/pegawai', 'description' => 'Fetch study profile applications in officer queue'],
            ]);

        $stp08 = $this->seed($mid, $stp->id, 'BNT-STP-08', 'Kemaskini Study Profile Pegawai',
            'pages/pengurusan-bantuan/bantuan/study-profile/pegawai/kemaskini/[id].vue', 80, $staff,
            [
                ['screen_name' => 'Kemaskini Study Profile Pegawai', 'label' => 'Maklumat Peribadi',           'type' => 'Display', 'condition' => 'Read-only profiling data for applicant'],
                ['screen_name' => 'Kemaskini Study Profile Pegawai', 'label' => 'Maklumat Institusi',          'type' => 'Text',    'condition' => 'Editable institution details'],
                ['screen_name' => 'Kemaskini Study Profile Pegawai', 'label' => 'Syor Bantuan',               'type' => 'Number',  'condition' => 'Officer recommended aid amount'],
                ['screen_name' => 'Kemaskini Study Profile Pegawai', 'label' => 'Keputusan Semakan',          'type' => 'Select',  'condition' => 'Disokong / Tidak Disokong', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Study Profile Pegawai', 'label' => 'Tahap Akademik',             'type' => 'Button',  'condition' => 'Navigate to tahap/[tahapId].vue for per-semester data'],
                ['screen_name' => 'Kemaskini Study Profile Pegawai', 'label' => 'Butang Simpan',              'type' => 'Button',  'condition' => 'Save officer assessment'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/study-profile/pegawai/{id}',   'description' => 'Fetch study profile detail for officer kemaskini'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/study-profile/pegawai/{id}',   'description' => 'Save officer assessment result'],
            ]);

        $this->seed($mid, $stp->id, 'BNT-STP-09', 'Kemaskini Tahap Study Profile Pegawai',
            'pages/pengurusan-bantuan/bantuan/study-profile/pegawai/kemaskini/[id]/tahap/[tahapId].vue', 90, $staff,
            [
                ['screen_name' => 'Kemaskini Tahap Pegawai', 'label' => 'Semester / Tahun',              'type' => 'Display', 'condition' => 'Read-only: current semester and academic year'],
                ['screen_name' => 'Kemaskini Tahap Pegawai', 'label' => 'Markah Semester',               'type' => 'Number',  'condition' => 'GPA or result input for this semester'],
                ['screen_name' => 'Kemaskini Tahap Pegawai', 'label' => 'Dokumen Sokongan',             'type' => 'Upload',  'condition' => 'Upload transcript'],
                ['screen_name' => 'Kemaskini Tahap Pegawai', 'label' => 'Catatan',                      'type' => 'Textarea', 'condition' => 'Officer notes for this tahap'],
                ['screen_name' => 'Kemaskini Tahap Pegawai', 'label' => 'Butang Simpan',                'type' => 'Button',  'condition' => 'Save tahap record'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/study-profile/pegawai/{id}/tahap/{tahapId}', 'description' => 'Fetch specific tahap record for officer study profile'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/study-profile/pegawai/{id}/tahap/{tahapId}', 'description' => 'Update officer study profile tahap entry'],
            ]);

        $stp10 = $this->seed($mid, $stp->id, 'BNT-STP-10', 'Siasatan Study Profile',
            'pages/pengurusan-bantuan/bantuan/study-profile/siasatan/index.vue', 100, $staff,
            [
                ['screen_name' => 'Menunggu Siasatan',  'label' => 'Jadual Menunggu Siasatan',          'type' => 'Table',  'condition' => 'Columns: Rujukan, Nama, Institusi, Tarikh Diterima'],
                ['screen_name' => 'Menunggu Siasatan',  'label' => 'Butang Siasatan',                   'type' => 'Button', 'condition' => 'Navigate to siasatan/kemaskini/[id].vue'],
                ['screen_name' => 'Selesai',            'label' => 'Jadual Siasatan Selesai',           'type' => 'Table',  'condition' => 'Completed siasatan records'],
                ['screen_name' => 'Semua',              'label' => 'Jadual Semua',                      'type' => 'Table',  'condition' => 'All siasatan records'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/study-profile/siasatan', 'description' => 'Fetch paginated siasatan queue for study profile'],
            ]);

        $stp11 = $this->seed($mid, $stp->id, 'BNT-STP-11', 'Kemaskini Siasatan Study Profile',
            'pages/pengurusan-bantuan/bantuan/study-profile/siasatan/kemaskini/[id].vue', 110, $staff,
            [
                ['screen_name' => 'Kemaskini Siasatan Study Profile', 'label' => 'Maklumat Permohonan',         'type' => 'Display', 'condition' => 'Read-only applicant and institution summary'],
                ['screen_name' => 'Kemaskini Siasatan Study Profile', 'label' => 'Dapatan Siasatan',           'type' => 'Textarea', 'condition' => 'Officer investigation findings', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Siasatan Study Profile', 'label' => 'Syor Tindakan',              'type' => 'Select',  'condition' => 'Recommend Lulus / Tidak Lulus / Tangguh', 'mandatory' => true],
                ['screen_name' => 'Kemaskini Siasatan Study Profile', 'label' => 'Lampiran Siasatan',          'type' => 'Upload',  'condition' => 'Upload supporting investigation documents'],
                ['screen_name' => 'Kemaskini Siasatan Study Profile', 'label' => 'Butang Simpan',              'type' => 'Button',  'condition' => 'Submit siasatan result'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/study-profile/siasatan/{id}',  'description' => 'Fetch siasatan detail for study profile'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/study-profile/siasatan/{id}',  'description' => 'Submit siasatan findings and recommendation'],
            ]);

        $this->seed($mid, $stp->id, 'BNT-STP-12', 'Muat Naik SPM Study Profile',
            'pages/pengurusan-bantuan/bantuan/study-profile/spm/muat-naik/index.vue', 120, $staff,
            [
                ['screen_name' => 'Muat Naik SPM', 'label' => 'Carian Pemohon',                        'type' => 'Text',   'condition' => 'Search by IC or rujukan to locate student record'],
                ['screen_name' => 'Muat Naik SPM', 'label' => 'Maklumat Pelajar',                     'type' => 'Display', 'condition' => 'Auto-filled: Nama, IC, Program from profiling'],
                ['screen_name' => 'Muat Naik SPM', 'label' => 'Keputusan SPM',                        'type' => 'Upload',  'condition' => 'Upload SPM result document (PDF/image)', 'mandatory' => true],
                ['screen_name' => 'Muat Naik SPM', 'label' => 'Gred Subjek',                          'type' => 'Table',   'condition' => 'Manually enter subject grades from SPM slip'],
                ['screen_name' => 'Muat Naik SPM', 'label' => 'Butang Simpan',                        'type' => 'Button',  'condition' => 'Save SPM record upload'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/study-profile/spm?ic={ic}',   'description' => 'Lookup student record by IC for SPM upload'],
                ['method' => 'POST', 'endpoint' => '/bantuan/study-profile/spm/muat-naik', 'description' => 'Upload and save SPM result for student'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // BANTUAN DENGAN SIASATAN (BDS) — 2 pages
        // ══════════════════════════════════════════════════════════════════

        $bds01 = $this->seed($mid, $bds->id, 'BNT-BDS-01', 'Bantuan Dengan Siasatan',
            'pages/pengurusan-bantuan/bantuan-dengan-siasatan/index.vue', 10, $staff,
            [
                ['screen_name' => 'Menunggu Siasatan',   'label' => 'Jadual Menunggu Siasatan',         'type' => 'Table',  'condition' => 'Columns: Rujukan, Nama Pemohon, Jenis Bantuan, Tarikh'],
                ['screen_name' => 'Menunggu Siasatan',   'label' => 'Butang Buat Siasatan',             'type' => 'Button', 'condition' => 'Open investigation form for selected application'],
                ['screen_name' => 'Dalam Siasatan',      'label' => 'Jadual Dalam Siasatan',            'type' => 'Table',  'condition' => 'Active investigations by current officer'],
                ['screen_name' => 'Selesai',             'label' => 'Jadual Selesai',                   'type' => 'Table',  'condition' => 'Completed investigations'],
                ['screen_name' => 'Semua',               'label' => 'Jadual Semua',                     'type' => 'Table',  'condition' => 'All records regardless of status'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/bantuan-dengan-siasatan',       'description' => 'Fetch paginated list of aid-with-investigation applications'],
                ['method' => 'POST', 'endpoint' => '/bantuan/bantuan-dengan-siasatan/{id}/siasatan', 'description' => 'Submit investigation findings for the selected application'],
            ]);

        $bds02 = $this->seed($mid, $bds->id, 'BNT-BDS-02', 'Bantuan Dengan Siasatan Teknikal',
            'pages/pengurusan-bantuan/bantuan-dengan-siasatan-teknikal/index.vue', 20, $staff,
            [
                ['screen_name' => 'Menunggu Siasatan Teknikal', 'label' => 'Jadual Menunggu',            'type' => 'Table',  'condition' => 'Technical inspection queue: Rujukan, Alamat, Jenis Kerja, Tarikh'],
                ['screen_name' => 'Menunggu Siasatan Teknikal', 'label' => 'Butang Buat Laporan',        'type' => 'Button', 'condition' => 'Open technical inspection form'],
                ['screen_name' => 'Selesai',                    'label' => 'Jadual Selesai Teknikal',    'type' => 'Table',  'condition' => 'Completed technical inspections'],
                ['screen_name' => 'Semua',                      'label' => 'Jadual Semua Teknikal',      'type' => 'Table',  'condition' => 'All technical inspection records'],
                ['screen_name' => 'Menunggu Siasatan Teknikal', 'label' => 'Laporan Gambar',             'type' => 'Upload', 'condition' => 'Upload site photos as part of technical inspection report'],
                ['screen_name' => 'Menunggu Siasatan Teknikal', 'label' => 'Laporan Teknikal',           'type' => 'Upload', 'condition' => 'Upload signed technical report (PDF)'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/bantuan-dengan-siasatan-teknikal',          'description' => 'Fetch paginated list of technical investigation applications'],
                ['method' => 'POST', 'endpoint' => '/bantuan/bantuan-dengan-siasatan-teknikal/{id}/laporan', 'description' => 'Submit technical inspection report'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // LAPORAN (LAP) — 5 pages
        // ══════════════════════════════════════════════════════════════════

        $this->seed($mid, $lap->id, 'BNT-LAP-01', 'Laporan',
            'pages/pengurusan-bantuan/laporan/index.vue', 10, $all,
            [
                ['screen_name' => 'Laporan', 'label' => 'Kad Navigasi Agihan Bantuan',          'type' => 'Display', 'condition' => 'Link card to laporan/agihan-bantuan'],
                ['screen_name' => 'Laporan', 'label' => 'Kad Navigasi Senarai Permohonan',      'type' => 'Display', 'condition' => 'Link card to laporan/senarai-permohonan-tuntutan'],
                ['screen_name' => 'Laporan', 'label' => 'Kad Navigasi Statistik Permohonan',   'type' => 'Display', 'condition' => 'Link card to laporan/statistik-permohonan'],
                ['screen_name' => 'Laporan', 'label' => 'Kad Navigasi Statistik Tuntutan',     'type' => 'Display', 'condition' => 'Link card to laporan/statistik-tuntutan'],
            ],
            []);

        $this->seed($mid, $lap->id, 'BNT-LAP-02', 'Laporan Agihan Bantuan',
            'pages/pengurusan-bantuan/laporan/agihan-bantuan/index.vue', 20, $all,
            [
                ['screen_name' => 'Laporan Agihan Bantuan', 'label' => 'Filter Tarikh',           'type' => 'Date',    'condition' => 'Date range filter for agihan period'],
                ['screen_name' => 'Laporan Agihan Bantuan', 'label' => 'Filter Jenis Bantuan',    'type' => 'Select',  'condition' => 'Dropdown to filter by aid type'],
                ['screen_name' => 'Laporan Agihan Bantuan', 'label' => 'Filter Kariah / Daerah',  'type' => 'Select',  'condition' => 'Scope filter by kariah or daerah'],
                ['screen_name' => 'Laporan Agihan Bantuan', 'label' => 'Jadual Laporan',          'type' => 'Table',   'condition' => 'Agihan summary: Jenis Bantuan, Bilangan, Jumlah (RM), Kariah'],
                ['screen_name' => 'Laporan Agihan Bantuan', 'label' => 'Muat Turun Excel',        'type' => 'Button',  'condition' => 'Export agihan report to Excel'],
                ['screen_name' => 'Laporan Agihan Bantuan', 'label' => 'Muat Turun PDF',          'type' => 'Button',  'condition' => 'Export agihan report to PDF'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/laporan/agihan-bantuan',          'description' => 'Fetch agihan bantuan report data with filters'],
                ['method' => 'GET', 'endpoint' => '/bantuan/laporan/agihan-bantuan/export',   'description' => 'Export agihan bantuan report as Excel/PDF'],
            ]);

        $this->seed($mid, $lap->id, 'BNT-LAP-03', 'Laporan Senarai Permohonan Tuntutan',
            'pages/pengurusan-bantuan/laporan/senarai-permohonan-tuntutan/index.vue', 30, $all,
            [
                ['screen_name' => 'Senarai Permohonan Tuntutan', 'label' => 'Filter Tarikh',      'type' => 'Date',    'condition' => 'Date range filter'],
                ['screen_name' => 'Senarai Permohonan Tuntutan', 'label' => 'Filter Status',      'type' => 'Select',  'condition' => 'Filter by permohonan/tuntutan status'],
                ['screen_name' => 'Senarai Permohonan Tuntutan', 'label' => 'Filter Jenis',       'type' => 'Select',  'condition' => 'Filter by bantuan type'],
                ['screen_name' => 'Senarai Permohonan Tuntutan', 'label' => 'Jadual Laporan',     'type' => 'Table',   'condition' => 'Columns: Rujukan, Nama, Jenis Bantuan, Tuntutan, Status, Tarikh'],
                ['screen_name' => 'Senarai Permohonan Tuntutan', 'label' => 'Muat Turun',         'type' => 'Button',  'condition' => 'Export to Excel'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/laporan/senarai-permohonan-tuntutan', 'description' => 'Fetch permohonan and tuntutan combined list for reporting'],
                ['method' => 'GET', 'endpoint' => '/bantuan/laporan/senarai-permohonan-tuntutan/export', 'description' => 'Export report to Excel'],
            ]);

        $this->seed($mid, $lap->id, 'BNT-LAP-04', 'Laporan Statistik Permohonan',
            'pages/pengurusan-bantuan/laporan/statistik-permohonan/index.vue', 40, $all,
            [
                ['screen_name' => 'Statistik Permohonan', 'label' => 'Filter Tahun / Bulan',     'type' => 'Date',    'condition' => 'Year/month selector for statistics period'],
                ['screen_name' => 'Statistik Permohonan', 'label' => 'Carta Bar Permohonan',     'type' => 'Display', 'condition' => 'Bar chart: applications per month by status'],
                ['screen_name' => 'Statistik Permohonan', 'label' => 'Carta Pai Jenis Bantuan',  'type' => 'Display', 'condition' => 'Pie chart: distribution by bantuan type'],
                ['screen_name' => 'Statistik Permohonan', 'label' => 'Ringkasan Statistik',      'type' => 'Display', 'condition' => 'Summary cards: Total, Lulus, Ditolak, Dalam Proses'],
                ['screen_name' => 'Statistik Permohonan', 'label' => 'Muat Turun',               'type' => 'Button',  'condition' => 'Export statistics to Excel/PDF'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/laporan/statistik-permohonan',        'description' => 'Fetch application statistics aggregated by month/type/status'],
                ['method' => 'GET', 'endpoint' => '/bantuan/laporan/statistik-permohonan/export', 'description' => 'Export statistics report'],
            ]);

        $this->seed($mid, $lap->id, 'BNT-LAP-05', 'Laporan Statistik Tuntutan',
            'pages/pengurusan-bantuan/laporan/statistik-tuntutan/index.vue', 50, $all,
            [
                ['screen_name' => 'Statistik Tuntutan', 'label' => 'Filter Tahun / Bulan',       'type' => 'Date',    'condition' => 'Year/month selector'],
                ['screen_name' => 'Statistik Tuntutan', 'label' => 'Carta Bar Tuntutan',         'type' => 'Display', 'condition' => 'Bar chart: tuntutan count per month'],
                ['screen_name' => 'Statistik Tuntutan', 'label' => 'Carta Pai Jenis Tuntutan',   'type' => 'Display', 'condition' => 'Pie chart: distribution by tuntutan type'],
                ['screen_name' => 'Statistik Tuntutan', 'label' => 'Ringkasan Statistik',        'type' => 'Display', 'condition' => 'Summary cards: Total, Lulus, Ditolak, Dalam Proses'],
                ['screen_name' => 'Statistik Tuntutan', 'label' => 'Muat Turun',                 'type' => 'Button',  'condition' => 'Export statistics'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/laporan/statistik-tuntutan',        'description' => 'Fetch tuntutan statistics aggregated by period'],
                ['method' => 'GET', 'endpoint' => '/bantuan/laporan/statistik-tuntutan/export', 'description' => 'Export tuntutan statistics report'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // PROFILING LIHAT (PLH) — 1 page
        // ══════════════════════════════════════════════════════════════════

        $this->seed($mid, $plh->id, 'BNT-PLH-01', 'Lihat Profil Bantuan',
            'pages/pengurusan-bantuan/profiling/lihat/[id].vue', 10, $staff,
            [
                ['screen_name' => 'Lihat Profil', 'label' => 'Maklumat Peribadi',                'type' => 'Display', 'condition' => 'Read-only: Nama, IC, Alamat, Hubungan dari modul profiling'],
                ['screen_name' => 'Lihat Profil', 'label' => 'Sejarah Bantuan',                 'type' => 'Table',   'condition' => 'Past and current aid records for this asnaf: Jenis Bantuan, Tempoh, Status'],
                ['screen_name' => 'Lihat Profil', 'label' => 'Sejarah Tuntutan',                'type' => 'Table',   'condition' => 'Past and current tuntutan linked to this asnaf'],
                ['screen_name' => 'Lihat Profil', 'label' => 'Status Profiling Semasa',         'type' => 'Display', 'condition' => 'Current profiling status badge (Asnaf, PPP, etc)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/profiling/{id}',               'description' => 'Fetch asnaf profiling detail as seen from bantuan module'],
                ['method' => 'GET', 'endpoint' => '/bantuan/agihan/senarai?asnafId={id}',  'description' => 'Fetch bantuan history for this asnaf'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // Page Links
        // ══════════════════════════════════════════════════════════════════

        $links = [
            ['BNT-BLK-01', 'BNT-BLK-02'],
            ['BNT-BLK-01', 'BNT-BLK-03'],
            ['BNT-BTU-01', 'BNT-BTU-02'],
            ['BNT-STP-01', 'BNT-STP-02'],
            ['BNT-STP-02', 'BNT-STP-03'],
            ['BNT-STP-05', 'BNT-STP-06'],
            ['BNT-STP-07', 'BNT-STP-08'],
            ['BNT-STP-08', 'BNT-STP-09'],
            ['BNT-STP-10', 'BNT-STP-11'],
            ['BNT-LAP-01', 'BNT-LAP-02'],
            ['BNT-LAP-01', 'BNT-LAP-03'],
            ['BNT-LAP-01', 'BNT-LAP-04'],
            ['BNT-LAP-01', 'BNT-LAP-05'],
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
