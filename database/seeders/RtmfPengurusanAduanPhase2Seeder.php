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

class RtmfPengurusanAduanPhase2Seeder extends Seeder
{
    public function run(): void
    {
        $project = RtmfProject::first();

        $module = RtmfModule::firstOrCreate(
            ['code' => 'ADU'],
            ['name' => 'Pengurusan Aduan', 'project_id' => $project->id, 'sort_order' => 50],
        );
        if (! $module->project_id) {
            $module->update(['project_id' => $project->id]);
        }

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);
        $admin    = RtmfActor::firstOrCreate(['name' => 'Admin']);

        // ── Sub-module: LAP — Laporan ──────────────────────────────────────────
        $lap = RtmfSubModule::firstOrCreate(
            ['code' => 'ADU-LAP'],
            ['name' => 'Laporan Aduan', 'module_id' => $module->id, 'sort_order' => 60],
        );

        $pages = [];

        $pages[] = $this->seed($module->id, $lap->id, 'ADU-LAP-01', 'Laporan Aduan (Indeks)',
            'pages/pengurusan-aduan/laporan/index.vue', 10,
            [$pegawai->id, $penyelia->id, $pelulus->id, $admin->id],
            [
                ['label' => 'Jenis Laporan', 'type' => 'Select'],
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/laporan', 'description' => 'Indeks laporan pengurusan aduan']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'ADU-LAP-02', 'Laporan Prestasi Pegawai',
            'pages/pengurusan-aduan/laporan/laporan-prestasi-pegawai/index.vue', 20,
            [$penyelia->id, $pelulus->id, $admin->id],
            [
                ['label' => 'Pegawai', 'type' => 'Select'],
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Jumlah Aduan Diterima', 'type' => 'Text'],
                ['label' => 'Jumlah Aduan Selesai', 'type' => 'Text'],
                ['label' => 'Masa Penyelesaian Purata', 'type' => 'Text'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/laporan/prestasi-pegawai', 'description' => 'Laporan prestasi penyelesaian aduan mengikut pegawai']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'ADU-LAP-03', 'Laporan SLA Aduan',
            'pages/pengurusan-aduan/laporan/laporan-sla-aduan/index.vue', 30,
            [$penyelia->id, $pelulus->id, $admin->id],
            [
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Kategori Masalah', 'type' => 'Select'],
                ['label' => 'Patuhi SLA', 'type' => 'Badge'],
                ['label' => 'Langgar SLA', 'type' => 'Badge'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/laporan/sla', 'description' => 'Laporan pematuhan SLA aduan']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'ADU-LAP-04', 'Laporan Status Aduan',
            'pages/pengurusan-aduan/laporan/laporan-status-aduan/index.vue', 40,
            [$pegawai->id, $penyelia->id, $pelulus->id, $admin->id],
            [
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Status', 'type' => 'Select'],
                ['label' => 'Kategori Masalah', 'type' => 'Select'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/laporan/status', 'description' => 'Laporan status aduan mengikut tempoh']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'ADU-LAP-05', 'Laporan Taburan Aduan',
            'pages/pengurusan-aduan/laporan/laporan-taburan/index.vue', 50,
            [$penyelia->id, $pelulus->id, $admin->id],
            [
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Kawasan', 'type' => 'Select'],
                ['label' => 'Graf Taburan Geografi', 'type' => 'Chart'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/laporan/taburan', 'description' => 'Laporan taburan aduan mengikut kawasan']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'ADU-LAP-06', 'Laporan Statistik Status',
            'pages/pengurusan-aduan/laporan/laporan_statistik_status_2/index.vue', 60,
            [$penyelia->id, $pelulus->id, $admin->id],
            [
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Graf Statistik Status', 'type' => 'Chart'],
                ['label' => 'Jadual Ringkasan', 'type' => 'Table'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/laporan/statistik-status', 'description' => 'Laporan statistik status aduan']],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'ADU-LAP-07', 'Statistik Aduan',
            'pages/pengurusan-aduan/laporan/statistik-aduan/index.vue', 70,
            [$penyelia->id, $pelulus->id, $admin->id],
            [
                ['label' => 'Tempoh', 'type' => 'Select'],
                ['label' => 'Jumlah Aduan Diterima', 'type' => 'Counter'],
                ['label' => 'Jumlah Aduan Selesai', 'type' => 'Counter'],
                ['label' => 'Jumlah Aduan Dalam Proses', 'type' => 'Counter'],
                ['label' => 'Graf Trend', 'type' => 'Chart'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/laporan/statistik', 'description' => 'Paparan statistik aduan keseluruhan']],
        );

        // ── Sub-module: KFQ — Konfigurasi FAQ ─────────────────────────────────
        $kfq = RtmfSubModule::firstOrCreate(
            ['code' => 'ADU-KFQ'],
            ['name' => 'Konfigurasi FAQ', 'module_id' => $module->id, 'sort_order' => 70],
        );

        $pages[] = $this->seed($module->id, $kfq->id, 'ADU-KFQ-01', 'Senarai FAQ',
            'pages/pengurusan-aduan/konfigurasi-aduan/faq/senarai/index.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Soalan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Kategori', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/faq', 'description' => 'Senarai konfigurasi FAQ aduan']],
        );

        $pages[] = $this->seed($module->id, $kfq->id, 'ADU-KFQ-02', 'Butiran FAQ',
            'pages/pengurusan-aduan/konfigurasi-aduan/faq/senarai/butiran/[id]/index.vue', 20,
            [$admin->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Soalan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Jawapan', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Kategori', 'type' => 'Select'],
                ['label' => 'Status', 'type' => 'Select'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/faq/{id}', 'description' => 'Butiran / kemaskini FAQ'],
                ['method' => 'PUT', 'endpoint' => '/pengurusan-aduan/konfigurasi/faq/{id}', 'description' => 'Simpan kemaskini FAQ'],
            ],
        );

        $pages[] = $this->seed($module->id, $kfq->id, 'ADU-KFQ-03', 'Semakan Kelulusan FAQ',
            'pages/pengurusan-aduan/konfigurasi-aduan/faq/semakan-kelulusan/index.vue', 30,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Soalan', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Dikemaskini Oleh', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Soalan', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/faq/semakan-kelulusan', 'description' => 'Semakan kelulusan kemaskini FAQ']],
        );

        // ── Sub-module: KKM — Konfigurasi Kategori Masalah ────────────────────
        $kkm = RtmfSubModule::firstOrCreate(
            ['code' => 'ADU-KKM'],
            ['name' => 'Konfigurasi Kategori Masalah', 'module_id' => $module->id, 'sort_order' => 80],
        );

        $pages[] = $this->seed($module->id, $kkm->id, 'ADU-KKM-01', 'Senarai Tahap Aduan',
            'pages/pengurusan-aduan/konfigurasi-aduan/kategori-masalah/senarai/tahap_aduan/index.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Kod Tahap', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Nama Tahap', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Bilangan Kategori', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/tahap-aduan', 'description' => 'Senarai tahap aduan']],
        );

        $pages[] = $this->seed($module->id, $kkm->id, 'ADU-KKM-02', 'Butiran Tahap Aduan',
            'pages/pengurusan-aduan/konfigurasi-aduan/kategori-masalah/senarai/tahap_aduan/butiran/[id]/index.vue', 20,
            [$admin->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Kod Tahap', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Nama Tahap', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Penerangan', 'type' => 'Textarea'],
                ['label' => 'Status', 'type' => 'Select'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/tahap-aduan/{id}', 'description' => 'Butiran tahap aduan'],
                ['method' => 'PUT', 'endpoint' => '/pengurusan-aduan/konfigurasi/tahap-aduan/{id}', 'description' => 'Kemaskini tahap aduan'],
            ],
        );

        $pages[] = $this->seed($module->id, $kkm->id, 'ADU-KKM-03', 'Senarai Kategori Masalah',
            'pages/pengurusan-aduan/konfigurasi-aduan/kategori-masalah/senarai/tahap_aduan/kategori_masalah/index.vue', 30,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Kod Kategori', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Nama Kategori', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tahap Aduan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/kategori-masalah', 'description' => 'Senarai kategori masalah aduan']],
        );

        $pages[] = $this->seed($module->id, $kkm->id, 'ADU-KKM-04', 'Kemaskini Kategori Masalah',
            'pages/pengurusan-aduan/konfigurasi-aduan/kategori-masalah/senarai/tahap_aduan/kategori_masalah/[id]/index.vue', 40,
            [$admin->id, $penyelia->id],
            [
                ['label' => 'Kod Kategori', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Nama Kategori', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Tahap Aduan', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Penerangan', 'type' => 'Textarea'],
                ['label' => 'SLA (hari)', 'type' => 'Number'],
                ['label' => 'Kemaskini', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/kategori-masalah/{id}', 'description' => 'Muatkan data kategori masalah untuk kemaskini'],
                ['method' => 'PUT', 'endpoint' => '/pengurusan-aduan/konfigurasi/kategori-masalah/{id}', 'description' => 'Kemaskini kategori masalah'],
            ],
        );

        $pages[] = $this->seed($module->id, $kkm->id, 'ADU-KKM-05', 'Butiran Kategori Masalah',
            'pages/pengurusan-aduan/konfigurasi-aduan/kategori-masalah/senarai/tahap_aduan/kategori_masalah/butiran/[id]/index.vue', 50,
            [$admin->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Kod Kategori', 'type' => 'Text'],
                ['label' => 'Nama Kategori', 'type' => 'Text'],
                ['label' => 'Tahap Aduan', 'type' => 'Text'],
                ['label' => 'SLA (hari)', 'type' => 'Text'],
                ['label' => 'Status', 'type' => 'Badge'],
                ['label' => 'Penerangan', 'type' => 'Textarea'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/kategori-masalah/{id}/butiran', 'description' => 'Butiran kategori masalah']],
        );

        $pages[] = $this->seed($module->id, $kkm->id, 'ADU-KKM-06', 'Semakan Kelulusan Kategori Masalah',
            'pages/pengurusan-aduan/konfigurasi-aduan/kategori-masalah/semakan-kelulusan/index.vue', 60,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Nama Kategori', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Tahap Aduan', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Dikemaskini Oleh', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Nama Kategori', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/kategori-masalah/semakan-kelulusan', 'description' => 'Semakan kelulusan kemaskini kategori masalah']],
        );

        // ── Sub-module: KPN — Konfigurasi Penutupan Aduan ─────────────────────
        $kpn = RtmfSubModule::firstOrCreate(
            ['code' => 'ADU-KPN'],
            ['name' => 'Konfigurasi Penutupan Aduan', 'module_id' => $module->id, 'sort_order' => 90],
        );

        $pages[] = $this->seed($module->id, $kpn->id, 'ADU-KPN-01', 'Senarai Penutupan Aduan',
            'pages/pengurusan-aduan/konfigurasi-aduan/penutupan-aduan/senarai/index.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Kod Penutupan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Penerangan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/penutupan-aduan', 'description' => 'Senarai konfigurasi kod penutupan aduan']],
        );

        $pages[] = $this->seed($module->id, $kpn->id, 'ADU-KPN-02', 'Butiran Penutupan Aduan',
            'pages/pengurusan-aduan/konfigurasi-aduan/penutupan-aduan/senarai/butiran/[id]/index.vue', 20,
            [$admin->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Kod Penutupan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Penerangan', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Status', 'type' => 'Select'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/penutupan-aduan/{id}', 'description' => 'Butiran / kemaskini kod penutupan aduan'],
                ['method' => 'PUT', 'endpoint' => '/pengurusan-aduan/konfigurasi/penutupan-aduan/{id}', 'description' => 'Simpan kemaskini penutupan aduan'],
            ],
        );

        $pages[] = $this->seed($module->id, $kpn->id, 'ADU-KPN-03', 'Semakan Kelulusan Penutupan Aduan',
            'pages/pengurusan-aduan/konfigurasi-aduan/penutupan-aduan/semakan-kelulusan/index.vue', 30,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Kod Penutupan', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Dikemaskini Oleh', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Kod Penutupan', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/penutupan-aduan/semakan-kelulusan', 'description' => 'Semakan kelulusan konfigurasi penutupan aduan']],
        );

        // ── Sub-module: KVI — Konfigurasi Validasi Input ──────────────────────
        $kvi = RtmfSubModule::firstOrCreate(
            ['code' => 'ADU-KVI'],
            ['name' => 'Konfigurasi Validasi Input', 'module_id' => $module->id, 'sort_order' => 100],
        );

        $pages[] = $this->seed($module->id, $kvi->id, 'ADU-KVI-01', 'Senarai Validasi Input',
            'pages/pengurusan-aduan/konfigurasi-aduan/validasi-input/senarai/index.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Medan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jenis Validasi', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Mesej Ralat', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/validasi-input', 'description' => 'Senarai konfigurasi peraturan validasi input']],
        );

        $pages[] = $this->seed($module->id, $kvi->id, 'ADU-KVI-02', 'Butiran Validasi Input',
            'pages/pengurusan-aduan/konfigurasi-aduan/validasi-input/senarai/butiran/[id]/index.vue', 20,
            [$admin->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'Nama Medan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Jenis Validasi', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Nilai Min', 'type' => 'Text'],
                ['label' => 'Nilai Max', 'type' => 'Text'],
                ['label' => 'Pattern (Regex)', 'type' => 'Text'],
                ['label' => 'Mesej Ralat', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/validasi-input/{id}', 'description' => 'Butiran / kemaskini peraturan validasi'],
                ['method' => 'PUT', 'endpoint' => '/pengurusan-aduan/konfigurasi/validasi-input/{id}', 'description' => 'Simpan kemaskini validasi input'],
            ],
        );

        $pages[] = $this->seed($module->id, $kvi->id, 'ADU-KVI-03', 'Semakan Kelulusan Validasi Input',
            'pages/pengurusan-aduan/konfigurasi-aduan/validasi-input/semakan-kelulusan/index.vue', 30,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Nama Medan', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Jenis Validasi', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Dikemaskini Oleh', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Nama Medan', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/konfigurasi/validasi-input/semakan-kelulusan', 'description' => 'Semakan kelulusan konfigurasi validasi input']],
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
            ['ADU-LAP-01', 'ADU-LAP-02'],
            ['ADU-LAP-01', 'ADU-LAP-03'],
            ['ADU-LAP-01', 'ADU-LAP-04'],
            ['ADU-LAP-01', 'ADU-LAP-05'],
            ['ADU-LAP-01', 'ADU-LAP-06'],
            ['ADU-LAP-01', 'ADU-LAP-07'],
            ['ADU-KFQ-01', 'ADU-KFQ-02'],
            ['ADU-KFQ-01', 'ADU-KFQ-03'],
            ['ADU-KKM-01', 'ADU-KKM-02'],
            ['ADU-KKM-03', 'ADU-KKM-04'],
            ['ADU-KKM-03', 'ADU-KKM-05'],
            ['ADU-KKM-01', 'ADU-KKM-06'],
            ['ADU-KPN-01', 'ADU-KPN-02'],
            ['ADU-KPN-01', 'ADU-KPN-03'],
            ['ADU-KVI-01', 'ADU-KVI-02'],
            ['ADU-KVI-01', 'ADU-KVI-03'],
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
