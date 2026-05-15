<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RtmfModule;
use App\Models\RtmfSubModule;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendItem;
use App\Models\RtmfFrontendApiEndpoint;
use App\Models\RtmfActor;
use App\Models\RtmfProject;

class RtmfPengurusanTunaiPhase3Seeder extends Seeder
{
    public function run(): void
    {
        $project = RtmfProject::first();

        $module = RtmfModule::firstOrCreate(
            ['code' => 'TUN'],
            ['name' => 'Pengurusan Tunai', 'project_id' => $project->id, 'sort_order' => 40],
        );
        if (! $module->project_id) {
            $module->update(['project_id' => $project->id]);
        }

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        // ── Sub-module: RKP — Rekupmen ─────────────────────────────────────────
        $rkp = RtmfSubModule::firstOrCreate(
            ['code' => 'RKP'],
            ['name' => 'Rekupmen', 'module_id' => $module->id, 'sort_order' => 50],
        );

        $pages = [];

        // Semakan Kelulusan Rekupmen (2)
        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-01', 'Senarai Semakan Kelulusan Rekupmen',
            'pages/pengurusan-tunai/rekupmen/semakan-kelulusan-rekupmen/index.vue', 10,
            [$pegawai->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'No. Rujukan Rekupmen', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Permohonan', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Pegawai Pemohon', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jumlah Rekupmen (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Butiran', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/rekupmen/semakan-kelulusan', 'description' => 'Senarai semakan kelulusan rekupmen']],
        );

        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-02', 'Butiran Semakan Kelulusan Rekupmen',
            'pages/pengurusan-tunai/rekupmen/semakan-kelulusan-rekupmen/[id].vue', 20,
            [$pegawai->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'No. Rujukan Rekupmen', 'type' => 'Text'],
                ['label' => 'Tarikh Permohonan', 'type' => 'Date'],
                ['label' => 'Pegawai Pemohon', 'type' => 'Text'],
                ['label' => 'Tabung', 'type' => 'Text'],
                ['label' => 'Jumlah Rekupmen (RM)', 'type' => 'Text'],
                ['label' => 'Catatan', 'type' => 'Textarea'],
                ['label' => 'Lulus / Tolak', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/rekupmen/semakan-kelulusan/{id}', 'description' => 'Butiran semakan kelulusan rekupmen']],
        );

