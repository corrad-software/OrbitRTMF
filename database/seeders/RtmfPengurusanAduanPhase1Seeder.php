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

class RtmfPengurusanAduanPhase1Seeder extends Seeder
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

        // ── Sub-module: DSH — Dashboard ────────────────────────────────────────
        $dsh = RtmfSubModule::firstOrCreate(
            ['code' => 'ADU-DSH'],
            ['name' => 'Dashboard Aduan', 'module_id' => $module->id, 'sort_order' => 10],
        );

        $pages = [];

        $pages[] = $this->seed($module->id, $dsh->id, 'ADU-DSH-01', 'Dashboard Pengurusan Aduan',
            'pages/pengurusan-aduan/dashboard/index.vue', 10,
            [$pegawai->id, $penyelia->id, $pelulus->id, $admin->id],
            [
                ['label' => 'Jumlah Aduan Baru', 'type' => 'Counter'],
                ['label' => 'Jumlah Aduan Dalam Proses', 'type' => 'Counter'],
                ['label' => 'Jumlah Aduan Selesai', 'type' => 'Counter'],
                ['label' => 'Graf Trend Aduan', 'type' => 'Chart'],
                ['label' => 'Aduan Mengikut Status', 'type' => 'Chart'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/dashboard', 'description' => 'Dashboard statistik pengurusan aduan']],
        );

        // ── Sub-module: ADU — Senarai Aduan ───────────────────────────────────
        $adu = RtmfSubModule::firstOrCreate(
            ['code' => 'ADU-SNR'],
            ['name' => 'Senarai Aduan', 'module_id' => $module->id, 'sort_order' => 20],
        );

        $pages[] = $this->seed($module->id, $adu->id, 'ADU-SNR-01', 'Senarai Aduan',
            'pages/pengurusan-aduan/senarai/index.vue', 10,
            [$pegawai->id, $penyelia->id, $pelulus->id, $admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Nama Pengadu', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Kategori Masalah', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Aduan', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Butiran', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/senarai', 'description' => 'Senarai semua aduan']],
        );

        $pages[] = $this->seed($module->id, $adu->id, 'ADU-SNR-02', 'Senarai Aduan (Modul Aduan)',
            'pages/pengurusan-aduan/senarai-aduan/index.vue', 20,
            [$pegawai->id, $penyelia->id, $pelulus->id, $admin->id],
            [
                ['screen_name' => 'Baharu', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Nama Pengadu', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Kategori Masalah', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Tarikh Aduan', 'type' => 'Date'],
                ['screen_name' => 'Baharu', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Baharu', 'label' => 'Butiran', 'type' => 'Button'],
                ['screen_name' => 'Dalam Proses', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Dalam Proses', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Selesai', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan', 'description' => 'Senarai aduan dengan tab status']],
        );

        $pages[] = $this->seed($module->id, $adu->id, 'ADU-SNR-03', 'Senarai Aduan Saya',
            'pages/pengurusan-aduan/senarai-aduan-saya/index.vue', 30,
            [$pegawai->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Nama Pengadu', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Kategori Masalah', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Diterima', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Butiran', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/senarai-aduan-saya', 'description' => 'Senarai aduan yang dipertanggungjawabkan kepada pegawai semasa']],
        );

        $pages[] = $this->seed($module->id, $adu->id, 'ADU-SNR-04', 'Senarai Aduan Terbuka',
            'pages/pengurusan-aduan/senarai-aduan-terbuka/index.vue', 40,
            [$pegawai->id, $penyelia->id, $admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Nama Pengadu', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Kategori Masalah', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Aduan', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Agih Tugas', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/senarai-aduan-terbuka', 'description' => 'Senarai aduan terbuka belum diagihkan']],
        );

        $pages[] = $this->seed($module->id, $adu->id, 'ADU-SNR-05', 'Senarai Aduan Pegawai',
            'pages/pengurusan-aduan/senarai-aduan-pegawai/index.vue', 50,
            [$penyelia->id, $admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Nama Pegawai', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jumlah Aduan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Aduan Selesai', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Aduan Dalam Proses', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Lihat Aduan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/senarai-aduan-pegawai', 'description' => 'Senarai aduan mengikut pegawai']],
        );

        $pages[] = $this->seed($module->id, $adu->id, 'ADU-SNR-06', 'Senarai Aduan Kelas 1',
            'pages/pengurusan-aduan/senarai-aduan-kelas-1/index.vue', 60,
            [$pegawai->id, $penyelia->id, $admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Nama Pengadu', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Kategori Masalah', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Kelas Aduan', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Aduan', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Butiran', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/senarai-aduan-kelas-1', 'description' => 'Senarai aduan berkelas tinggi (kelas 1)']],
        );

        // ── Sub-module: TGS — Tugasan ──────────────────────────────────────────
        $tgs = RtmfSubModule::firstOrCreate(
            ['code' => 'ADU-TGS'],
            ['name' => 'Tugasan Aduan', 'module_id' => $module->id, 'sort_order' => 30],
        );

        $pages[] = $this->seed($module->id, $tgs->id, 'ADU-TGS-01', 'Senarai Tugasan PAK',
            'pages/pengurusan-aduan/senarai-tugasan-pak/index.vue', 10,
            [$pegawai->id, $penyelia->id],
            [
                ['screen_name' => 'Baharu', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Nama Pengadu', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Kategori Masalah', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Tarikh Terima', 'type' => 'Date'],
                ['screen_name' => 'Baharu', 'label' => 'Tindakan', 'type' => 'Button'],
                ['screen_name' => 'Dalam Proses', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Dalam Proses', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Selesai', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Status', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/senarai-tugasan-pak', 'description' => 'Senarai tugasan PAK (Pegawai Aduan Khas)']],
        );

        $pages[] = $this->seed($module->id, $tgs->id, 'ADU-TGS-02', 'Senarai Tugasan SSU',
            'pages/pengurusan-aduan/senarai-tugasan-ssu/index.vue', 20,
            [$pegawai->id, $penyelia->id],
            [
                ['screen_name' => 'Baharu', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Nama Pengadu', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Kategori Masalah', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Tarikh Terima', 'type' => 'Date'],
                ['screen_name' => 'Baharu', 'label' => 'Tindakan', 'type' => 'Button'],
                ['screen_name' => 'Dalam Proses', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'No. Aduan', 'type' => 'Text'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/senarai-tugasan-ssu', 'description' => 'Senarai tugasan SSU (Sokongan Sosial Unit)']],
        );

        $pages[] = $this->seed($module->id, $tgs->id, 'ADU-TGS-03', 'Senarai Tugasan Tech',
            'pages/pengurusan-aduan/senarai-tugasan-tech/index.vue', 30,
            [$pegawai->id, $penyelia->id],
            [
                ['screen_name' => 'Baharu', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Nama Pengadu', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Kategori Masalah', 'type' => 'Text'],
                ['screen_name' => 'Baharu', 'label' => 'Tarikh Terima', 'type' => 'Date'],
                ['screen_name' => 'Baharu', 'label' => 'Tindakan', 'type' => 'Button'],
                ['screen_name' => 'Dalam Proses', 'label' => 'No. Aduan', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'No. Aduan', 'type' => 'Text'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/senarai-tugasan-tech', 'description' => 'Senarai tugasan teknikal']],
        );

        // ── Sub-module: DAF — Daftar Aduan ────────────────────────────────────
        $daf = RtmfSubModule::firstOrCreate(
            ['code' => 'ADU-DAF'],
            ['name' => 'Daftar Aduan', 'module_id' => $module->id, 'sort_order' => 40],
        );

        $pages[] = $this->seed($module->id, $daf->id, 'ADU-DAF-01', 'Daftar Aduan',
            'pages/pengurusan-aduan/daftar-aduan/index.vue', 10,
            [$pegawai->id, $penyelia->id, $admin->id],
            [
                ['label' => 'Nama Pengadu', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Telefon', 'type' => 'Tel'],
                ['label' => 'E-mel', 'type' => 'Email'],
                ['label' => 'Kategori Masalah', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Penerangan Aduan', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Alamat', 'type' => 'Textarea'],
                ['label' => 'Lampiran', 'type' => 'File'],
                ['label' => 'Hantar Aduan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-aduan/kategori-masalah', 'description' => 'Senarai kategori masalah untuk dropdown'],
                ['method' => 'POST', 'endpoint' => '/pengurusan-aduan/daftar-aduan', 'description' => 'Hantar pendaftaran aduan baharu'],
            ],
        );

        $pages[] = $this->seed($module->id, $daf->id, 'ADU-DAF-02', 'Daftar Pengguna Aduan',
            'pages/pengurusan-aduan/daftar-aduan/daftar-pengguna/index.vue', 20,
            [$pegawai->id, $penyelia->id, $admin->id],
            [
                ['label' => 'Nama Pengguna', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Telefon', 'type' => 'Tel', 'mandatory' => true],
                ['label' => 'E-mel', 'type' => 'Email'],
                ['label' => 'Kata Laluan', 'type' => 'Password', 'mandatory' => true],
                ['label' => 'Daftar', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-aduan/daftar-aduan/daftar-pengguna', 'description' => 'Daftar pengguna untuk portal aduan']],
        );

        $pages[] = $this->seed($module->id, $daf->id, 'ADU-DAF-03', 'Pengesahan Daftar Aduan',
            'pages/pengurusan-aduan/daftar-aduan/confirm-daftar/index.vue', 30,
            [$pegawai->id, $penyelia->id, $admin->id],
            [
                ['label' => 'Ringkasan Maklumat Aduan', 'type' => 'Text'],
                ['label' => 'Nama Pengadu', 'type' => 'Text'],
                ['label' => 'Kategori Masalah', 'type' => 'Text'],
                ['label' => 'Penerangan Aduan', 'type' => 'Textarea'],
                ['label' => 'Sahkan & Hantar', 'type' => 'Button'],
                ['label' => 'Kembali', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-aduan/daftar-aduan/confirm', 'description' => 'Pengesahan akhir sebelum hantar aduan']],
        );

        $pages[] = $this->seed($module->id, $daf->id, 'ADU-DAF-04', 'Berjaya Daftar Aduan',
            'pages/pengurusan-aduan/daftar-aduan/berjaya-daftar/index.vue', 40,
            [$pegawai->id, $penyelia->id, $admin->id],
            [
                ['label' => 'No. Rujukan Aduan', 'type' => 'Text'],
                ['label' => 'Mesej Kejayaan', 'type' => 'Text'],
                ['label' => 'Kembali ke Senarai', 'type' => 'Button'],
            ],
            [],
        );

        // ── Sub-module: BTR — Butiran & Workflow Aduan ────────────────────────
        $btr = RtmfSubModule::firstOrCreate(
            ['code' => 'ADU-BTR'],
            ['name' => 'Butiran Aduan', 'module_id' => $module->id, 'sort_order' => 50],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-01', 'Butiran Aduan',
            'pages/pengurusan-aduan/butiran/[id]/index.vue', 10,
            [$pegawai->id, $penyelia->id, $pelulus->id, $admin->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Nama Pengadu', 'type' => 'Text'],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text'],
                ['label' => 'No. Telefon', 'type' => 'Text'],
                ['label' => 'Kategori Masalah', 'type' => 'Text'],
                ['label' => 'Penerangan Aduan', 'type' => 'Textarea'],
                ['label' => 'Tarikh Aduan', 'type' => 'Date'],
                ['label' => 'Status', 'type' => 'Badge'],
                ['label' => 'Pegawai Bertanggungjawab', 'type' => 'Text'],
                ['label' => 'Sejarah Tindakan', 'type' => 'Table'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/{id}', 'description' => 'Butiran lengkap aduan']],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-02', 'Agih Tugas Aduan',
            'pages/pengurusan-aduan/butiran/[id]/agih-tugas/index.vue', 20,
            [$penyelia->id, $admin->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Pegawai Bertanggungjawab', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Catatan Agihan', 'type' => 'Textarea'],
                ['label' => 'Agih', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/kod/pegawai-by-lokasi', 'description' => 'Senarai pegawai untuk agihan'],
                ['method' => 'POST', 'endpoint' => '/pengurusan-aduan/{id}/agih-tugas', 'description' => 'Agih tugas aduan kepada pegawai'],
            ],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-03', 'Agih Tugas Semula Aduan',
            'pages/pengurusan-aduan/butiran/[id]/agih-tugas-semula/index.vue', 30,
            [$penyelia->id, $admin->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Sebab Agih Semula', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Pegawai Baharu', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Agih Semula', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/kod/pegawai-by-lokasi', 'description' => 'Senarai pegawai untuk agihan semula'],
                ['method' => 'POST', 'endpoint' => '/pengurusan-aduan/{id}/agih-tugas-semula', 'description' => 'Agih semula tugas aduan'],
            ],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-04', 'Quick Assessment Aduan',
            'pages/pengurusan-aduan/butiran/[id]/quick-assesment/index.vue', 40,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Penilaian Awal', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Kelas Aduan', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Tahap Keutamaan', 'type' => 'Select'],
                ['label' => 'Catatan', 'type' => 'Textarea'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-aduan/{id}/quick-assessment', 'description' => 'Simpan penilaian pantas aduan']],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-05', 'PAK Assessment Aduan',
            'pages/pengurusan-aduan/butiran/[id]/pak-assessment/index.vue', 50,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Penilaian PAK', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Skor Penilaian', 'type' => 'Number'],
                ['label' => 'Cadangan Tindakan', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Lampiran', 'type' => 'File'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-aduan/{id}/pak-assessment', 'description' => 'Simpan penilaian PAK']],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-06', 'Siasatan Ringkas Aduan',
            'pages/pengurusan-aduan/butiran/[id]/siasatan-ringkas/index.vue', 60,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Tarikh Siasatan', 'type' => 'Date', 'mandatory' => true],
                ['label' => 'Dapatan Siasatan', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Cadangan', 'type' => 'Textarea'],
                ['label' => 'Lampiran', 'type' => 'File'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-aduan/{id}/siasatan-ringkas', 'description' => 'Simpan dapatan siasatan ringkas']],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-07', 'Siasatan Lapangan Aduan',
            'pages/pengurusan-aduan/butiran/[id]/siasatan-lapangan/index.vue', 70,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Tarikh Siasatan Lapangan', 'type' => 'Date', 'mandatory' => true],
                ['label' => 'Lokasi', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Dapatan Lapangan', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Cadangan Tindakan', 'type' => 'Textarea'],
                ['label' => 'Foto / Lampiran', 'type' => 'File'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-aduan/{id}/siasatan-lapangan', 'description' => 'Simpan dapatan siasatan lapangan']],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-08', 'Daftar Asnaf dari Aduan',
            'pages/pengurusan-aduan/butiran/[id]/daftar-asnaf/index.vue', 80,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Nama', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Kategori Asnaf', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Alamat', 'type' => 'Textarea'],
                ['label' => 'Daftar sebagai Asnaf', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-aduan/{id}/daftar-asnaf', 'description' => 'Daftar pengadu sebagai asnaf baharu']],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-09', 'Kemaskini Asnaf dari Aduan',
            'pages/pengurusan-aduan/butiran/[id]/kemaskini-asnaf/index.vue', 90,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Nama', 'type' => 'Text'],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text'],
                ['label' => 'Kategori Asnaf', 'type' => 'Select'],
                ['label' => 'Alamat', 'type' => 'Textarea'],
                ['label' => 'Simpan Kemaskini', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-aduan/{id}/asnaf', 'description' => 'Muatkan data asnaf dari aduan'],
                ['method' => 'PUT', 'endpoint' => '/pengurusan-aduan/{id}/kemaskini-asnaf', 'description' => 'Kemaskini maklumat asnaf dari aduan'],
            ],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-10', 'Kemaskini Profil dari Aduan',
            'pages/pengurusan-aduan/butiran/[id]/kemaskini-profil/index.vue', 100,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Nama', 'type' => 'Text'],
                ['label' => 'No. Telefon', 'type' => 'Tel'],
                ['label' => 'E-mel', 'type' => 'Email'],
                ['label' => 'Alamat', 'type' => 'Textarea'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [['method' => 'PUT', 'endpoint' => '/pengurusan-aduan/{id}/kemaskini-profil', 'description' => 'Kemaskini profil pengadu dari halaman aduan']],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-11', 'Pendaftaran Lengkap dari Aduan',
            'pages/pengurusan-aduan/butiran/[id]/pendaftaran-lengkap/index.vue', 110,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Maklumat Peribadi', 'type' => 'Section'],
                ['label' => 'Nama', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Maklumat Kewangan', 'type' => 'Section'],
                ['label' => 'Pendapatan Bulanan (RM)', 'type' => 'Number'],
                ['label' => 'Simpan Pendaftaran', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-aduan/{id}/pendaftaran-lengkap', 'description' => 'Pendaftaran lengkap asnaf dari aduan']],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-12', 'Bantuan dari Aduan',
            'pages/pengurusan-aduan/butiran/[id]/bantuan/index.vue', 120,
            [$pegawai->id, $penyelia->id],
            [
                ['screen_name' => 'Semua', 'label' => 'No. Permohonan Bantuan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Jenis Bantuan', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Permohonan', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Mohon Bantuan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pengurusan-aduan/{id}/bantuan', 'description' => 'Senarai permohonan bantuan berkaitan aduan']],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-13', 'Mohon Bantuan dari Aduan',
            'pages/pengurusan-aduan/butiran/[id]/bantuan/mohon/index.vue', 130,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Jenis Bantuan', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Amaun Bantuan (RM)', 'type' => 'Number'],
                ['label' => 'Justifikasi', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Lampiran', 'type' => 'File'],
                ['label' => 'Mohon', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/bantuan/jenis', 'description' => 'Senarai jenis bantuan tersedia'],
                ['method' => 'POST', 'endpoint' => '/pengurusan-aduan/{id}/bantuan/mohon', 'description' => 'Mohon bantuan untuk pengadu'],
            ],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-14', 'Mohon Pengeluaran Tunai dari Aduan',
            'pages/pengurusan-aduan/butiran/[id]/pengeluaran-tunai/mohon/index.vue', 140,
            [$pegawai->id, $penyelia->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Tabung', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Amaun (RM)', 'type' => 'Number', 'mandatory' => true],
                ['label' => 'Tujuan Pengeluaran', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Lampiran', 'type' => 'File'],
                ['label' => 'Mohon', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pengurusan-tunai/konfigurasi-tabung', 'description' => 'Senarai tabung tersedia'],
                ['method' => 'POST', 'endpoint' => '/pengurusan-aduan/{id}/pengeluaran-tunai/mohon', 'description' => 'Mohon pengeluaran tunai untuk pengadu'],
            ],
        );

        $pages[] = $this->seed($module->id, $btr->id, 'ADU-BTR-15', 'Pengesah Tutup Aduan',
            'pages/pengurusan-aduan/butiran/[id]/pengesah-tutup-aduan/index.vue', 150,
            [$pegawai->id, $penyelia->id, $pelulus->id],
            [
                ['label' => 'No. Aduan', 'type' => 'Text'],
                ['label' => 'Ringkasan Tindakan', 'type' => 'Textarea'],
                ['label' => 'Keputusan Akhir', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Catatan Penutupan', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Tutup Aduan', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pengurusan-aduan/{id}/tutup', 'description' => 'Tutup aduan dengan pengesahan akhir']],
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
            ['ADU-SNR-01', 'ADU-BTR-01'],
            ['ADU-SNR-02', 'ADU-BTR-01'],
            ['ADU-SNR-03', 'ADU-BTR-01'],
            ['ADU-SNR-04', 'ADU-BTR-02'],
            ['ADU-BTR-01', 'ADU-BTR-02'],
            ['ADU-BTR-01', 'ADU-BTR-03'],
            ['ADU-BTR-01', 'ADU-BTR-04'],
            ['ADU-BTR-01', 'ADU-BTR-05'],
            ['ADU-BTR-01', 'ADU-BTR-06'],
            ['ADU-BTR-01', 'ADU-BTR-07'],
            ['ADU-BTR-01', 'ADU-BTR-08'],
            ['ADU-BTR-01', 'ADU-BTR-09'],
            ['ADU-BTR-01', 'ADU-BTR-10'],
            ['ADU-BTR-01', 'ADU-BTR-11'],
            ['ADU-BTR-01', 'ADU-BTR-12'],
            ['ADU-BTR-12', 'ADU-BTR-13'],
            ['ADU-BTR-01', 'ADU-BTR-14'],
            ['ADU-BTR-01', 'ADU-BTR-15'],
            ['ADU-DAF-01', 'ADU-DAF-03'],
            ['ADU-DAF-03', 'ADU-DAF-04'],
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
