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

class RtmfPengurusanTunaiPhase1Seeder extends Seeder
{
    public function run(): void
    {
        $project = RtmfProject::firstOrFail();

        $module = RtmfModule::firstOrCreate(
            ['code' => 'TUN'],
            ['name' => 'Pengurusan Tunai', 'sort_order' => 40, 'project_id' => $project->id],
        );
        if (! $module->project_id) {
            $module->update(['project_id' => $project->id]);
        }

        $opr = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'OPR'],
            ['name' => 'Opening Operasi', 'sort_order' => 10],
        );
        $opt = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'OPT'],
            ['name' => 'Opening Operasi Tabung', 'sort_order' => 20],
        );
        $cls = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'CLS'],
            ['name' => 'Closing Operasi', 'sort_order' => 30],
        );
        $clt = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'CLT'],
            ['name' => 'Closing Operasi Tabung', 'sort_order' => 40],
        );

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        $staff = [$pegawai->id, $penyelia->id];
        $all   = [$pegawai->id, $penyelia->id, $pelulus->id];

        $mid = $module->id;

        // ══════════════════════════════════════════════════════════════════
        // OPENING OPERASI (OPR) — 3 pages
        // ══════════════════════════════════════════════════════════════════

        $opr01 = $this->seed($mid, $opr->id, 'TUN-OPR-01', 'Senarai Penerimaan Tunai',
            'pages/pengurusan-tunai/opening-operasi/senarai-penerimaan-tunai.vue', 10, $staff,
            [
                ['screen_name' => 'Senarai Penerimaan Tunai', 'label' => 'Filter Tarikh',         'type' => 'Date',   'condition' => 'Filter by receipt date'],
                ['screen_name' => 'Senarai Penerimaan Tunai', 'label' => 'Filter Tabung',          'type' => 'Select', 'condition' => 'Filter by tabung utama or tabung PIC'],
                ['screen_name' => 'Senarai Penerimaan Tunai', 'label' => 'Jadual Penerimaan',      'type' => 'Table',  'condition' => 'Columns: Rujukan, Tabung, Jumlah (RM), Tarikh, Status'],
                ['screen_name' => 'Senarai Penerimaan Tunai', 'label' => 'Butang Opening Utama',   'type' => 'Button', 'condition' => 'Navigate to opening-tabung-utama.vue'],
                ['screen_name' => 'Senarai Penerimaan Tunai', 'label' => 'Butang Opening PIC',     'type' => 'Button', 'condition' => 'Navigate to opening-tabung-pic.vue'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/opening-operasi/penerimaan', 'description' => 'Fetch list of daily cash receipts for all tabung'],
            ]);

        $opr02 = $this->seed($mid, $opr->id, 'TUN-OPR-02', 'Opening Tabung Utama',
            'pages/pengurusan-tunai/opening-operasi/opening-tabung-utama.vue', 20, $staff,
            [
                ['screen_name' => 'Opening Tabung Utama', 'label' => 'Tabung Utama',              'type' => 'Select', 'condition' => 'Select tabung utama to open for the day', 'mandatory' => true],
                ['screen_name' => 'Opening Tabung Utama', 'label' => 'Baki Awal (RM)',            'type' => 'Display', 'condition' => 'Auto-filled: balance carried over from previous closing'],
                ['screen_name' => 'Opening Tabung Utama', 'label' => 'Jumlah Penerimaan Baru',    'type' => 'Number', 'condition' => 'New cash received today (top-up if applicable)'],
                ['screen_name' => 'Opening Tabung Utama', 'label' => 'Jumlah Tunai Tersedia',     'type' => 'Display', 'condition' => 'Auto-calculated: Baki Awal + Penerimaan Baru'],
                ['screen_name' => 'Opening Tabung Utama', 'label' => 'Catatan',                   'type' => 'Textarea', 'condition' => 'Optional remarks for today opening'],
                ['screen_name' => 'Opening Tabung Utama', 'label' => 'Butang Buka Operasi',       'type' => 'Button', 'condition' => 'Confirm opening of tabung utama for today'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/tunai/tabung-utama?status=aktif',      'description' => 'Fetch active tabung utama list for selection'],
                ['method' => 'POST', 'endpoint' => '/tunai/opening-operasi/tabung-utama',   'description' => 'Open tabung utama for daily operation'],
            ]);

        $opr03 = $this->seed($mid, $opr->id, 'TUN-OPR-03', 'Opening Tabung PIC',
            'pages/pengurusan-tunai/opening-operasi/opening-tabung-pic.vue', 30, $staff,
            [
                ['screen_name' => 'Opening Tabung PIC', 'label' => 'Tabung PIC',                  'type' => 'Select', 'condition' => 'Select PIC tabung to open for the day', 'mandatory' => true],
                ['screen_name' => 'Opening Tabung PIC', 'label' => 'Pegawai PIC',                 'type' => 'Display', 'condition' => 'Auto-filled: officer assigned to this tabung PIC'],
                ['screen_name' => 'Opening Tabung PIC', 'label' => 'Jumlah Diperuntukkan (RM)',   'type' => 'Number', 'condition' => 'Cash amount drawn from tabung utama for PIC officer today', 'mandatory' => true],
                ['screen_name' => 'Opening Tabung PIC', 'label' => 'Baki Tabung Utama Semasa',    'type' => 'Display', 'condition' => 'Real-time balance of parent tabung utama'],
                ['screen_name' => 'Opening Tabung PIC', 'label' => 'Butang Buka Operasi PIC',     'type' => 'Button', 'condition' => 'Confirm opening of PIC tabung with allocated amount'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/tunai/tabung-pic?status=aktif',         'description' => 'Fetch active PIC tabung for selection'],
                ['method' => 'POST', 'endpoint' => '/tunai/opening-operasi/tabung-pic',       'description' => 'Open PIC tabung with daily allocation from tabung utama'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // OPENING OPERASI TABUNG (OPT) — 6 pages
        // ══════════════════════════════════════════════════════════════════

        $opt01 = $this->seed($mid, $opt->id, 'TUN-OPT-01', 'Senarai Tabung Opening',
            'pages/pengurusan-tunai/opening-operasi-tabung/senarai-tabung-opening/index.vue', 10, $all,
            [
                ['screen_name' => 'Senarai Tabung Opening', 'label' => 'Filter Tarikh',           'type' => 'Date',   'condition' => 'Filter by opening date'],
                ['screen_name' => 'Senarai Tabung Opening', 'label' => 'Filter Jenis Tabung',     'type' => 'Select', 'condition' => 'Tabung Utama / Tabung PIC'],
                ['screen_name' => 'Senarai Tabung Opening', 'label' => 'Jadual Tabung Opening',   'type' => 'Table',  'condition' => 'Columns: Tabung, Pegawai, Baki Awal, Penerimaan, Jumlah, Status Opening'],
                ['screen_name' => 'Senarai Tabung Opening', 'label' => 'Butang Lihat',            'type' => 'Button', 'condition' => 'Navigate to opening details or penerimaan tunai'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/opening-operasi-tabung', 'description' => 'Fetch list of tabung opening records'],
            ]);

        $opt02 = $this->seed($mid, $opt->id, 'TUN-OPT-02', 'Status Opening Tabung Utama',
            'pages/pengurusan-tunai/opening-operasi-tabung/status-opening-tabung-utama/index.vue', 20, $all,
            [
                ['screen_name' => 'Status Opening Tabung Utama', 'label' => 'Maklumat Status',    'type' => 'Display', 'condition' => 'Read-only: Tabung Utama name, opening status, baki awal, baki semasa'],
                ['screen_name' => 'Status Opening Tabung Utama', 'label' => 'Senarai Tabung PIC', 'type' => 'Table',   'condition' => 'PIC sub-tabung linked to this utama: Pegawai, Jumlah Diperuntuk, Status'],
                ['screen_name' => 'Status Opening Tabung Utama', 'label' => 'Butang Buka Operasi', 'type' => 'Button', 'condition' => 'Initiate opening of this tabung utama for today (if not yet opened)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/opening-operasi-tabung/status-utama', 'description' => 'Fetch tabung utama opening status for today'],
            ]);

        $opt03 = $this->seed($mid, $opt->id, 'TUN-OPT-03', 'Opening Tabung Utama (Operasi)',
            'pages/pengurusan-tunai/opening-operasi-tabung/opening-tabung-utama/index.vue', 30, $staff,
            [
                ['screen_name' => 'Opening Tabung Utama', 'label' => 'Tabung Utama',              'type' => 'Select', 'condition' => 'Select tabung utama to open', 'mandatory' => true],
                ['screen_name' => 'Opening Tabung Utama', 'label' => 'Baki Semalam (RM)',         'type' => 'Display', 'condition' => 'Previous day closing balance (auto-filled)'],
                ['screen_name' => 'Opening Tabung Utama', 'label' => 'Penerimaan Baru (RM)',      'type' => 'Number', 'condition' => 'New cash received today (added to balance)'],
                ['screen_name' => 'Opening Tabung Utama', 'label' => 'Jumlah Baki Operasi (RM)', 'type' => 'Display', 'condition' => 'Total available for today operations'],
                ['screen_name' => 'Opening Tabung Utama', 'label' => 'Butang Sahkan Opening',    'type' => 'Button', 'condition' => 'Confirm daily opening'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/tunai/tabung-utama/{id}/baki',                    'description' => 'Fetch previous day closing balance for tabung utama'],
                ['method' => 'POST', 'endpoint' => '/tunai/opening-operasi-tabung/opening-utama',       'description' => 'Create opening record for tabung utama'],
            ]);

        $opt04 = $this->seed($mid, $opt->id, 'TUN-OPT-04', 'Senarai Opening Tabung',
            'pages/pengurusan-tunai/opening-operasi-tabung/senarai-opening-tabung/index.vue', 40, $all,
            [
                ['screen_name' => 'Senarai Opening Tabung', 'label' => 'Filter Tarikh',           'type' => 'Date',   'condition' => 'Date range filter'],
                ['screen_name' => 'Senarai Opening Tabung', 'label' => 'Jadual Opening',           'type' => 'Table',  'condition' => 'Columns: Tabung, Tarikh Opening, Baki Awal, Penerimaan, Status'],
                ['screen_name' => 'Senarai Opening Tabung', 'label' => 'Butang Lihat Penerimaan', 'type' => 'Button', 'condition' => 'Navigate to senarai-penerimaan-tunai-tabung'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/opening-operasi-tabung/senarai', 'description' => 'Fetch full list of tabung opening history'],
            ]);

        $opt05 = $this->seed($mid, $opt->id, 'TUN-OPT-05', 'Senarai Penerimaan Tunai Tabung',
            'pages/pengurusan-tunai/opening-operasi-tabung/senarai-penerimaan-tunai-tabung/index.vue', 50, $all,
            [
                ['screen_name' => 'Senarai Penerimaan Tunai Tabung', 'label' => 'Filter Tabung',  'type' => 'Select', 'condition' => 'Filter by specific tabung'],
                ['screen_name' => 'Senarai Penerimaan Tunai Tabung', 'label' => 'Filter Tarikh',  'type' => 'Date',   'condition' => 'Date range for receipts'],
                ['screen_name' => 'Senarai Penerimaan Tunai Tabung', 'label' => 'Jadual Penerimaan', 'type' => 'Table', 'condition' => 'Columns: Rujukan, Tabung, Jumlah (RM), Tarikh, Disahkan'],
                ['screen_name' => 'Senarai Penerimaan Tunai Tabung', 'label' => 'Butang Lihat Butiran', 'type' => 'Button', 'condition' => 'Navigate to butiran-penerimaan-tunai-tabung'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/opening-operasi-tabung/penerimaan', 'description' => 'Fetch cash receipt records for all tabung'],
            ]);

        $opt06 = $this->seed($mid, $opt->id, 'TUN-OPT-06', 'Butiran Penerimaan Tunai Tabung',
            'pages/pengurusan-tunai/opening-operasi-tabung/butiran-penerimaan-tunai-tabung/index.vue', 60, $all,
            [
                ['screen_name' => 'Butiran Penerimaan Tunai', 'label' => 'Rujukan Penerimaan',    'type' => 'Display', 'condition' => 'Read-only receipt reference number'],
                ['screen_name' => 'Butiran Penerimaan Tunai', 'label' => 'Maklumat Tabung',       'type' => 'Display', 'condition' => 'Tabung name, officer, date'],
                ['screen_name' => 'Butiran Penerimaan Tunai', 'label' => 'Jumlah Diterima (RM)',  'type' => 'Display', 'condition' => 'Amount received'],
                ['screen_name' => 'Butiran Penerimaan Tunai', 'label' => 'Status Pengesahan',     'type' => 'Display', 'condition' => 'Confirmed / Pending confirmation status'],
                ['screen_name' => 'Butiran Penerimaan Tunai', 'label' => 'Audit Trail',           'type' => 'Display', 'condition' => 'Receipt history and verification chain'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/opening-operasi-tabung/penerimaan/{id}', 'description' => 'Fetch detailed receipt record for a specific tabung opening'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // CLOSING OPERASI (CLS) — 4 pages
        // ══════════════════════════════════════════════════════════════════

        $cls01 = $this->seed($mid, $cls->id, 'TUN-CLS-01', 'Senarai Pengesahan Tunai',
            'pages/pengurusan-tunai/closing-operasi/senarai-pengesahan-tunai.vue', 10, $staff,
            [
                ['screen_name' => 'Senarai Pengesahan Tunai', 'label' => 'Filter Tarikh',         'type' => 'Date',   'condition' => 'Date range for verification records'],
                ['screen_name' => 'Senarai Pengesahan Tunai', 'label' => 'Jadual Pengesahan',      'type' => 'Table',  'condition' => 'Columns: Tabung, Tarikh, Jumlah Agihan, Baki, Status Pengesahan'],
                ['screen_name' => 'Senarai Pengesahan Tunai', 'label' => 'Butang Closing Utama',  'type' => 'Button', 'condition' => 'Navigate to closing-tabung-utama.vue'],
                ['screen_name' => 'Senarai Pengesahan Tunai', 'label' => 'Butang Closing PIC',    'type' => 'Button', 'condition' => 'Navigate to closing-tabung-pic.vue'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/closing-operasi/pengesahan', 'description' => 'Fetch list of daily cash verification/closing records'],
            ]);

        $cls02 = $this->seed($mid, $cls->id, 'TUN-CLS-02', 'Closing Tabung Utama',
            'pages/pengurusan-tunai/closing-operasi/closing-tabung-utama.vue', 20, $staff,
            [
                ['screen_name' => 'Closing Tabung Utama', 'label' => 'Tabung Utama',              'type' => 'Select', 'condition' => 'Select tabung utama to close for the day', 'mandatory' => true],
                ['screen_name' => 'Closing Tabung Utama', 'label' => 'Jumlah Baki Fizikal (RM)',  'type' => 'Number', 'condition' => 'Physical cash count at end of day', 'mandatory' => true],
                ['screen_name' => 'Closing Tabung Utama', 'label' => 'Jumlah Sistem (RM)',        'type' => 'Display', 'condition' => 'System-computed remaining balance (auto-filled)'],
                ['screen_name' => 'Closing Tabung Utama', 'label' => 'Perbezaan (RM)',            'type' => 'Display', 'condition' => 'Auto-calculated difference: Fizikal − Sistem'],
                ['screen_name' => 'Closing Tabung Utama', 'label' => 'Sebab Perbezaan',           'type' => 'Textarea', 'condition' => 'Required if perbezaan ≠ 0'],
                ['screen_name' => 'Closing Tabung Utama', 'label' => 'Butang Tutup Operasi',      'type' => 'Button', 'condition' => 'Confirm end-of-day closing for tabung utama'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/tunai/tabung-utama/{id}/baki-semasa',          'description' => 'Fetch current system balance for selected tabung utama'],
                ['method' => 'POST', 'endpoint' => '/tunai/closing-operasi/tabung-utama',            'description' => 'Submit closing record with physical count for tabung utama'],
            ]);

        $cls03 = $this->seed($mid, $cls->id, 'TUN-CLS-03', 'Closing Tabung PIC',
            'pages/pengurusan-tunai/closing-operasi/closing-tabung-pic.vue', 30, $staff,
            [
                ['screen_name' => 'Closing Tabung PIC', 'label' => 'Tabung PIC',                  'type' => 'Select', 'condition' => 'Select PIC tabung to close', 'mandatory' => true],
                ['screen_name' => 'Closing Tabung PIC', 'label' => 'Pegawai PIC',                 'type' => 'Display', 'condition' => 'Auto-filled: officer name for selected tabung PIC'],
                ['screen_name' => 'Closing Tabung PIC', 'label' => 'Jumlah Baki Fizikal (RM)',    'type' => 'Number', 'condition' => 'Physical cash remaining with PIC officer', 'mandatory' => true],
                ['screen_name' => 'Closing Tabung PIC', 'label' => 'Jumlah Sistem (RM)',          'type' => 'Display', 'condition' => 'System balance for PIC tabung'],
                ['screen_name' => 'Closing Tabung PIC', 'label' => 'Perbezaan (RM)',              'type' => 'Display', 'condition' => 'Difference between physical and system'],
                ['screen_name' => 'Closing Tabung PIC', 'label' => 'Butang Tutup Operasi PIC',    'type' => 'Button', 'condition' => 'Submit PIC tabung closing'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/tunai/tabung-pic/{id}/baki-semasa',           'description' => 'Fetch current system balance for PIC tabung'],
                ['method' => 'POST', 'endpoint' => '/tunai/closing-operasi/tabung-pic',             'description' => 'Submit closing record for PIC tabung'],
            ]);

        $cls04 = $this->seed($mid, $cls->id, 'TUN-CLS-04', 'Sejarah Tabung PIC',
            'pages/pengurusan-tunai/closing-operasi/sejarah-tabung-pic.vue', 40, $all,
            [
                ['screen_name' => 'Sejarah Tabung PIC', 'label' => 'Filter Tabung PIC',           'type' => 'Select', 'condition' => 'Select specific PIC tabung to view history'],
                ['screen_name' => 'Sejarah Tabung PIC', 'label' => 'Filter Tarikh',               'type' => 'Date',   'condition' => 'Date range for history'],
                ['screen_name' => 'Sejarah Tabung PIC', 'label' => 'Jadual Sejarah',              'type' => 'Table',  'condition' => 'Columns: Tarikh, Pembukaan, Agihan, Pemulangan, Closing, Perbezaan'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/closing-operasi/sejarah-tabung-pic', 'description' => 'Fetch historical daily records for PIC tabung'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // CLOSING OPERASI TABUNG (CLT) — 4 pages
        // ══════════════════════════════════════════════════════════════════

        $clt01 = $this->seed($mid, $clt->id, 'TUN-CLT-01', 'Senarai Tabung Closing',
            'pages/pengurusan-tunai/closing-operasi-tabung/senarai-tabung-closing/index.vue', 10, $all,
            [
                ['screen_name' => 'Senarai Tabung Closing', 'label' => 'Filter Tarikh',           'type' => 'Date',   'condition' => 'Date range filter for closing records'],
                ['screen_name' => 'Senarai Tabung Closing', 'label' => 'Filter Jenis Tabung',     'type' => 'Select', 'condition' => 'Tabung Utama / Tabung PIC'],
                ['screen_name' => 'Senarai Tabung Closing', 'label' => 'Jadual Closing',          'type' => 'Table',  'condition' => 'Columns: Tabung, Tarikh, Baki Sistem, Baki Fizikal, Perbezaan, Status'],
                ['screen_name' => 'Senarai Tabung Closing', 'label' => 'Butang Pengesahan',       'type' => 'Button', 'condition' => 'Navigate to pengesahan-terimaan-tunai for selected record'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/closing-operasi-tabung', 'description' => 'Fetch list of tabung closing records'],
            ]);

        $clt02 = $this->seed($mid, $clt->id, 'TUN-CLT-02', 'Closing Tabung Utama (Operasi)',
            'pages/pengurusan-tunai/closing-operasi-tabung/closing-tabung-utama/index.vue', 20, $staff,
            [
                ['screen_name' => 'Closing Tabung Utama', 'label' => 'Tabung Utama',              'type' => 'Select', 'condition' => 'Select tabung utama to close', 'mandatory' => true],
                ['screen_name' => 'Closing Tabung Utama', 'label' => 'Ringkasan Hari Ini',        'type' => 'Display', 'condition' => 'Baki Awal, Total Agihan, Total Pemulangan, Baki Sistem'],
                ['screen_name' => 'Closing Tabung Utama', 'label' => 'Kiraan Tunai Fizikal (RM)', 'type' => 'Number', 'condition' => 'Actual physical cash count at end of day', 'mandatory' => true],
                ['screen_name' => 'Closing Tabung Utama', 'label' => 'Perbezaan (RM)',            'type' => 'Display', 'condition' => 'Auto-calculated variance'],
                ['screen_name' => 'Closing Tabung Utama', 'label' => 'Butang Hantar',             'type' => 'Button', 'condition' => 'Submit closing record for tabung utama'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/tunai/closing-operasi-tabung/tabung-utama/{id}', 'description' => 'Fetch today summary for tabung utama closing'],
                ['method' => 'POST', 'endpoint' => '/tunai/closing-operasi-tabung/tabung-utama',      'description' => 'Submit closing record for tabung utama'],
            ]);

        $clt03 = $this->seed($mid, $clt->id, 'TUN-CLT-03', 'Closing Tabung PIC (Operasi)',
            'pages/pengurusan-tunai/closing-operasi-tabung/closing-tabung-pic/index.vue', 30, $staff,
            [
                ['screen_name' => 'Closing Tabung PIC', 'label' => 'Tabung PIC',                  'type' => 'Select', 'condition' => 'Select PIC tabung to close', 'mandatory' => true],
                ['screen_name' => 'Closing Tabung PIC', 'label' => 'Pegawai PIC',                 'type' => 'Display', 'condition' => 'Auto-filled PIC officer name'],
                ['screen_name' => 'Closing Tabung PIC', 'label' => 'Ringkasan Hari Ini',          'type' => 'Display', 'condition' => 'Peruntukan, Agihan, Pemulangan, Baki Sistem'],
                ['screen_name' => 'Closing Tabung PIC', 'label' => 'Kiraan Fizikal (RM)',         'type' => 'Number', 'condition' => 'Physical cash count by PIC officer', 'mandatory' => true],
                ['screen_name' => 'Closing Tabung PIC', 'label' => 'Butang Hantar',               'type' => 'Button', 'condition' => 'Submit PIC closing record'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/tunai/closing-operasi-tabung/tabung-pic/{id}', 'description' => 'Fetch today summary for PIC tabung closing'],
                ['method' => 'POST', 'endpoint' => '/tunai/closing-operasi-tabung/tabung-pic',       'description' => 'Submit closing record for PIC tabung'],
            ]);

        $clt04 = $this->seed($mid, $clt->id, 'TUN-CLT-04', 'Pengesahan Terimaan Tunai',
            'pages/pengurusan-tunai/closing-operasi-tabung/pengesahan-terimaan-tunai/[idBukaOperasi].vue', 40, $all,
            [
                ['screen_name' => 'Pengesahan Terimaan Tunai', 'label' => 'Maklumat Operasi',     'type' => 'Display', 'condition' => 'Read-only: Tabung, Tarikh, Pegawai, Baki Pembukaan'],
                ['screen_name' => 'Pengesahan Terimaan Tunai', 'label' => 'Senarai Pengeluaran',  'type' => 'Table',   'condition' => 'All disbursements made during this operation period'],
                ['screen_name' => 'Pengesahan Terimaan Tunai', 'label' => 'Senarai Agihan',       'type' => 'Table',   'condition' => 'Cash distributions recorded against this operasi'],
                ['screen_name' => 'Pengesahan Terimaan Tunai', 'label' => 'Jumlah Closing',       'type' => 'Display', 'condition' => 'Final closing amount verified against physical count'],
                ['screen_name' => 'Pengesahan Terimaan Tunai', 'label' => 'Butang Sahkan',        'type' => 'Button',  'condition' => 'Confirm terimaan for this buka-operasi record'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/tunai/closing-operasi-tabung/pengesahan/{idBukaOperasi}',        'description' => 'Fetch full transaction detail for a buka-operasi record'],
                ['method' => 'PATCH', 'endpoint' => '/tunai/closing-operasi-tabung/pengesahan/{idBukaOperasi}/sahkan', 'description' => 'Confirm/verify cash terimaan for this operasi period'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // Page Links
        // ══════════════════════════════════════════════════════════════════

        $links = [
            ['TUN-OPR-01', 'TUN-OPR-02'],
            ['TUN-OPR-01', 'TUN-OPR-03'],
            ['TUN-OPT-01', 'TUN-OPT-02'],
            ['TUN-OPT-01', 'TUN-OPT-03'],
            ['TUN-OPT-01', 'TUN-OPT-04'],
            ['TUN-OPT-04', 'TUN-OPT-05'],
            ['TUN-OPT-05', 'TUN-OPT-06'],
            ['TUN-CLS-01', 'TUN-CLS-02'],
            ['TUN-CLS-01', 'TUN-CLS-03'],
            ['TUN-CLS-01', 'TUN-CLS-04'],
            ['TUN-CLT-01', 'TUN-CLT-02'],
            ['TUN-CLT-01', 'TUN-CLT-03'],
            ['TUN-CLT-01', 'TUN-CLT-04'],
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
