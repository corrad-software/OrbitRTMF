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

class RtmfBantuanPhase1Seeder extends Seeder
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

        $moh = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'MOH'],
            ['name' => 'Mohon Bantuan', 'sort_order' => 10],
        );
        $sbn = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'SBN'],
            ['name' => 'Senarai Bantuan', 'sort_order' => 20],
        );
        $tut = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'TUT'],
            ['name' => 'Tuntutan', 'sort_order' => 30],
        );

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);
        $pemohon  = RtmfActor::firstOrCreate(['name' => 'Pemohon']);

        $staff   = [$pegawai->id, $penyelia->id];
        $all     = [$pegawai->id, $penyelia->id, $pelulus->id];
        $allIncPemohon = [$pegawai->id, $penyelia->id, $pelulus->id, $pemohon->id];

        $mid = $module->id;

        // ══════════════════════════════════════════════════════════════════
        // MOHON BANTUAN (MOH)  — 11 pages
        // ══════════════════════════════════════════════════════════════════

        $m01 = $this->seed($mid, $moh->id, 'BNT-MOH-01', 'Mohon Bantuan',
            'pages/pengurusan-bantuan/mohon-bantuan/index.vue', 10, $allIncPemohon,
            [
                ['screen_name' => 'Mohon Bantuan', 'label' => 'Workflow Iframe',        'type' => 'Display', 'condition' => 'Renders Flowable workflow as iframe; auto-advances through form processes; shows success on completion'],
                ['screen_name' => 'Mohon Bantuan', 'label' => 'Status Memuatkan',       'type' => 'Display', 'condition' => 'Loading spinner while fetching workflow processes'],
                ['screen_name' => 'Mohon Bantuan', 'label' => 'Status Ralat',           'type' => 'Display', 'condition' => 'Error panel with retry button if workflow fetch fails'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/workflow/processes',         'description' => 'Fetch list of workflow form processes for mohon bantuan'],
                ['method' => 'GET', 'endpoint' => '/bantuan/workflow/iframe-url',        'description' => 'Generate iframe URL for current workflow step'],
            ]);

        $m02 = $this->seed($mid, $moh->id, 'BNT-MOH-02', 'Kemaskini Permohonan Bantuan',
            'pages/pengurusan-bantuan/mohon-bantuan/kemaskini/[rujukan]/index.vue', 20, $allIncPemohon,
            [
                ['screen_name' => 'Kemaskini Permohonan Bantuan', 'label' => 'Maklumat Permohonan',  'type' => 'Text',    'condition' => 'Editable form fields for the application — pre-filled with existing data by rujukan'],
                ['screen_name' => 'Kemaskini Permohonan Bantuan', 'label' => 'Butang Simpan',        'type' => 'Button',  'condition' => 'Submit kemaskini'],
                ['screen_name' => 'Kemaskini Permohonan Bantuan', 'label' => 'Butang Kembali',       'type' => 'Button',  'condition' => 'Return without saving'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/permohonan/{rujukan}',    'description' => 'Fetch permohonan data by rujukan for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/permohonan/{rujukan}',    'description' => 'Update permohonan bantuan'],
            ]);

        $m03 = $this->seed($mid, $moh->id, 'BNT-MOH-03', 'Pendaftaran Permohonan Bantuan',
            'pages/pengurusan-bantuan/mohon-bantuan/pendaftaran/[kategori]/[id]/index.vue', 30, $allIncPemohon,
            [
                ['screen_name' => 'Pendaftaran Permohonan Bantuan', 'label' => 'Maklumat Pemohon',     'type' => 'Display', 'condition' => 'Read-only panel — Nama, IC, jenis bantuan, kategori from route params'],
                ['screen_name' => 'Pendaftaran Permohonan Bantuan', 'label' => 'Borang Permohonan',    'type' => 'Text',    'condition' => 'Full permohonan form — fields vary by kategori; dynamic form structure'],
                ['screen_name' => 'Pendaftaran Permohonan Bantuan', 'label' => 'Butang Hantar',        'type' => 'Button',  'condition' => 'Submit new permohonan bantuan'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/permohonan/kategori/{kategori}', 'description' => 'Fetch form schema/fields for the given bantuan kategori'],
                ['method' => 'POST', 'endpoint' => '/bantuan/permohonan',                     'description' => 'Submit new permohonan bantuan registration'],
            ]);

        $m04 = $this->seed($mid, $moh->id, 'BNT-MOH-04', 'Senarai Tugasan',
            'pages/pengurusan-bantuan/mohon-bantuan/tugasan/index.vue', 40, $staff,
            [
                ['screen_name' => 'Senarai Tugasan', 'label' => 'Jadual Tugasan',    'type' => 'Table',  'condition' => 'Table listing assigned bantuan tasks for the officer; columns: No. Rujukan, Pemohon, Jenis Bantuan, Status, Tindakan'],
                ['screen_name' => 'Senarai Tugasan', 'label' => 'Filter Status',     'type' => 'Select', 'condition' => 'Filter dropdown by status (Baharu, Dalam Proses, Selesai)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/tugasan',                'description' => 'Fetch list of bantuan tasks assigned to current officer'],
            ]);

        $m05 = $this->seed($mid, $moh->id, 'BNT-MOH-05', 'Maklumat Tugasan',
            'pages/pengurusan-bantuan/mohon-bantuan/tugasan/maklumat/[rujukan]/index.vue', 50, $staff,
            [
                ['screen_name' => 'Maklumat Tugasan', 'label' => 'Maklumat Permohonan',   'type' => 'Display', 'condition' => 'Full read-only details of the bantuan application (Semakan stage)'],
                ['screen_name' => 'Maklumat Tugasan', 'label' => 'Butang Semak / Proses', 'type' => 'Button',  'condition' => 'Action button to proceed to semakan or mark as reviewed'],
                ['screen_name' => 'Maklumat Tugasan', 'label' => 'Butang Kembali',        'type' => 'Button',  'condition' => 'Return to senarai tugasan'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/permohonan/{rujukan}',       'description' => 'Fetch full permohonan detail for semakan review'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tugasan/{rujukan}/semakan',  'description' => 'Submit semakan decision/action'],
            ]);

        $m06 = $this->seed($mid, $moh->id, 'BNT-MOH-06', 'Maklumat Siasatan',
            'pages/pengurusan-bantuan/mohon-bantuan/tugasan/maklumat-siasatan/[rujukan]/index.vue', 60, $staff,
            [
                ['screen_name' => 'Maklumat Siasatan', 'label' => 'Maklumat Permohonan',    'type' => 'Display', 'condition' => 'Full permohonan details for siasatan stage'],
                ['screen_name' => 'Maklumat Siasatan', 'label' => 'Borang Siasatan',        'type' => 'Text',    'condition' => 'Siasatan form — officer fills in investigation findings, lawatan fields'],
                ['screen_name' => 'Maklumat Siasatan', 'label' => 'Butang Hantar Siasatan', 'type' => 'Button',  'condition' => 'Submit siasatan result to proceed workflow'],
                ['screen_name' => 'Maklumat Siasatan', 'label' => 'Butang Kembali',         'type' => 'Button',  'condition' => 'Return to senarai tugasan'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/permohonan/{rujukan}',          'description' => 'Fetch permohonan detail for siasatan'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tugasan/{rujukan}/siasatan',    'description' => 'Submit siasatan findings'],
            ]);

        $m07 = $this->seed($mid, $moh->id, 'BNT-MOH-07', 'Maklumat Kelulusan',
            'pages/pengurusan-bantuan/mohon-bantuan/tugasan/maklumat-kelulusan/[rujukan]/index.vue', 70, [$pelulus->id],
            [
                ['screen_name' => 'Maklumat Kelulusan', 'label' => 'Maklumat Permohonan',    'type' => 'Display', 'condition' => 'Full permohonan details including siasatan summary for kelulusan decision'],
                ['screen_name' => 'Maklumat Kelulusan', 'label' => 'Butang Lulus / Tolak',   'type' => 'Button',  'condition' => 'Approve or reject the permohonan; remarks required for rejection'],
                ['screen_name' => 'Maklumat Kelulusan', 'label' => 'Butang Kembali',         'type' => 'Button',  'condition' => 'Return to senarai tugasan'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/permohonan/{rujukan}',           'description' => 'Fetch permohonan detail for kelulusan review'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tugasan/{rujukan}/kelulusan',    'description' => 'Submit kelulusan decision (lulus/tolak) with remarks'],
            ]);

        $m08 = $this->seed($mid, $moh->id, 'BNT-MOH-08', 'Laporan Gambar',
            'pages/pengurusan-bantuan/mohon-bantuan/tugasan/laporan-gambar/[rujukan]/index.vue', 80, $staff,
            [
                ['screen_name' => 'Laporan Gambar', 'label' => 'Galeri Gambar',       'type' => 'Display', 'condition' => 'Photo gallery showing uploaded site/inspection photos for the permohonan'],
                ['screen_name' => 'Laporan Gambar', 'label' => 'Butang Muat Naik',    'type' => 'Button',  'condition' => 'Upload new photos (file input)'],
                ['screen_name' => 'Laporan Gambar', 'label' => 'Butang Padam Gambar', 'type' => 'Button',  'condition' => 'Delete photo with confirmation dialog'],
            ],
            [
                ['method' => 'GET',    'endpoint' => '/bantuan/tugasan/{rujukan}/laporan-gambar',       'description' => 'Fetch uploaded photos for this rujukan'],
                ['method' => 'POST',   'endpoint' => '/bantuan/tugasan/{rujukan}/laporan-gambar',       'description' => 'Upload new photo(s)'],
                ['method' => 'DELETE', 'endpoint' => '/bantuan/tugasan/{rujukan}/laporan-gambar/{id}',  'description' => 'Delete a photo'],
            ]);

        $m09 = $this->seed($mid, $moh->id, 'BNT-MOH-09', 'Laporan Teknikal',
            'pages/pengurusan-bantuan/mohon-bantuan/tugasan/laporan-teknikal/[rujukan]/index.vue', 90, $staff,
            [
                ['screen_name' => 'Laporan Teknikal', 'label' => 'Maklumat Laporan Teknikal',  'type' => 'Display', 'condition' => 'Technical report details for the permohonan; may include file attachments'],
                ['screen_name' => 'Laporan Teknikal', 'label' => 'Butang Muat Naik Dokumen',   'type' => 'Button',  'condition' => 'Upload technical report document (PDF/image)'],
                ['screen_name' => 'Laporan Teknikal', 'label' => 'Butang Simpan',              'type' => 'Button',  'condition' => 'Save laporan teknikal'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/tugasan/{rujukan}/laporan-teknikal', 'description' => 'Fetch technical report for this rujukan'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tugasan/{rujukan}/laporan-teknikal', 'description' => 'Save/upload technical report'],
            ]);

        $m10 = $this->seed($mid, $moh->id, 'BNT-MOH-10', 'Sokongan Bill of Quantity',
            'pages/pengurusan-bantuan/mohon-bantuan/tugasan/bill-quantity/[rujukan]/index.vue', 100, $staff,
            [
                ['screen_name' => 'Sokongan BQ', 'label' => 'Jadual Bill of Quantity',  'type' => 'Table',  'condition' => 'Table listing BQ items for the construction/repair works associated with the permohonan'],
                ['screen_name' => 'Sokongan BQ', 'label' => 'Ringkasan Jumlah',         'type' => 'Display','condition' => 'Summary total of BQ amounts'],
                ['screen_name' => 'Sokongan BQ', 'label' => 'Butang Kemaskini BQ',      'type' => 'Button', 'condition' => 'Navigate to kemaskini BQ page'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/tugasan/{rujukan}/bill-quantity', 'description' => 'Fetch bill of quantity items for rujukan'],
            ]);

        $m11 = $this->seed($mid, $moh->id, 'BNT-MOH-11', 'Kemaskini Bill of Quantity',
            'pages/pengurusan-bantuan/mohon-bantuan/tugasan/bill-quantity/[rujukan]/kemaskini/index.vue', 110, $staff,
            [
                ['screen_name' => 'Kemaskini BQ', 'label' => 'Borang Kemaskini BQ',       'type' => 'Text',    'condition' => 'Editable BQ form — item descriptions, quantities, unit rates'],
                ['screen_name' => 'Kemaskini BQ', 'label' => 'Butang Tambah Item',        'type' => 'Button',  'condition' => 'Add new BQ row'],
                ['screen_name' => 'Kemaskini BQ', 'label' => 'Butang Simpan',             'type' => 'Button',  'condition' => 'Save all BQ changes'],
                ['screen_name' => 'Kemaskini BQ', 'label' => 'Butang Kembali',            'type' => 'Button',  'condition' => 'Return to sokongan BQ view'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/tugasan/{rujukan}/bill-quantity',         'description' => 'Fetch current BQ data for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/tugasan/{rujukan}/bill-quantity',         'description' => 'Save updated BQ items'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // SENARAI BANTUAN (SBN)  — 9 pages
        // (Asnaf / Organisasi / Pegawai each have: Senarai, Kemaskini, Padam)
        // ══════════════════════════════════════════════════════════════════

        $s01 = $this->seed($mid, $sbn->id, 'BNT-SBN-01', 'Senarai Bantuan (Asnaf)',
            'pages/pengurusan-bantuan/senarai-bantuan/asnaf/senarai/index.vue', 10, $allIncPemohon,
            [
                ['screen_name' => 'Senarai Bantuan', 'label' => 'Tab: Permohonan',    'type' => 'Table', 'condition' => 'Bantuan records filtered by isPermohonan=1 (active applications by current user)'],
                ['screen_name' => 'Senarai Bantuan', 'label' => 'Tab: Lulus',         'type' => 'Table', 'condition' => 'Bantuan records with kodTindakanProses=600 (approved)'],
                ['screen_name' => 'Senarai Bantuan', 'label' => 'Tab: Ditolak',       'type' => 'Table', 'condition' => 'Bantuan records with kodTindakanProses=601 (rejected)'],
                ['screen_name' => 'Senarai Bantuan', 'label' => 'Kolum Jadual',       'type' => 'Table', 'condition' => 'Columns: No. Rujukan, Jenis Bantuan, Tarikh Permohonan, Status, Tindakan (Kemaskini/Padam)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/agihan/senarai?filterByCreatedUser=true&isPermohonan=1', 'description' => 'Fetch asnaf bantuan list (Permohonan tab)'],
                ['method' => 'GET', 'endpoint' => '/bantuan/agihan/senarai?filterByCreatedUser=true&kodTindakanProses=600', 'description' => 'Fetch asnaf bantuan list (Lulus tab)'],
            ]);

        $s02 = $this->seed($mid, $sbn->id, 'BNT-SBN-02', 'Kemaskini Bantuan (Asnaf)',
            'pages/pengurusan-bantuan/senarai-bantuan/asnaf/kemaskini/[id]/index.vue', 20, $allIncPemohon,
            [
                ['screen_name' => 'Kemaskini Bantuan', 'label' => 'Maklumat Bantuan',   'type' => 'Text',    'condition' => 'Editable form — bantuan details for the selected asnaf application'],
                ['screen_name' => 'Kemaskini Bantuan', 'label' => 'Butang Simpan',      'type' => 'Button',  'condition' => 'Submit kemaskini'],
                ['screen_name' => 'Kemaskini Bantuan', 'label' => 'Butang Kembali',     'type' => 'Button',  'condition' => 'Return to senarai bantuan'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/agihan/{id}',              'description' => 'Fetch bantuan detail for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/agihan/{id}',              'description' => 'Update bantuan record'],
            ]);

        $s03 = $this->seed($mid, $sbn->id, 'BNT-SBN-03', 'Permohonan Pembatalan Bantuan (Asnaf)',
            'pages/pengurusan-bantuan/senarai-bantuan/asnaf/padam/[id]/index.vue', 30, $allIncPemohon,
            [
                ['screen_name' => 'Permohonan Pembatalan Bantuan', 'label' => 'Maklumat Bantuan Yang Dibatalkan', 'type' => 'Display', 'condition' => 'Read-only display of the bantuan record to be cancelled'],
                ['screen_name' => 'Permohonan Pembatalan Bantuan', 'label' => 'Sebab Pembatalan',                'type' => 'Text',    'condition' => 'Textarea — mandatory reason for cancellation'],
                ['screen_name' => 'Permohonan Pembatalan Bantuan', 'label' => 'Butang Hantar Pembatalan',        'type' => 'Button',  'condition' => 'Submit cancellation request'],
                ['screen_name' => 'Permohonan Pembatalan Bantuan', 'label' => 'Butang Kembali',                  'type' => 'Button',  'condition' => 'Return to senarai without cancelling'],
            ],
            [
                ['method' => 'GET',    'endpoint' => '/bantuan/agihan/{id}',              'description' => 'Fetch bantuan detail for cancellation confirmation'],
                ['method' => 'DELETE', 'endpoint' => '/bantuan/agihan/{id}',              'description' => 'Submit bantuan cancellation request'],
            ]);

        $s04 = $this->seed($mid, $sbn->id, 'BNT-SBN-04', 'Senarai Bantuan (Organisasi)',
            'pages/pengurusan-bantuan/senarai-bantuan/organisasi/senarai/index.vue', 40, $allIncPemohon,
            [
                ['screen_name' => 'Senarai Bantuan Organisasi', 'label' => 'Tab: Permohonan', 'type' => 'Table', 'condition' => 'Organisasi bantuan active applications'],
                ['screen_name' => 'Senarai Bantuan Organisasi', 'label' => 'Tab: Lulus',      'type' => 'Table', 'condition' => 'Approved organisasi bantuan records'],
                ['screen_name' => 'Senarai Bantuan Organisasi', 'label' => 'Tab: Ditolak',    'type' => 'Table', 'condition' => 'Rejected organisasi bantuan records'],
                ['screen_name' => 'Senarai Bantuan Organisasi', 'label' => 'Kolum Jadual',    'type' => 'Table', 'condition' => 'Columns: No. Rujukan, Jenis Bantuan, Nama Organisasi, Status, Tindakan'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/agihan/senarai?filterByCreatedUser=true&isPermohonan=1&jenisPemohon=organisasi', 'description' => 'Fetch organisasi bantuan list'],
            ]);

        $s05 = $this->seed($mid, $sbn->id, 'BNT-SBN-05', 'Kemaskini Bantuan (Organisasi)',
            'pages/pengurusan-bantuan/senarai-bantuan/organisasi/kemaskini/[id]/index.vue', 50, $allIncPemohon,
            [
                ['screen_name' => 'Kemaskini Bantuan Organisasi', 'label' => 'Maklumat Bantuan',  'type' => 'Text',   'condition' => 'Editable form for organisasi bantuan record'],
                ['screen_name' => 'Kemaskini Bantuan Organisasi', 'label' => 'Butang Simpan',     'type' => 'Button', 'condition' => 'Submit kemaskini'],
                ['screen_name' => 'Kemaskini Bantuan Organisasi', 'label' => 'Butang Kembali',    'type' => 'Button', 'condition' => 'Return to senarai'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/agihan/{id}', 'description' => 'Fetch organisasi bantuan for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/agihan/{id}', 'description' => 'Update organisasi bantuan record'],
            ]);

        $s06 = $this->seed($mid, $sbn->id, 'BNT-SBN-06', 'Permohonan Pembatalan Bantuan (Organisasi)',
            'pages/pengurusan-bantuan/senarai-bantuan/organisasi/padam/[id]/index.vue', 60, $allIncPemohon,
            [
                ['screen_name' => 'Permohonan Pembatalan Bantuan Organisasi', 'label' => 'Maklumat Bantuan Yang Dibatalkan', 'type' => 'Display', 'condition' => 'Read-only display of the organisasi bantuan to cancel'],
                ['screen_name' => 'Permohonan Pembatalan Bantuan Organisasi', 'label' => 'Sebab Pembatalan',                'type' => 'Text',    'condition' => 'Mandatory cancellation reason'],
                ['screen_name' => 'Permohonan Pembatalan Bantuan Organisasi', 'label' => 'Butang Hantar',                   'type' => 'Button',  'condition' => 'Submit cancellation request'],
            ],
            [
                ['method' => 'GET',    'endpoint' => '/bantuan/agihan/{id}', 'description' => 'Fetch organisasi bantuan for cancellation'],
                ['method' => 'DELETE', 'endpoint' => '/bantuan/agihan/{id}', 'description' => 'Submit cancellation'],
            ]);

        $s07 = $this->seed($mid, $sbn->id, 'BNT-SBN-07', 'Senarai Bantuan (Pegawai)',
            'pages/pengurusan-bantuan/senarai-bantuan/pegawai/senarai/index.vue', 70, $staff,
            [
                ['screen_name' => 'Senarai Bantuan Pegawai', 'label' => 'Tab: Permohonan', 'type' => 'Table', 'condition' => 'Bantuan records assigned/managed by the current officer'],
                ['screen_name' => 'Senarai Bantuan Pegawai', 'label' => 'Tab: Lulus',      'type' => 'Table', 'condition' => 'Approved bantuan records managed by officer'],
                ['screen_name' => 'Senarai Bantuan Pegawai', 'label' => 'Tab: Ditolak',    'type' => 'Table', 'condition' => 'Rejected bantuan records managed by officer'],
                ['screen_name' => 'Senarai Bantuan Pegawai', 'label' => 'Kolum Jadual',    'type' => 'Table', 'condition' => 'Columns: No. Rujukan, Nama Pemohon, Jenis Bantuan, Status, Tindakan'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/agihan/senarai?filterByCreatedUser=true&isPermohonan=1', 'description' => 'Fetch pegawai-managed bantuan list'],
            ]);

        $s08 = $this->seed($mid, $sbn->id, 'BNT-SBN-08', 'Kemaskini Bantuan (Pegawai)',
            'pages/pengurusan-bantuan/senarai-bantuan/pegawai/kemaskini/[id]/index.vue', 80, $staff,
            [
                ['screen_name' => 'Kemaskini Bantuan Pegawai', 'label' => 'Maklumat Bantuan',  'type' => 'Text',   'condition' => 'Editable bantuan form for officer to update application details'],
                ['screen_name' => 'Kemaskini Bantuan Pegawai', 'label' => 'Butang Simpan',     'type' => 'Button', 'condition' => 'Submit kemaskini'],
                ['screen_name' => 'Kemaskini Bantuan Pegawai', 'label' => 'Butang Kembali',    'type' => 'Button', 'condition' => 'Return to senarai'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/agihan/{id}', 'description' => 'Fetch bantuan for pegawai editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/agihan/{id}', 'description' => 'Update bantuan record by pegawai'],
            ]);

        $s09 = $this->seed($mid, $sbn->id, 'BNT-SBN-09', 'Permohonan Pembatalan Bantuan (Pegawai)',
            'pages/pengurusan-bantuan/senarai-bantuan/pegawai/padam/[id]/index.vue', 90, $staff,
            [
                ['screen_name' => 'Permohonan Pembatalan Bantuan Pegawai', 'label' => 'Maklumat Bantuan Yang Dibatalkan', 'type' => 'Display', 'condition' => 'Read-only display of bantuan to be cancelled by officer'],
                ['screen_name' => 'Permohonan Pembatalan Bantuan Pegawai', 'label' => 'Sebab Pembatalan',                'type' => 'Text',    'condition' => 'Mandatory cancellation reason'],
                ['screen_name' => 'Permohonan Pembatalan Bantuan Pegawai', 'label' => 'Butang Hantar',                   'type' => 'Button',  'condition' => 'Submit cancellation request'],
            ],
            [
                ['method' => 'GET',    'endpoint' => '/bantuan/agihan/{id}', 'description' => 'Fetch bantuan for cancellation by pegawai'],
                ['method' => 'DELETE', 'endpoint' => '/bantuan/agihan/{id}', 'description' => 'Submit pegawai cancellation request'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // TUNTUTAN (TUT)  — 12 pages
        // ══════════════════════════════════════════════════════════════════

        $t01 = $this->seed($mid, $tut->id, 'BNT-TUT-01', 'Senarai Tuntutan',
            'pages/pengurusan-bantuan/tuntutan/senarai/index.vue', 10, $all,
            [
                ['screen_name' => 'Senarai Tuntutan', 'label' => 'Tab: Draf',          'type' => 'Table', 'condition' => 'Tuntutan in draft status'],
                ['screen_name' => 'Senarai Tuntutan', 'label' => 'Tab: Dalam Proses',  'type' => 'Table', 'condition' => 'Tuntutan currently in process (Pending)'],
                ['screen_name' => 'Senarai Tuntutan', 'label' => 'Tab: Pindaan',       'type' => 'Table', 'condition' => 'Tuntutan requiring amendment (Amend)'],
                ['screen_name' => 'Senarai Tuntutan', 'label' => 'Tab: Lulus',         'type' => 'Table', 'condition' => 'Approved tuntutan (Approved)'],
                ['screen_name' => 'Senarai Tuntutan', 'label' => 'Tab: Tidak Lulus',   'type' => 'Table', 'condition' => 'Rejected tuntutan (Rejected)'],
                ['screen_name' => 'Senarai Tuntutan', 'label' => 'Kolum Jadual',       'type' => 'Table', 'condition' => 'Columns: No. GL, No. Tuntutan, Nama Pemohon/Institusi, Jenis Bantuan, Tarikh, Amaun (RM), Status, Tindakan'],
                ['screen_name' => 'Senarai Tuntutan', 'label' => 'Stat Kad',           'type' => 'Display', 'condition' => 'Statistics cards showing count per status (colour-coded)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/tuntutan/senarai',         'description' => 'Fetch tuntutan list with status filter (Draf/Pending/Amend/Approved/Rejected)'],
            ]);

        $t02 = $this->seed($mid, $tut->id, 'BNT-TUT-02', 'Senarai Tuntutan — Semakan',
            'pages/pengurusan-bantuan/tuntutan/senarai/semakan/index.vue', 20, $staff,
            [
                ['screen_name' => 'Senarai Tuntutan — Semakan', 'label' => 'Tab: Menunggu Semakan', 'type' => 'Table', 'condition' => 'Tuntutan awaiting semakan by officer'],
                ['screen_name' => 'Senarai Tuntutan — Semakan', 'label' => 'Tab: Selesai',          'type' => 'Table', 'condition' => 'Completed semakan records'],
                ['screen_name' => 'Senarai Tuntutan — Semakan', 'label' => 'Butang Amaran',         'type' => 'Display', 'condition' => 'Alert modal for overdue or flagged tuntutan'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/tuntutan/senarai/semakan', 'description' => 'Fetch tuntutan semakan list for officer review'],
            ]);

        $t03 = $this->seed($mid, $tut->id, 'BNT-TUT-03', 'Butiran Semakan Tuntutan',
            'pages/pengurusan-bantuan/tuntutan/senarai/semakan/[id]/index.vue', 30, $staff,
            [
                ['screen_name' => 'Butiran Semakan Tuntutan', 'label' => 'Maklumat Permohonan Tuntutan',  'type' => 'Display', 'condition' => 'Full tuntutan details for review — Pemohon, Jenis Bantuan, Amaun, Dokumen sokongan'],
                ['screen_name' => 'Butiran Semakan Tuntutan', 'label' => 'Butang Lulus / Tolak',         'type' => 'Button',  'condition' => 'Approve or reject with confirmation dialog (Adakah anda pasti?)'],
                ['screen_name' => 'Butiran Semakan Tuntutan', 'label' => 'Butang Kembali',               'type' => 'Button',  'condition' => 'Return to senarai semakan'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/tuntutan/{id}',                 'description' => 'Fetch tuntutan detail for semakan'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tuntutan/{id}/semakan',         'description' => 'Submit semakan decision (lulus/tolak)'],
            ]);

        $t04 = $this->seed($mid, $tut->id, 'BNT-TUT-04', 'Senarai Tuntutan — Kelulusan',
            'pages/pengurusan-bantuan/tuntutan/senarai/kelulusan/index.vue', 40, [$pelulus->id],
            [
                ['screen_name' => 'Senarai Tuntutan — Kelulusan', 'label' => 'Tab: Menunggu Kelulusan', 'type' => 'Table', 'condition' => 'Tuntutan awaiting approval by pelulus'],
                ['screen_name' => 'Senarai Tuntutan — Kelulusan', 'label' => 'Tab: Selesai',            'type' => 'Table', 'condition' => 'Completed kelulusan records'],
                ['screen_name' => 'Senarai Tuntutan — Kelulusan', 'label' => 'Butang Amaran',           'type' => 'Display', 'condition' => 'Alert modal for overdue tuntutan'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/tuntutan/senarai/kelulusan', 'description' => 'Fetch tuntutan kelulusan list for pelulus'],
            ]);

        $t05 = $this->seed($mid, $tut->id, 'BNT-TUT-05', 'Butiran Kelulusan Tuntutan',
            'pages/pengurusan-bantuan/tuntutan/senarai/kelulusan/[id]/index.vue', 50, [$pelulus->id],
            [
                ['screen_name' => 'Butiran Kelulusan Tuntutan', 'label' => 'Maklumat Permohonan Tuntutan',  'type' => 'Display', 'condition' => 'Full tuntutan details with semakan summary for kelulusan decision'],
                ['screen_name' => 'Butiran Kelulusan Tuntutan', 'label' => 'Butang Lulus / Tolak',         'type' => 'Button',  'condition' => 'Approve or reject with confirmation dialog'],
                ['screen_name' => 'Butiran Kelulusan Tuntutan', 'label' => 'Butang Kembali',               'type' => 'Button',  'condition' => 'Return to senarai kelulusan'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/tuntutan/{id}',                 'description' => 'Fetch tuntutan detail for kelulusan'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tuntutan/{id}/kelulusan',       'description' => 'Submit kelulusan decision (lulus/tolak)'],
            ]);

        $t06 = $this->seed($mid, $tut->id, 'BNT-TUT-06', 'Permohonan Tuntutan Bantuan',
            'pages/pengurusan-bantuan/tuntutan/permohonan/index.vue', 60, $all,
            [
                ['screen_name' => 'Permohonan Tuntutan Bantuan', 'label' => 'Maklumat Pemohon',     'type' => 'Display', 'condition' => 'Pemohon info panel — Nama, No. GL, Jenis Bantuan, Bulan/Tahun, Amaun, Baki'],
                ['screen_name' => 'Permohonan Tuntutan Bantuan', 'label' => 'Jadual Senarai GL',    'type' => 'Table',   'condition' => 'Table of GL records — columns: No. GL, Jenis Bantuan, Bulan/Tahun, Amaun (RM), Baki (RM), Tindakan'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/tuntutan/permohonan',            'description' => 'Fetch permohonan tuntutan list with GL details'],
            ]);

        $t07 = $this->seed($mid, $tut->id, 'BNT-TUT-07', 'Permohonan Tuntutan Baharu',
            'pages/pengurusan-bantuan/tuntutan/permohonan/baru/index.vue', 70, $all,
            [
                ['screen_name' => 'Permohonan Tuntutan Baharu', 'label' => 'Borang Permohonan Tuntutan',  'type' => 'Text',   'condition' => 'Form for new tuntutan application — Jenis Bantuan, No. GL, Amaun, period, supporting docs'],
                ['screen_name' => 'Permohonan Tuntutan Baharu', 'label' => 'Butang Hantar',               'type' => 'Button', 'condition' => 'Submit new tuntutan permohonan'],
            ],
            [
                ['method' => 'POST', 'endpoint' => '/bantuan/tuntutan/permohonan',           'description' => 'Submit new tuntutan bantuan permohonan'],
                ['method' => 'GET',  'endpoint' => '/bantuan/agihan/senarai',                'description' => 'Fetch GL list for tuntutan application'],
            ]);

        $t08 = $this->seed($mid, $tut->id, 'BNT-TUT-08', 'Borang Permohonan Tuntutan (Per GL)',
            'pages/pengurusan-bantuan/tuntutan/permohonan/baru/[id].vue', 80, $all,
            [
                ['screen_name' => 'Borang Permohonan Tuntutan (Per GL)', 'label' => 'Maklumat GL',              'type' => 'Display', 'condition' => 'Auto-filled GL details — No. GL, Jenis Bantuan, Amaun diluluskan, Baki'],
                ['screen_name' => 'Borang Permohonan Tuntutan (Per GL)', 'label' => 'Borang Tuntutan',         'type' => 'Text',    'condition' => 'Tuntutan amount, period, payee details; confirmation dialog before submit'],
                ['screen_name' => 'Borang Permohonan Tuntutan (Per GL)', 'label' => 'Butang Hantar',           'type' => 'Button',  'condition' => 'Submit tuntutan for the selected GL'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/agihan/{id}',                   'description' => 'Fetch GL bantuan detail'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tuntutan/permohonan',            'description' => 'Submit tuntutan for selected GL'],
            ]);

        $t09 = $this->seed($mid, $tut->id, 'BNT-TUT-09', 'Permohonan Tuntutan Bulk (Asnaf)',
            'pages/pengurusan-bantuan/tuntutan/permohonan/baru/bulk/[id].vue', 90, $staff,
            [
                ['screen_name' => 'Permohonan Tuntutan Bulk', 'label' => 'Maklumat GL',                'type' => 'Display', 'condition' => 'GL details for bulk tuntutan — Jenis Bantuan, period'],
                ['screen_name' => 'Permohonan Tuntutan Bulk', 'label' => 'Jadual Asnaf Tuntutan',     'type' => 'Table',   'condition' => 'Table of asnaf recipients to include in bulk tuntutan with amount per recipient'],
                ['screen_name' => 'Permohonan Tuntutan Bulk', 'label' => 'Butang Hantar',             'type' => 'Button',  'condition' => 'Submit bulk tuntutan permohonan for all selected asnaf'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/agihan/{id}/asnaf',            'description' => 'Fetch asnaf list for bulk tuntutan'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tuntutan/permohonan/bulk',     'description' => 'Submit bulk tuntutan for multiple asnaf'],
            ]);

        $t10 = $this->seed($mid, $tut->id, 'BNT-TUT-10', 'Permohonan Tuntutan Baharu (dari GL)',
            'pages/pengurusan-bantuan/tuntutan/permohonan/[id]/baru.vue', 100, $all,
            [
                ['screen_name' => 'Permohonan Tuntutan Baharu (dari GL)', 'label' => 'Maklumat GL',          'type' => 'Display', 'condition' => 'GL record details pre-filled from parent permohonan ID'],
                ['screen_name' => 'Permohonan Tuntutan Baharu (dari GL)', 'label' => 'Borang Tuntutan',     'type' => 'Text',    'condition' => 'New tuntutan form linked to specific permohonan; confirmation before submit'],
                ['screen_name' => 'Permohonan Tuntutan Baharu (dari GL)', 'label' => 'Butang Hantar',       'type' => 'Button',  'condition' => 'Submit tuntutan'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/tuntutan/permohonan/{id}',     'description' => 'Fetch parent permohonan data for new tuntutan'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tuntutan/permohonan/{id}/baru','description' => 'Submit new tuntutan linked to existing permohonan'],
            ]);

        $t11 = $this->seed($mid, $tut->id, 'BNT-TUT-11', 'Maklumat Tuntutan',
            'pages/pengurusan-bantuan/tuntutan/[id]/index.vue', 110, $all,
            [
                ['screen_name' => 'Maklumat Tuntutan', 'label' => 'Butiran Tuntutan',    'type' => 'Display', 'condition' => 'Full read-only display of tuntutan — Pemohon, Amaun, Status, Tarikh, Dokumen sokongan'],
                ['screen_name' => 'Maklumat Tuntutan', 'label' => 'Butang Kemaskini',    'type' => 'Button',  'condition' => 'Navigate to edit tuntutan (only visible for Draf/Amend status)'],
                ['screen_name' => 'Maklumat Tuntutan', 'label' => 'Butang Kembali',      'type' => 'Button',  'condition' => 'Return to senarai tuntutan'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/tuntutan/{id}',              'description' => 'Fetch full tuntutan detail'],
            ]);

        $t12 = $this->seed($mid, $tut->id, 'BNT-TUT-12', 'Kemaskini Tuntutan',
            'pages/pengurusan-bantuan/tuntutan/[id]/edit.vue', 120, $all,
            [
                ['screen_name' => 'Kemaskini Tuntutan', 'label' => 'Borang Kemaskini Tuntutan',  'type' => 'Text',    'condition' => 'Editable tuntutan form — Amaun, period, payee details; confirmation before submit'],
                ['screen_name' => 'Kemaskini Tuntutan', 'label' => 'Butang Simpan',              'type' => 'Button',  'condition' => 'Submit tuntutan changes'],
                ['screen_name' => 'Kemaskini Tuntutan', 'label' => 'Butang Kembali',             'type' => 'Button',  'condition' => 'Return to maklumat tuntutan'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/bantuan/tuntutan/{id}',            'description' => 'Fetch tuntutan for editing'],
                ['method' => 'PATCH', 'endpoint' => '/bantuan/tuntutan/{id}',            'description' => 'Update tuntutan record'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // PAGE LINKS
        // ══════════════════════════════════════════════════════════════════

        $links = [
            // Mohon Bantuan workflow
            [$m01->id, $m02->id],
            [$m01->id, $m03->id],
            [$m04->id, $m05->id],
            [$m04->id, $m06->id],
            [$m04->id, $m07->id],
            [$m04->id, $m08->id],
            [$m04->id, $m09->id],
            [$m04->id, $m10->id],
            [$m10->id, $m11->id],
            // Senarai Bantuan
            [$s01->id, $s02->id],
            [$s01->id, $s03->id],
            [$s04->id, $s05->id],
            [$s04->id, $s06->id],
            [$s07->id, $s08->id],
            [$s07->id, $s09->id],
            // Tuntutan
            [$t01->id, $t11->id],
            [$t01->id, $t12->id],
            [$t02->id, $t03->id],
            [$t04->id, $t05->id],
            [$t06->id, $t07->id],
            [$t06->id, $t08->id],
            [$t06->id, $t09->id],
            [$t06->id, $t10->id],
            [$t11->id, $t12->id],
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
