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

class RtmfPenolongAmilPhase1Seeder extends Seeder
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

        $dsh = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'DSH'],
            ['name' => 'Dashboard', 'sort_order' => 10],
        );
        $ppa = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'PPA'],
            ['name' => 'Pendaftaran Penolong Amil', 'sort_order' => 20],
        );
        $ptl = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'PTL'],
            ['name' => 'Penamatan Lantikan', 'sort_order' => 30],
        );
        $msy = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'MSY'],
            ['name' => 'Elaun Mesyuarat', 'sort_order' => 40],
        );
        $tgs = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'TGS'],
            ['name' => 'Elaun Tugasan', 'sort_order' => 50],
        );
        $lap = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'LAP'],
            ['name' => 'Laporan', 'sort_order' => 60],
        );

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        $staff = [$pegawai->id, $penyelia->id];
        $all   = [$pegawai->id, $penyelia->id, $pelulus->id];

        $mid = $module->id;

        // ══════════════════════════════════════════════════════════════════
        // DASHBOARD (DSH) — 1 page
        // ══════════════════════════════════════════════════════════════════

        $this->seed($mid, $dsh->id, 'PAM-DSH-01', 'Dashboard Penolong Amil',
            'pages/penolong-amil/dashboard/index.vue', 10, $all,
            [
                ['screen_name' => 'Dashboard', 'label' => 'Jumlah PA Aktif',                  'type' => 'Display', 'condition' => 'Summary card: total active Penolong Amil count'],
                ['screen_name' => 'Dashboard', 'label' => 'PA Baru Bulan Ini',               'type' => 'Display', 'condition' => 'Summary card: newly registered PA this month'],
                ['screen_name' => 'Dashboard', 'label' => 'Penamatan Akan Datang',           'type' => 'Display', 'condition' => 'Summary card: PA whose appointment expires within 30 days'],
                ['screen_name' => 'Dashboard', 'label' => 'Tuntutan Elaun Menunggu',         'type' => 'Display', 'condition' => 'Summary card: pending elaun claims (mesyuarat + tugasan)'],
                ['screen_name' => 'Dashboard', 'label' => 'Carta Pendaftaran Bulanan',       'type' => 'Display', 'condition' => 'Bar chart: PA registrations per month for current year'],
                ['screen_name' => 'Dashboard', 'label' => 'Carta Agihan Kariah',             'type' => 'Display', 'condition' => 'Pie chart: PA distribution by kariah/daerah'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/dashboard', 'description' => 'Fetch dashboard summary stats and chart data'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // PENDAFTARAN PENOLONG AMIL (PPA) — 4 pages
        // ══════════════════════════════════════════════════════════════════

        $ppa01 = $this->seed($mid, $ppa->id, 'PAM-PPA-01', 'Senarai Penolong Amil',
            'pages/penolong-amil/pendaftaran-penolong-amil/index.vue', 10, $all,
            [
                ['screen_name' => 'Aktif',      'label' => 'Jadual PA Aktif',                 'type' => 'Table',  'condition' => 'Columns: Nama, No. IC, Kariah, Jawatan, Tarikh Lantikan, Status'],
                ['screen_name' => 'Aktif',      'label' => 'Butang Lihat',                    'type' => 'Button', 'condition' => 'Navigate to [id]/index.vue for detail'],
                ['screen_name' => 'Aktif',      'label' => 'Butang Tambah',                   'type' => 'Button', 'condition' => 'Navigate to tambah.vue to register new PA'],
                ['screen_name' => 'Tidak Aktif', 'label' => 'Jadual PA Tidak Aktif',          'type' => 'Table',  'condition' => 'PA whose appointment has ended or been terminated'],
                ['screen_name' => 'Semua',      'label' => 'Jadual Semua',                    'type' => 'Table',  'condition' => 'All PA regardless of status with search/filter'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/pendaftaran', 'description' => 'Fetch paginated list of Penolong Amil records'],
            ]);

        $ppa02 = $this->seed($mid, $ppa->id, 'PAM-PPA-02', 'Tambah Penolong Amil',
            'pages/penolong-amil/pendaftaran-penolong-amil/tambah.vue', 20, $staff,
            [
                ['screen_name' => 'Tambah Penolong Amil', 'label' => 'No. Kad Pengenalan',   'type' => 'Text',   'condition' => 'IC number — used to look up existing profiling data', 'mandatory' => true],
                ['screen_name' => 'Tambah Penolong Amil', 'label' => 'Nama Penuh',           'type' => 'Text',   'condition' => 'Full name (auto-filled from IC lookup if available)', 'mandatory' => true],
                ['screen_name' => 'Tambah Penolong Amil', 'label' => 'No. Telefon',          'type' => 'Text',   'condition' => 'Contact number'],
                ['screen_name' => 'Tambah Penolong Amil', 'label' => 'Kariah',               'type' => 'Select', 'condition' => 'Kariah assigned to this PA', 'mandatory' => true],
                ['screen_name' => 'Tambah Penolong Amil', 'label' => 'Jawatan',              'type' => 'Select', 'condition' => 'PA position/role from konfigurasi jawatan', 'mandatory' => true],
                ['screen_name' => 'Tambah Penolong Amil', 'label' => 'Kategori PA',          'type' => 'Select', 'condition' => 'PA category from konfigurasi kategori', 'mandatory' => true],
                ['screen_name' => 'Tambah Penolong Amil', 'label' => 'Tarikh Lantikan',      'type' => 'Date',   'condition' => 'Appointment start date', 'mandatory' => true],
                ['screen_name' => 'Tambah Penolong Amil', 'label' => 'Tarikh Tamat',         'type' => 'Date',   'condition' => 'Appointment end date (based on sesi konfigurasi)'],
                ['screen_name' => 'Tambah Penolong Amil', 'label' => 'Dokumen Sokongan',     'type' => 'Upload', 'condition' => 'Upload supporting documents per configured dokumen requirements'],
                ['screen_name' => 'Tambah Penolong Amil', 'label' => 'Butang Simpan',        'type' => 'Button', 'condition' => 'Submit new PA registration'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/penolong-amil/konfigurasi/kategori',    'description' => 'Fetch PA categories for dropdown'],
                ['method' => 'GET',  'endpoint' => '/organisasi/kariah',                      'description' => 'Fetch kariah list for assignment'],
                ['method' => 'POST', 'endpoint' => '/penolong-amil/pendaftaran',              'description' => 'Register new Penolong Amil'],
            ]);

        $ppa03 = $this->seed($mid, $ppa->id, 'PAM-PPA-03', 'Butiran Penolong Amil',
            'pages/penolong-amil/pendaftaran-penolong-amil/[id]/index.vue', 30, $all,
            [
                ['screen_name' => 'Butiran PA', 'label' => 'Maklumat Peribadi',              'type' => 'Display', 'condition' => 'Read-only: Nama, IC, No. Telefon, Alamat'],
                ['screen_name' => 'Butiran PA', 'label' => 'Maklumat Lantikan',              'type' => 'Display', 'condition' => 'Kariah, Jawatan, Kategori, Tarikh Lantikan, Tarikh Tamat'],
                ['screen_name' => 'Butiran PA', 'label' => 'Status Aktif',                  'type' => 'Display', 'condition' => 'Current appointment status badge'],
                ['screen_name' => 'Butiran PA', 'label' => 'Dokumen Terlampir',             'type' => 'Table',   'condition' => 'Uploaded documents list with download links'],
                ['screen_name' => 'Butiran PA', 'label' => 'Sejarah Tugasan',               'type' => 'Table',   'condition' => 'Past tugasan and mesyuarat attendance summary'],
                ['screen_name' => 'Butiran PA', 'label' => 'Butang Edit',                   'type' => 'Button',  'condition' => 'Navigate to [id]/edit.vue'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/pendaftaran/{id}',         'description' => 'Fetch full PA record with lantikan history and documents'],
            ]);

        $ppa04 = $this->seed($mid, $ppa->id, 'PAM-PPA-04', 'Edit Penolong Amil',
            'pages/penolong-amil/pendaftaran-penolong-amil/[id]/edit.vue', 40, $staff,
            [
                ['screen_name' => 'Edit PA', 'label' => 'No. Telefon',                       'type' => 'Text',   'condition' => 'Editable contact number'],
                ['screen_name' => 'Edit PA', 'label' => 'Kariah',                            'type' => 'Select', 'condition' => 'Editable kariah assignment', 'mandatory' => true],
                ['screen_name' => 'Edit PA', 'label' => 'Jawatan',                           'type' => 'Select', 'condition' => 'Editable PA position', 'mandatory' => true],
                ['screen_name' => 'Edit PA', 'label' => 'Kategori PA',                       'type' => 'Select', 'condition' => 'Editable PA category', 'mandatory' => true],
                ['screen_name' => 'Edit PA', 'label' => 'Tarikh Tamat',                      'type' => 'Date',   'condition' => 'Editable appointment end date'],
                ['screen_name' => 'Edit PA', 'label' => 'Dokumen Sokongan',                  'type' => 'Upload', 'condition' => 'Replace or add supporting documents'],
                ['screen_name' => 'Edit PA', 'label' => 'Butang Simpan',                     'type' => 'Button', 'condition' => 'Save PA edits (triggers kelulusan if applicable)'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/pendaftaran/{id}',        'description' => 'Fetch PA record for editing'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/pendaftaran/{id}',        'description' => 'Update PA record'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // PENAMATAN LANTIKAN (PTL) — 2 pages
        // ══════════════════════════════════════════════════════════════════

        $ptl01 = $this->seed($mid, $ptl->id, 'PAM-PTL-01', 'Senarai Penamatan Lantikan',
            'pages/penolong-amil/penamatan-lantikan/index.vue', 10, $all,
            [
                ['screen_name' => 'Menunggu Penamatan',  'label' => 'Jadual Menunggu',        'type' => 'Table',  'condition' => 'Columns: Nama PA, Kariah, Tarikh Tamat, Sebab Penamatan, Status'],
                ['screen_name' => 'Menunggu Penamatan',  'label' => 'Butang Tamatkan',        'type' => 'Button', 'condition' => 'Navigate to [id]/kelulusan.vue to submit termination for approval'],
                ['screen_name' => 'Tamat',               'label' => 'Jadual Tamat',           'type' => 'Table',  'condition' => 'PA whose appointment has been terminated and approved'],
                ['screen_name' => 'Semua',               'label' => 'Jadual Semua',           'type' => 'Table',  'condition' => 'All penamatan records including pending and completed'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/penamatan-lantikan', 'description' => 'Fetch list of PA with expiring or terminated appointments'],
            ]);

        $ptl02 = $this->seed($mid, $ptl->id, 'PAM-PTL-02', 'Kelulusan Penamatan Lantikan',
            'pages/penolong-amil/penamatan-lantikan/[id]/kelulusan.vue', 20, $all,
            [
                ['screen_name' => 'Kelulusan Penamatan', 'label' => 'Maklumat PA',            'type' => 'Display', 'condition' => 'Read-only: Nama, IC, Kariah, Tarikh Lantikan, Tarikh Tamat'],
                ['screen_name' => 'Kelulusan Penamatan', 'label' => 'Sebab Penamatan',        'type' => 'Select',  'condition' => 'Reason: Tamat Tempoh / Mengundurkan Diri / Diberhentikan / Lain-lain', 'mandatory' => true],
                ['screen_name' => 'Kelulusan Penamatan', 'label' => 'Catatan',                'type' => 'Textarea', 'condition' => 'Additional remarks for termination'],
                ['screen_name' => 'Kelulusan Penamatan', 'label' => 'Keputusan',              'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus (for pelulus role)', 'mandatory' => true],
                ['screen_name' => 'Kelulusan Penamatan', 'label' => 'Butang Simpan',          'type' => 'Button',  'condition' => 'Submit penamatan decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/penamatan-lantikan/{id}',          'description' => 'Fetch PA record for termination processing'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/penamatan-lantikan/{id}/kelulusan', 'description' => 'Submit or approve termination of PA appointment'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // ELAUN MESYUARAT (MSY) — 6 pages
        // ══════════════════════════════════════════════════════════════════

        $msy01 = $this->seed($mid, $msy->id, 'PAM-MSY-01', 'Dashboard Elaun Mesyuarat',
            'pages/penolong-amil/pengurusan-elaun/mesyuarat/index.vue', 10, $all,
            [
                ['screen_name' => 'Dashboard Elaun Mesyuarat', 'label' => 'Mesyuarat Akan Datang',     'type' => 'Display', 'condition' => 'Upcoming meetings with elaun claim status'],
                ['screen_name' => 'Dashboard Elaun Mesyuarat', 'label' => 'Tuntutan Belum Selesai',    'type' => 'Display', 'condition' => 'Count of pending elaun claims for meetings'],
                ['screen_name' => 'Dashboard Elaun Mesyuarat', 'label' => 'Pautan ke Senarai',         'type' => 'Button',  'condition' => 'Navigate to mesyuarat/senarai.vue for full list'],
                ['screen_name' => 'Dashboard Elaun Mesyuarat', 'label' => 'Pautan ke Tambah',          'type' => 'Button',  'condition' => 'Navigate to mesyuarat/tambah.vue to create new meeting record'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/elaun/mesyuarat/summary', 'description' => 'Fetch elaun mesyuarat dashboard summary'],
            ]);

        $msy02 = $this->seed($mid, $msy->id, 'PAM-MSY-02', 'Senarai Elaun Mesyuarat',
            'pages/penolong-amil/pengurusan-elaun/mesyuarat/senarai.vue', 20, $all,
            [
                ['screen_name' => 'Belum Dituntut',  'label' => 'Jadual Belum Dituntut',      'type' => 'Table',  'condition' => 'Columns: Tarikh Mesyuarat, Jenis, Bilangan Hadir, Status Tuntutan'],
                ['screen_name' => 'Belum Dituntut',  'label' => 'Butang Tuntut',              'type' => 'Button', 'condition' => 'Navigate to [id]/index.vue to process claim'],
                ['screen_name' => 'Dalam Proses',    'label' => 'Jadual Dalam Proses',        'type' => 'Table',  'condition' => 'Claims currently under review/approval'],
                ['screen_name' => 'Selesai',         'label' => 'Jadual Selesai',             'type' => 'Table',  'condition' => 'Fully processed and paid claims'],
                ['screen_name' => 'Semua',           'label' => 'Jadual Semua',               'type' => 'Table',  'condition' => 'All mesyuarat elaun records'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/elaun/mesyuarat', 'description' => 'Fetch paginated mesyuarat elaun list with status filter'],
            ]);

        $msy03 = $this->seed($mid, $msy->id, 'PAM-MSY-03', 'Tambah Elaun Mesyuarat',
            'pages/penolong-amil/pengurusan-elaun/mesyuarat/tambah.vue', 30, $staff,
            [
                ['screen_name' => 'Tambah Elaun Mesyuarat', 'label' => 'Tarikh Mesyuarat',   'type' => 'Date',    'condition' => 'Meeting date', 'mandatory' => true],
                ['screen_name' => 'Tambah Elaun Mesyuarat', 'label' => 'Jenis Mesyuarat',    'type' => 'Select',  'condition' => 'Meeting type from konfigurasi kategori', 'mandatory' => true],
                ['screen_name' => 'Tambah Elaun Mesyuarat', 'label' => 'Lokasi',             'type' => 'Text',    'condition' => 'Meeting venue'],
                ['screen_name' => 'Tambah Elaun Mesyuarat', 'label' => 'Senarai Kehadiran',  'type' => 'Table',   'condition' => 'PA attendance list: Nama, toggle Hadir/Tidak, Kadar Elaun (auto from konfigurasi)'],
                ['screen_name' => 'Tambah Elaun Mesyuarat', 'label' => 'Jumlah Tuntutan',    'type' => 'Display', 'condition' => 'Auto-calculated total elaun amount based on attendance'],
                ['screen_name' => 'Tambah Elaun Mesyuarat', 'label' => 'Dokumen Sokongan',   'type' => 'Upload',  'condition' => 'Upload meeting minutes or attendance sheet'],
                ['screen_name' => 'Tambah Elaun Mesyuarat', 'label' => 'Butang Simpan',      'type' => 'Button',  'condition' => 'Submit mesyuarat elaun record'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/penolong-amil/konfigurasi/kategori',    'description' => 'Fetch meeting types and elaun rates from kategori config'],
                ['method' => 'GET',  'endpoint' => '/penolong-amil/pendaftaran?status=aktif', 'description' => 'Fetch active PA list for attendance selection'],
                ['method' => 'POST', 'endpoint' => '/penolong-amil/elaun/mesyuarat',         'description' => 'Create new mesyuarat elaun record'],
            ]);

        $msy04 = $this->seed($mid, $msy->id, 'PAM-MSY-04', 'Butiran Elaun Mesyuarat',
            'pages/penolong-amil/pengurusan-elaun/mesyuarat/[id]/index.vue', 40, $all,
            [
                ['screen_name' => 'Butiran Elaun Mesyuarat', 'label' => 'Maklumat Mesyuarat',  'type' => 'Display', 'condition' => 'Read-only: Tarikh, Jenis, Lokasi, Status Tuntutan'],
                ['screen_name' => 'Butiran Elaun Mesyuarat', 'label' => 'Senarai Kehadiran',   'type' => 'Table',   'condition' => 'PA attendance with elaun per person and total'],
                ['screen_name' => 'Butiran Elaun Mesyuarat', 'label' => 'Dokumen Terlampir',   'type' => 'Display', 'condition' => 'Meeting minutes and attendance sheet download links'],
                ['screen_name' => 'Butiran Elaun Mesyuarat', 'label' => 'Status Kelulusan',    'type' => 'Display', 'condition' => 'Current approval status and approver info'],
                ['screen_name' => 'Butiran Elaun Mesyuarat', 'label' => 'Butang Edit',         'type' => 'Button',  'condition' => 'Navigate to [id]/edit.vue (only if not yet submitted)'],
                ['screen_name' => 'Butiran Elaun Mesyuarat', 'label' => 'Butang Kelulusan',    'type' => 'Button',  'condition' => 'Navigate to [id]/kelulusan.vue for approval (pelulus)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/elaun/mesyuarat/{id}', 'description' => 'Fetch mesyuarat elaun detail with attendance and approval status'],
            ]);

        $msy05 = $this->seed($mid, $msy->id, 'PAM-MSY-05', 'Edit Elaun Mesyuarat',
            'pages/penolong-amil/pengurusan-elaun/mesyuarat/[id]/edit.vue', 50, $staff,
            [
                ['screen_name' => 'Edit Elaun Mesyuarat', 'label' => 'Tarikh Mesyuarat',     'type' => 'Date',    'condition' => 'Editable meeting date', 'mandatory' => true],
                ['screen_name' => 'Edit Elaun Mesyuarat', 'label' => 'Jenis Mesyuarat',      'type' => 'Select',  'condition' => 'Editable meeting type', 'mandatory' => true],
                ['screen_name' => 'Edit Elaun Mesyuarat', 'label' => 'Lokasi',               'type' => 'Text',    'condition' => 'Editable venue'],
                ['screen_name' => 'Edit Elaun Mesyuarat', 'label' => 'Senarai Kehadiran',    'type' => 'Table',   'condition' => 'Editable attendance — toggle Hadir per PA'],
                ['screen_name' => 'Edit Elaun Mesyuarat', 'label' => 'Dokumen Sokongan',     'type' => 'Upload',  'condition' => 'Replace or add documents'],
                ['screen_name' => 'Edit Elaun Mesyuarat', 'label' => 'Butang Simpan',        'type' => 'Button',  'condition' => 'Save edits and optionally resubmit for approval'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/elaun/mesyuarat/{id}',   'description' => 'Fetch mesyuarat elaun for editing'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/elaun/mesyuarat/{id}',   'description' => 'Update mesyuarat elaun record'],
            ]);

        $msy06 = $this->seed($mid, $msy->id, 'PAM-MSY-06', 'Kelulusan Elaun Mesyuarat',
            'pages/penolong-amil/pengurusan-elaun/mesyuarat/[id]/kelulusan.vue', 60, [$pelulus->id],
            [
                ['screen_name' => 'Kelulusan Elaun Mesyuarat', 'label' => 'Ringkasan Tuntutan',   'type' => 'Display', 'condition' => 'Read-only: Tarikh, Jenis, Jumlah Peserta Hadir, Jumlah Elaun (RM)'],
                ['screen_name' => 'Kelulusan Elaun Mesyuarat', 'label' => 'Senarai Kehadiran',    'type' => 'Table',   'condition' => 'Per-PA attendance and elaun amount for verification'],
                ['screen_name' => 'Kelulusan Elaun Mesyuarat', 'label' => 'Keputusan Pelulus',    'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Kelulusan Elaun Mesyuarat', 'label' => 'Catatan Pelulus',      'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Kelulusan Elaun Mesyuarat', 'label' => 'Butang Simpan',        'type' => 'Button',  'condition' => 'Submit approval decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/elaun/mesyuarat/{id}',           'description' => 'Fetch mesyuarat elaun detail for pelulus review'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/elaun/mesyuarat/{id}/kelulusan', 'description' => 'Submit pelulus approval decision for mesyuarat elaun'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // ELAUN TUGASAN (TGS) — 6 pages
        // ══════════════════════════════════════════════════════════════════

        $tgs01 = $this->seed($mid, $tgs->id, 'PAM-TGS-01', 'Dashboard Elaun Tugasan',
            'pages/penolong-amil/pengurusan-elaun/tugasan/index.vue', 10, $all,
            [
                ['screen_name' => 'Dashboard Elaun Tugasan', 'label' => 'Tugasan Aktif',          'type' => 'Display', 'condition' => 'Count of ongoing PA tugasan this period'],
                ['screen_name' => 'Dashboard Elaun Tugasan', 'label' => 'Tuntutan Belum Selesai', 'type' => 'Display', 'condition' => 'Pending elaun tugasan claims'],
                ['screen_name' => 'Dashboard Elaun Tugasan', 'label' => 'Pautan ke Senarai',      'type' => 'Button',  'condition' => 'Navigate to tugasan/senarai.vue for full list'],
                ['screen_name' => 'Dashboard Elaun Tugasan', 'label' => 'Pautan ke Tambah',       'type' => 'Button',  'condition' => 'Navigate to tugasan/tambah.vue to record new tugasan claim'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/elaun/tugasan/summary', 'description' => 'Fetch elaun tugasan dashboard summary stats'],
            ]);

        $tgs02 = $this->seed($mid, $tgs->id, 'PAM-TGS-02', 'Senarai Elaun Tugasan',
            'pages/penolong-amil/pengurusan-elaun/tugasan/senarai.vue', 20, $all,
            [
                ['screen_name' => 'Belum Dituntut',  'label' => 'Jadual Belum Dituntut',     'type' => 'Table',  'condition' => 'Columns: Nama PA, Jenis Tugasan, Tarikh, Kadar Elaun, Status'],
                ['screen_name' => 'Belum Dituntut',  'label' => 'Butang Tuntut',             'type' => 'Button', 'condition' => 'Navigate to [id]/index.vue to process claim'],
                ['screen_name' => 'Dalam Proses',    'label' => 'Jadual Dalam Proses',       'type' => 'Table',  'condition' => 'Claims under review by approver'],
                ['screen_name' => 'Selesai',         'label' => 'Jadual Selesai',            'type' => 'Table',  'condition' => 'Processed tugasan claims'],
                ['screen_name' => 'Semua',           'label' => 'Jadual Semua',              'type' => 'Table',  'condition' => 'All tugasan elaun records'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/elaun/tugasan', 'description' => 'Fetch paginated tugasan elaun list'],
            ]);

        $tgs03 = $this->seed($mid, $tgs->id, 'PAM-TGS-03', 'Tambah Elaun Tugasan',
            'pages/penolong-amil/pengurusan-elaun/tugasan/tambah.vue', 30, $staff,
            [
                ['screen_name' => 'Tambah Elaun Tugasan', 'label' => 'Nama PA',              'type' => 'Select', 'condition' => 'Select the Penolong Amil for this claim', 'mandatory' => true],
                ['screen_name' => 'Tambah Elaun Tugasan', 'label' => 'Jenis Tugasan',        'type' => 'Select', 'condition' => 'Tugasan type from konfigurasi kategori', 'mandatory' => true],
                ['screen_name' => 'Tambah Elaun Tugasan', 'label' => 'Tarikh Tugasan',       'type' => 'Date',   'condition' => 'Date the tugasan was performed', 'mandatory' => true],
                ['screen_name' => 'Tambah Elaun Tugasan', 'label' => 'Bilangan Unit',        'type' => 'Number', 'condition' => 'Quantity (hours, days, or count based on jenis)'],
                ['screen_name' => 'Tambah Elaun Tugasan', 'label' => 'Kadar Elaun (RM)',     'type' => 'Display', 'condition' => 'Auto-filled from konfigurasi elaun for selected jenis'],
                ['screen_name' => 'Tambah Elaun Tugasan', 'label' => 'Jumlah Tuntutan',      'type' => 'Display', 'condition' => 'Auto-calculated: Bilangan Unit × Kadar Elaun'],
                ['screen_name' => 'Tambah Elaun Tugasan', 'label' => 'Dokumen Sokongan',     'type' => 'Upload', 'condition' => 'Supporting documents for the tugasan (optional)'],
                ['screen_name' => 'Tambah Elaun Tugasan', 'label' => 'Butang Simpan',        'type' => 'Button', 'condition' => 'Submit tugasan elaun record'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/penolong-amil/pendaftaran?status=aktif',  'description' => 'Fetch active PA list for selection'],
                ['method' => 'GET',  'endpoint' => '/penolong-amil/konfigurasi/kategori',       'description' => 'Fetch tugasan types and elaun rates'],
                ['method' => 'POST', 'endpoint' => '/penolong-amil/elaun/tugasan',              'description' => 'Create new tugasan elaun claim'],
            ]);

        $tgs04 = $this->seed($mid, $tgs->id, 'PAM-TGS-04', 'Butiran Elaun Tugasan',
            'pages/penolong-amil/pengurusan-elaun/tugasan/[id]/index.vue', 40, $all,
            [
                ['screen_name' => 'Butiran Elaun Tugasan', 'label' => 'Maklumat Tuntutan',   'type' => 'Display', 'condition' => 'Read-only: Nama PA, Jenis Tugasan, Tarikh, Kadar, Jumlah'],
                ['screen_name' => 'Butiran Elaun Tugasan', 'label' => 'Dokumen Terlampir',   'type' => 'Display', 'condition' => 'Download links for uploaded supporting docs'],
                ['screen_name' => 'Butiran Elaun Tugasan', 'label' => 'Status Kelulusan',    'type' => 'Display', 'condition' => 'Current approval status badge'],
                ['screen_name' => 'Butiran Elaun Tugasan', 'label' => 'Butang Edit',         'type' => 'Button',  'condition' => 'Navigate to [id]/edit.vue (if not yet submitted)'],
                ['screen_name' => 'Butiran Elaun Tugasan', 'label' => 'Butang Kelulusan',    'type' => 'Button',  'condition' => 'Navigate to [id]/kelulusan.vue (pelulus only)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/elaun/tugasan/{id}', 'description' => 'Fetch tugasan elaun detail'],
            ]);

        $tgs05 = $this->seed($mid, $tgs->id, 'PAM-TGS-05', 'Edit Elaun Tugasan',
            'pages/penolong-amil/pengurusan-elaun/tugasan/[id]/edit.vue', 50, $staff,
            [
                ['screen_name' => 'Edit Elaun Tugasan', 'label' => 'Nama PA',                'type' => 'Display', 'condition' => 'Read-only: PA name (cannot change after creation)'],
                ['screen_name' => 'Edit Elaun Tugasan', 'label' => 'Jenis Tugasan',          'type' => 'Select',  'condition' => 'Editable tugasan type', 'mandatory' => true],
                ['screen_name' => 'Edit Elaun Tugasan', 'label' => 'Tarikh Tugasan',         'type' => 'Date',    'condition' => 'Editable date'],
                ['screen_name' => 'Edit Elaun Tugasan', 'label' => 'Bilangan Unit',          'type' => 'Number',  'condition' => 'Editable unit count'],
                ['screen_name' => 'Edit Elaun Tugasan', 'label' => 'Dokumen Sokongan',       'type' => 'Upload',  'condition' => 'Replace or add supporting documents'],
                ['screen_name' => 'Edit Elaun Tugasan', 'label' => 'Butang Simpan',          'type' => 'Button',  'condition' => 'Save tugasan elaun edits'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/elaun/tugasan/{id}',     'description' => 'Fetch tugasan elaun for editing'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/elaun/tugasan/{id}',     'description' => 'Update tugasan elaun record'],
            ]);

        $tgs06 = $this->seed($mid, $tgs->id, 'PAM-TGS-06', 'Kelulusan Elaun Tugasan',
            'pages/penolong-amil/pengurusan-elaun/tugasan/[id]/kelulusan.vue', 60, [$pelulus->id],
            [
                ['screen_name' => 'Kelulusan Elaun Tugasan', 'label' => 'Ringkasan Tuntutan',  'type' => 'Display', 'condition' => 'Nama PA, Jenis Tugasan, Tarikh, Kadar, Jumlah Tuntutan'],
                ['screen_name' => 'Kelulusan Elaun Tugasan', 'label' => 'Keputusan Pelulus',   'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Kelulusan Elaun Tugasan', 'label' => 'Catatan Pelulus',     'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Kelulusan Elaun Tugasan', 'label' => 'Butang Simpan',       'type' => 'Button',  'condition' => 'Submit approval decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/penolong-amil/elaun/tugasan/{id}',            'description' => 'Fetch tugasan elaun for pelulus review'],
                ['method' => 'PATCH', 'endpoint' => '/penolong-amil/elaun/tugasan/{id}/kelulusan',  'description' => 'Submit pelulus approval for tugasan elaun'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // LAPORAN (LAP) — 6 pages
        // ══════════════════════════════════════════════════════════════════

        $this->seed($mid, $lap->id, 'PAM-LAP-01', 'Laporan',
            'pages/penolong-amil/laporan/index.vue', 10, $all,
            [
                ['screen_name' => 'Laporan', 'label' => 'Kad Navigasi Senarai PA',                 'type' => 'Display', 'condition' => 'Link to laporan-senarai-pa/index.vue'],
                ['screen_name' => 'Laporan', 'label' => 'Kad Navigasi Statistik Pendaftaran',      'type' => 'Display', 'condition' => 'Link to laporan-statistik-pendaftaran-pa/index.vue'],
                ['screen_name' => 'Laporan', 'label' => 'Kad Navigasi Statistik Penamatan',        'type' => 'Display', 'condition' => 'Link to laporan-statistik-penamatan-tempoh-pa/index.vue'],
                ['screen_name' => 'Laporan', 'label' => 'Kad Navigasi Statistik Tugasan',          'type' => 'Display', 'condition' => 'Link to laporan-statistik-tugasan-pa/index.vue'],
            ],
            []);

        $this->seed($mid, $lap->id, 'PAM-LAP-02', 'Laporan Senarai PA',
            'pages/penolong-amil/laporan/laporan-senarai-pa/index.vue', 20, $all,
            [
                ['screen_name' => 'Laporan Senarai PA', 'label' => 'Filter Kariah / Daerah',   'type' => 'Select',  'condition' => 'Scope filter'],
                ['screen_name' => 'Laporan Senarai PA', 'label' => 'Filter Kategori',          'type' => 'Select',  'condition' => 'PA category filter'],
                ['screen_name' => 'Laporan Senarai PA', 'label' => 'Filter Status',            'type' => 'Select',  'condition' => 'Aktif / Tidak Aktif / Semua'],
                ['screen_name' => 'Laporan Senarai PA', 'label' => 'Jadual Senarai PA',        'type' => 'Table',   'condition' => 'Columns: Nama, IC, Kariah, Jawatan, Tarikh Lantikan, Status'],
                ['screen_name' => 'Laporan Senarai PA', 'label' => 'Butang Lihat Terperinci',  'type' => 'Button',  'condition' => 'Navigate to maklumat-terperinci.vue for detailed PA info'],
                ['screen_name' => 'Laporan Senarai PA', 'label' => 'Muat Turun Excel',         'type' => 'Button',  'condition' => 'Export PA list to Excel'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/laporan/senarai-pa',        'description' => 'Fetch PA list report data with filters'],
                ['method' => 'GET', 'endpoint' => '/penolong-amil/laporan/senarai-pa/export', 'description' => 'Export PA list to Excel'],
            ]);

        $this->seed($mid, $lap->id, 'PAM-LAP-03', 'Maklumat Terperinci PA',
            'pages/penolong-amil/laporan/laporan-senarai-pa/maklumat-terperinci.vue', 30, $all,
            [
                ['screen_name' => 'Maklumat Terperinci PA', 'label' => 'Maklumat Peribadi',   'type' => 'Display', 'condition' => 'Full PA personal and appointment details'],
                ['screen_name' => 'Maklumat Terperinci PA', 'label' => 'Sejarah Elaun',       'type' => 'Table',   'condition' => 'All past mesyuarat and tugasan elaun claims for this PA'],
                ['screen_name' => 'Maklumat Terperinci PA', 'label' => 'Dokumen Terlampir',   'type' => 'Display', 'condition' => 'All documents submitted during registration'],
                ['screen_name' => 'Maklumat Terperinci PA', 'label' => 'Muat Turun PDF',      'type' => 'Button',  'condition' => 'Export detailed PA profile to PDF'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/laporan/senarai-pa/{id}/terperinci', 'description' => 'Fetch detailed PA profile for report view'],
            ]);

        $this->seed($mid, $lap->id, 'PAM-LAP-04', 'Laporan Statistik Pendaftaran PA',
            'pages/penolong-amil/laporan/laporan-statistik-pendaftaran-pa/index.vue', 40, $all,
            [
                ['screen_name' => 'Statistik Pendaftaran PA', 'label' => 'Filter Tahun',      'type' => 'Select',  'condition' => 'Year filter for registration statistics'],
                ['screen_name' => 'Statistik Pendaftaran PA', 'label' => 'Carta Pendaftaran', 'type' => 'Display', 'condition' => 'Bar/line chart: monthly registration trend'],
                ['screen_name' => 'Statistik Pendaftaran PA', 'label' => 'Carta Agihan Kariah', 'type' => 'Display', 'condition' => 'Distribution by kariah/daerah'],
                ['screen_name' => 'Statistik Pendaftaran PA', 'label' => 'Ringkasan Statistik', 'type' => 'Display', 'condition' => 'Summary cards: Total Baru, Aktif, Tidak Aktif'],
                ['screen_name' => 'Statistik Pendaftaran PA', 'label' => 'Muat Turun',        'type' => 'Button',  'condition' => 'Export statistics to Excel/PDF'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/laporan/statistik-pendaftaran', 'description' => 'Fetch PA registration statistics aggregated by period'],
                ['method' => 'GET', 'endpoint' => '/penolong-amil/laporan/statistik-pendaftaran/export', 'description' => 'Export registration statistics'],
            ]);

        $this->seed($mid, $lap->id, 'PAM-LAP-05', 'Laporan Statistik Penamatan Tempoh PA',
            'pages/penolong-amil/laporan/laporan-statistik-penamatan-tempoh-pa/index.vue', 50, $all,
            [
                ['screen_name' => 'Statistik Penamatan PA', 'label' => 'Filter Tahun',        'type' => 'Select',  'condition' => 'Year filter for termination statistics'],
                ['screen_name' => 'Statistik Penamatan PA', 'label' => 'Carta Penamatan',     'type' => 'Display', 'condition' => 'Monthly breakdown: Tamat Tempoh vs Diberhentikan vs Mengundurkan Diri'],
                ['screen_name' => 'Statistik Penamatan PA', 'label' => 'Ringkasan Statistik', 'type' => 'Display', 'condition' => 'Summary cards: Total Tamat, Sebab Penamatan breakdown'],
                ['screen_name' => 'Statistik Penamatan PA', 'label' => 'Muat Turun',          'type' => 'Button',  'condition' => 'Export to Excel/PDF'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/laporan/statistik-penamatan', 'description' => 'Fetch PA termination statistics'],
                ['method' => 'GET', 'endpoint' => '/penolong-amil/laporan/statistik-penamatan/export', 'description' => 'Export termination statistics'],
            ]);

        $this->seed($mid, $lap->id, 'PAM-LAP-06', 'Laporan Statistik Tugasan PA',
            'pages/penolong-amil/laporan/laporan-statistik-tugasan-pa/index.vue', 60, $all,
            [
                ['screen_name' => 'Statistik Tugasan PA', 'label' => 'Filter Tahun / Bulan', 'type' => 'Date',    'condition' => 'Date range for tugasan statistics'],
                ['screen_name' => 'Statistik Tugasan PA', 'label' => 'Carta Tugasan',        'type' => 'Display', 'condition' => 'Bar chart: tugasan count per month by jenis'],
                ['screen_name' => 'Statistik Tugasan PA', 'label' => 'Carta Elaun Dibayar',  'type' => 'Display', 'condition' => 'Line chart: total elaun paid per month'],
                ['screen_name' => 'Statistik Tugasan PA', 'label' => 'Ringkasan Statistik',  'type' => 'Display', 'condition' => 'Summary: Total Tugasan, Total Elaun (RM), Bilangan PA Terlibat'],
                ['screen_name' => 'Statistik Tugasan PA', 'label' => 'Muat Turun',           'type' => 'Button',  'condition' => 'Export to Excel/PDF'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/penolong-amil/laporan/statistik-tugasan',        'description' => 'Fetch PA tugasan statistics aggregated by period'],
                ['method' => 'GET', 'endpoint' => '/penolong-amil/laporan/statistik-tugasan/export', 'description' => 'Export tugasan statistics'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // Page Links
        // ══════════════════════════════════════════════════════════════════

        $links = [
            ['PAM-PPA-01', 'PAM-PPA-02'],
            ['PAM-PPA-01', 'PAM-PPA-03'],
            ['PAM-PPA-03', 'PAM-PPA-04'],
            ['PAM-PTL-01', 'PAM-PTL-02'],
            ['PAM-MSY-01', 'PAM-MSY-02'],
            ['PAM-MSY-01', 'PAM-MSY-03'],
            ['PAM-MSY-02', 'PAM-MSY-04'],
            ['PAM-MSY-04', 'PAM-MSY-05'],
            ['PAM-MSY-04', 'PAM-MSY-06'],
            ['PAM-TGS-01', 'PAM-TGS-02'],
            ['PAM-TGS-01', 'PAM-TGS-03'],
            ['PAM-TGS-02', 'PAM-TGS-04'],
            ['PAM-TGS-04', 'PAM-TGS-05'],
            ['PAM-TGS-04', 'PAM-TGS-06'],
            ['PAM-LAP-01', 'PAM-LAP-02'],
            ['PAM-LAP-01', 'PAM-LAP-04'],
            ['PAM-LAP-01', 'PAM-LAP-05'],
            ['PAM-LAP-01', 'PAM-LAP-06'],
            ['PAM-LAP-02', 'PAM-LAP-03'],
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
