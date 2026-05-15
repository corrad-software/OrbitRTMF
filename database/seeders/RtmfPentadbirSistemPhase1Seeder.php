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

class RtmfPentadbirSistemPhase1Seeder extends Seeder
{
    public function run(): void
    {
        $project = RtmfProject::first();

        $module = RtmfModule::firstOrCreate(
            ['code' => 'PNT'],
            ['name' => 'Pentadbir Sistem', 'project_id' => $project->id, 'sort_order' => 60],
        );
        if (! $module->project_id) {
            $module->update(['project_id' => $project->id]);
        }

        $admin    = RtmfActor::firstOrCreate(['name' => 'Admin']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);

        // ── Sub-module: DSH — Dashboard ────────────────────────────────────────
        $dsh = RtmfSubModule::firstOrCreate(
            ['code' => 'PNT-DSH'],
            ['name' => 'Dashboard Pentadbir', 'module_id' => $module->id, 'sort_order' => 10],
        );

        $pages = [];

        $pages[] = $this->seed($module->id, $dsh->id, 'PNT-DSH-01', 'Dashboard Pentadbir Sistem',
            'pages/pentadbir-sistem/dashboard/index.vue', 10,
            [$admin->id],
            [
                ['label' => 'Jumlah Pengguna Aktif', 'type' => 'Counter'],
                ['label' => 'Jumlah Log Masuk Hari Ini', 'type' => 'Counter'],
                ['label' => 'Ralat Sistem', 'type' => 'Counter'],
                ['label' => 'Graf Aktiviti Pengguna', 'type' => 'Chart'],
                ['label' => 'Sesi Aktif', 'type' => 'Table'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/dashboard', 'description' => 'Dashboard ringkasan aktiviti sistem']],
        );

        // ── Sub-module: PNG — Pengurusan Pengguna ─────────────────────────────
        $png = RtmfSubModule::firstOrCreate(
            ['code' => 'PNT-PNG'],
            ['name' => 'Pengurusan Pengguna', 'module_id' => $module->id, 'sort_order' => 20],
        );

        $pages[] = $this->seed($module->id, $png->id, 'PNT-PNG-01', 'Senarai Pengguna',
            'pages/pentadbir-sistem/senarai-pengguna/index.vue', 10,
            [$admin->id],
            [
                ['screen_name' => 'Aktif', 'label' => 'Nama Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Aktif', 'label' => 'E-mel', 'type' => 'Text'],
                ['screen_name' => 'Aktif', 'label' => 'Peranan', 'type' => 'Badge'],
                ['screen_name' => 'Aktif', 'label' => 'Tarikh Daftar', 'type' => 'Date'],
                ['screen_name' => 'Aktif', 'label' => 'Tindakan', 'type' => 'Button'],
                ['screen_name' => 'Tidak Aktif', 'label' => 'Nama Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Tidak Aktif', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Nama Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Peranan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/pengguna', 'description' => 'Senarai semua pengguna sistem']],
        );

        $pages[] = $this->seed($module->id, $png->id, 'PNT-PNG-02', 'Carian Pengguna',
            'pages/pentadbir-sistem/carian-pengguna/index.vue', 20,
            [$admin->id],
            [
                ['label' => 'Nama / E-mel / No. KP', 'type' => 'Text'],
                ['label' => 'Peranan', 'type' => 'Select'],
                ['label' => 'Status', 'type' => 'Select'],
                ['label' => 'Cari', 'type' => 'Button'],
                ['label' => 'Keputusan Carian', 'type' => 'Table'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/pengguna/carian', 'description' => 'Carian pengguna dengan pelbagai kriteria']],
        );

        $pages[] = $this->seed($module->id, $png->id, 'PNT-PNG-03', 'Daftar Pengguna Baru',
            'pages/pentadbir-sistem/daftar-pengguna-baru/index.vue', 30,
            [$admin->id],
            [
                ['label' => 'Nama Penuh', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'E-mel', 'type' => 'Email', 'mandatory' => true],
                ['label' => 'No. Telefon', 'type' => 'Tel'],
                ['label' => 'Peranan', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Kata Laluan Sementara', 'type' => 'Password', 'mandatory' => true],
                ['label' => 'Daftar', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pentadbir-sistem/pengguna', 'description' => 'Daftar akaun pengguna baharu']],
        );

        $pages[] = $this->seed($module->id, $png->id, 'PNT-PNG-04', 'Kemaskini Pengguna',
            'pages/pentadbir-sistem/kemaskini-pengguna/index.vue', 40,
            [$admin->id],
            [
                ['label' => 'Nama Penuh', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'E-mel', 'type' => 'Email', 'mandatory' => true],
                ['label' => 'No. Telefon', 'type' => 'Tel'],
                ['label' => 'Peranan', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Status Akaun', 'type' => 'Select'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/pengguna/{id}', 'description' => 'Muatkan data pengguna untuk kemaskini'],
                ['method' => 'PUT', 'endpoint' => '/pentadbir-sistem/pengguna/{id}', 'description' => 'Kemaskini maklumat pengguna'],
            ],
        );

        $pages[] = $this->seed($module->id, $png->id, 'PNT-PNG-05', 'Kelulusan Peranan Pengguna',
            'pages/pentadbir-sistem/kelulusan-peranan-pengguna/index.vue', 50,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Nama Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Peranan Dimohon', 'type' => 'Badge'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Tarikh Permohonan', 'type' => 'Date'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Nama Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/pengguna/kelulusan-peranan', 'description' => 'Senarai permohonan kelulusan peranan pengguna']],
        );

        $pages[] = $this->seed($module->id, $png->id, 'PNT-PNG-06', 'Senarai Pegawai',
            'pages/pentadbir-sistem/pegawai/senarai-pegawai/index.vue', 60,
            [$admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'No. Pekerja', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Nama Pegawai', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jawatan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Cawangan / Daerah', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/pegawai', 'description' => 'Senarai pegawai dalam sistem']],
        );

        $pages[] = $this->seed($module->id, $png->id, 'PNT-PNG-07', 'Maklumat Pegawai',
            'pages/pentadbir-sistem/pegawai/maklumat-pegawai/index.vue', 70,
            [$admin->id],
            [
                ['label' => 'No. Pekerja', 'type' => 'Text'],
                ['label' => 'Nama Pegawai', 'type' => 'Text'],
                ['label' => 'Jawatan', 'type' => 'Text'],
                ['label' => 'Cawangan / Daerah', 'type' => 'Text'],
                ['label' => 'Peranan Sistem', 'type' => 'Badge'],
                ['label' => 'Status Akaun', 'type' => 'Badge'],
                ['label' => 'Sejarah Log Masuk', 'type' => 'Table'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/pegawai/{id}', 'description' => 'Maklumat lengkap pegawai']],
        );

        // ── Sub-module: KNF — Konfigurasi Sistem ──────────────────────────────
        $knf = RtmfSubModule::firstOrCreate(
            ['code' => 'PNT-KNF'],
            ['name' => 'Konfigurasi Sistem', 'module_id' => $module->id, 'sort_order' => 30],
        );

        $pages[] = $this->seed($module->id, $knf->id, 'PNT-KNF-01', 'Konfigurasi Umum Sistem',
            'pages/pentadbir-sistem/konfigurasi/index.vue', 10,
            [$admin->id],
            [
                ['label' => 'Nama Sistem', 'type' => 'Text'],
                ['label' => 'Versi Sistem', 'type' => 'Text'],
                ['label' => 'E-mel Pentadbir', 'type' => 'Email'],
                ['label' => 'Tempoh Sesi (minit)', 'type' => 'Number'],
                ['label' => 'Mod Penyelenggaraan', 'type' => 'Toggle'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/konfigurasi', 'description' => 'Muatkan konfigurasi sistem'],
                ['method' => 'PUT', 'endpoint' => '/pentadbir-sistem/konfigurasi', 'description' => 'Simpan konfigurasi sistem'],
            ],
        );

        $pages[] = $this->seed($module->id, $knf->id, 'PNT-KNF-02', 'Konfigurasi Menu',
            'pages/pentadbir-sistem/konfigurasi-menu/index.vue', 20,
            [$admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Menu', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Ikon', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'URL', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Peranan Akses', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Susunan', 'type' => 'Number'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/konfigurasi-menu', 'description' => 'Senarai konfigurasi menu navigasi'],
                ['method' => 'POST', 'endpoint' => '/pentadbir-sistem/konfigurasi-menu', 'description' => 'Tambah menu baharu'],
                ['method' => 'PUT', 'endpoint' => '/pentadbir-sistem/konfigurasi-menu/{id}', 'description' => 'Kemaskini menu'],
            ],
        );

        $pages[] = $this->seed($module->id, $knf->id, 'PNT-KNF-03', 'Kelulusan Konfigurasi Menu',
            'pages/pentadbir-sistem/konfigurasi-menu/pelulus/index.vue', 30,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Nama Menu', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Jenis Perubahan', 'type' => 'Badge'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Dikemaskini Oleh', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Nama Menu', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/konfigurasi-menu/kelulusan', 'description' => 'Semakan kelulusan perubahan konfigurasi menu']],
        );

        $pages[] = $this->seed($module->id, $knf->id, 'PNT-KNF-04', 'Konfigurasi Peranan',
            'pages/pentadbir-sistem/konfigurasi-peranan/index.vue', 40,
            [$admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Peranan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Kod Peranan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Bilangan Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/konfigurasi-peranan', 'description' => 'Senarai konfigurasi peranan pengguna'],
                ['method' => 'POST', 'endpoint' => '/pentadbir-sistem/konfigurasi-peranan', 'description' => 'Tambah peranan baharu'],
                ['method' => 'PUT', 'endpoint' => '/pentadbir-sistem/konfigurasi-peranan/{id}', 'description' => 'Kemaskini peranan'],
            ],
        );

        $pages[] = $this->seed($module->id, $knf->id, 'PNT-KNF-05', 'Kelulusan Konfigurasi Peranan',
            'pages/pentadbir-sistem/konfigurasi-peranan/pelulus/index.vue', 50,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Nama Peranan', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Jenis Perubahan', 'type' => 'Badge'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Dikemaskini Oleh', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Nama Peranan', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/konfigurasi-peranan/kelulusan', 'description' => 'Semakan kelulusan perubahan konfigurasi peranan']],
        );

        $pages[] = $this->seed($module->id, $knf->id, 'PNT-KNF-06', 'Senarai Konfigurasi Rekod',
            'pages/pentadbir-sistem/konfigurasi-rekod/index.vue', 60,
            [$admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Rekod', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jadual DB', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tempoh Simpan (hari)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/konfigurasi-rekod', 'description' => 'Senarai konfigurasi pengurusan rekod']],
        );

        $pages[] = $this->seed($module->id, $knf->id, 'PNT-KNF-07', 'Tambah Konfigurasi Rekod',
            'pages/pentadbir-sistem/konfigurasi-rekod/create.vue', 70,
            [$admin->id],
            [
                ['label' => 'Nama Rekod', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Jadual DB', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Tempoh Simpan (hari)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Dasar Arkib', 'type' => 'Select'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pentadbir-sistem/konfigurasi-rekod', 'description' => 'Tambah konfigurasi rekod baharu']],
        );

        // ── Sub-module: KSL — Konfigurasi Level Kelulusan SLA ─────────────────
        $ksl = RtmfSubModule::firstOrCreate(
            ['code' => 'PNT-KSL'],
            ['name' => 'Konfigurasi Level Kelulusan SLA', 'module_id' => $module->id, 'sort_order' => 40],
        );

        $pages[] = $this->seed($module->id, $ksl->id, 'PNT-KSL-01', 'Konfigurasi Level Kelulusan SLA (Indeks)',
            'pages/pentadbir-sistem/konfigurasi-level-kelulusan-sla/index.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['label' => 'Pautan ke Senarai Konfigurasi SLA', 'type' => 'Button'],
                ['label' => 'Pautan ke Kelulusan SLA', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/konfigurasi-sla', 'description' => 'Halaman indeks konfigurasi level kelulusan SLA']],
        );

        $pages[] = $this->seed($module->id, $ksl->id, 'PNT-KSL-02', 'Senarai Konfigurasi SLA (Admin)',
            'pages/pentadbir-sistem/konfigurasi-level-kelulusan-sla/admin/index.vue', 20,
            [$admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Modul', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tahap Kelulusan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'SLA (jam)', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Peranan Pelulus', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/konfigurasi-sla/admin', 'description' => 'Senarai konfigurasi level kelulusan SLA (admin)']],
        );

        $pages[] = $this->seed($module->id, $ksl->id, 'PNT-KSL-03', 'Tambah Konfigurasi SLA',
            'pages/pentadbir-sistem/konfigurasi-level-kelulusan-sla/admin/tambah/index.vue', 30,
            [$admin->id],
            [
                ['label' => 'Modul', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Sub-modul', 'type' => 'Select'],
                ['label' => 'Tahap Kelulusan', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Peranan Pelulus', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'SLA (jam)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pentadbir-sistem/konfigurasi-sla', 'description' => 'Tambah konfigurasi level kelulusan SLA baharu']],
        );

        $pages[] = $this->seed($module->id, $ksl->id, 'PNT-KSL-04', 'Kemaskini Konfigurasi SLA',
            'pages/pentadbir-sistem/konfigurasi-level-kelulusan-sla/admin/kemaskini/[id].vue', 40,
            [$admin->id],
            [
                ['label' => 'Modul', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Tahap Kelulusan', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Peranan Pelulus', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'SLA (jam)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Kemaskini', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/konfigurasi-sla/{id}', 'description' => 'Muatkan data konfigurasi SLA untuk kemaskini'],
                ['method' => 'PUT', 'endpoint' => '/pentadbir-sistem/konfigurasi-sla/{id}', 'description' => 'Kemaskini konfigurasi level kelulusan SLA'],
            ],
        );

        $pages[] = $this->seed($module->id, $ksl->id, 'PNT-KSL-05', 'Senarai Kelulusan SLA (Pelulus)',
            'pages/pentadbir-sistem/konfigurasi-level-kelulusan-sla/pelulus/index.vue', 50,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Modul', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Tahap Kelulusan', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'SLA (jam)', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Tindakan', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Modul', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/konfigurasi-sla/kelulusan', 'description' => 'Senarai kelulusan konfigurasi SLA']],
        );

        $pages[] = $this->seed($module->id, $ksl->id, 'PNT-KSL-06', 'Butiran Kelulusan SLA',
            'pages/pentadbir-sistem/konfigurasi-level-kelulusan-sla/pelulus/kelulusan-pelulus/[id].vue', 60,
            [$admin->id, $pelulus->id],
            [
                ['label' => 'Modul', 'type' => 'Text'],
                ['label' => 'Tahap Kelulusan', 'type' => 'Text'],
                ['label' => 'Peranan Pelulus', 'type' => 'Text'],
                ['label' => 'SLA (jam)', 'type' => 'Text'],
                ['label' => 'Catatan', 'type' => 'Textarea'],
                ['label' => 'Lulus / Tolak', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pentadbir-sistem/konfigurasi-sla/kelulusan/{id}', 'description' => 'Lulus atau tolak konfigurasi SLA']],
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
            ['PNT-PNG-01', 'PNT-PNG-04'],
            ['PNT-PNG-01', 'PNT-PNG-07'],
            ['PNT-PNG-06', 'PNT-PNG-07'],
            ['PNT-KNF-02', 'PNT-KNF-03'],
            ['PNT-KNF-04', 'PNT-KNF-05'],
            ['PNT-KNF-06', 'PNT-KNF-07'],
            ['PNT-KSL-01', 'PNT-KSL-02'],
            ['PNT-KSL-01', 'PNT-KSL-05'],
            ['PNT-KSL-02', 'PNT-KSL-03'],
            ['PNT-KSL-02', 'PNT-KSL-04'],
            ['PNT-KSL-05', 'PNT-KSL-06'],
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
