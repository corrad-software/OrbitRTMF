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

class RtmfPentadbirSistemPhase2Seeder extends Seeder
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
        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);

        // ── Sub-module: KOD — Penyelenggaraan Kod ─────────────────────────────
        $kod = RtmfSubModule::firstOrCreate(
            ['code' => 'PNT-KOD'],
            ['name' => 'Penyelenggaraan Kod', 'module_id' => $module->id, 'sort_order' => 50],
        );

        $pages = [];

        $pages[] = $this->seed($module->id, $kod->id, 'PNT-KOD-01', 'Konfigurasi Kategori Kod (Admin)',
            'pages/pentadbir-sistem/penyelenggaraan-kod/konfigurasi-kategori-kod/admin/index.vue', 10,
            [$admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Kod Kategori', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Nama Kategori', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Bilangan Sub-kod', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/kod/kategori', 'description' => 'Senarai kategori kod sistem'],
                ['method' => 'POST', 'endpoint' => '/pentadbir-sistem/kod/kategori', 'description' => 'Tambah kategori kod baharu'],
                ['method' => 'PUT', 'endpoint' => '/pentadbir-sistem/kod/kategori/{id}', 'description' => 'Kemaskini kategori kod'],
            ],
        );

        $pages[] = $this->seed($module->id, $kod->id, 'PNT-KOD-02', 'Hierarki Kategori Kod',
            'pages/pentadbir-sistem/penyelenggaraan-kod/konfigurasi-kategori-kod/hierarchy/[type]/[id]/index.vue', 20,
            [$admin->id],
            [
                ['label' => 'Kategori Induk', 'type' => 'Text'],
                ['label' => 'Senarai Sub-kod', 'type' => 'Table'],
                ['label' => 'Kod', 'type' => 'Text'],
                ['label' => 'Keterangan', 'type' => 'Text'],
                ['label' => 'Status', 'type' => 'Badge'],
                ['label' => 'Tambah Sub-kod', 'type' => 'Button'],
                ['label' => 'Kemaskini', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/kod/kategori/{id}/hierarki', 'description' => 'Paparan hierarki kategori kod dan sub-kodnya'],
                ['method' => 'POST', 'endpoint' => '/pentadbir-sistem/kod/kategori/{id}/sub', 'description' => 'Tambah sub-kod ke dalam kategori'],
            ],
        );

        $pages[] = $this->seed($module->id, $kod->id, 'PNT-KOD-03', 'Kelulusan Kategori Kod (Pelulus)',
            'pages/pentadbir-sistem/penyelenggaraan-kod/konfigurasi-kategori-kod/pelulus/index.vue', 30,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Kod Kategori', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Jenis Perubahan', 'type' => 'Badge'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Dikemaskini Oleh', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Kod Kategori', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/kod/kategori/kelulusan', 'description' => 'Senarai kelulusan perubahan kategori kod']],
        );

        $pages[] = $this->seed($module->id, $kod->id, 'PNT-KOD-04', 'Paparan Pelulus Kategori Kod',
            'pages/pentadbir-sistem/penyelenggaraan-kod/konfigurasi-kategori-kod/pelulus/pelulus-view/index.vue', 40,
            [$admin->id, $pelulus->id],
            [
                ['label' => 'Kod Kategori', 'type' => 'Text'],
                ['label' => 'Nama Kategori', 'type' => 'Text'],
                ['label' => 'Perubahan Dicadangkan', 'type' => 'Table'],
                ['label' => 'Catatan Pelulus', 'type' => 'Textarea'],
                ['label' => 'Lulus / Tolak', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pentadbir-sistem/kod/kategori/kelulusan/{id}', 'description' => 'Lulus atau tolak perubahan kategori kod']],
        );

        $pages[] = $this->seed($module->id, $kod->id, 'PNT-KOD-05', 'Sub-Kod Pelulus',
            'pages/pentadbir-sistem/penyelenggaraan-kod/konfigurasi-kategori-kod/pelulus/subKod/[id].vue', 50,
            [$admin->id, $pelulus->id],
            [
                ['label' => 'Kategori Induk', 'type' => 'Text'],
                ['label' => 'Kod', 'type' => 'Text'],
                ['label' => 'Keterangan', 'type' => 'Text'],
                ['label' => 'Jenis Perubahan', 'type' => 'Badge'],
                ['label' => 'Catatan', 'type' => 'Textarea'],
                ['label' => 'Lulus / Tolak', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/pentadbir-sistem/kod/sub/{id}/kelulusan', 'description' => 'Lulus atau tolak perubahan sub-kod']],
        );

        // ── Sub-module: MKS — Pengurusan Manual Kuasa ─────────────────────────
        $mks = RtmfSubModule::firstOrCreate(
            ['code' => 'PNT-MKS'],
            ['name' => 'Pengurusan Manual Kuasa', 'module_id' => $module->id, 'sort_order' => 60],
        );

        $pages[] = $this->seed($module->id, $mks->id, 'PNT-MKS-01', 'Senarai Manual Kuasa (Admin)',
            'pages/pentadbir-sistem/pengurusan-manual-kuasa/admin/index.vue', 10,
            [$admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'Tajuk Manual', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Versi', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Kuatkuasa', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Status', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/manual-kuasa', 'description' => 'Senarai manual kuasa sistem'],
                ['method' => 'POST', 'endpoint' => '/pentadbir-sistem/manual-kuasa', 'description' => 'Muat naik manual kuasa baharu'],
            ],
        );

        $pages[] = $this->seed($module->id, $mks->id, 'PNT-MKS-02', 'Butiran Manual Kuasa',
            'pages/pentadbir-sistem/pengurusan-manual-kuasa/admin/butiran/[id]/index.vue', 20,
            [$admin->id, $pelulus->id],
            [
                ['label' => 'Tajuk Manual', 'type' => 'Text'],
                ['label' => 'Versi', 'type' => 'Text'],
                ['label' => 'Tarikh Kuatkuasa', 'type' => 'Date'],
                ['label' => 'Fail Manual (PDF)', 'type' => 'File'],
                ['label' => 'Status', 'type' => 'Badge'],
                ['label' => 'Kemaskini', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/manual-kuasa/{id}', 'description' => 'Butiran manual kuasa'],
                ['method' => 'PUT', 'endpoint' => '/pentadbir-sistem/manual-kuasa/{id}', 'description' => 'Kemaskini manual kuasa'],
            ],
        );

        $pages[] = $this->seed($module->id, $mks->id, 'PNT-MKS-03', 'Senarai Manual Kuasa (Pelulus)',
            'pages/pentadbir-sistem/pengurusan-manual-kuasa/pelulus/index.vue', 30,
            [$admin->id, $pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Tajuk Manual', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Versi', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Dimuat Naik Oleh', 'type' => 'Text'],
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Lulus / Tolak', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'Tajuk Manual', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/manual-kuasa/kelulusan', 'description' => 'Senarai kelulusan manual kuasa']],
        );

        // ── Sub-module: NTF — Notifikasi Admin ────────────────────────────────
        $ntf = RtmfSubModule::firstOrCreate(
            ['code' => 'PNT-NTF'],
            ['name' => 'Notifikasi Admin', 'module_id' => $module->id, 'sort_order' => 70],
        );

        $pages[] = $this->seed($module->id, $ntf->id, 'PNT-NTF-01', 'Notifikasi Admin',
            'pages/pentadbir-sistem/notifikasi-admin/index.vue', 10,
            [$admin->id],
            [
                ['label' => 'Jumlah Notifikasi Terhantar', 'type' => 'Counter'],
                ['label' => 'Notifikasi Belum Dibaca', 'type' => 'Counter'],
                ['label' => 'Hantar ke Peranan', 'type' => 'Button'],
                ['label' => 'Hantar ke Pengguna', 'type' => 'Button'],
                ['label' => 'Siaran Am (Broadcast)', 'type' => 'Button'],
                ['label' => 'Templat Notifikasi', 'type' => 'Button'],
                ['label' => 'Pengemasan (Cleanup)', 'type' => 'Button'],
                ['label' => 'Rekod Penerimaan', 'type' => 'Button'],
                ['label' => 'Log Aktiviti', 'type' => 'Table'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/notifikasi/statistik', 'description' => 'Statistik dan log notifikasi admin'],
                ['method' => 'POST', 'endpoint' => '/pentadbir-sistem/notifikasi/broadcast', 'description' => 'Hantar notifikasi siaran am'],
                ['method' => 'POST', 'endpoint' => '/pentadbir-sistem/notifikasi/hantar-peranan', 'description' => 'Hantar notifikasi kepada peranan tertentu'],
                ['method' => 'POST', 'endpoint' => '/pentadbir-sistem/notifikasi/hantar-pengguna', 'description' => 'Hantar notifikasi kepada pengguna tertentu'],
            ],
        );

        // ── Sub-module: AUD — Audit & Monitoring ──────────────────────────────
        $aud = RtmfSubModule::firstOrCreate(
            ['code' => 'PNT-AUD'],
            ['name' => 'Audit & Monitoring', 'module_id' => $module->id, 'sort_order' => 80],
        );

        $pages[] = $this->seed($module->id, $aud->id, 'PNT-AUD-01', 'Audit System',
            'pages/pentadbir-sistem/audit-system/index.vue', 10,
            [$admin->id],
            [
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Pengguna', 'type' => 'Select'],
                ['label' => 'Jenis Tindakan', 'type' => 'Select'],
                ['label' => 'Cari', 'type' => 'Button'],
                ['screen_name' => 'Keputusan', 'label' => 'Tarikh Masa', 'type' => 'Date'],
                ['screen_name' => 'Keputusan', 'label' => 'Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Tindakan', 'type' => 'Badge'],
                ['screen_name' => 'Keputusan', 'label' => 'Entiti', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'IP Address', 'type' => 'Text'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/audit-system', 'description' => 'Log audit aktiviti sistem']],
        );

        $pages[] = $this->seed($module->id, $aud->id, 'PNT-AUD-02', 'Audit Trail',
            'pages/pentadbir-sistem/audit-trail/index.vue', 20,
            [$admin->id],
            [
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Modul', 'type' => 'Select'],
                ['label' => 'Pengguna', 'type' => 'Select'],
                ['label' => 'Cari', 'type' => 'Button'],
                ['screen_name' => 'Keputusan', 'label' => 'Tarikh Masa', 'type' => 'Date'],
                ['screen_name' => 'Keputusan', 'label' => 'Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Modul', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Tindakan', 'type' => 'Badge'],
                ['screen_name' => 'Keputusan', 'label' => 'Data Lama / Baharu', 'type' => 'Text'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/audit-trail', 'description' => 'Audit trail perubahan data']],
        );

        $pages[] = $this->seed($module->id, $aud->id, 'PNT-AUD-03', 'Error Logs',
            'pages/pentadbir-sistem/error-logs/index.vue', 30,
            [$admin->id],
            [
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Tahap Ralat', 'type' => 'Select'],
                ['label' => 'Cari', 'type' => 'Button'],
                ['screen_name' => 'Keputusan', 'label' => 'Tarikh Masa', 'type' => 'Date'],
                ['screen_name' => 'Keputusan', 'label' => 'Tahap', 'type' => 'Badge'],
                ['screen_name' => 'Keputusan', 'label' => 'Mesej Ralat', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Stack Trace', 'type' => 'Text'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/error-logs', 'description' => 'Log ralat sistem']],
        );

        $pages[] = $this->seed($module->id, $aud->id, 'PNT-AUD-04', 'Session Tracking',
            'pages/pentadbir-sistem/session-tracking/index.vue', 40,
            [$admin->id],
            [
                ['screen_name' => 'Aktif', 'label' => 'Nama Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Aktif', 'label' => 'IP Address', 'type' => 'Text'],
                ['screen_name' => 'Aktif', 'label' => 'Masa Log Masuk', 'type' => 'Date'],
                ['screen_name' => 'Aktif', 'label' => 'Masa Aktif Terakhir', 'type' => 'Date'],
                ['screen_name' => 'Aktif', 'label' => 'Tamatkan Sesi', 'type' => 'Button'],
                ['screen_name' => 'Semua', 'label' => 'Nama Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status Sesi', 'type' => 'Badge'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/sesi', 'description' => 'Senarai sesi pengguna aktif'],
                ['method' => 'DELETE', 'endpoint' => '/pentadbir-sistem/sesi/{id}', 'description' => 'Tamatkan sesi pengguna'],
            ],
        );

        // ── Sub-module: LAP — Statistik Laporan ───────────────────────────────
        $lap = RtmfSubModule::firstOrCreate(
            ['code' => 'PNT-LAP'],
            ['name' => 'Statistik & Laporan Sistem', 'module_id' => $module->id, 'sort_order' => 90],
        );

        $pages[] = $this->seed($module->id, $lap->id, 'PNT-LAP-01', 'Statistik Laporan (Indeks)',
            'pages/pentadbir-sistem/statistik-laporan/index.vue', 10,
            [$admin->id, $penyelia->id],
            [
                ['label' => 'Jenis Laporan', 'type' => 'Select'],
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Jana Laporan', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/statistik-laporan', 'description' => 'Indeks laporan dan statistik sistem']],
        );

        foreach ([
            ['PNT-LAP-02', 'Laporan Sistem 1', 'laporan1', 20],
            ['PNT-LAP-03', 'Laporan Sistem 2', 'laporan2', 30],
            ['PNT-LAP-04', 'Laporan Sistem 3', 'laporan3', 40],
            ['PNT-LAP-05', 'Laporan Sistem 4', 'laporan4', 50],
        ] as [$specId, $title, $folder, $sortOrder]) {
            $pages[] = $this->seed($module->id, $lap->id, $specId, $title,
                "pages/pentadbir-sistem/statistik-laporan/{$folder}/index.vue", $sortOrder,
                [$admin->id, $penyelia->id],
                [
                    ['label' => 'Tarikh Dari', 'type' => 'Date'],
                    ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                    ['label' => 'Parameter Tambahan', 'type' => 'Select'],
                    ['label' => 'Jana Laporan', 'type' => 'Button'],
                    ['label' => 'Keputusan Laporan', 'type' => 'Table'],
                ],
                [['method' => 'GET', 'endpoint' => "/pentadbir-sistem/statistik-laporan/{$folder}", 'description' => "Jana {$title}"]],
            );
        }

        // ── Sub-module: UTL — Utiliti Pengguna ────────────────────────────────
        $utl = RtmfSubModule::firstOrCreate(
            ['code' => 'PNT-UTL'],
            ['name' => 'Utiliti Pengguna', 'module_id' => $module->id, 'sort_order' => 100],
        );

        $pages[] = $this->seed($module->id, $utl->id, 'PNT-UTL-01', 'Utiliti Pengguna (Indeks)',
            'pages/pentadbir-sistem/utiliti-pengguna/index.vue', 10,
            [$admin->id],
            [
                ['label' => 'Pautan Carian Pengguna Peranan', 'type' => 'Button'],
                ['label' => 'Pautan Emel Whitelist', 'type' => 'Button'],
                ['label' => 'Pautan Kod Inspector', 'type' => 'Button'],
                ['label' => 'Pautan Profiling Carian', 'type' => 'Button'],
                ['label' => 'Pautan RUU Routing', 'type' => 'Button'],
                ['label' => 'Pautan Sync Pengguna', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/utiliti', 'description' => 'Halaman indeks utiliti pentadbir pengguna']],
        );

        $pages[] = $this->seed($module->id, $utl->id, 'PNT-UTL-02', 'Carian Pengguna Peranan',
            'pages/pentadbir-sistem/utiliti-pengguna/carian-pengguna-peranan/index.vue', 20,
            [$admin->id],
            [
                ['label' => 'Peranan', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Cawangan / Daerah', 'type' => 'Select'],
                ['label' => 'Cari', 'type' => 'Button'],
                ['screen_name' => 'Keputusan', 'label' => 'Nama Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Peranan', 'type' => 'Badge'],
                ['screen_name' => 'Keputusan', 'label' => 'Cawangan', 'type' => 'Text'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/utiliti/carian-pengguna-peranan', 'description' => 'Cari pengguna mengikut peranan']],
        );

        $pages[] = $this->seed($module->id, $utl->id, 'PNT-UTL-03', 'Emel Whitelist',
            'pages/pentadbir-sistem/utiliti-pengguna/emel-whitelist/index.vue', 30,
            [$admin->id],
            [
                ['screen_name' => 'Semua', 'label' => 'E-mel', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Domain', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Ditambah', 'type' => 'Date'],
                ['screen_name' => 'Semua', 'label' => 'Tindakan', 'type' => 'Button'],
                ['label' => 'Tambah E-mel / Domain', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Tambah', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/utiliti/emel-whitelist', 'description' => 'Senarai domain/emel dalam whitelist'],
                ['method' => 'POST', 'endpoint' => '/pentadbir-sistem/utiliti/emel-whitelist', 'description' => 'Tambah emel/domain ke whitelist'],
                ['method' => 'DELETE', 'endpoint' => '/pentadbir-sistem/utiliti/emel-whitelist/{id}', 'description' => 'Buang daripada whitelist'],
            ],
        );

        $pages[] = $this->seed($module->id, $utl->id, 'PNT-UTL-04', 'Kod Inspector',
            'pages/pentadbir-sistem/utiliti-pengguna/kod-inspector/index.vue', 40,
            [$admin->id],
            [
                ['label' => 'Kategori Kod', 'type' => 'Select'],
                ['label' => 'Cari Kod', 'type' => 'Text'],
                ['label' => 'Cari', 'type' => 'Button'],
                ['screen_name' => 'Keputusan', 'label' => 'Kod', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Keterangan', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Kategori', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Status', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/utiliti/kod-inspector', 'description' => 'Semak dan cari kod sistem']],
        );

        $pages[] = $this->seed($module->id, $utl->id, 'PNT-UTL-05', 'Profiling Carian',
            'pages/pentadbir-sistem/utiliti-pengguna/profiling-carian/index.vue', 50,
            [$admin->id],
            [
                ['label' => 'Tarikh Dari', 'type' => 'Date'],
                ['label' => 'Tarikh Hingga', 'type' => 'Date'],
                ['label' => 'Pengguna', 'type' => 'Select'],
                ['label' => 'Modul', 'type' => 'Select'],
                ['label' => 'Cari', 'type' => 'Button'],
                ['screen_name' => 'Keputusan', 'label' => 'Tarikh Masa', 'type' => 'Date'],
                ['screen_name' => 'Keputusan', 'label' => 'Pengguna', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Kata Kunci Carian', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Modul', 'type' => 'Text'],
            ],
            [['method' => 'GET', 'endpoint' => '/pentadbir-sistem/utiliti/profiling-carian', 'description' => 'Profil dan log aktiviti carian pengguna']],
        );

        $pages[] = $this->seed($module->id, $utl->id, 'PNT-UTL-06', 'RUU Routing',
            'pages/pentadbir-sistem/utiliti-pengguna/ruu-routing.vue', 60,
            [$admin->id],
            [
                ['label' => 'Modul Asal', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Modul Destinasi', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Peraturan Routing', 'type' => 'Textarea'],
                ['label' => 'Simpan Routing', 'type' => 'Button'],
                ['screen_name' => 'Senarai Routing', 'label' => 'Modul Asal', 'type' => 'Text'],
                ['screen_name' => 'Senarai Routing', 'label' => 'Modul Destinasi', 'type' => 'Text'],
                ['screen_name' => 'Senarai Routing', 'label' => 'Tindakan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/utiliti/ruu-routing', 'description' => 'Konfigurasi routing RUU antara modul'],
                ['method' => 'POST', 'endpoint' => '/pentadbir-sistem/utiliti/ruu-routing', 'description' => 'Simpan peraturan RUU routing'],
            ],
        );

        $pages[] = $this->seed($module->id, $utl->id, 'PNT-UTL-07', 'Sync Pengguna',
            'pages/pentadbir-sistem/utiliti-pengguna/sync-pengguna/index.vue', 70,
            [$admin->id],
            [
                ['label' => 'Sumber Data', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Mod Sinkronisasi', 'type' => 'Select'],
                ['label' => 'Mulakan Sync', 'type' => 'Button'],
                ['label' => 'Status Sync', 'type' => 'Badge'],
                ['label' => 'Log Sync', 'type' => 'Table'],
                ['label' => 'Bilangan Rekod Diproses', 'type' => 'Counter'],
                ['label' => 'Ralat Sync', 'type' => 'Counter'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/pentadbir-sistem/utiliti/sync-pengguna/status', 'description' => 'Status sync pengguna terkini'],
                ['method' => 'POST', 'endpoint' => '/pentadbir-sistem/utiliti/sync-pengguna', 'description' => 'Mulakan proses sinkronisasi data pengguna'],
            ],
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
            ['PNT-KOD-01', 'PNT-KOD-02'],
            ['PNT-KOD-01', 'PNT-KOD-03'],
            ['PNT-KOD-03', 'PNT-KOD-04'],
            ['PNT-KOD-03', 'PNT-KOD-05'],
            ['PNT-MKS-01', 'PNT-MKS-02'],
            ['PNT-MKS-01', 'PNT-MKS-03'],
            ['PNT-LAP-01', 'PNT-LAP-02'],
            ['PNT-LAP-01', 'PNT-LAP-03'],
            ['PNT-LAP-01', 'PNT-LAP-04'],
            ['PNT-LAP-01', 'PNT-LAP-05'],
            ['PNT-UTL-01', 'PNT-UTL-02'],
            ['PNT-UTL-01', 'PNT-UTL-03'],
            ['PNT-UTL-01', 'PNT-UTL-04'],
            ['PNT-UTL-01', 'PNT-UTL-05'],
            ['PNT-UTL-01', 'PNT-UTL-06'],
            ['PNT-UTL-01', 'PNT-UTL-07'],
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
