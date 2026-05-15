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

class RtmfPengurusanTunaiPhase4Seeder extends Seeder
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
        $admin    = RtmfActor::firstOrCreate(['name' => 'Admin']);

        // ── Sub-module: KPG — Konfigurasi Pegawai ─────────────────────────────
        $kpg = RtmfSubModule::firstOrCreate(
            ['code' => 'TUN-KPG'],
            ['name' => 'Konfigurasi Pegawai Tunai', 'module_id' => $module->id, 'sort_order' => 70],
        );

        $pages = [];

        $pages[] = $this->seed($module->id, $kpg->id, 'TUN-KPG-01', 'Senarai Pegawai Berkuasa',
            'pages/pengurusan-tunai/konfigurasi-pegawai/senarai-pegawai-berkuasa.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Pegawai', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'No. Pekerja', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jawatan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-pegawai/senarai-pegawai-berkuasa', 'description' => 'Senarai pegawai berkuasa']],
        );

        $pages[] = $this->seed($module->id, $kpg->id, 'TUN-KPG-02', 'Senarai Kelulusan Pegawai Berkuasa',
            'pages/pengurusan-tunai/konfigurasi-pegawai/senarai-kelulusan-pegawai-berkuasa.vue', 20,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Nama Pegawai', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'No. Pekerja', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Tabung', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Nama Pegawai', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-pegawai/kelulusan', 'description' => 'Senarai kelulusan pegawai berkuasa']],
        );

        // ── Sub-module: KTB — Konfigurasi Tabung ──────────────────────────────
        $ktb = RtmfSubModule::firstOrCreate(
            ['code' => 'TUN-KTB'],
            ['name' => 'Konfigurasi Tabung', 'module_id' => $module->id, 'sort_order' => 80],
        );

        $pages[] = $this->seed($module->id, $ktb->id, 'TUN-KTB-01', 'Senarai Tabung Utama',
            'pages/pengurusan-tunai/konfigurasi-tabung/senarai-tabung-utama.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Kod Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Nama Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Baki (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung/utama', 'description' => 'Senarai tabung utama']],
        );

        $pages[] = $this->seed($module->id, $ktb->id, 'TUN-KTB-02', 'Senarai Tabung PIC PS',
            'pages/pengurusan-tunai/konfigurasi-tabung/senarai-tabung-pic-ps.vue', 20,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Pegawai PIC', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Had Agihan (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung/pic-ps', 'description' => 'Senarai tabung PIC peringkat PS']],
        );

        $pages[] = $this->seed($module->id, $ktb->id, 'TUN-KTB-03', 'Senarai Tabung Pelulus',
            'pages/pengurusan-tunai/konfigurasi-tabung/senarai-tabung-pelulus.vue', 30,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Pelulus', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Had Kelulusan (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung/pelulus', 'description' => 'Senarai tabung pelulus']],
        );

        $pages[] = $this->seed($module->id, $ktb->id, 'TUN-KTB-04', 'Senarai Permohonan Tabung PIC KJ',
            'pages/pengurusan-tunai/konfigurasi-tabung/senarai-permohonan-tabung-pic-kj.vue', 40,
            [$admin->id, $penyelia->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'No. Rujukan Permohonan', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Nama Pegawai PIC', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Tabung', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'No. Rujukan Permohonan', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung/permohonan-pic-kj', 'description' => 'Senarai permohonan tabung PIC KJ']],
        );

        // ── Sub-module: KTP — Konfigurasi Tabung PIC ──────────────────────────
        $ktp = RtmfSubModule::firstOrCreate(
            ['code' => 'TUN-KTP'],
            ['name' => 'Konfigurasi Tabung PIC', 'module_id' => $module->id, 'sort_order' => 90],
        );

        $pages[] = $this->seed($module->id, $ktp->id, 'TUN-KTP-01', 'Senarai Tabung PIC',
            'pages/pengurusan-tunai/konfigurasi/konfigurasi-tabung-pic/senarai-tabung-pic/index.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Pegawai PIC', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Had Agihan (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-pic', 'description' => 'Senarai konfigurasi tabung PIC']],
        );

        $pages[] = $this->seed($module->id, $ktp->id, 'TUN-KTP-02', 'Tambah Tabung PIC',
            'pages/pengurusan-tunai/konfigurasi/konfigurasi-tabung-pic/senarai-tabung-pic/tambah/index.vue', 20,
            [$admin->id, $penyelia->id],
            [
                ['label' => 'Pegawai PIC', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Tabung', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Had Agihan (RM)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Tarikh Mula', 'type' => 'Date'],
                ['label' => 'Tarikh Tamat', 'type' => 'Date'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung', 'description' => 'Senarai tabung untuk dropdown'],
                ['method' => 'POST', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-pic', 'description' => 'Tambah konfigurasi tabung PIC baharu'],
            ],
        );

        $pages[] = $this->seed($module->id, $ktp->id, 'TUN-KTP-03', 'Kemaskini Tabung PIC',
            'pages/pengurusan-tunai/konfigurasi/konfigurasi-tabung-pic/senarai-tabung-pic/[id]/index.vue', 30,
            [$admin->id, $penyelia->id],
            [
                ['label' => 'Pegawai PIC', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Tabung', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Had Agihan (RM)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Tarikh Mula', 'type' => 'Date'],
                ['label' => 'Tarikh Tamat', 'type' => 'Date'],
                ['label' => 'Kemaskini', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-pic/{id}', 'description' => 'Muatkan data tabung PIC untuk kemaskini'],
                ['method' => 'PUT', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-pic/{id}', 'description' => 'Kemaskini konfigurasi tabung PIC'],
            ],
        );

        $pages[] = $this->seed($module->id, $ktp->id, 'TUN-KTP-04', 'Butiran Tabung PIC',
            'pages/pengurusan-tunai/konfigurasi/konfigurasi-tabung-pic/senarai-tabung-pic/butiran/[id]/index.vue', 40,
            [$admin->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Pegawai PIC', 'type' => 'Text'],
                ['label' => 'Tabung', 'type' => 'Text'],
                ['label' => 'Had Agihan (RM)', 'type' => 'Text'],
                ['label' => 'Tarikh Mula', 'type' => 'Date'],
                ['label' => 'Tarikh Tamat', 'type' => 'Date'],
                ['label' => 'Status', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-pic/{id}/butiran', 'description' => 'Butiran konfigurasi tabung PIC']],
        );

        $pages[] = $this->seed($module->id, $ktp->id, 'TUN-KTP-05', 'Semakan Kelulusan Tabung PIC',
            'pages/pengurusan-tunai/konfigurasi/konfigurasi-tabung-pic/semakan-kelulusan-tabung-pic/index.vue', 50,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Nama Pegawai PIC', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Tabung', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Had Agihan (RM)', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Nama Pegawai PIC', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-pic/semakan-kelulusan', 'description' => 'Semakan kelulusan tabung PIC']],
        );

        $pages[] = $this->seed($module->id, $ktp->id, 'TUN-KTP-06', 'Senarai Kelulusan Tabung PIC',
            'pages/pengurusan-tunai/konfigurasi/konfigurasi-tabung-pic/senarai-kelulusan-tabung-pic/index.vue', 60,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Pegawai PIC', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Keputusan', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Kelulusan', 'type' => 'Date'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-pic/senarai-kelulusan', 'description' => 'Senarai kelulusan tabung PIC']],
        );

        // ── Sub-module: KTU — Konfigurasi Tabung Utama ────────────────────────
        $ktu = RtmfSubModule::firstOrCreate(
            ['code' => 'TUN-KTU'],
            ['name' => 'Konfigurasi Tabung Utama', 'module_id' => $module->id, 'sort_order' => 100],
        );

        $pages[] = $this->seed($module->id, $ktu->id, 'TUN-KTU-01', 'Senarai Tabung Utama (Konfigurasi)',
            'pages/pengurusan-tunai/konfigurasi/konfigurasi-tabung-utama/senarai-tabung-utama/index.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Kod Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Nama Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Had Amaun (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-utama', 'description' => 'Senarai konfigurasi tabung utama']],
        );

        $pages[] = $this->seed($module->id, $ktu->id, 'TUN-KTU-02', 'Tambah Tabung Utama',
            'pages/pengurusan-tunai/konfigurasi/konfigurasi-tabung-utama/senarai-tabung-utama/tambah/index.vue', 20,
            [$admin->id],
            [
                ['label' => 'Kod Tabung', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Nama Tabung', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Had Amaun (RM)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Keterangan', 'type' => 'Textarea'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-utama', 'description' => 'Tambah konfigurasi tabung utama baharu']],
        );

        $pages[] = $this->seed($module->id, $ktu->id, 'TUN-KTU-03', 'Butiran Tabung Utama',
            'pages/pengurusan-tunai/konfigurasi/konfigurasi-tabung-utama/senarai-tabung-utama/butiran/[id]/index.vue', 30,
            [$admin->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Kod Tabung', 'type' => 'Text'],
                ['label' => 'Nama Tabung', 'type' => 'Text'],
                ['label' => 'Had Amaun (RM)', 'type' => 'Text'],
                ['label' => 'Baki Semasa (RM)', 'type' => 'Text'],
                ['label' => 'Status', 'type' => 'Badge'],
                ['label' => 'Keterangan', 'type' => 'Textarea'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-utama/{id}', 'description' => 'Butiran konfigurasi tabung utama']],
        );

        $pages[] = $this->seed($module->id, $ktu->id, 'TUN-KTU-04', 'Tabung PIC Tabung Utama',
            'pages/pengurusan-tunai/konfigurasi/konfigurasi-tabung-utama/senarai-tabung-utama/tabung_pic/[id]/index.vue', 40,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Pegawai PIC', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Had Agihan (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-utama/{id}/tabung-pic', 'description' => 'Senarai PIC tabung utama']],
        );

        $pages[] = $this->seed($module->id, $ktu->id, 'TUN-KTU-05', 'Semakan Kelulusan Tabung Utama',
            'pages/pengurusan-tunai/konfigurasi/konfigurasi-tabung-utama/semakan-kelulusan-tabung-utama/index.vue', 50,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Kod Tabung', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Nama Tabung', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Had Amaun (RM)', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Kod Tabung', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi/tabung-utama/semakan-kelulusan', 'description' => 'Semakan kelulusan konfigurasi tabung utama']],
        );

        // ── Sub-module: KUP — Konfigurasi Tabung Utama PIC ────────────────────
        $kup = RtmfSubModule::firstOrCreate(
            ['code' => 'TUN-KUP'],
            ['name' => 'Konfigurasi Tabung Utama PIC', 'module_id' => $module->id, 'sort_order' => 110],
        );

        $pages[] = $this->seed($module->id, $kup->id, 'TUN-KUP-01', 'Konfigurasi Tabung Utama PIC (Indeks)',
            'pages/pengurusan-tunai/konfigurasi-tabung-utama-pic/index.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['label' => 'Pautan Konfigurasi Tabung Utama PIC', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung-utama-pic', 'description' => 'Halaman indeks konfigurasi tabung utama PIC']],
        );

        $pages[] = $this->seed($module->id, $kup->id, 'TUN-KUP-02', 'Senarai Tabung Utama PIC',
            'pages/pengurusan-tunai/konfigurasi-tabung-utama-pic/senarai-tabung-utama-pic/index.vue', 20,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Pegawai PIC', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tabung Utama', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Had Agihan (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung-utama-pic/senarai', 'description' => 'Senarai konfigurasi tabung utama PIC']],
        );

        $pages[] = $this->seed($module->id, $kup->id, 'TUN-KUP-03', 'Tambah Tabung Utama PIC',
            'pages/pengurusan-tunai/konfigurasi-tabung-utama-pic/senarai-tabung-utama-pic/tambah/index.vue', 30,
            [$admin->id, $penyelia->id],
            [
                ['label' => 'Pegawai PIC', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Tabung Utama', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Had Agihan (RM)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Tarikh Mula', 'type' => 'Date'],
                ['label' => 'Tarikh Tamat', 'type' => 'Date'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung-utama-pic/senarai', 'description' => 'Tambah konfigurasi tabung utama PIC baharu']],
        );

        $pages[] = $this->seed($module->id, $kup->id, 'TUN-KUP-04', 'Butiran Tabung Utama PIC',
            'pages/pengurusan-tunai/konfigurasi-tabung-utama-pic/senarai-tabung-utama-pic/butiran/[id]/index.vue', 40,
            [$admin->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Pegawai PIC', 'type' => 'Text'],
                ['label' => 'Tabung Utama', 'type' => 'Text'],
                ['label' => 'Had Agihan (RM)', 'type' => 'Text'],
                ['label' => 'Tarikh Mula', 'type' => 'Date'],
                ['label' => 'Tarikh Tamat', 'type' => 'Date'],
                ['label' => 'Status', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung-utama-pic/senarai/{id}', 'description' => 'Butiran konfigurasi tabung utama PIC']],
        );

        $pages[] = $this->seed($module->id, $kup->id, 'TUN-KUP-05', 'Semakan Kelulusan Tabung Utama PIC',
            'pages/pengurusan-tunai/konfigurasi-tabung-utama-pic/semakan-kelulusan-tabung-utama-pic/index.vue', 50,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Nama Pegawai PIC', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Tabung Utama', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Had Agihan (RM)', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Nama Pegawai PIC', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung-utama-pic/semakan-kelulusan', 'description' => 'Semakan kelulusan tabung utama PIC']],
        );

        // ── Sub-module: KAT — Konfigurasi Pegawai Ambilan Tunai ──────────────
        $kat = RtmfSubModule::firstOrCreate(
            ['code' => 'TUN-KAT'],
            ['name' => 'Konfigurasi Pegawai Ambilan Tunai', 'module_id' => $module->id, 'sort_order' => 120],
        );

        $pages[] = $this->seed($module->id, $kat->id, 'TUN-KAT-01', 'Senarai Pegawai Ambilan Tunai',
            'pages/pengurusan-tunai/konfigurasi-pegawai-ambilan-tunai/senarai-pegawai-ambilan/index.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Pegawai', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'No. Pekerja', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tabung', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Had Ambilan (RM)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-pegawai-ambilan-tunai', 'description' => 'Senarai konfigurasi pegawai ambilan tunai']],
        );

        $pages[] = $this->seed($module->id, $kat->id, 'TUN-KAT-02', 'Tambah Pegawai Ambilan Tunai',
            'pages/pengurusan-tunai/konfigurasi-pegawai-ambilan-tunai/senarai-pegawai-ambilan/tambah/index.vue', 20,
            [$admin->id, $penyelia->id],
            [
                ['label' => 'Pegawai', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Tabung', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Had Ambilan (RM)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Tarikh Mula', 'type' => 'Date'],
                ['label' => 'Tarikh Tamat', 'type' => 'Date'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-tunai/konfigurasi-pegawai-ambilan-tunai', 'description' => 'Tambah konfigurasi pegawai ambilan tunai baharu']],
        );

        $pages[] = $this->seed($module->id, $kat->id, 'TUN-KAT-03', 'Butiran Pegawai Ambilan Tunai',
            'pages/pengurusan-tunai/konfigurasi-pegawai-ambilan-tunai/senarai-pegawai-ambilan/butiran/[id]/index.vue', 30,
            [$admin->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Nama Pegawai', 'type' => 'Text'],
                ['label' => 'No. Pekerja', 'type' => 'Text'],
                ['label' => 'Tabung', 'type' => 'Text'],
                ['label' => 'Had Ambilan (RM)', 'type' => 'Text'],
                ['label' => 'Tarikh Mula', 'type' => 'Date'],
                ['label' => 'Tarikh Tamat', 'type' => 'Date'],
                ['label' => 'Status', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-pegawai-ambilan-tunai/{id}', 'description' => 'Butiran konfigurasi pegawai ambilan tunai']],
        );

        $pages[] = $this->seed($module->id, $kat->id, 'TUN-KAT-04', 'Semakan Kelulusan Pegawai Ambilan Tunai',
            'pages/pengurusan-tunai/konfigurasi-pegawai-ambilan-tunai/semakan-kelulusan-pegawai-ambilan/index.vue', 40,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Nama Pegawai', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Tabung', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Had Ambilan (RM)', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Nama Pegawai', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-pegawai-ambilan-tunai/semakan-kelulusan', 'description' => 'Semakan kelulusan pegawai ambilan tunai']],
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
            ['TUN-KPG-01', 'TUN-KPG-02'],
            ['TUN-KTP-01', 'TUN-KTP-02'],
            ['TUN-KTP-01', 'TUN-KTP-03'],
            ['TUN-KTP-01', 'TUN-KTP-04'],
            ['TUN-KTP-01', 'TUN-KTP-05'],
            ['TUN-KTU-01', 'TUN-KTU-02'],
            ['TUN-KTU-01', 'TUN-KTU-03'],
            ['TUN-KTU-01', 'TUN-KTU-04'],
            ['TUN-KTU-01', 'TUN-KTU-05'],
            ['TUN-KUP-01', 'TUN-KUP-02'],
            ['TUN-KUP-02', 'TUN-KUP-03'],
            ['TUN-KUP-02', 'TUN-KUP-04'],
            ['TUN-KUP-02', 'TUN-KUP-05'],
            ['TUN-KAT-01', 'TUN-KAT-02'],
            ['TUN-KAT-01', 'TUN-KAT-03'],
            ['TUN-KAT-01', 'TUN-KAT-04'],
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