        // Senarai Penerimaan Tunai (4)
        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-03', 'Senarai Penerimaan Tunai',
            'pages/pengurusan-tunai/rekupmen/senarai-penerimaan-tunai/index.vue', 30,
            [$pegawai->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'No. Rujukan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Penerimaan', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Pegawai Penerima', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jumlah Diterima (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-penerimaan-tunai', 'description' => 'Senarai penerimaan tunai']],
        );

        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-04', 'Butiran Rekupmen Penerimaan',
            'pages/pengurusan-tunai/rekupmen/senarai-penerimaan-tunai/butiran-rekupmen/[id].vue', 40,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Rujukan Rekupmen', 'type' => 'Text'],
                ['label' => 'Tarikh', 'type' => 'Date'],
                ['label' => 'Tabung', 'type' => 'Text'],
                ['label' => 'Jumlah (RM)', 'type' => 'Text'],
                ['label' => 'Catatan', 'type' => 'Textarea'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-penerimaan-tunai/butiran-rekupmen/{id}', 'description' => 'Butiran rekupmen penerimaan tunai']],
        );

        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-05', 'Rekod Penerimaan Tunai',
            'pages/pengurusan-tunai/rekupmen/senarai-penerimaan-tunai/rekod-penerimaan/[id].vue', 50,
            [$pegawai->id],
            [
                ['label' => 'No. Rujukan', 'type' => 'Text'],
                ['label' => 'Tarikh Penerimaan', 'type' => 'Date', 'mandatory' => true],
                ['label' => 'Jumlah Diterima (RM)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Catatan', 'type' => 'Textarea'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-penerimaan-tunai/{id}', 'description' => 'Muatkan data rekod penerimaan'],
                ['method' => 'POST', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-penerimaan-tunai/rekod-penerimaan', 'description' => 'Simpan rekod penerimaan tunai'],
            ],
        );

        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-06', 'Sahkan Penerimaan Tunai',
            'pages/pengurusan-tunai/rekupmen/senarai-penerimaan-tunai/sahkan-penerimaan/[id].vue', 60,
            [$penyelia->id, $pelulus->id],
            [
                ['label' => 'No. Rujukan', 'type' => 'Text'],
                ['label' => 'Jumlah Diterima (RM)', 'type' => 'Text'],
                ['label' => 'Tarikh Penerimaan', 'type' => 'Date'],
                ['label' => 'Pengesahan', 'type' => 'Checkbox', 'mandatory' => true],
                ['label' => 'Sahkan', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-penerimaan-tunai/sahkan-penerimaan/{id}', 'description' => 'Sahkan penerimaan tunai']],
        );

        // Senarai Permohonan Rekupmen (3)
        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-07', 'Senarai Permohonan Rekupmen',
            'pages/pengurusan-tunai/rekupmen/senarai-permohonan-rekupmen/index.vue', 70,
            [$pegawai->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'No. Rujukan Rekupmen', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Permohonan', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Pegawai Pemohon', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jumlah (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-permohonan-rekupmen', 'description' => 'Senarai permohonan rekupmen']],
        );

        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-08', 'Tambah Permohonan Rekupmen',
            'pages/pengurusan-tunai/rekupmen/senarai-permohonan-rekupmen/tambah/index.vue', 80,
            [$pegawai->id],
            [
                ['label' => 'Tabung', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Jumlah Rekupmen (RM)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Tarikh Permohonan', 'type' => 'Date', 'mandatory' => true],
                ['label' => 'Catatan', 'type' => 'Textarea'],
                ['label' => 'Hantar Permohonan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung', 'description' => 'Senarai tabung untuk dropdown'],
                ['method' => 'POST', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-permohonan-rekupmen', 'description' => 'Hantar permohonan rekupmen baharu'],
            ],
        );

        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-09', 'Butiran Permohonan Rekupmen',
            'pages/pengurusan-tunai/rekupmen/senarai-permohonan-rekupmen/butiran/[id].vue', 90,
            [$pegawai->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'No. Rujukan Rekupmen', 'type' => 'Text'],
                ['label' => 'Tarikh Permohonan', 'type' => 'Date'],
                ['label' => 'Tabung', 'type' => 'Text'],
                ['label' => 'Jumlah (RM)', 'type' => 'Text'],
                ['label' => 'Status', 'type' => 'Badge'],
                ['label' => 'Catatan', 'type' => 'Textarea'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-permohonan-rekupmen/{id}', 'description' => 'Butiran permohonan rekupmen']],
        );

        // Senarai Surat Arahan Pindahan Tunai (3 pages, excluding template-surat.vue)
        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-10', 'Senarai Surat Arahan Pindahan Tunai',
            'pages/pengurusan-tunai/rekupmen/senarai-surat-arahan-pindahan-tunai/index.vue', 100,
            [$pegawai->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'No. Rujukan Surat', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Surat', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Daripada Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Kepada Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jumlah (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-surat-arahan-pindahan-tunai', 'description' => 'Senarai surat arahan pindahan tunai']],
        );

        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-11', 'Jana Surat Arahan Pindahan Tunai',
            'pages/pengurusan-tunai/rekupmen/senarai-surat-arahan-pindahan-tunai/jana-surat/index.vue', 110,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'Daripada Tabung', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Kepada Tabung', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Jumlah Pindahan (RM)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Tarikh Surat', 'type' => 'Date', 'mandatory' => true],
                ['label' => 'Jana Surat', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung', 'description' => 'Senarai tabung untuk dropdown'],
                ['method' => 'POST', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-surat-arahan-pindahan-tunai/jana-surat', 'description' => 'Jana surat arahan pindahan tunai'],
            ],
        );

        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-12', 'Muat Naik Surat Arahan Pindahan Tunai',
            'pages/pengurusan-tunai/rekupmen/senarai-surat-arahan-pindahan-tunai/muat-naik/index.vue', 120,
            [$pegawai->id],
            [
                ['label' => 'No. Rujukan Surat', 'type' => 'Text'],
                ['label' => 'Fail Surat (PDF)', 'type' => 'File', 'mandatory' => true],
                ['label' => 'Muat Naik', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-surat-arahan-pindahan-tunai/muat-naik', 'description' => 'Muat naik surat arahan pindahan tunai']],
        );

        $pages[] = $this->seed($module->id, $rkp->id, 'TUN-RKP-13', 'Lihat Surat Arahan Pindahan Tunai',
            'pages/pengurusan-tunai/rekupmen/senarai-surat-arahan-pindahan-tunai/view-surat/[id].vue', 130,
            [$pegawai->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'No. Rujukan Surat', 'type' => 'Text'],
                ['label' => 'Tarikh Surat', 'type' => 'Date'],
                ['label' => 'Daripada Tabung', 'type' => 'Text'],
                ['label' => 'Kepada Tabung', 'type' => 'Text'],
                ['label' => 'Jumlah (RM)', 'type' => 'Text'],
                ['label' => 'Pratonton Surat', 'type' => 'File'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/rekupmen/senarai-surat-arahan-pindahan-tunai/{id}', 'description' => 'Lihat surat arahan pindahan tunai']],
        );

        // ── Sub-module: LAP — Laporan ─────────────────────────────────────────
        $lap = RtmfSubModule::firstOrCreate(
            ['code' => 'TUN-LAP'],
            ['name' => 'Laporan Tunai', 'module_id' => $module->id, 'sort_order' => 60],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'TUN-LAP-01', 'Laporan Pengurusan Tunai',
            'pages/pengurusan-tunai/laporan/index.vue', 10,
            [$pegawai->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Jenis Laporan', 'type' => 'Select'],
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/laporan', 'description' => 'Indeks laporan pengurusan tunai']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'TUN-LAP-02', 'Laporan Baki Semasa Tabung',
            'pages/pengurusan-tunai/laporan/baki-semasa-tabung/index.vue', 20,
            [$pegawai->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Tabung', 'type' => 'Select'],
                ['label' => 'Tarikh', 'type' => 'Date'],
                ['label' => 'Baki Semasa (RM)', 'type' => 'Text'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/laporan/baki-semasa-tabung', 'description' => 'Laporan baki semasa tabung']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'TUN-LAP-03', 'Laporan Pemulangan Tunai Tabung',
            'pages/pengurusan-tunai/laporan/pemulangan-tunai-tabung/index.vue', 30,
            [$pegawai->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Tabung', 'type' => 'Select'],
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/laporan/pemulangan-tunai-tabung', 'description' => 'Laporan pemulangan tunai tabung']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'TUN-LAP-04', 'Laporan Serahan Bantuan Tunai',
            'pages/pengurusan-tunai/laporan/serahan-bantuan-tunai/index.vue', 40,
            [$pegawai->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Tabung', 'type' => 'Select'],
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/laporan/serahan-bantuan-tunai', 'description' => 'Laporan serahan bantuan tunai']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'TUN-LAP-05', 'Senarai Slip Penerimaan',
            'pages/pengurusan-tunai/laporan/slip-penerimaan/index.vue', 50,
            [$pegawai->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'No. Slip', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Nama Penerima', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jumlah (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jana Slip', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/laporan/slip-penerimaan', 'description' => 'Senarai slip penerimaan']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'TUN-LAP-06', 'Janaan Slip Penerimaan',
            'pages/pengurusan-tunai/laporan/slip-penerimaan/janaan-slip-penerimaan.vue', 60,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Slip', 'type' => 'Text'],
                ['label' => 'Tarikh', 'type' => 'Date'],
                ['label' => 'Nama Penerima', 'type' => 'Text'],
                ['label' => 'Jumlah (RM)', 'type' => 'Text'],
                ['label' => 'Cetak / Muat Turun', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/laporan/slip-penerimaan/janaan', 'description' => 'Jana slip penerimaan']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'TUN-LAP-07', 'Laporan Tamat Hari',
            'pages/pengurusan-tunai/laporan/tamat-hari/index.vue', 70,
            [$pegawai->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Tarikh', 'type' => 'Date'],
                ['label' => 'Tabung', 'type' => 'Select'],
                ['label' => 'Baki Awal (RM)', 'type' => 'Text'],
                ['label' => 'Jumlah Agihan (RM)', 'type' => 'Text'],
                ['label' => 'Baki Akhir (RM)', 'type' => 'Text'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/laporan/tamat-hari', 'description' => 'Laporan tamat hari operasi']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'TUN-LAP-08', 'Laporan Tambah Nilai Akaun',
            'pages/pengurusan-tunai/laporan/tambah-nilai-akaun/index.vue', 80,
            [$pegawai->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Tabung', 'type' => 'Select'],
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/laporan/tambah-nilai-akaun', 'description' => 'Laporan tambah nilai akaun']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'TUN-LAP-09', 'Laporan Transaksi Pengeluaran Tunai',
            'pages/pengurusan-tunai/laporan/transaksi-pengeluaran-tunai/index.vue', 90,
            [$pegawai->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Tabung', 'type' => 'Select'],
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Pegawai', 'type' => 'Select'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/laporan/transaksi-pengeluaran-tunai', 'description' => 'Laporan transaksi pengeluaran tunai']],
        );

        $this->seedLinks($pages);
    }

    private function seedLinks(array $pages): void
    {
        $map = [];
        foreach ($pages as $p) {
            $map[$p->spec_id] = $p;
        }

        $links = [
            ['TUN-RKP-01', 'TUN-RKP-02'],
            ['TUN-RKP-03', 'TUN-RKP-04'],
            ['TUN-RKP-03', 'TUN-RKP-05'],
            ['TUN-RKP-03', 'TUN-RKP-06'],
            ['TUN-RKP-07', 'TUN-RKP-08'],
            ['TUN-RKP-07', 'TUN-RKP-09'],
            ['TUN-RKP-10', 'TUN-RKP-11'],
            ['TUN-RKP-10', 'TUN-RKP-12'],
            ['TUN-RKP-10', 'TUN-RKP-13'],
            ['TUN-LAP-05', 'TUN-LAP-06'],
        ];

        foreach ($links as [$from, $to]) {
            if (isset($map[$from], $map[$to])) {
                $map[$from]->linksTo()->syncWithoutDetaching([$map[$to]->id]);
            }
        }
    }

    private function seed(
        int $moduleId, int $subModuleId, string $specId, string $title,
        string $vuePath, int $sortOrder, array $actorIds, array $items, array $endpoints,
    ): RtmfFrontend {
        $fe = RtmfFrontend::updateOrCreate(
            ['spec_id' => $specId],
            ['module_id' => $moduleId, 'sub_module_id' => $subModuleId,
             'title' => $title, 'vue_path' => $vuePath, 'sort_order' => $sortOrder],
        );
        $fe->actors()->sync($actorIds);
        RtmfFrontendItem::where('rtmf_frontend_id', $fe->id)->delete();
        foreach ($items as $i => $item) {
            RtmfFrontendItem::create([
                'rtmf_frontend_id' => $fe->id, 'sort_order' => $i,
                'screen_name' => $item['screen_name'] ?? $title,
                'id_fr' => $fe->spec_id . '-FR-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'label' => $item['label'], 'type' => $item['type'] ?? 'Text',
                'condition' => $item['condition'] ?? null,
                'mandatory' => $item['mandatory'] ?? false,
                'table_fieldname' => $item['table_fieldname'] ?? null,
                'validation' => $item['validation'] ?? null,
                'status' => $item['status'] ?? 'missing',
            ]);
        }
        RtmfFrontendApiEndpoint::where('rtmf_frontend_id', $fe->id)->delete();
        foreach ($endpoints as $k => $ep) {
            RtmfFrontendApiEndpoint::create([
                'rtmf_frontend_id' => $fe->id, 'sort_order' => $k,
                'method' => $ep['method'], 'endpoint' => $ep['endpoint'],
                'description' => $ep['description'] ?? null,
            ]);
        }
        return $fe;
    }
}
