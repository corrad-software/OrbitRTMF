<?php

namespace Database\Seeders;

use App\Models\RtmfActor;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendApiEndpoint;
use App\Models\RtmfFrontendItem;
use App\Models\RtmfModule;
use App\Models\RtmfSubModule;
use Illuminate\Database\Seeder;

class RtmfProfilingPhase4Seeder extends Seeder
{
    public function run(): void
    {
        $module = RtmfModule::where('code', 'PRF')->firstOrFail();

        $rpt = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'RPT'],
            ['name' => 'Pelaporan', 'sort_order' => 40],
        );
        $ftr = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'FTR'],
            ['name' => 'Salasilah Keluarga', 'sort_order' => 50],
        );
        $pyg = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'PYG'],
            ['name' => 'Penyelenggaraan', 'sort_order' => 60],
        );
        $dsh = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'DSH'],
            ['name' => 'Dashboard', 'sort_order' => 70],
        );

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        $staff = [$pegawai->id, $penyelia->id];
        $all   = [$pegawai->id, $penyelia->id, $pelulus->id];

        // ══════════════════════════════════════════════════════════════════
        // PELAPORAN (RPT)
        // ══════════════════════════════════════════════════════════════════

        $r01 = $this->seed($module->id, $rpt->id, 'PRF-RPT-01', 'Pelaporan',
            'pages/profiling/pelaporan/index.vue', 10, $all,
            [
                ['screen_name' => 'Asnaf',      'label' => 'Senarai Laporan Asnaf',       'type' => 'Table',  'condition' => 'Table listing available Asnaf reports with Nama Laporan column and View action button'],
                ['screen_name' => 'Organisasi', 'label' => 'Senarai Laporan Organisasi',  'type' => 'Table',  'condition' => 'Table listing available Organisasi reports with Nama Laporan column and View action button'],
                ['screen_name' => 'Recipient',  'label' => 'Senarai Laporan Recipient',   'type' => 'Table',  'condition' => 'Table listing available Recipient reports with Nama Laporan column and View action button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan', 'description' => 'Fetch list of available reports by category'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-02', 'Laporan Senarai Asnaf',
            'pages/profiling/pelaporan/laporan-senarai-asnaf/index.vue', 20, $all,
            [
                ['screen_name' => 'Laporan Senarai Asnaf', 'label' => 'Filter Daerah',         'type' => 'Select',   'condition' => 'Dropdown — Daerah (required filter)'],
                ['screen_name' => 'Laporan Senarai Asnaf', 'label' => 'Filter Kategori Asnaf', 'type' => 'Select',   'condition' => 'Dropdown — Kategori Asnaf'],
                ['screen_name' => 'Laporan Senarai Asnaf', 'label' => 'Butang Tapis',          'type' => 'Button',   'condition' => 'TAPIS button — triggers report generation'],
                ['screen_name' => 'Laporan Senarai Asnaf', 'label' => 'Jadual Laporan',        'type' => 'Table',    'condition' => 'Report result table with sortable/filterable columns'],
                ['screen_name' => 'Laporan Senarai Asnaf', 'label' => 'Butang Muat Turun',     'type' => 'Button',   'condition' => 'Download report as PDF/Excel'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/senarai-asnaf', 'description' => 'Fetch Laporan Senarai Asnaf data (params: kodDaerah, kategoriAsnaf)'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',               'description' => 'Fetch district list for filter'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-03', 'Laporan Aging Profil Asnaf',
            'pages/profiling/pelaporan/laporan-aging-asnaf/index.vue', 30, $all,
            $this->itemsReport('Laporan Aging Profil Asnaf', ['Daerah', 'Kategori Asnaf', 'Julat Umur']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/aging-asnaf', 'description' => 'Fetch aging profil asnaf report data'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',             'description' => 'Fetch district list'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-04', 'Laporan Profil Asnaf dan Tanggungan',
            'pages/profiling/pelaporan/laporan-asnaf-tanggungan/index.vue', 40, $all,
            $this->itemsReport('Laporan Profil Asnaf dan Tanggungan', ['Daerah', 'Kategori Asnaf']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/asnaf-tanggungan', 'description' => 'Fetch asnaf and tanggungan report data'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',                  'description' => 'Fetch district list'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-05', 'Senarai Pendaftaran Asnaf mengikut Tahun/Bulan',
            'pages/profiling/pelaporan/senarai-pendaftaran-asnaf/index.vue', 50, $all,
            $this->itemsReport('Senarai Pendaftaran Asnaf mengikut Tahun/Bulan', ['Tahun', 'Bulan', 'Daerah']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/pendaftaran-asnaf', 'description' => 'Fetch asnaf registration by year/month report'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',                   'description' => 'Fetch district list'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-06', 'Statistik Asnaf KK',
            'pages/profiling/pelaporan/statistik-asnaf-kk/index.vue', 60, $all,
            $this->itemsReport('Statistik Asnaf KK', ['Daerah', 'Kariah']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/statistik-asnaf-kk', 'description' => 'Fetch statistik asnaf by kariah/daerah report'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',                     'description' => 'Fetch district list'],
                ['method' => 'GET', 'endpoint' => '/organisasi/kariah',                     'description' => 'Fetch kariah list filtered by daerah'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-07', 'Laporan Pendaftaran Agensi Mengikut Jenis Organisasi',
            'pages/profiling/pelaporan/laporan-pendaftaran-agensi/index.vue', 70, $all,
            $this->itemsReport('Laporan Pendaftaran Agensi Mengikut Jenis Organisasi', ['Jenis Organisasi', 'Tahun']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/pendaftaran-agensi-jenis', 'description' => 'Fetch organisation registration by type report'],
                ['method' => 'GET', 'endpoint' => '/kod',                                        'description' => 'Fetch organisation type codes'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-08', 'Laporan Pendaftaran Agensi Mengikut Daerah',
            'pages/profiling/pelaporan/organisasi-mengikut-daerah/index.vue', 80, $all,
            $this->itemsReport('Laporan Pendaftaran Agensi Mengikut Daerah', ['Daerah', 'Jenis Organisasi']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/pendaftaran-agensi-daerah', 'description' => 'Fetch organisation registration by district report'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',                           'description' => 'Fetch district list'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-09', 'Laporan Organisasi yang Telah Tamat Tempoh Pendaftaran',
            'pages/profiling/pelaporan/organisasi-tamat-tempoh/index.vue', 90, $all,
            $this->itemsReport('Laporan Organisasi yang Telah Tamat Tempoh Pendaftaran', ['Daerah', 'Jenis Organisasi', 'Julat Tarikh']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/organisasi-tamat-tempoh', 'description' => 'Fetch expired organisation registration report'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',                         'description' => 'Fetch district list'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-10', 'Laporan Pendaftaran Agensi Mengikut Tarikh Daftar',
            'pages/profiling/pelaporan/organisasi-tarikh-daftar/index.vue', 100, $all,
            $this->itemsReport('Laporan Pendaftaran Agensi Mengikut Tarikh Daftar', ['Tarikh Dari', 'Tarikh Hingga', 'Daerah']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/pendaftaran-agensi-tarikh', 'description' => 'Fetch organisation registration by date range report'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',                           'description' => 'Fetch district list'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-11', 'Laporan Pendaftaran Recipient Mengikut Jenis',
            'pages/profiling/pelaporan/laporan-pendaftaran-recipient/index.vue', 110, $all,
            $this->itemsReport('Laporan Pendaftaran Recipient Mengikut Jenis', ['Jenis Recipient', 'Tahun']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/pendaftaran-recipient-jenis', 'description' => 'Fetch recipient registration by type report'],
                ['method' => 'GET', 'endpoint' => '/kod',                                           'description' => 'Fetch recipient type codes'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-12', 'Laporan Pendaftaran Recipient Mengikut Daerah',
            'pages/profiling/pelaporan/pendaftaran-recipient-daerah/index.vue', 120, $all,
            $this->itemsReport('Laporan Pendaftaran Recipient Mengikut Daerah', ['Daerah']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/pendaftaran-recipient-daerah', 'description' => 'Fetch recipient registration by district report'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',                              'description' => 'Fetch district list'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-13', 'Laporan Pendaftaran Recipient Mengikut Status',
            'pages/profiling/pelaporan/pendaftaran-recipient-status/index.vue', 130, $all,
            $this->itemsReport('Laporan Pendaftaran Recipient Mengikut Status', ['Status', 'Daerah']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/pendaftaran-recipient-status', 'description' => 'Fetch recipient registration by status report'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',                              'description' => 'Fetch district list'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-14', 'Laporan Pendaftaran Recipient Mengikut Tahun Daftar',
            'pages/profiling/pelaporan/recipient-tahun-daftar/index.vue', 140, $all,
            $this->itemsReport('Laporan Pendaftaran Recipient Mengikut Tahun Daftar', ['Tahun', 'Daerah']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/recipient-tahun-daftar', 'description' => 'Fetch recipient registration by year report'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',                        'description' => 'Fetch district list'],
            ]);

        $this->seed($module->id, $rpt->id, 'PRF-RPT-15', 'Laporan Pendaftaran Recipient Mengikut Status Permohonan Bantuan',
            'pages/profiling/pelaporan/status-permohonan-bantuan/index.vue', 150, $all,
            $this->itemsReport('Laporan Pendaftaran Recipient Mengikut Status Permohonan Bantuan', ['Status Permohonan', 'Daerah', 'Jenis Bantuan']),
            [
                ['method' => 'GET', 'endpoint' => '/profiling/laporan/recipient-status-bantuan', 'description' => 'Fetch recipient by bantuan application status report'],
                ['method' => 'GET', 'endpoint' => '/kod/getSub/DAERAH',                          'description' => 'Fetch district list'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // SALASILAH KELUARGA / FAMILY TREE (FTR)
        // ══════════════════════════════════════════════════════════════════

        $f01 = $this->seed($module->id, $ftr->id, 'PRF-FTR-01', 'Carian Salasilah Keluarga',
            'pages/profiling/family-tree/index.vue', 10, $all,
            [
                ['screen_name' => 'Carian Salasilah Keluarga', 'label' => 'Jenis Pengenalan ID', 'type' => 'Select',  'condition' => 'Dropdown — select ID type (MyKad, Passport, etc.)'],
                ['screen_name' => 'Carian Salasilah Keluarga', 'label' => 'No Pengenalan',       'type' => 'Text',    'condition' => 'Text input — identification number; enabled after ID type selected; format validated by type'],
                ['screen_name' => 'Carian Salasilah Keluarga', 'label' => 'Nama',                'type' => 'Text',    'condition' => 'Text input — name search (uppercase)'],
                ['screen_name' => 'Carian Salasilah Keluarga', 'label' => 'Butang Carian',       'type' => 'Button',  'condition' => 'CARI button — triggers profile search'],
                ['screen_name' => 'Carian Salasilah Keluarga', 'label' => 'Keputusan Carian',    'type' => 'Table',   'condition' => 'Search result table — Nama, No. Pengenalan, Kategori; row click navigates to family tree view'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/family-tree/carian', 'description' => 'Search individual profiles for family tree lookup'],
                ['method' => 'GET', 'endpoint' => '/kod',                          'description' => 'Fetch ID type reference codes'],
            ]);

        $f02 = $this->seed($module->id, $ftr->id, 'PRF-FTR-02', 'Salasilah Keluarga',
            'pages/profiling/family-tree/[id].vue', 20, $all,
            [
                ['screen_name' => 'Salasilah Keluarga', 'label' => 'Carta Salasilah Keluarga',  'type' => 'Display', 'condition' => 'Interactive family tree chart visualisation (3/4 width column); shows household members with relationships'],
                ['screen_name' => 'Salasilah Keluarga', 'label' => 'Panel Maklumat Household',  'type' => 'Display', 'condition' => 'Side panel (1/4 width) — No. Rujukan, household summary info'],
                ['screen_name' => 'Salasilah Keluarga', 'label' => 'Kembali',                   'type' => 'Button',  'condition' => 'Back button to return to Carian Salasilah'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/family-tree/{id}', 'description' => 'Fetch family tree data for household by ID'],
            ]);

        $f03 = $this->seed($module->id, $ftr->id, 'PRF-FTR-03', 'Maklumat Ahli Keluarga',
            'pages/profiling/family-tree/[id]/ahli/[memberId].vue', 30, $all,
            [
                ['screen_name' => 'Maklumat Ahli Keluarga', 'label' => 'Maklumat Individu',     'type' => 'Display', 'condition' => 'Display panel — member personal details (Nama, IC, Hubungan, etc.)'],
                ['screen_name' => 'Maklumat Ahli Keluarga', 'label' => 'Kembali ke Salasilah',  'type' => 'Button',  'condition' => 'Back button to return to family tree chart'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/family-tree/{id}/ahli/{memberId}', 'description' => 'Fetch individual family member details'],
            ]);

        $f04 = $this->seed($module->id, $ftr->id, 'PRF-FTR-04', 'Salasilah Keluarga (Individu)',
            'pages/profiling/family-tree/individu/[id].vue', 40, $all,
            [
                ['screen_name' => 'Salasilah Keluarga (Individu)', 'label' => 'Carta Salasilah Keluarga',  'type' => 'Display', 'condition' => 'Interactive family tree chart for a specific individual; no back button (direct link entry point)'],
                ['screen_name' => 'Salasilah Keluarga (Individu)', 'label' => 'Panel Maklumat Household',  'type' => 'Display', 'condition' => 'Side panel — No. Rujukan, household summary info'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/family-tree/individu/{id}', 'description' => 'Fetch family tree for individual by profil ID'],
            ]);

        $f05 = $this->seed($module->id, $ftr->id, 'PRF-FTR-05', 'Salasilah Keluarga Saya',
            'pages/profiling/family-tree/saya/index.vue', 50, $all,
            [
                ['screen_name' => 'Salasilah Keluarga Saya', 'label' => 'Carta Salasilah Keluarga',  'type' => 'Display', 'condition' => 'Family tree chart for logged-in user\'s own household (auto-loaded by IC from user profile)'],
                ['screen_name' => 'Salasilah Keluarga Saya', 'label' => 'Amaran No. Pengenalan',     'type' => 'Display', 'condition' => 'Warning panel shown when logged-in user has no IC registered in their profile'],
                ['screen_name' => 'Salasilah Keluarga Saya', 'label' => 'Kembali',                   'type' => 'Button',  'condition' => 'Back button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/family-tree/saya', 'description' => 'Fetch family tree for current authenticated user (resolved via user IC)'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // PENYELENGGARAAN (PYG)
        // ══════════════════════════════════════════════════════════════════

        $p01 = $this->seed($module->id, $pyg->id, 'PRF-PYG-01', 'Permohonan Carian Profil',
            'pages/profiling/penyelenggaraan/carian-profil/index.vue', 10, $staff,
            [
                ['screen_name' => 'Permohonan Carian Profil', 'label' => 'Jenis Pengenalan Pertama',  'type' => 'Select', 'condition' => 'Dropdown — ID type for Profile 1'],
                ['screen_name' => 'Permohonan Carian Profil', 'label' => 'No. Pengenalan Pertama',    'type' => 'Text',   'condition' => 'Text input — ID number for Profile 1'],
                ['screen_name' => 'Permohonan Carian Profil', 'label' => 'Nama Pertama',              'type' => 'Text',   'condition' => 'Text input — name for Profile 1 (auto-filled after ID lookup)'],
                ['screen_name' => 'Permohonan Carian Profil', 'label' => 'Jenis Pengenalan Kedua',    'type' => 'Select', 'condition' => 'Dropdown — ID type for Profile 2'],
                ['screen_name' => 'Permohonan Carian Profil', 'label' => 'No. Pengenalan Kedua',      'type' => 'Text',   'condition' => 'Text input — ID number for Profile 2'],
                ['screen_name' => 'Permohonan Carian Profil', 'label' => 'Nama Kedua',                'type' => 'Text',   'condition' => 'Text input — name for Profile 2 (auto-filled after ID lookup)'],
                ['screen_name' => 'Permohonan Carian Profil', 'label' => 'Butang Cari',               'type' => 'Button', 'condition' => 'CARI button — searches both profiles and shows comparison for merge'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/individu/by-id-number',   'description' => 'Lookup profile by ID number (auto-fill name)'],
                ['method' => 'GET', 'endpoint' => '/kod',                                'description' => 'Fetch ID type reference codes'],
            ]);

        $p02 = $this->seed($module->id, $pyg->id, 'PRF-PYG-02', 'Merge Maklumat Antara Dua Pengenalan ID',
            'pages/profiling/penyelenggaraan/carian-profil/merge/index.vue', 20, $staff,
            [
                ['screen_name' => 'Merge Maklumat', 'label' => 'Pengenalan ID',           'type' => 'Display', 'condition' => 'ID fields for both profiles with checkboxes to select which ID to keep as primary'],
                ['screen_name' => 'Merge Maklumat', 'label' => 'Semakan Maklumat',        'type' => 'Display', 'condition' => 'Side-by-side comparison form (3/4 layout) showing differences between two profiles; checkboxes to pick winning value per field'],
                ['screen_name' => 'Merge Maklumat', 'label' => 'Panel Status Kebenaran',  'type' => 'Display', 'condition' => 'Right panel (1/4) — shows permission granted/required badge; RsBadge variant changes by state'],
                ['screen_name' => 'Merge Maklumat', 'label' => 'Butang Merge',            'type' => 'Button',  'condition' => 'Submit merge action — sends selected field values to backend'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/profiling/penyelenggaraan/merge/preview', 'description' => 'Preview merge diff between two profiles'],
                ['method' => 'POST', 'endpoint' => '/profiling/penyelenggaraan/merge',         'description' => 'Submit merge request for approval'],
            ]);

        $p03 = $this->seed($module->id, $pyg->id, 'PRF-PYG-03', 'Semakan Perubahan Profil',
            'pages/profiling/penyelenggaraan/semakan-perubahan/index.vue', 30, $staff,
            [
                ['screen_name' => 'Semakan Perubahan Profil', 'label' => 'Stat Dalam Proses Semakan',  'type' => 'Display', 'condition' => 'Statistics card — count of records currently in semakan stage'],
                ['screen_name' => 'Semakan Perubahan Profil', 'label' => 'Stat Menunggu Kelulusan',    'type' => 'Display', 'condition' => 'Statistics card — count of records awaiting kelulusan'],
                ['screen_name' => 'Semakan Perubahan Profil', 'label' => 'Stat Selesai',               'type' => 'Display', 'condition' => 'Statistics card — count of completed records'],
                ['screen_name' => 'Semakan Perubahan Profil', 'label' => 'Jadual Senarai Semakan',     'type' => 'Table',   'condition' => 'Table listing profile change requests for review; row click navigates to detail page'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/penyelenggaraan/semakan-perubahan',        'description' => 'Fetch list of profile change requests for semakan'],
                ['method' => 'GET', 'endpoint' => '/profiling/penyelenggaraan/semakan-perubahan/stats',  'description' => 'Fetch statistics counts by status'],
            ]);

        $p04 = $this->seed($module->id, $pyg->id, 'PRF-PYG-04', 'Butiran Semakan Perubahan Profil',
            'pages/profiling/penyelenggaraan/semakan-perubahan/[id].vue', 40, $staff,
            [
                ['screen_name' => 'Butiran Semakan Perubahan', 'label' => 'Perbandingan Maklumat Profil',  'type' => 'Display', 'condition' => 'Side-by-side comparison renderer (ComparisonFormRenderer component) showing old vs new profile values with highlighted differences'],
                ['screen_name' => 'Butiran Semakan Perubahan', 'label' => 'Seksyen Tanggungan',            'type' => 'Display', 'condition' => 'Tanggungan section comparison using shared multi-section form modules'],
                ['screen_name' => 'Butiran Semakan Perubahan', 'label' => 'Seksyen Pengesahan',            'type' => 'Display', 'condition' => 'Pengesahan section — verification status and remarks'],
                ['screen_name' => 'Butiran Semakan Perubahan', 'label' => 'Butang Lulus / Tolak',          'type' => 'Button',  'condition' => 'Action buttons — Lulus (approve) or Tolak (reject) the profile change request'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/profiling/penyelenggaraan/semakan-perubahan/{id}', 'description' => 'Fetch profile change request detail with old/new snapshot comparison'],
                ['method' => 'POST', 'endpoint' => '/profiling/penyelenggaraan/semakan-perubahan/{id}/keputusan', 'description' => 'Submit semakan decision (lulus/tolak) with remarks'],
            ]);

        $p05 = $this->seed($module->id, $pyg->id, 'PRF-PYG-05', 'Senarai Kelulusan Gabung Maklumat',
            'pages/profiling/penyelenggaraan/kelulusan/index.vue', 50, [$pelulus->id],
            [
                ['screen_name' => 'Menunggu Kelulusan', 'label' => 'Jadual Menunggu Kelulusan',  'type' => 'Table',   'condition' => 'Table listing merge requests awaiting approval'],
                ['screen_name' => 'Diluluskan',         'label' => 'Jadual Diluluskan',           'type' => 'Table',   'condition' => 'Table listing approved merge requests'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/penyelenggaraan/kelulusan', 'description' => 'Fetch merge approval list (params: status)'],
            ]);

        $p06 = $this->seed($module->id, $pyg->id, 'PRF-PYG-06', 'Butiran Kelulusan Gabung Maklumat',
            'pages/profiling/penyelenggaraan/kelulusan/[id].vue', 60, [$pelulus->id],
            [
                ['screen_name' => 'Butiran Kelulusan Gabung', 'label' => 'Maklumat Permohonan Merge',  'type' => 'Display', 'condition' => 'Display panel showing merge request details — both ID numbers, requestor, date submitted'],
                ['screen_name' => 'Butiran Kelulusan Gabung', 'label' => 'Perbandingan Profil',        'type' => 'Display', 'condition' => 'Side-by-side profile comparison showing which fields differ between the two IDs'],
                ['screen_name' => 'Butiran Kelulusan Gabung', 'label' => 'Butang Lulus / Tolak',       'type' => 'Button',  'condition' => 'Action buttons — Lulus (approve merge) or Tolak (reject merge) with remarks modal'],
            ],
            [
                ['method' => 'GET',  'endpoint' => '/profiling/penyelenggaraan/kelulusan/{id}',           'description' => 'Fetch merge approval request detail'],
                ['method' => 'POST', 'endpoint' => '/profiling/penyelenggaraan/kelulusan/{id}/keputusan', 'description' => 'Submit kelulusan decision (lulus/tolak)'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // DASHBOARD (DSH)
        // ══════════════════════════════════════════════════════════════════

        $this->seed($module->id, $dsh->id, 'PRF-DSH-01', 'Dashboard Organisasi & Recipient',
            'pages/profiling/main-dashboard/index.vue', 10, $all,
            [
                ['screen_name' => 'Dashboard Organisasi & Recipient', 'label' => 'Kad Jumlah Profil Organisasi',  'type' => 'Display', 'condition' => 'Summary card — total active organisation profiles count'],
                ['screen_name' => 'Dashboard Organisasi & Recipient', 'label' => 'Kad Jumlah Recipient',          'type' => 'Display', 'condition' => 'Summary card — total recipient profiles count'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/dashboard/organisasi-count',  'description' => 'Fetch total active organisation profiles count'],
                ['method' => 'GET', 'endpoint' => '/profiling/dashboard/recipient-count',   'description' => 'Fetch total recipient profiles count'],
            ]);

        // ══════════════════════════════════════════════════════════════════
        // PAGE LINKS
        // ══════════════════════════════════════════════════════════════════

        $links = [
            // Pelaporan index → individual report pages
            [$r01->id, $this->fe('PRF-RPT-02')->id],
            [$r01->id, $this->fe('PRF-RPT-03')->id],
            [$r01->id, $this->fe('PRF-RPT-04')->id],
            [$r01->id, $this->fe('PRF-RPT-05')->id],
            [$r01->id, $this->fe('PRF-RPT-06')->id],
            [$r01->id, $this->fe('PRF-RPT-07')->id],
            [$r01->id, $this->fe('PRF-RPT-08')->id],
            [$r01->id, $this->fe('PRF-RPT-09')->id],
            [$r01->id, $this->fe('PRF-RPT-10')->id],
            [$r01->id, $this->fe('PRF-RPT-11')->id],
            [$r01->id, $this->fe('PRF-RPT-12')->id],
            [$r01->id, $this->fe('PRF-RPT-13')->id],
            [$r01->id, $this->fe('PRF-RPT-14')->id],
            [$r01->id, $this->fe('PRF-RPT-15')->id],
            // Family tree: carian → detail → member detail
            [$f01->id, $f02->id],
            [$f02->id, $f03->id],
            // Penyelenggaraan: carian → merge, semakan → butiran, kelulusan → butiran
            [$p01->id, $p02->id],
            [$p03->id, $p04->id],
            [$p05->id, $p06->id],
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
    // Helpers
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
                'id_fr'            => $item['id_fr'] ?? ($fe->spec_id . '-FR-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT)),
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

    private function fe(string $specId): RtmfFrontend
    {
        return RtmfFrontend::where('spec_id', $specId)->firstOrFail();
    }

    /** Generic report page FR items: filters + table + download */
    private function itemsReport(string $screenName, array $filterLabels): array
    {
        $items = [];
        foreach ($filterLabels as $label) {
            $items[] = ['screen_name' => $screenName, 'label' => "Filter $label", 'type' => 'Select', 'condition' => "Filter dropdown — $label"];
        }
        $items[] = ['screen_name' => $screenName, 'label' => 'Butang Tapis',      'type' => 'Button', 'condition' => 'TAPIS button — triggers report generation'];
        $items[] = ['screen_name' => $screenName, 'label' => 'Jadual Laporan',    'type' => 'Table',  'condition' => 'Report result table with sortable/filterable columns'];
        $items[] = ['screen_name' => $screenName, 'label' => 'Butang Muat Turun', 'type' => 'Button', 'condition' => 'Download report as PDF/Excel'];
        return $items;
    }
}
