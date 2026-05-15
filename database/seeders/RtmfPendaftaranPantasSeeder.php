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

class RtmfPendaftaranPantasSeeder extends Seeder
{
    public function run(): void
    {
        $project = RtmfProject::firstOrFail();

        // ── Module ─────────────────────────────────────────────────────────
        $module = RtmfModule::firstOrCreate(
            ['code' => 'PPB'],
            ['name' => 'Pendaftaran Pantas Bantuan', 'sort_order' => 30, 'project_id' => $project->id],
        );

        // Ensure project_id is set on existing records
        if (! $module->project_id) {
            $module->update(['project_id' => $project->id]);
        }

        // ── Sub-modules ────────────────────────────────────────────────────
        $bnc = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'BNC'],
            ['name' => 'Bencana', 'sort_order' => 10],
        );
        $prm = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'PRM'],
            ['name' => 'Permohonan', 'sort_order' => 20],
        );
        $car = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'CAR'],
            ['name' => 'Carian', 'sort_order' => 30],
        );
        $prs = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'PRS'],
            ['name' => 'Perseorangan', 'sort_order' => 40],
        );

        // ── Actors ─────────────────────────────────────────────────────────
        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $pengguna = RtmfActor::firstOrCreate(['name' => 'Pengguna']);
        $actors   = [$pegawai->id, $pengguna->id];

        // ── Frontend entries ───────────────────────────────────────────────
        $bncFrontend = $this->upsertFrontend(
            specId:      'PPB-BNC-01',
            moduleId:    $module->id,
            subModuleId: $bnc->id,
            title:       'Senarai Bencana',
            vuePath:     'pages/profiling/asnaf/pendaftaran-pantas-pukal/index.vue',
            sortOrder:   10,
            actors:      $actors,
            items:       $this->itemsBnc01(),
            endpoints:   $this->endpointsBnc01(),
        );

        $prm01 = $this->upsertFrontend(
            specId:      'PPB-PRM-01',
            moduleId:    $module->id,
            subModuleId: $prm->id,
            title:       'Senarai Permohonan',
            vuePath:     'pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/index.vue',
            sortOrder:   20,
            actors:      $actors,
            items:       $this->itemsPrm01(),
            endpoints:   $this->endpointsPrm01(),
        );

        $prm02 = $this->upsertFrontend(
            specId:      'PPB-PRM-02',
            moduleId:    $module->id,
            subModuleId: $prm->id,
            title:       'Tambah Permohonan',
            vuePath:     'pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/tambah.vue',
            sortOrder:   30,
            actors:      $actors,
            items:       $this->itemsPrm02(),
            endpoints:   $this->endpointsPrm02(),
        );

        $prm03 = $this->upsertFrontend(
            specId:      'PPB-PRM-03',
            moduleId:    $module->id,
            subModuleId: $prm->id,
            title:       'Butiran Batch',
            vuePath:     'pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/[batchId]/index.vue',
            sortOrder:   40,
            actors:      $actors,
            items:       $this->itemsPrm03(),
            endpoints:   $this->endpointsPrm03(),
        );

        $prm04 = $this->upsertFrontend(
            specId:      'PPB-PRM-04',
            moduleId:    $module->id,
            subModuleId: $prm->id,
            title:       'Edit Draf',
            vuePath:     'pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/draft/[draftId]/index.vue',
            sortOrder:   50,
            actors:      $actors,
            items:       $this->itemsPrm04(),
            endpoints:   $this->endpointsPrm04(),
        );

        $car01 = $this->upsertFrontend(
            specId:      'PPB-CAR-01',
            moduleId:    $module->id,
            subModuleId: $car->id,
            title:       'Carian Pemohon',
            vuePath:     'pages/profiling/asnaf/pendaftaran-pantas-pukal/carian-pemohon/index.vue',
            sortOrder:   60,
            actors:      $actors,
            items:       $this->itemsCar01(),
            endpoints:   $this->endpointsCar01(),
        );

        $this->upsertFrontend(
            specId:      'PPB-PRS-01',
            moduleId:    $module->id,
            subModuleId: $prs->id,
            title:       'Pendaftaran Pantas Perseorangan',
            vuePath:     'pages/profiling/asnaf/carian-profil-ppp/pendaftaran-pantas-perseorangan/index.vue',
            sortOrder:   70,
            actors:      $actors,
            items:       $this->itemsPrs01(),
            endpoints:   $this->endpointsPrs01(),
        );

        // ── Page Links (navigation flow) ───────────────────────────────────
        $bncFrontend->linksTo()->sync([$prm01->id]);
        $prm01->linksTo()->sync([$prm02->id, $prm03->id, $prm04->id, $car01->id]);
    }

    // ── Helper ─────────────────────────────────────────────────────────────

    private function upsertFrontend(
        string $specId,
        int    $moduleId,
        int    $subModuleId,
        string $title,
        string $vuePath,
        int    $sortOrder,
        array  $actors,
        array  $items,
        array  $endpoints,
    ): RtmfFrontend {
        $frontend = RtmfFrontend::updateOrCreate(
            ['spec_id' => $specId],
            [
                'module_id'     => $moduleId,
                'sub_module_id' => $subModuleId,
                'tab_code'      => $specId,
                'vue_path'      => $vuePath,
                'title'         => $title,
                'is_done'       => false,
                'sort_order'    => $sortOrder,
            ],
        );

        $frontend->actors()->sync($actors);

        RtmfFrontendItem::where('rtmf_frontend_id', $frontend->id)->delete();
        foreach ($items as $i => $item) {
            RtmfFrontendItem::create(['rtmf_frontend_id' => $frontend->id, 'sort_order' => $i, ...$item]);
        }

        RtmfFrontendApiEndpoint::where('rtmf_frontend_id', $frontend->id)->delete();
        foreach ($endpoints as $k => $ep) {
            RtmfFrontendApiEndpoint::create(['rtmf_frontend_id' => $frontend->id, 'sort_order' => $k, ...$ep]);
        }

        return $frontend;
    }

    // ── FR Items ───────────────────────────────────────────────────────────

    /** @return array<int, array<string, mixed>> */
    private function itemsBnc01(): array
    {
        return [
            ['screen_name' => 'Tab: Aktif',               'id_fr' => 'PPB-BNC-01-FR-001', 'type' => 'Table',      'label' => 'Senarai Bencana Aktif',        'mandatory' => false, 'table_fieldname' => null,                'condition' => 'Tarikh mula/tamat dalam julat hari ini', 'validation' => null,               'status' => 'missing'],
            ['screen_name' => 'Tab: Tidak Aktif',          'id_fr' => 'PPB-BNC-01-FR-002', 'type' => 'Table',      'label' => 'Senarai Bencana Tidak Aktif',   'mandatory' => false, 'table_fieldname' => null,                'condition' => 'Tarikh tamat sebelum hari ini',          'validation' => null,               'status' => 'missing'],
            ['screen_name' => 'Tab: Semua',                'id_fr' => 'PPB-BNC-01-FR-003', 'type' => 'Table',      'label' => 'Semua Bencana',                 'mandatory' => false, 'table_fieldname' => null,                'condition' => null,                                     'validation' => null,               'status' => 'missing'],
            ['screen_name' => 'Modal: Tambah/Edit Bencana','id_fr' => 'PPB-BNC-01-FR-004', 'type' => 'Text',       'label' => 'Nama Bencana',                  'mandatory' => true,  'table_fieldname' => 'profil_bencana.nama_bencana',       'condition' => null,                                     'validation' => 'required|max:255',  'status' => 'missing'],
            ['screen_name' => 'Modal: Tambah/Edit Bencana','id_fr' => 'PPB-BNC-01-FR-005', 'type' => 'Date',       'label' => 'Tarikh Mula',                   'mandatory' => true,  'table_fieldname' => 'profil_bencana.tarikh_mula',        'condition' => null,                                     'validation' => 'required|date',    'status' => 'missing'],
            ['screen_name' => 'Modal: Tambah/Edit Bencana','id_fr' => 'PPB-BNC-01-FR-006', 'type' => 'Date',       'label' => 'Tarikh Tamat',                  'mandatory' => true,  'table_fieldname' => 'profil_bencana.tarikh_tamat',       'condition' => null,                                     'validation' => 'required|date|after:tarikh_mula', 'status' => 'missing'],
            ['screen_name' => 'Modal: Tambah/Edit Bencana','id_fr' => 'PPB-BNC-01-FR-007', 'type' => 'Map',        'label' => 'Koordinat Lokasi Bencana',      'mandatory' => false, 'table_fieldname' => 'profil_bencana.latitude,longitude', 'condition' => null,                                     'validation' => 'nullable',         'status' => 'missing'],
            ['screen_name' => 'Modal: Import',             'id_fr' => 'PPB-BNC-01-FR-008', 'type' => 'FileUpload', 'label' => 'Import Excel/CSV Bencana',      'mandatory' => false, 'table_fieldname' => null,                'condition' => null,                                     'validation' => 'nullable|mimes:xlsx,csv', 'status' => 'missing'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function itemsPrm01(): array
    {
        return [
            ['screen_name' => 'Tab: Draf',         'id_fr' => 'PPB-PRM-01-FR-001', 'type' => 'Table',  'label' => 'Senarai Draf',           'mandatory' => false, 'table_fieldname' => null,  'condition' => 'Status PPP_DRAF',        'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Dalam Proses',  'id_fr' => 'PPB-PRM-01-FR-002', 'type' => 'Table',  'label' => 'Senarai Dalam Proses',   'mandatory' => false, 'table_fieldname' => null,  'condition' => 'Status BARU/LENGKAP',    'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Pindaan',       'id_fr' => 'PPB-PRM-01-FR-003', 'type' => 'Table',  'label' => 'Senarai Pindaan',        'mandatory' => false, 'table_fieldname' => null,  'condition' => 'Status PINDAAN_*',       'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Lulus',         'id_fr' => 'PPB-PRM-01-FR-004', 'type' => 'Table',  'label' => 'Senarai Lulus',          'mandatory' => false, 'table_fieldname' => null,  'condition' => 'Status LULUS',           'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Tidak Lulus',   'id_fr' => 'PPB-PRM-01-FR-005', 'type' => 'Table',  'label' => 'Senarai Tidak Lulus',    'mandatory' => false, 'table_fieldname' => null,  'condition' => 'Status TIDAK_LULUS',     'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Semua',         'id_fr' => 'PPB-PRM-01-FR-006', 'type' => 'Table',  'label' => 'Semua Permohonan',       'mandatory' => false, 'table_fieldname' => null,  'condition' => null,                     'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Filter Bar',         'id_fr' => 'PPB-PRM-01-FR-007', 'type' => 'Select', 'label' => 'Filter Kategori Asnaf',  'mandatory' => false, 'table_fieldname' => 'kategori_asnaf', 'condition' => 'Fakir/Miskin/Non FK-MS/Semua', 'validation' => 'nullable', 'status' => 'missing'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function itemsPrm02(): array
    {
        return [
            ['screen_name' => 'Import',          'id_fr' => 'PPB-PRM-02-FR-001', 'type' => 'FileUpload', 'label' => 'Upload Excel/CSV Pemohon',               'mandatory' => false, 'table_fieldname' => null,            'condition' => null,                                      'validation' => 'nullable|mimes:xlsx,csv',   'status' => 'missing'],
            ['screen_name' => 'Senarai Semakan', 'id_fr' => 'PPB-PRM-02-FR-002', 'type' => 'Table',      'label' => 'Staging Pool (PENDING/CHECKING)',        'mandatory' => false, 'table_fieldname' => 'staging_pool',  'condition' => 'Menunggu semakan JPN',                    'validation' => null,                        'status' => 'missing'],
            ['screen_name' => 'Senarai Semakan', 'id_fr' => 'PPB-PRM-02-FR-003', 'type' => 'Button',     'label' => 'Semak JPN',                              'mandatory' => false, 'table_fieldname' => null,            'condition' => 'Mencetuskan semakan identiti via JPN API', 'validation' => null,                        'status' => 'missing'],
            ['screen_name' => 'Data Rosak',      'id_fr' => 'PPB-PRM-02-FR-004', 'type' => 'Table',      'label' => 'Data Rosak (Ralat Validasi)',             'mandatory' => false, 'table_fieldname' => 'staging_pool',  'condition' => 'Status ROSAK',                            'validation' => null,                        'status' => 'missing'],
            ['screen_name' => 'Senarai Pemohon', 'id_fr' => 'PPB-PRM-02-FR-005', 'type' => 'Table',      'label' => 'Senarai Pemohon (Lulus Semakan)',        'mandatory' => false, 'table_fieldname' => 'staging_pool',  'condition' => 'Status OK',                               'validation' => null,                        'status' => 'missing'],
            ['screen_name' => 'Peta',            'id_fr' => 'PPB-PRM-02-FR-006', 'type' => 'Map',        'label' => 'Peta Radius Bencana + Lokasi Pemohon',   'mandatory' => false, 'table_fieldname' => null,            'condition' => 'Leaflet; titik tengah dari koordinat bencana', 'validation' => null,                   'status' => 'missing'],
            ['screen_name' => 'Kategori',        'id_fr' => 'PPB-PRM-02-FR-007', 'type' => 'Select',     'label' => 'Kategori Asnaf',                         'mandatory' => true,  'table_fieldname' => 'kategori_asnaf','condition' => 'Fakir/Miskin/Non FK-MS; semak via API',    'validation' => 'required',                  'status' => 'missing'],
            ['screen_name' => 'Form Manual',     'id_fr' => 'PPB-PRM-02-FR-008', 'type' => 'Text',       'label' => 'Nama Penuh',                             'mandatory' => true,  'table_fieldname' => 'nama_penuh',    'condition' => null,                                      'validation' => 'required|max:100|uppercase', 'status' => 'missing'],
            ['screen_name' => 'Form Manual',     'id_fr' => 'PPB-PRM-02-FR-009', 'type' => 'Text',       'label' => 'No. Pengenalan',                         'mandatory' => true,  'table_fieldname' => 'no_pengenalan', 'condition' => null,                                      'validation' => 'required|max:20',           'status' => 'missing'],
            ['screen_name' => 'Form Manual',     'id_fr' => 'PPB-PRM-02-FR-010', 'type' => 'Text',       'label' => 'Poskod',                                 'mandatory' => true,  'table_fieldname' => 'poskod',        'condition' => 'Mencetuskan geocod & carian negeri/daerah', 'validation' => 'required|digits:5',        'status' => 'missing'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function itemsPrm03(): array
    {
        return [
            ['screen_name' => 'Lihat',    'id_fr' => 'PPB-PRM-03-FR-001', 'type' => 'Table', 'label' => 'Senarai Pemohon dalam Batch (Baca Sahaja)', 'mandatory' => false, 'table_fieldname' => null, 'condition' => 'mode=lihat',   'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Edit',     'id_fr' => 'PPB-PRM-03-FR-002', 'type' => 'Table', 'label' => 'Senarai Pemohon (Mod Edit)',               'mandatory' => false, 'table_fieldname' => null, 'condition' => 'mode=permohonan', 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Pindaan', 'id_fr' => 'PPB-PRM-03-FR-003', 'type' => 'Table', 'label' => 'Senarai Pemohon (Mod Pindaan)',            'mandatory' => false, 'table_fieldname' => null, 'condition' => 'Status PINDAAN_*', 'validation' => null, 'status' => 'missing'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function itemsPrm04(): array
    {
        return [
            ['screen_name' => 'Staging',  'id_fr' => 'PPB-PRM-04-FR-001', 'type' => 'Table', 'label' => 'Sambung Semula Staging Pool',    'mandatory' => false, 'table_fieldname' => 'staging_pool', 'condition' => 'Draf yang belum dihantar', 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Pemohon',  'id_fr' => 'PPB-PRM-04-FR-002', 'type' => 'Table', 'label' => 'Sambung Semula Senarai Pemohon', 'mandatory' => false, 'table_fieldname' => 'staging_pool', 'condition' => 'Status OK dari draf', 'validation' => null, 'status' => 'missing'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function itemsCar01(): array
    {
        return [
            ['screen_name' => 'Form Carian', 'id_fr' => 'PPB-CAR-01-FR-001', 'type' => 'Text',  'label' => 'Nama',                      'mandatory' => false, 'table_fieldname' => 'nama_penuh',     'condition' => 'Min 2 aksara; auto uppercase', 'validation' => 'nullable|min:2',   'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PPB-CAR-01-FR-002', 'type' => 'Text',  'label' => 'No. Pengenalan',            'mandatory' => false, 'table_fieldname' => 'no_pengenalan',  'condition' => 'MyKad 12 digit / Pasport max 20', 'validation' => 'nullable|max:20', 'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PPB-CAR-01-FR-003', 'type' => 'Text',  'label' => 'No. Rujukan Permohonan',    'mandatory' => false, 'table_fieldname' => 'no_rujukan',     'condition' => null,                          'validation' => 'nullable',         'status' => 'missing'],
            ['screen_name' => 'Keputusan',   'id_fr' => 'PPB-CAR-01-FR-004', 'type' => 'Table', 'label' => 'Keputusan Carian Pemohon',  'mandatory' => false, 'table_fieldname' => null,             'condition' => null,                          'validation' => null,               'status' => 'missing'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function itemsPrs01(): array
    {
        return [
            ['screen_name' => 'Tab: Maklumat Pemohon', 'id_fr' => 'PPB-PRS-01-FR-001', 'type' => 'Text',    'label' => 'Nama Penuh',         'mandatory' => true,  'table_fieldname' => 'nama_penuh',      'condition' => null,                           'validation' => 'required|max:100|uppercase', 'status' => 'missing'],
            ['screen_name' => 'Tab: Maklumat Pemohon', 'id_fr' => 'PPB-PRS-01-FR-002', 'type' => 'Text',    'label' => 'No. Pengenalan',     'mandatory' => true,  'table_fieldname' => 'no_pengenalan',   'condition' => null,                           'validation' => 'required|max:20',            'status' => 'missing'],
            ['screen_name' => 'Tab: Maklumat Pemohon', 'id_fr' => 'PPB-PRS-01-FR-003', 'type' => 'Select',  'label' => 'Kategori Asnaf',     'mandatory' => true,  'table_fieldname' => 'kategori_asnaf',  'condition' => 'Fakir/Miskin/Non FK-MS',       'validation' => 'required',                   'status' => 'missing'],
            ['screen_name' => 'Tab: Maklumat Pemohon', 'id_fr' => 'PPB-PRS-01-FR-004', 'type' => 'Select',  'label' => 'Bencana',            'mandatory' => true,  'table_fieldname' => 'id_bencana',      'condition' => 'Senarai bencana aktif',        'validation' => 'required',                   'status' => 'missing'],
            ['screen_name' => 'Tab: Ringkasan',        'id_fr' => 'PPB-PRS-01-FR-005', 'type' => 'Summary', 'label' => 'Had Kifayah Summary','mandatory' => false, 'table_fieldname' => null,              'condition' => 'Auto-dikira berdasarkan tanggungan', 'validation' => null,                    'status' => 'missing'],
        ];
    }

    // ── API Endpoints ──────────────────────────────────────────────────────

    /** @return array<int, array<string, mixed>> */
    private function endpointsBnc01(): array
    {
        return [
            ['method' => 'GET',    'endpoint' => '/profiling/bantuan-pukal/bencana',      'description' => 'Fetch all disasters (supports status filter)'],
            ['method' => 'POST',   'endpoint' => '/profiling/bantuan-pukal/bencana',      'description' => 'Create new disaster record'],
            ['method' => 'PUT',    'endpoint' => '/profiling/bantuan-pukal/bencana/{id}', 'description' => 'Update disaster record'],
            ['method' => 'DELETE', 'endpoint' => '/profiling/bantuan-pukal/bencana/{id}', 'description' => 'Delete disaster record'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function endpointsPrm01(): array
    {
        return [
            ['method' => 'GET',  'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}',               'description' => 'Fetch disaster details (header info)'],
            ['method' => 'GET',  'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/batch',          'description' => 'Fetch batch list for a disaster'],
            ['method' => 'GET',  'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/drafts',         'description' => 'Fetch PPP draft list for a disaster'],
            ['method' => 'POST', 'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/hantar-draft',   'description' => 'Submit all drafts as batch applications'],
            ['method' => 'DELETE','endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/batch/{batchId}','description' => 'Soft-delete a batch'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function endpointsPrm02(): array
    {
        return [
            ['method' => 'GET',    'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/staging',              'description' => 'Fetch staging pool rows'],
            ['method' => 'POST',   'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/staging/semak',        'description' => 'Trigger JPN identity check on staging rows'],
            ['method' => 'DELETE', 'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/staging/{id}',         'description' => 'Remove a staging row'],
            ['method' => 'GET',    'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/drafts',               'description' => 'Fetch PPP drafts (resume draft state)'],
            ['method' => 'POST',   'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/batch',                'description' => 'Create new batch from applicant list'],
            ['method' => 'POST',   'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/hantar-draft',         'description' => 'Convert drafts to batch applications'],
            ['method' => 'GET',    'endpoint' => '/profiling/pendaftaran-pantas/geocode-poskod/{poskod}',             'description' => 'Geocode postal code to negeri/daerah/coordinates'],
            ['method' => 'GET',    'endpoint' => '/kod/poskod/{poskod}',                                              'description' => 'Validate postal code reference'],
            ['method' => 'GET',    'endpoint' => '/kod/getSub/NEGERI',                                                'description' => 'Fetch state list'],
            ['method' => 'GET',    'endpoint' => '/kod/getSub/DAERAH',                                                'description' => 'Fetch district list (filter: adalah_aktif=1)'],
            ['method' => 'POST',   'endpoint' => '/profiling/bantuan-pukal/bencana/check-kategori-asnaf/{noPengenalan}', 'description' => 'Check asnaf category for an individual'],
            ['method' => 'GET',    'endpoint' => '/profiling/bantuan-pukal/bencana/check-aid-history/{noPengenalan}', 'description' => 'Check prior aid history for a disaster applicant'],
            ['method' => 'GET',    'endpoint' => '/konfigurasi/bantuan-hierarchy/bantuan',                            'description' => 'Fetch aid types'],
            ['method' => 'GET',    'endpoint' => '/konfigurasi/bantuan-hierarchy/bantuan-produk',                     'description' => 'Fetch aid products'],
            ['method' => 'GET',    'endpoint' => '/konfigurasi/bantuan-hierarchy/pakej-kelayakan',                    'description' => 'Fetch eligibility packages'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function endpointsPrm03(): array
    {
        return [
            ['method' => 'GET', 'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/batch/{batchId}', 'description' => 'Fetch batch details with full applicant list'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function endpointsPrm04(): array
    {
        return [
            ['method' => 'GET',  'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/drafts',      'description' => 'Load saved draft state (staging + applicants)'],
            ['method' => 'GET',  'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/staging',     'description' => 'Resume staging pool from draft'],
            ['method' => 'POST', 'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/hantar-draft','description' => 'Submit draft as batch application'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function endpointsCar01(): array
    {
        return [
            ['method' => 'GET', 'endpoint' => '/profiling/bantuan-pukal/bencana/carian-pemohon',          'description' => 'Search applicants by name, IC, or reference number'],
            ['method' => 'GET', 'endpoint' => '/profiling/bantuan-pukal/bencana/batch-bencana/{batchId}', 'description' => 'Resolve disaster ID from batch ID (for navigation)'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function endpointsPrs01(): array
    {
        return [
            ['method' => 'GET',  'endpoint' => '/profiling/pendaftaran-pantas/perseorangan',      'description' => 'Fetch individual registration list'],
            ['method' => 'POST', 'endpoint' => '/profiling/pendaftaran-pantas/perseorangan',      'description' => 'Submit new individual registration'],
            ['method' => 'GET',  'endpoint' => '/profiling/pendaftaran-pantas/perseorangan/{id}', 'description' => 'Fetch individual registration detail'],
        ];
    }
}
