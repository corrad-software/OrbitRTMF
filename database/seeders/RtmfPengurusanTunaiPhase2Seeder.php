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

class RtmfPengurusanTunaiPhase2Seeder extends Seeder
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

        $pgt = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'PGT'],
            ['name' => 'Pengeluaran Tunai', 'sort_order' => 50],
        );
        $pmt = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'PMT'],
            ['name' => 'Pemulangan Tunai', 'sort_order' => 60],
        );
        $all_ = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'ALL'],
            ['name' => 'Agihan Lain-lain', 'sort_order' => 70],
        );
        $tni = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'TNI'],
            ['name' => 'Tambah Nilai', 'sort_order' => 80],
        );

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        $staff = [$pegawai->id, $penyelia->id];
        $all   = [$pegawai->id, $penyelia->id, $pelulus->id];

        $mid = $module->id;

        // ══════════════════════════════════════════════════════════════════
        // PENGELUARAN TUNAI (PGT) — 9 pages  (excluding _bck backup file)
        // ══════════════════════════════════════════════════════════════════

        $pgt01 = $this->seed($mid, $pgt->id, 'TUN-PGT-01', 'Senarai Pengeluaran Tunai',
            'pages/pengurusan-tunai/pengeluaran-tunai/index.vue', 10, $all,
            [
                ['screen_name' => 'Belum Diproses',  'label' => 'Jadual Belum Diproses',     'type' => 'Table',  'condition' => 'Columns: Rujukan, Nama Penerima, Jenis Bantuan, Jumlah (RM), Tarikh'],
                ['screen_name' => 'Belum Diproses',  'label' => 'Butang Proses',             'type' => 'Button', 'condition' => 'Navigate to [id]/index.vue to process withdrawal'],
                ['screen_name' => 'Dalam Proses',    'label' => 'Jadual Dalam Proses',       'type' => 'Table',  'condition' => 'Withdrawals awaiting agihan or kelulusan'],
                ['screen_name' => 'Selesai',         'label' => 'Jadual Selesai',            'type' => 'Table',  'condition' => 'Completed disbursements with receipt confirmation'],
                ['screen_name' => 'Semua',           'label' => 'Jadual Semua',              'type' => 'Table',  'condition' => 'All withdrawal records with search'],
                ['screen_name' => 'Belum Diproses',  'label' => 'Butang Tambah',             'type' => 'Button', 'condition' => 'Navigate to tambah.vue to create new withdrawal request'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/pengeluaran-tunai', 'description' => 'Fetch paginated list of cash withdrawal records'],
            ]);

        $pgt02 = $this->seed($mid, $pgt->id, 'TUN-PGT-02', 'Tambah Pengeluaran Tunai',
            'pages/pengurusan-tunai/pengeluaran-tunai/tambah.vue', 20, $staff,
            [
                ['screen_name' => 'Tambah Pengeluaran Tunai', 'label' => 'Rujukan Bantuan',   'type' => 'Text',    'condition' => 'Aid reference number to link this withdrawal', 'mandatory' => true],
                ['screen_name' => 'Tambah Pengeluaran Tunai', 'label' => 'Nama Penerima',     'type' => 'Display', 'condition' => 'Auto-filled from bantuan rujukan lookup'],
                ['screen_name' => 'Tambah Pengeluaran Tunai', 'label' => 'Jenis Bantuan',     'type' => 'Display', 'condition' => 'Auto-filled: aid type from rujukan'],
                ['screen_name' => 'Tambah Pengeluaran Tunai', 'label' => 'Jumlah (RM)',       'type' => 'Number',  'condition' => 'Cash amount to be withdrawn', 'mandatory' => true],
                ['screen_name' => 'Tambah Pengeluaran Tunai', 'label' => 'Tabung PIC',        'type' => 'Select',  'condition' => 'PIC tabung to deduct from', 'mandatory' => true],
                ['screen_name' => 'Tambah Pengeluaran Tunai', 'label' => 'Catatan',           'type' => 'Textarea', 'condition' => 'Optional remarks for this withdrawal'],
                ['screen_name' => 'Tambah Pengeluaran Tunai', 'label' => 'Butang Simpan',     'type' => 'Button',  'condition' => 'Create withdrawal request'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/agihan/senarai?rujukan={ref}',  'description' => 'Lookup approved bantuan record by rujukan'],
                ['method' => 'GET',  'endpoint' => '/tunai/tabung-pic?status=buka',          'description' => 'Fetch open PIC tabung for deduction selection'],
                ['method' => 'POST', 'endpoint' => '/tunai/pengeluaran-tunai',               'description' => 'Create new cash withdrawal request'],
            ]);

        $pgt03 = $this->seed($mid, $pgt->id, 'TUN-PGT-03', 'Butiran Pengeluaran Tunai',
            'pages/pengurusan-tunai/pengeluaran-tunai/[id]/index.vue', 30, $all,
            [
                ['screen_name' => 'Butiran Pengeluaran Tunai', 'label' => 'Maklumat Pengeluaran',  'type' => 'Display', 'condition' => 'Rujukan, Nama Penerima, Jenis Bantuan, Jumlah, Tabung PIC, Status'],
                ['screen_name' => 'Butiran Pengeluaran Tunai', 'label' => 'Tab Agihan',            'type' => 'Button',  'condition' => 'Navigate to [id]/agihan.vue'],
                ['screen_name' => 'Butiran Pengeluaran Tunai', 'label' => 'Tab Serahan',           'type' => 'Button',  'condition' => 'Navigate to [id]/serahan.vue'],
                ['screen_name' => 'Butiran Pengeluaran Tunai', 'label' => 'Tab Penerimaan',        'type' => 'Button',  'condition' => 'Navigate to [id]/penerimaan.vue'],
                ['screen_name' => 'Butiran Pengeluaran Tunai', 'label' => 'Tab Akuan',             'type' => 'Button',  'condition' => 'Navigate to [id]/akuan.vue'],
                ['screen_name' => 'Butiran Pengeluaran Tunai', 'label' => 'Tab Slip Penerimaan',   'type' => 'Button',  'condition' => 'Navigate to [id]/slip-penerimaan.vue'],
                ['screen_name' => 'Butiran Pengeluaran Tunai', 'label' => 'Butang Kelulusan',      'type' => 'Button',  'condition' => 'Navigate to [id]/kelulusan.vue (pelulus)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/pengeluaran-tunai/{id}', 'description' => 'Fetch cash withdrawal detail'],
            ]);

        $pgt04 = $this->seed($mid, $pgt->id, 'TUN-PGT-04', 'Agihan Pengeluaran Tunai',
            'pages/pengurusan-tunai/pengeluaran-tunai/[id]/agihan.vue', 40, $staff,
            [
                ['screen_name' => 'Agihan', 'label' => 'Senarai Agihan',                          'type' => 'Table',  'condition' => 'Distribution records: Penerima, Jumlah, Status Terima'],
                ['screen_name' => 'Agihan', 'label' => 'Butang Tambah Agihan',                    'type' => 'Button', 'condition' => 'Add a distribution record for a recipient'],
                ['screen_name' => 'Agihan', 'label' => 'Jumlah Diagihkan (RM)',                   'type' => 'Display', 'condition' => 'Running total of disbursed amount vs approved'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/tunai/pengeluaran-tunai/{id}/agihan',   'description' => 'Fetch distribution records for this withdrawal'],
                ['method' => 'POST', 'endpoint' => '/tunai/pengeluaran-tunai/{id}/agihan',   'description' => 'Add a cash distribution record'],
            ]);

        $pgt05 = $this->seed($mid, $pgt->id, 'TUN-PGT-05', 'Serahan Pengeluaran Tunai',
            'pages/pengurusan-tunai/pengeluaran-tunai/[id]/serahan.vue', 50, $staff,
            [
                ['screen_name' => 'Serahan', 'label' => 'Maklumat Serahan',                       'type' => 'Display', 'condition' => 'Handover details: Officer, Date, Amount'],
                ['screen_name' => 'Serahan', 'label' => 'Pengesahan Serahan',                     'type' => 'Toggle',  'condition' => 'Confirm physical handover completed'],
                ['screen_name' => 'Serahan', 'label' => 'Tandatangan / Akuan',                    'type' => 'Upload',  'condition' => 'Upload signed handover acknowledgement'],
                ['screen_name' => 'Serahan', 'label' => 'Butang Simpan',                          'type' => 'Button',  'condition' => 'Record serahan confirmation'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/tunai/pengeluaran-tunai/{id}/serahan',  'description' => 'Fetch handover details for this withdrawal'],
                ['method' => 'PATCH', 'endpoint' => '/tunai/pengeluaran-tunai/{id}/serahan',  'description' => 'Confirm cash handover'],
            ]);

        $pgt06 = $this->seed($mid, $pgt->id, 'TUN-PGT-06', 'Penerimaan Pengeluaran Tunai',
            'pages/pengurusan-tunai/pengeluaran-tunai/[id]/penerimaan.vue', 60, $staff,
            [
                ['screen_name' => 'Penerimaan', 'label' => 'Maklumat Penerimaan',                 'type' => 'Display', 'condition' => 'Receipt confirmation details: penerima, tarikh, jumlah'],
                ['screen_name' => 'Penerimaan', 'label' => 'Pengesahan Penerimaan',               'type' => 'Toggle',  'condition' => 'Confirm recipient has received the cash'],
                ['screen_name' => 'Penerimaan', 'label' => 'Tandatangan Penerima',                'type' => 'Upload',  'condition' => 'Upload recipient signature or acknowledgement'],
                ['screen_name' => 'Penerimaan', 'label' => 'Butang Simpan',                       'type' => 'Button',  'condition' => 'Record receipt confirmation'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/tunai/pengeluaran-tunai/{id}/penerimaan', 'description' => 'Fetch receipt confirmation status'],
                ['method' => 'PATCH', 'endpoint' => '/tunai/pengeluaran-tunai/{id}/penerimaan', 'description' => 'Confirm cash received by penerima'],
            ]);

        $pgt07 = $this->seed($mid, $pgt->id, 'TUN-PGT-07', 'Akuan Pengeluaran Tunai',
            'pages/pengurusan-tunai/pengeluaran-tunai/[id]/akuan.vue', 70, $staff,
            [
                ['screen_name' => 'Akuan', 'label' => 'Maklumat Akuan',                           'type' => 'Display', 'condition' => 'Read-only: declaration details for this withdrawal'],
                ['screen_name' => 'Akuan', 'label' => 'Akuan Pegawai',                            'type' => 'Toggle',  'condition' => 'Officer declares accuracy of disbursement record'],
                ['screen_name' => 'Akuan', 'label' => 'Butang Simpan',                            'type' => 'Button',  'condition' => 'Record officer declaration'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/tunai/pengeluaran-tunai/{id}/akuan',   'description' => 'Fetch officer declaration status'],
                ['method' => 'PATCH', 'endpoint' => '/tunai/pengeluaran-tunai/{id}/akuan',   'description' => 'Submit officer declaration for withdrawal record'],
            ]);

        $pgt08 = $this->seed($mid, $pgt->id, 'TUN-PGT-08', 'Slip Penerimaan Pengeluaran Tunai',
            'pages/pengurusan-tunai/pengeluaran-tunai/[id]/slip-penerimaan.vue', 80, $all,
            [
                ['screen_name' => 'Slip Penerimaan', 'label' => 'Maklumat Slip',                  'type' => 'Display', 'condition' => 'Printable slip: Rujukan, Nama Penerima, Jumlah, Tarikh, Cop'],
                ['screen_name' => 'Slip Penerimaan', 'label' => 'Butang Cetak',                   'type' => 'Button',  'condition' => 'Print or download receipt slip as PDF'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/pengeluaran-tunai/{id}/slip-penerimaan', 'description' => 'Fetch receipt slip data for print/download'],
            ]);

        $pgt09 = $this->seed($mid, $pgt->id, 'TUN-PGT-09', 'Kelulusan Pengeluaran Tunai',
            'pages/pengurusan-tunai/pengeluaran-tunai/[id]/kelulusan.vue', 90, [$pelulus->id],
            [
                ['screen_name' => 'Kelulusan Pengeluaran Tunai', 'label' => 'Ringkasan Permohonan', 'type' => 'Display', 'condition' => 'Rujukan, Penerima, Jumlah, Tabung PIC, Pegawai Pemohon'],
                ['screen_name' => 'Kelulusan Pengeluaran Tunai', 'label' => 'Keputusan Pelulus',    'type' => 'Select',  'condition' => 'Lulus / Tidak Lulus', 'mandatory' => true],
                ['screen_name' => 'Kelulusan Pengeluaran Tunai', 'label' => 'Catatan Pelulus',      'type' => 'Textarea', 'condition' => 'Pelulus remarks'],
                ['screen_name' => 'Kelulusan Pengeluaran Tunai', 'label' => 'Butang Simpan',        'type' => 'Button',  'condition' => 'Submit approval decision'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/tunai/pengeluaran-tunai/{id}',            'description' => 'Fetch withdrawal for pelulus review'],
                ['method' => 'PATCH', 'endpoint' => '/tunai/pengeluaran-tunai/{id}/kelulusan',  'description' => 'Submit pelulus approval decision for cash withdrawal'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // PEMULANGAN TUNAI (PMT) — 5 pages
        // ══════════════════════════════════════════════════════════════════

        $pmt01 = $this->seed($mid, $pmt->id, 'TUN-PMT-01', 'Senarai Pemulangan Tunai',
            'pages/pengurusan-tunai/pemulangan-tunai/index.vue', 10, $all,
            [
                ['screen_name' => 'Belum Dipulang',  'label' => 'Jadual Belum Dipulang',     'type' => 'Table',  'condition' => 'Columns: Rujukan, Pegawai PIC, Jumlah Lebihan (RM), Tarikh'],
                ['screen_name' => 'Belum Dipulang',  'label' => 'Butang Proses',             'type' => 'Button', 'condition' => 'Navigate to [id]/index.vue to process return'],
                ['screen_name' => 'Selesai',         'label' => 'Jadual Selesai',            'type' => 'Table',  'condition' => 'Completed cash returns to tabung utama'],
                ['screen_name' => 'Semua',           'label' => 'Jadual Semua',              'type' => 'Table',  'condition' => 'All pemulangan records'],
                ['screen_name' => 'Belum Dipulang',  'label' => 'Butang Tambah',             'type' => 'Button', 'condition' => 'Navigate to tambah.vue'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/pemulangan-tunai', 'description' => 'Fetch paginated list of cash return records'],
            ]);

        $pmt02 = $this->seed($mid, $pmt->id, 'TUN-PMT-02', 'Tambah Pemulangan Tunai',
            'pages/pengurusan-tunai/pemulangan-tunai/tambah.vue', 20, $staff,
            [
                ['screen_name' => 'Tambah Pemulangan Tunai', 'label' => 'Tabung PIC',         'type' => 'Select',  'condition' => 'Select PIC tabung returning surplus cash', 'mandatory' => true],
                ['screen_name' => 'Tambah Pemulangan Tunai', 'label' => 'Pegawai PIC',        'type' => 'Display', 'condition' => 'Auto-filled officer for selected tabung PIC'],
                ['screen_name' => 'Tambah Pemulangan Tunai', 'label' => 'Jumlah Dipulang (RM)', 'type' => 'Number', 'condition' => 'Amount being returned to tabung utama', 'mandatory' => true],
                ['screen_name' => 'Tambah Pemulangan Tunai', 'label' => 'Sebab Pemulangan',   'type' => 'Textarea', 'condition' => 'Reason for return (e.g. closing surplus, unused allocation)'],
                ['screen_name' => 'Tambah Pemulangan Tunai', 'label' => 'Butang Simpan',      'type' => 'Button',  'condition' => 'Submit pemulangan record'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/tunai/tabung-pic?status=buka',      'description' => 'Fetch open PIC tabung for selection'],
                ['method' => 'POST', 'endpoint' => '/tunai/pemulangan-tunai',             'description' => 'Create cash return record'],
            ]);

        $pmt03 = $this->seed($mid, $pmt->id, 'TUN-PMT-03', 'Butiran Pemulangan Tunai',
            'pages/pengurusan-tunai/pemulangan-tunai/[id]/index.vue', 30, $all,
            [
                ['screen_name' => 'Butiran Pemulangan', 'label' => 'Maklumat Pemulangan',     'type' => 'Display', 'condition' => 'Tabung PIC, Pegawai, Jumlah, Tarikh, Status'],
                ['screen_name' => 'Butiran Pemulangan', 'label' => 'Tab Serahan',             'type' => 'Button',  'condition' => 'Navigate to [id]/serahan.vue'],
                ['screen_name' => 'Butiran Pemulangan', 'label' => 'Tab Penerimaan',          'type' => 'Button',  'condition' => 'Navigate to [id]/penerimaan.vue'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/pemulangan-tunai/{id}', 'description' => 'Fetch pemulangan record detail'],
            ]);

        $pmt04 = $this->seed($mid, $pmt->id, 'TUN-PMT-04', 'Serahan Pemulangan Tunai',
            'pages/pengurusan-tunai/pemulangan-tunai/[id]/serahan.vue', 40, $staff,
            [
                ['screen_name' => 'Serahan Pemulangan', 'label' => 'Maklumat Serahan',        'type' => 'Display', 'condition' => 'Details: Tabung PIC, Pegawai, Jumlah Dipulang'],
                ['screen_name' => 'Serahan Pemulangan', 'label' => 'Pengesahan Serahan',      'type' => 'Toggle',  'condition' => 'PIC officer confirms physical handover of surplus'],
                ['screen_name' => 'Serahan Pemulangan', 'label' => 'Butang Simpan',           'type' => 'Button',  'condition' => 'Record handover confirmation'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/tunai/pemulangan-tunai/{id}/serahan',   'description' => 'Fetch handover status for pemulangan'],
                ['method' => 'PATCH', 'endpoint' => '/tunai/pemulangan-tunai/{id}/serahan',   'description' => 'Confirm physical handover of returned cash'],
            ]);

        $pmt05 = $this->seed($mid, $pmt->id, 'TUN-PMT-05', 'Penerimaan Pemulangan Tunai',
            'pages/pengurusan-tunai/pemulangan-tunai/[id]/penerimaan.vue', 50, $staff,
            [
                ['screen_name' => 'Penerimaan Pemulangan', 'label' => 'Maklumat Penerimaan',  'type' => 'Display', 'condition' => 'Details of cash received back into tabung utama'],
                ['screen_name' => 'Penerimaan Pemulangan', 'label' => 'Pengesahan Penerimaan', 'type' => 'Toggle', 'condition' => 'Tabung utama officer confirms receipt of returned cash'],
                ['screen_name' => 'Penerimaan Pemulangan', 'label' => 'Butang Simpan',        'type' => 'Button',  'condition' => 'Record receipt confirmation and update tabung balance'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/tunai/pemulangan-tunai/{id}/penerimaan',  'description' => 'Fetch receipt confirmation status for pemulangan'],
                ['method' => 'PATCH', 'endpoint' => '/tunai/pemulangan-tunai/{id}/penerimaan',  'description' => 'Confirm receipt of returned cash into tabung utama'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // AGIHAN LAIN-LAIN (ALL) — 4 pages
        // ══════════════════════════════════════════════════════════════════

        $all01 = $this->seed($mid, $all_->id, 'TUN-ALL-01', 'Senarai Bantuan Tunai',
            'pages/pengurusan-tunai/agihan-lain-lain/senarai-bantuan-tunai.vue', 10, $all,
            [
                ['screen_name' => 'Senarai Bantuan Tunai', 'label' => 'Filter Tarikh',        'type' => 'Date',   'condition' => 'Date range filter'],
                ['screen_name' => 'Senarai Bantuan Tunai', 'label' => 'Filter Jenis Bantuan', 'type' => 'Select', 'condition' => 'Aid type filter'],
                ['screen_name' => 'Senarai Bantuan Tunai', 'label' => 'Jadual Bantuan Tunai', 'type' => 'Table',  'condition' => 'Columns: Rujukan, Penerima, Jenis, Jumlah (RM), Status'],
                ['screen_name' => 'Senarai Bantuan Tunai', 'label' => 'Butang Pengambilan',   'type' => 'Button', 'condition' => 'Navigate to senarai-pengambilan-bantuan-tunai'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/agihan-lain-lain/bantuan', 'description' => 'Fetch list of ad-hoc cash aid records'],
            ]);

        $all02 = $this->seed($mid, $all_->id, 'TUN-ALL-02', 'Senarai Pengambilan Bantuan Tunai',
            'pages/pengurusan-tunai/agihan-lain-lain/senarai-pengambilan-bantuan-tunai.vue', 20, $all,
            [
                ['screen_name' => 'Senarai Pengambilan', 'label' => 'Filter Status',          'type' => 'Select', 'condition' => 'Belum Diambil / Dalam Proses / Selesai'],
                ['screen_name' => 'Senarai Pengambilan', 'label' => 'Jadual Pengambilan',     'type' => 'Table',  'condition' => 'Columns: Rujukan, Penerima, Jumlah, Status Pengambilan, Tarikh'],
                ['screen_name' => 'Senarai Pengambilan', 'label' => 'Butang Proses',          'type' => 'Button', 'condition' => 'Navigate to permohonan-pengambilan-bantuan-tunai'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/agihan-lain-lain/pengambilan', 'description' => 'Fetch list of cash collection requests'],
            ]);

        $all03 = $this->seed($mid, $all_->id, 'TUN-ALL-03', 'Permohonan Pengambilan Bantuan Tunai',
            'pages/pengurusan-tunai/agihan-lain-lain/permohonan-pengambilan-bantuan-tunai.vue', 30, $staff,
            [
                ['screen_name' => 'Permohonan Pengambilan', 'label' => 'Rujukan Bantuan',     'type' => 'Text',    'condition' => 'Reference number of the approved aid', 'mandatory' => true],
                ['screen_name' => 'Permohonan Pengambilan', 'label' => 'Maklumat Penerima',   'type' => 'Display', 'condition' => 'Auto-filled from rujukan lookup: Nama, IC, Alamat'],
                ['screen_name' => 'Permohonan Pengambilan', 'label' => 'Jumlah Diluluskan (RM)', 'type' => 'Display', 'condition' => 'Approved aid amount from bantuan module'],
                ['screen_name' => 'Permohonan Pengambilan', 'label' => 'Kaedah Pengambilan',  'type' => 'Select',  'condition' => 'Cash counter / Hantar terus / Lain-lain', 'mandatory' => true],
                ['screen_name' => 'Permohonan Pengambilan', 'label' => 'Catatan',             'type' => 'Textarea', 'condition' => 'Remarks for collection arrangement'],
                ['screen_name' => 'Permohonan Pengambilan', 'label' => 'Butang Simpan',       'type' => 'Button',  'condition' => 'Submit collection arrangement'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/bantuan/agihan/senarai?rujukan={ref}',           'description' => 'Lookup bantuan record by rujukan'],
                ['method' => 'POST', 'endpoint' => '/tunai/agihan-lain-lain/pengambilan',              'description' => 'Submit cash collection arrangement for approved aid'],
            ]);

        $all04 = $this->seed($mid, $all_->id, 'TUN-ALL-04', 'Carian Asnaf (Agihan)',
            'pages/pengurusan-tunai/agihan-lain-lain/carian-asnaf.vue', 40, $staff,
            [
                ['screen_name' => 'Carian Asnaf', 'label' => 'Carian',                        'type' => 'Text',   'condition' => 'Search by Nama / IC / No. Rujukan'],
                ['screen_name' => 'Carian Asnaf', 'label' => 'Hasil Carian',                  'type' => 'Table',  'condition' => 'Columns: Nama, IC, Kategori Asnaf, Bantuan Aktif, Baki'],
                ['screen_name' => 'Carian Asnaf', 'label' => 'Butang Pilih',                  'type' => 'Button', 'condition' => 'Select asnaf to proceed with cash agihan'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/agihan-lain-lain/carian-asnaf?q={q}', 'description' => 'Search asnaf records for ad-hoc cash agihan'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // TAMBAH NILAI (TNI) — 4 pages
        // ══════════════════════════════════════════════════════════════════

        $tni01 = $this->seed($mid, $tni->id, 'TUN-TNI-01', 'Senarai Permohonan Kewangan',
            'pages/pengurusan-tunai/tambah-nilai/senarai-permohonan-kewangan.vue', 10, $all,
            [
                ['screen_name' => 'Senarai Permohonan Kewangan', 'label' => 'Filter Status',  'type' => 'Select', 'condition' => 'Menunggu / Diluluskan / Ditolak'],
                ['screen_name' => 'Senarai Permohonan Kewangan', 'label' => 'Jadual Permohonan', 'type' => 'Table', 'condition' => 'Columns: Rujukan, Tabung, Jumlah (RM), Pemohon, Tarikh, Status'],
                ['screen_name' => 'Senarai Permohonan Kewangan', 'label' => 'Butang Lulus',   'type' => 'Button', 'condition' => 'Approve top-up request (pelulus role)'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/tambah-nilai/permohonan-kewangan', 'description' => 'Fetch list of top-up requests to be approved by finance/pelulus'],
            ]);

        $tni02 = $this->seed($mid, $tni->id, 'TUN-TNI-02', 'Senarai Permohonan KC/KB Eksekutif/KOAD',
            'pages/pengurusan-tunai/tambah-nilai/senarai-permohonan-kc-kb-eksekutif-koad.vue', 20, $all,
            [
                ['screen_name' => 'Permohonan KC/KB/Eksekutif/KOAD', 'label' => 'Filter Jenis', 'type' => 'Select', 'condition' => 'KC / KB / Eksekutif / KOAD role filter'],
                ['screen_name' => 'Permohonan KC/KB/Eksekutif/KOAD', 'label' => 'Jadual Permohonan', 'type' => 'Table', 'condition' => 'Columns: Rujukan, Jenis, Tabung, Jumlah, Status, Tarikh'],
                ['screen_name' => 'Permohonan KC/KB/Eksekutif/KOAD', 'label' => 'Butang Proses', 'type' => 'Button', 'condition' => 'Process or approve the top-up request'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/tambah-nilai/permohonan-kc-kb', 'description' => 'Fetch top-up requests for KC/KB/Eksekutif/KOAD approval workflow'],
            ]);

        $tni03 = $this->seed($mid, $tni->id, 'TUN-TNI-03', 'Senarai Penerimaan Tunai CC/ePOAD',
            'pages/pengurusan-tunai/tambah-nilai/senarai-penerimaan-tunai-cc-epoad.vue', 30, $all,
            [
                ['screen_name' => 'Penerimaan CC/ePOAD', 'label' => 'Filter Tarikh',          'type' => 'Date',   'condition' => 'Date range for cash receipts from CC or ePOAD'],
                ['screen_name' => 'Penerimaan CC/ePOAD', 'label' => 'Filter Kaedah',          'type' => 'Select', 'condition' => 'Cash Centre (CC) / ePOAD transfer'],
                ['screen_name' => 'Penerimaan CC/ePOAD', 'label' => 'Jadual Penerimaan',      'type' => 'Table',  'condition' => 'Columns: Rujukan, Kaedah, Tabung, Jumlah (RM), Tarikh, Disahkan'],
                ['screen_name' => 'Penerimaan CC/ePOAD', 'label' => 'Butang Sahkan',          'type' => 'Button', 'condition' => 'Confirm receipt from CC or ePOAD system'],
            ],
            [
                ['method' => 'GET',   'endpoint' => '/tunai/tambah-nilai/penerimaan-cc-epoad',         'description' => 'Fetch cash receipts from Cash Centre or ePOAD transfers'],
                ['method' => 'PATCH', 'endpoint' => '/tunai/tambah-nilai/penerimaan-cc-epoad/{id}/sahkan', 'description' => 'Confirm CC/ePOAD cash receipt and update tabung balance'],
            ]);

        $tni04 = $this->seed($mid, $tni->id, 'TUN-TNI-04', 'Senarai Surat Arahan Pindahan Tunai (Tambah Nilai)',
            'pages/pengurusan-tunai/tambah-nilai/senarai-surat-arahan-pindahan-tunai.vue', 40, $all,
            [
                ['screen_name' => 'Surat Arahan Pindahan Tunai', 'label' => 'Filter Tarikh',  'type' => 'Date',   'condition' => 'Date range filter'],
                ['screen_name' => 'Surat Arahan Pindahan Tunai', 'label' => 'Jadual Surat',   'type' => 'Table',  'condition' => 'Columns: Nombor Surat, Tabung Penerima, Jumlah (RM), Tarikh, Status'],
                ['screen_name' => 'Surat Arahan Pindahan Tunai', 'label' => 'Butang Lihat',   'type' => 'Button', 'condition' => 'View the transfer instruction letter'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tunai/tambah-nilai/surat-arahan-pindahan', 'description' => 'Fetch list of cash transfer instruction letters for top-up'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // Page Links
        // ══════════════════════════════════════════════════════════════════

        $links = [
            // Pengeluaran Tunai
            ['TUN-PGT-01', 'TUN-PGT-02'],
            ['TUN-PGT-01', 'TUN-PGT-03'],
            ['TUN-PGT-03', 'TUN-PGT-04'],
            ['TUN-PGT-03', 'TUN-PGT-05'],
            ['TUN-PGT-03', 'TUN-PGT-06'],
            ['TUN-PGT-03', 'TUN-PGT-07'],
            ['TUN-PGT-03', 'TUN-PGT-08'],
            ['TUN-PGT-03', 'TUN-PGT-09'],
            // Pemulangan Tunai
            ['TUN-PMT-01', 'TUN-PMT-02'],
            ['TUN-PMT-01', 'TUN-PMT-03'],
            ['TUN-PMT-03', 'TUN-PMT-04'],
            ['TUN-PMT-03', 'TUN-PMT-05'],
            // Agihan Lain-lain
            ['TUN-ALL-01', 'TUN-ALL-02'],
            ['TUN-ALL-02', 'TUN-ALL-03'],
            ['TUN-ALL-03', 'TUN-ALL-04'],
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
