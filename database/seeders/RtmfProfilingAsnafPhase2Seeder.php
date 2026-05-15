<?php

namespace Database\Seeders;

use App\Models\RtmfActor;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendApiEndpoint;
use App\Models\RtmfFrontendItem;
use App\Models\RtmfModule;
use App\Models\RtmfSubModule;
use Illuminate\Database\Seeder;

class RtmfProfilingAsnafPhase2Seeder extends Seeder
{
    public function run(): void
    {
        $module = RtmfModule::where('code', 'PRF')->firstOrFail();
        $asn    = RtmfSubModule::where('module_id', $module->id)->where('code', 'ASN')->firstOrFail();

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $pengguna = RtmfActor::firstOrCreate(['name' => 'Pengguna']);
        $allActors = [$pegawai->id, $pengguna->id];

        // ── Pendaftaran Pantas Pukal ───────────────────────────────────────
        $asn23 = $this->seed($module->id, $asn->id, 'PRF-ASN-23', 'Senarai Bencana',
            'pages/profiling/asnaf/pendaftaran-pantas-pukal/index.vue', 230, $allActors,
            $this->itemsBnc(), $this->epBnc());

        $asn24 = $this->seed($module->id, $asn->id, 'PRF-ASN-24', 'Senarai Permohonan Bencana',
            'pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/index.vue', 240, $allActors,
            $this->itemsPrm01(), $this->epPrm01());

        $asn25 = $this->seed($module->id, $asn->id, 'PRF-ASN-25', 'Tambah Permohonan',
            'pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/tambah.vue', 250, $allActors,
            $this->itemsPrm02(), $this->epPrm02());

        $asn26 = $this->seed($module->id, $asn->id, 'PRF-ASN-26', 'Butiran Batch',
            'pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/[batchId]/index.vue', 260, $allActors,
            $this->itemsPrm03(), $this->epPrm03());

        $asn27 = $this->seed($module->id, $asn->id, 'PRF-ASN-27', 'Edit Draf Permohonan',
            'pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/draft/[draftId]/index.vue', 270, $allActors,
            $this->itemsPrm04(), $this->epPrm04());

        $asn28 = $this->seed($module->id, $asn->id, 'PRF-ASN-28', 'Carian Pemohon',
            'pages/profiling/asnaf/pendaftaran-pantas-pukal/carian-pemohon/index.vue', 280, $allActors,
            $this->itemsCar(), $this->epCar());

        // ── Pendaftaran Pantas Perseorangan ───────────────────────────────
        $asn29 = $this->seed($module->id, $asn->id, 'PRF-ASN-29', 'Pendaftaran Pantas Perseorangan',
            'pages/profiling/asnaf/carian-profil-ppp/pendaftaran-pantas-perseorangan/index.vue', 290, $allActors,
            $this->itemsPrs(), $this->epPrs());

        // ── Page Links ─────────────────────────────────────────────────────
        $asn23->linksTo()->sync([$asn24->id]);
        $asn24->linksTo()->sync([$asn25->id, $asn26->id, $asn27->id, $asn28->id]);
    }

    // ── Helper ─────────────────────────────────────────────────────────────

    private function seed(
        int    $moduleId,
        int    $subModuleId,
        string $specId,
        string $title,
        string $vuePath,
        int    $sortOrder,
        array  $actorIds,
        array  $items,
        array  $endpoints,
    ): RtmfFrontend {
        $fe = RtmfFrontend::updateOrCreate(
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

        $fe->actors()->sync($actorIds);

        RtmfFrontendItem::where('rtmf_frontend_id', $fe->id)->delete();
        foreach ($items as $i => $item) {
            RtmfFrontendItem::create(['rtmf_frontend_id' => $fe->id, 'sort_order' => $i, ...$item]);
        }

        RtmfFrontendApiEndpoint::where('rtmf_frontend_id', $fe->id)->delete();
        foreach ($endpoints as $k => $ep) {
            RtmfFrontendApiEndpoint::create(['rtmf_frontend_id' => $fe->id, 'sort_order' => $k, ...$ep]);
        }

        return $fe;
    }

    // ── FR Items ───────────────────────────────────────────────────────────

    private function itemsBnc(): array
    {
        return [
            ['screen_name' => 'Tab: Aktif',                'id_fr' => 'PRF-ASN-23-FR-001', 'type' => 'Table',      'label' => 'Senarai Bencana Aktif',        'mandatory' => false, 'table_fieldname' => null,                          'condition' => 'Tarikh mula/tamat dalam julat hari ini',          'validation' => null,                      'status' => 'missing'],
            ['screen_name' => 'Tab: Tidak Aktif',          'id_fr' => 'PRF-ASN-23-FR-002', 'type' => 'Table',      'label' => 'Senarai Bencana Tidak Aktif',   'mandatory' => false, 'table_fieldname' => null,                          'condition' => 'Tarikh tamat sebelum hari ini',                   'validation' => null,                      'status' => 'missing'],
            ['screen_name' => 'Tab: Semua',                'id_fr' => 'PRF-ASN-23-FR-003', 'type' => 'Table',      'label' => 'Semua Bencana',                 'mandatory' => false, 'table_fieldname' => null,                          'condition' => null,                                              'validation' => null,                      'status' => 'missing'],
            ['screen_name' => 'Modal: Tambah/Edit Bencana','id_fr' => 'PRF-ASN-23-FR-004', 'type' => 'Text',       'label' => 'Nama Bencana',                  'mandatory' => true,  'table_fieldname' => 'profil_bencana.nama_bencana', 'condition' => null,                                              'validation' => 'required|max:255',        'status' => 'missing'],
            ['screen_name' => 'Modal: Tambah/Edit Bencana','id_fr' => 'PRF-ASN-23-FR-005', 'type' => 'Date',       'label' => 'Tarikh Mula',                   'mandatory' => true,  'table_fieldname' => 'profil_bencana.tarikh_mula',  'condition' => null,                                              'validation' => 'required|date',           'status' => 'missing'],
            ['screen_name' => 'Modal: Tambah/Edit Bencana','id_fr' => 'PRF-ASN-23-FR-006', 'type' => 'Date',       'label' => 'Tarikh Tamat',                  'mandatory' => true,  'table_fieldname' => 'profil_bencana.tarikh_tamat', 'condition' => null,                                              'validation' => 'required|date|after:tarikh_mula', 'status' => 'missing'],
            ['screen_name' => 'Modal: Tambah/Edit Bencana','id_fr' => 'PRF-ASN-23-FR-007', 'type' => 'Map',        'label' => 'Koordinat Lokasi Bencana',      'mandatory' => false, 'table_fieldname' => 'profil_bencana.latitude,longitude', 'condition' => null,                                         'validation' => 'nullable',                'status' => 'missing'],
            ['screen_name' => 'Modal: Import',             'id_fr' => 'PRF-ASN-23-FR-008', 'type' => 'FileUpload', 'label' => 'Import Excel/CSV Bencana',      'mandatory' => false, 'table_fieldname' => null,                          'condition' => null,                                              'validation' => 'nullable|mimes:xlsx,csv', 'status' => 'missing'],
        ];
    }

    private function itemsPrm01(): array
    {
        return [
            ['screen_name' => 'Tab: Draf',        'id_fr' => 'PRF-ASN-24-FR-001', 'type' => 'Table',  'label' => 'Senarai Draf',          'mandatory' => false, 'table_fieldname' => null,             'condition' => 'Status PPP_DRAF',     'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Dalam Proses', 'id_fr' => 'PRF-ASN-24-FR-002', 'type' => 'Table',  'label' => 'Senarai Dalam Proses',  'mandatory' => false, 'table_fieldname' => null,             'condition' => 'Status BARU/LENGKAP', 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Pindaan',      'id_fr' => 'PRF-ASN-24-FR-003', 'type' => 'Table',  'label' => 'Senarai Pindaan',       'mandatory' => false, 'table_fieldname' => null,             'condition' => 'Status PINDAAN_*',    'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Lulus',        'id_fr' => 'PRF-ASN-24-FR-004', 'type' => 'Table',  'label' => 'Senarai Lulus',         'mandatory' => false, 'table_fieldname' => null,             'condition' => 'Status LULUS',        'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Tidak Lulus',  'id_fr' => 'PRF-ASN-24-FR-005', 'type' => 'Table',  'label' => 'Senarai Tidak Lulus',   'mandatory' => false, 'table_fieldname' => null,             'condition' => 'Status TIDAK_LULUS',  'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Semua',        'id_fr' => 'PRF-ASN-24-FR-006', 'type' => 'Table',  'label' => 'Semua Permohonan',      'mandatory' => false, 'table_fieldname' => null,             'condition' => null,                  'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Filter Bar',        'id_fr' => 'PRF-ASN-24-FR-007', 'type' => 'Select', 'label' => 'Filter Kategori Asnaf', 'mandatory' => false, 'table_fieldname' => 'kategori_asnaf', 'condition' => 'Fakir/Miskin/Non FK-MS/Semua', 'validation' => 'nullable', 'status' => 'missing'],
        ];
    }

    private function itemsPrm02(): array
    {
        return [
            ['screen_name' => 'Import',          'id_fr' => 'PRF-ASN-25-FR-001', 'type' => 'FileUpload', 'label' => 'Upload Excel/CSV Pemohon',             'mandatory' => false, 'table_fieldname' => null,             'condition' => null,                                          'validation' => 'nullable|mimes:xlsx,csv',    'status' => 'missing'],
            ['screen_name' => 'Senarai Semakan', 'id_fr' => 'PRF-ASN-25-FR-002', 'type' => 'Table',      'label' => 'Staging Pool (PENDING/CHECKING)',      'mandatory' => false, 'table_fieldname' => 'staging_pool',   'condition' => 'Menunggu semakan JPN',                        'validation' => null,                         'status' => 'missing'],
            ['screen_name' => 'Senarai Semakan', 'id_fr' => 'PRF-ASN-25-FR-003', 'type' => 'Button',     'label' => 'Semak JPN',                           'mandatory' => false, 'table_fieldname' => null,             'condition' => 'Mencetuskan semakan identiti via JPN API',    'validation' => null,                         'status' => 'missing'],
            ['screen_name' => 'Data Rosak',      'id_fr' => 'PRF-ASN-25-FR-004', 'type' => 'Table',      'label' => 'Data Rosak (Ralat Validasi)',          'mandatory' => false, 'table_fieldname' => 'staging_pool',   'condition' => 'Status ROSAK',                                'validation' => null,                         'status' => 'missing'],
            ['screen_name' => 'Senarai Pemohon', 'id_fr' => 'PRF-ASN-25-FR-005', 'type' => 'Table',      'label' => 'Senarai Pemohon (Lulus Semakan)',      'mandatory' => false, 'table_fieldname' => 'staging_pool',   'condition' => 'Status OK',                                   'validation' => null,                         'status' => 'missing'],
            ['screen_name' => 'Peta',            'id_fr' => 'PRF-ASN-25-FR-006', 'type' => 'Map',        'label' => 'Peta Radius Bencana + Lokasi Pemohon', 'mandatory' => false, 'table_fieldname' => null,             'condition' => 'Leaflet; titik tengah dari koordinat bencana','validation' => null,                         'status' => 'missing'],
            ['screen_name' => 'Kategori',        'id_fr' => 'PRF-ASN-25-FR-007', 'type' => 'Select',     'label' => 'Kategori Asnaf',                      'mandatory' => true,  'table_fieldname' => 'kategori_asnaf', 'condition' => 'Fakir/Miskin/Non FK-MS; semak via API',       'validation' => 'required',                   'status' => 'missing'],
            ['screen_name' => 'Form Manual',     'id_fr' => 'PRF-ASN-25-FR-008', 'type' => 'Text',       'label' => 'Nama Penuh',                          'mandatory' => true,  'table_fieldname' => 'nama_penuh',     'condition' => null,                                          'validation' => 'required|max:100|uppercase', 'status' => 'missing'],
            ['screen_name' => 'Form Manual',     'id_fr' => 'PRF-ASN-25-FR-009', 'type' => 'Text',       'label' => 'No. Pengenalan',                      'mandatory' => true,  'table_fieldname' => 'no_pengenalan',  'condition' => null,                                          'validation' => 'required|max:20',            'status' => 'missing'],
            ['screen_name' => 'Form Manual',     'id_fr' => 'PRF-ASN-25-FR-010', 'type' => 'Text',       'label' => 'Poskod',                              'mandatory' => true,  'table_fieldname' => 'poskod',         'condition' => 'Mencetuskan geocod & carian negeri/daerah',   'validation' => 'required|digits:5',          'status' => 'missing'],
        ];
    }

    private function itemsPrm03(): array
    {
        return [
            ['screen_name' => 'Lihat',   'id_fr' => 'PRF-ASN-26-FR-001', 'type' => 'Table', 'label' => 'Senarai Pemohon dalam Batch (Baca Sahaja)', 'mandatory' => false, 'table_fieldname' => null, 'condition' => 'mode=lihat',      'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Edit',    'id_fr' => 'PRF-ASN-26-FR-002', 'type' => 'Table', 'label' => 'Senarai Pemohon (Mod Edit)',               'mandatory' => false, 'table_fieldname' => null, 'condition' => 'mode=permohonan', 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Pindaan', 'id_fr' => 'PRF-ASN-26-FR-003', 'type' => 'Table', 'label' => 'Senarai Pemohon (Mod Pindaan)',            'mandatory' => false, 'table_fieldname' => null, 'condition' => 'Status PINDAAN_*','validation' => null, 'status' => 'missing'],
        ];
    }

    private function itemsPrm04(): array
    {
        return [
            ['screen_name' => 'Staging', 'id_fr' => 'PRF-ASN-27-FR-001', 'type' => 'Table', 'label' => 'Sambung Semula Staging Pool',    'mandatory' => false, 'table_fieldname' => 'staging_pool', 'condition' => 'Draf yang belum dihantar', 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Pemohon', 'id_fr' => 'PRF-ASN-27-FR-002', 'type' => 'Table', 'label' => 'Sambung Semula Senarai Pemohon', 'mandatory' => false, 'table_fieldname' => 'staging_pool', 'condition' => 'Status OK dari draf',     'validation' => null, 'status' => 'missing'],
        ];
    }

    private function itemsCar(): array
    {
        return [
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-ASN-28-FR-001', 'type' => 'Text',  'label' => 'Nama',                   'mandatory' => false, 'table_fieldname' => 'nama_penuh',    'condition' => 'Min 2 aksara; auto uppercase',    'validation' => 'nullable|min:2',   'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-ASN-28-FR-002', 'type' => 'Text',  'label' => 'No. Pengenalan',         'mandatory' => false, 'table_fieldname' => 'no_pengenalan', 'condition' => 'MyKad 12 digit / Pasport max 20', 'validation' => 'nullable|max:20',  'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-ASN-28-FR-003', 'type' => 'Text',  'label' => 'No. Rujukan Permohonan', 'mandatory' => false, 'table_fieldname' => 'no_rujukan',    'condition' => null,                             'validation' => 'nullable',         'status' => 'missing'],
            ['screen_name' => 'Keputusan',   'id_fr' => 'PRF-ASN-28-FR-004', 'type' => 'Table', 'label' => 'Keputusan Carian',       'mandatory' => false, 'table_fieldname' => null,             'condition' => null,                             'validation' => null,               'status' => 'missing'],
        ];
    }

    private function itemsPrs(): array
    {
        return [
            ['screen_name' => 'Tab: Maklumat Pemohon', 'id_fr' => 'PRF-ASN-29-FR-001', 'type' => 'Text',    'label' => 'Nama Penuh',      'mandatory' => true,  'table_fieldname' => 'nama_penuh',     'condition' => null,                                'validation' => 'required|max:100|uppercase', 'status' => 'missing'],
            ['screen_name' => 'Tab: Maklumat Pemohon', 'id_fr' => 'PRF-ASN-29-FR-002', 'type' => 'Text',    'label' => 'No. Pengenalan', 'mandatory' => true,  'table_fieldname' => 'no_pengenalan',  'condition' => null,                                'validation' => 'required|max:20',            'status' => 'missing'],
            ['screen_name' => 'Tab: Maklumat Pemohon', 'id_fr' => 'PRF-ASN-29-FR-003', 'type' => 'Select',  'label' => 'Kategori Asnaf', 'mandatory' => true,  'table_fieldname' => 'kategori_asnaf', 'condition' => 'Fakir/Miskin/Non FK-MS',            'validation' => 'required',                   'status' => 'missing'],
            ['screen_name' => 'Tab: Maklumat Pemohon', 'id_fr' => 'PRF-ASN-29-FR-004', 'type' => 'Select',  'label' => 'Bencana',        'mandatory' => true,  'table_fieldname' => 'id_bencana',     'condition' => 'Senarai bencana aktif',             'validation' => 'required',                   'status' => 'missing'],
            ['screen_name' => 'Tab: Ringkasan',        'id_fr' => 'PRF-ASN-29-FR-005', 'type' => 'Summary', 'label' => 'Had Kifayah',    'mandatory' => false, 'table_fieldname' => null,             'condition' => 'Auto-dikira berdasarkan tanggungan', 'validation' => null,                         'status' => 'missing'],
        ];
    }

    // ── API Endpoints ──────────────────────────────────────────────────────

    private function epBnc(): array
    {
        return [
            ['method' => 'GET',    'endpoint' => '/profiling/bantuan-pukal/bencana',       'description' => 'Fetch all disasters (supports status filter)'],
            ['method' => 'POST',   'endpoint' => '/profiling/bantuan-pukal/bencana',       'description' => 'Create new disaster record'],
            ['method' => 'PUT',    'endpoint' => '/profiling/bantuan-pukal/bencana/{id}',  'description' => 'Update disaster record'],
            ['method' => 'DELETE', 'endpoint' => '/profiling/bantuan-pukal/bencana/{id}',  'description' => 'Delete disaster record'],
        ];
    }

    private function epPrm01(): array
    {
        return [
            ['method' => 'GET',    'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}',                'description' => 'Fetch disaster details (header info)'],
            ['method' => 'GET',    'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/batch',          'description' => 'Fetch batch list for a disaster'],
            ['method' => 'GET',    'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/drafts',         'description' => 'Fetch PPP draft list for a disaster'],
            ['method' => 'POST',   'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/hantar-draft',   'description' => 'Submit all drafts as batch applications'],
            ['method' => 'DELETE', 'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/batch/{batchId}','description' => 'Soft-delete a batch'],
        ];
    }

    private function epPrm02(): array
    {
        return [
            ['method' => 'GET',    'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/staging',                'description' => 'Fetch staging pool rows'],
            ['method' => 'POST',   'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/staging/semak',          'description' => 'Trigger JPN identity check on staging rows'],
            ['method' => 'DELETE', 'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/staging/{id}',           'description' => 'Remove a staging row'],
            ['method' => 'GET',    'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/drafts',                 'description' => 'Fetch PPP drafts (resume draft state)'],
            ['method' => 'POST',   'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/batch',                  'description' => 'Create new batch from applicant list'],
            ['method' => 'POST',   'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/hantar-draft',           'description' => 'Convert drafts to batch applications'],
            ['method' => 'GET',    'endpoint' => '/profiling/pendaftaran-pantas/geocode-poskod/{poskod}',               'description' => 'Geocode postal code to coordinates'],
            ['method' => 'GET',    'endpoint' => '/kod/poskod/{poskod}',                                                'description' => 'Validate postal code reference'],
            ['method' => 'GET',    'endpoint' => '/kod/getSub/NEGERI',                                                  'description' => 'Fetch state list'],
            ['method' => 'GET',    'endpoint' => '/kod/getSub/DAERAH',                                                  'description' => 'Fetch district list'],
            ['method' => 'POST',   'endpoint' => '/profiling/bantuan-pukal/bencana/check-kategori-asnaf/{noPengenalan}','description' => 'Check asnaf category for applicant'],
            ['method' => 'GET',    'endpoint' => '/profiling/bantuan-pukal/bencana/check-aid-history/{noPengenalan}',   'description' => 'Check prior aid history for disaster'],
            ['method' => 'GET',    'endpoint' => '/konfigurasi/bantuan-hierarchy/bantuan',                              'description' => 'Fetch aid types'],
            ['method' => 'GET',    'endpoint' => '/konfigurasi/bantuan-hierarchy/pakej-kelayakan',                      'description' => 'Fetch eligibility packages'],
        ];
    }

    private function epPrm03(): array
    {
        return [
            ['method' => 'GET', 'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/batch/{batchId}', 'description' => 'Fetch batch details with full applicant list'],
        ];
    }

    private function epPrm04(): array
    {
        return [
            ['method' => 'GET',  'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/drafts',      'description' => 'Load saved draft state'],
            ['method' => 'GET',  'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/staging',     'description' => 'Resume staging pool from draft'],
            ['method' => 'POST', 'endpoint' => '/profiling/bantuan-pukal/bencana/{bencanaId}/hantar-draft','description' => 'Submit draft as batch application'],
        ];
    }

    private function epCar(): array
    {
        return [
            ['method' => 'GET', 'endpoint' => '/profiling/bantuan-pukal/bencana/carian-pemohon',           'description' => 'Search applicants by name, IC, or reference number'],
            ['method' => 'GET', 'endpoint' => '/profiling/bantuan-pukal/bencana/batch-bencana/{batchId}',  'description' => 'Resolve disaster ID from batch ID (for navigation)'],
        ];
    }

    private function epPrs(): array
    {
        return [
            ['method' => 'GET',  'endpoint' => '/profiling/pendaftaran-pantas/perseorangan',       'description' => 'Fetch individual registration list'],
            ['method' => 'POST', 'endpoint' => '/profiling/pendaftaran-pantas/perseorangan',       'description' => 'Submit new individual registration'],
            ['method' => 'GET',  'endpoint' => '/profiling/pendaftaran-pantas/perseorangan/{id}',  'description' => 'Fetch individual registration detail'],
        ];
    }
}
