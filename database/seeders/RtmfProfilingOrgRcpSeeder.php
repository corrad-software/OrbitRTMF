<?php

namespace Database\Seeders;

use App\Models\RtmfActor;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendApiEndpoint;
use App\Models\RtmfFrontendItem;
use App\Models\RtmfModule;
use App\Models\RtmfSubModule;
use Illuminate\Database\Seeder;

class RtmfProfilingOrgRcpSeeder extends Seeder
{
    public function run(): void
    {
        $module = RtmfModule::where('code', 'PRF')->firstOrFail();

        $org = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'ORG'],
            ['name' => 'Organisasi', 'sort_order' => 20],
        );
        $rcp = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'RCP'],
            ['name' => 'Recipient', 'sort_order' => 30],
        );

        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        $staff   = [$pegawai->id, $penyelia->id];
        $approve = [$pelulus->id];
        $all     = [$pegawai->id, $penyelia->id, $pelulus->id];

        // ══════════════════════════════════════════════════════════════════
        // ORGANISASI
        // ══════════════════════════════════════════════════════════════════

        $o01 = $this->seed($module->id, $org->id, 'PRF-ORG-01', 'Carian Organisasi',
            'pages/profiling/organisasi/carian/index.vue', 10, $staff,
            $this->itemsOrgCarian(), [
                ['method' => 'GET', 'endpoint' => '/profiling/organisasi/carian', 'description' => 'Search organisations (params: jenisOrganisasi, namaOrganisasi, daerah, jenisId, noPendaftaran)'],
                ['method' => 'GET', 'endpoint' => '/kod',                          'description' => 'Fetch organisation type reference codes'],
            ]);

        $o02 = $this->seed($module->id, $org->id, 'PRF-ORG-02', 'Senarai Organisasi',
            'pages/profiling/organisasi/senarai/index.vue', 20, $staff,
            $this->itemsSenarai('Senarai Organisasi'), [
                ['method' => 'GET', 'endpoint' => '/profiling/organisasi/semua-status', 'description' => 'Fetch all organisations with status filter'],
            ]);

        $o03 = $this->seed($module->id, $org->id, 'PRF-ORG-03', 'Pendaftaran Organisasi',
            'pages/profiling/organisasi/pendaftaran/index.vue', 30, $staff,
            $this->itemsOrgPendaftaran(), [
                ['method' => 'GET',  'endpoint' => '/kod',                          'description' => 'Fetch reference codes (jenis organisasi, masjid, institusi, struktur)'],
                ['method' => 'POST', 'endpoint' => '/profiling/organisasi',         'description' => 'Submit new organisation registration'],
                ['method' => 'GET',  'endpoint' => '/kod/getSub/NEGERI',            'description' => 'Fetch state list'],
                ['method' => 'GET',  'endpoint' => '/kod/getSub/DAERAH',            'description' => 'Fetch district list'],
                ['method' => 'GET',  'endpoint' => '/kod/getSub/KARIAH',            'description' => 'Fetch kariah list'],
            ]);

        $o04 = $this->seed($module->id, $org->id, 'PRF-ORG-04', 'Pengesahan Organisasi',
            'pages/profiling/organisasi/pengesahan/[id].vue', 40, $staff,
            $this->itemsOrgDetail('Pengesahan'), [
                ['method' => 'GET',  'endpoint' => '/profiling/organisasi/{id}',    'description' => 'Fetch organisation detail for verification'],
                ['method' => 'POST', 'endpoint' => '/profiling/organisasi/{id}/pengesahan', 'description' => 'Submit verification decision'],
            ]);

        $o05 = $this->seed($module->id, $org->id, 'PRF-ORG-05', 'Kemaskini Organisasi',
            'pages/profiling/organisasi/kemaskini/[id].vue', 50, $staff,
            $this->itemsOrgDetail('Kemaskini'), [
                ['method' => 'GET',   'endpoint' => '/profiling/organisasi/{id}',   'description' => 'Fetch organisation for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/organisasi/{id}',   'description' => 'Update organisation record'],
            ]);

        $o06 = $this->seed($module->id, $org->id, 'PRF-ORG-06', 'Kemaskini Profil Organisasi',
            'pages/profiling/organisasi/kemaskini-profil/[id].vue', 60, $staff,
            $this->itemsOrgDetail('Kemaskini Profil'), [
                ['method' => 'GET',   'endpoint' => '/profiling/organisasi/{id}',   'description' => 'Fetch organisation profile for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/organisasi/{id}',   'description' => 'Update organisation profile'],
            ]);

        $o07 = $this->seed($module->id, $org->id, 'PRF-ORG-07', 'Lihat Profil Organisasi',
            'pages/profiling/organisasi/view/[id].vue', 70, $all,
            $this->itemsOrgDetail('Lihat'), [
                ['method' => 'GET', 'endpoint' => '/profiling/organisasi/{id}',     'description' => 'Fetch full organisation profile (read-only)'],
            ]);

        $o08 = $this->seed($module->id, $org->id, 'PRF-ORG-08', 'Lihat Profil Organisasi (Variant)',
            'pages/profiling/organisasi/view-profile/[id].vue', 80, $all,
            $this->itemsOrgDetail('Lihat'), [
                ['method' => 'GET', 'endpoint' => '/profiling/organisasi/{id}',     'description' => 'Fetch full organisation profile (variant view)'],
            ]);

        $o09 = $this->seed($module->id, $org->id, 'PRF-ORG-09', 'Senarai Kelulusan Organisasi',
            'pages/profiling/organisasi/kelulusan/index.vue', 90, $approve,
            $this->itemsKelulusanSenarai(), [
                ['method' => 'GET', 'endpoint' => '/profiling/organisasi/semua-status', 'description' => 'Fetch organisations by approval status (kodStatus filter)'],
                ['method' => 'GET', 'endpoint' => '/kod',                               'description' => 'Fetch organisation type reference codes'],
            ]);

        $o10 = $this->seed($module->id, $org->id, 'PRF-ORG-10', 'Butiran Kelulusan Organisasi',
            'pages/profiling/organisasi/kelulusan/[id].vue', 100, $approve,
            $this->itemsOrgDetail('Kelulusan'), [
                ['method' => 'GET',  'endpoint' => '/profiling/organisasi/{id}',        'description' => 'Fetch organisation detail for approval'],
                ['method' => 'POST', 'endpoint' => '/profiling/organisasi/{id}/kelulusan', 'description' => 'Submit approval decision (Lulus/Tolak)'],
            ]);

        // Bantuan workflow
        $o11 = $this->seed($module->id, $org->id, 'PRF-ORG-11', 'Bantuan Organisasi',
            'pages/profiling/organisasi/bantuan/[id].vue', 110, $staff,
            $this->itemsBantuanDetail(), [
                ['method' => 'GET',  'endpoint' => '/bantuan/tugasan/organisasi/{id}',  'description' => 'Fetch organisation assistance detail'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tugasan/organisasi/{id}',  'description' => 'Submit assistance application'],
            ]);

        $o12 = $this->seed($module->id, $org->id, 'PRF-ORG-12', 'Senarai Kelulusan Bantuan Organisasi',
            'pages/profiling/organisasi/bantuan/kelulusan-bantuan/index.vue', 120, $approve,
            $this->itemsBantuanSenarai(), [
                ['method' => 'GET', 'endpoint' => '/bantuan/tugasan/senarai-organisasi', 'description' => 'Fetch organisation assistance pending approval'],
            ]);

        $o13 = $this->seed($module->id, $org->id, 'PRF-ORG-13', 'Butiran Kelulusan Bantuan Organisasi',
            'pages/profiling/organisasi/bantuan/kelulusan-bantuan/[id].vue', 130, $approve,
            $this->itemsBantuanDetail(), [
                ['method' => 'GET',  'endpoint' => '/bantuan/tugasan/organisasi/{id}',          'description' => 'Fetch assistance detail for approval'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tugasan/organisasi/{id}/kelulusan', 'description' => 'Submit assistance approval (Lulus/Tolak)'],
            ]);

        $o14 = $this->seed($module->id, $org->id, 'PRF-ORG-14', 'Senarai Semakan Bantuan Organisasi',
            'pages/profiling/organisasi/bantuan/semakan-bantuan/index.vue', 140, $staff,
            $this->itemsBantuanSenarai(), [
                ['method' => 'GET', 'endpoint' => '/bantuan/tugasan/senarai-organisasi', 'description' => 'Fetch organisation assistance for review'],
            ]);

        $o15 = $this->seed($module->id, $org->id, 'PRF-ORG-15', 'Butiran Semakan Bantuan Organisasi',
            'pages/profiling/organisasi/bantuan/semakan-bantuan/[id].vue', 150, $staff,
            $this->itemsBantuanDetail(), [
                ['method' => 'GET',  'endpoint' => '/bantuan/tugasan/organisasi/{id}',       'description' => 'Fetch assistance detail for review'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tugasan/organisasi/{id}/semak', 'description' => 'Submit review decision'],
            ]);

        $o16 = $this->seed($module->id, $org->id, 'PRF-ORG-16', 'Senarai Siasatan Bantuan Organisasi',
            'pages/profiling/organisasi/bantuan/siasatan-bantuan/index.vue', 160, $staff,
            $this->itemsBantuanSenarai(), [
                ['method' => 'GET', 'endpoint' => '/bantuan/tugasan/senarai-organisasi', 'description' => 'Fetch organisation assistance for investigation'],
            ]);

        $o17 = $this->seed($module->id, $org->id, 'PRF-ORG-17', 'Butiran Siasatan Bantuan Organisasi',
            'pages/profiling/organisasi/bantuan/siasatan-bantuan/[id].vue', 170, $staff,
            $this->itemsBantuanDetail(), [
                ['method' => 'GET',  'endpoint' => '/bantuan/tugasan/organisasi/{id}',         'description' => 'Fetch assistance detail for investigation'],
                ['method' => 'POST', 'endpoint' => '/bantuan/tugasan/organisasi/{id}/siasatan','description' => 'Submit investigation decision'],
            ]);

        // Organisasi page links
        $o01->linksTo()->sync([$o07->id, $o08->id]);
        $o02->linksTo()->sync([$o07->id, $o08->id]);
        $o09->linksTo()->sync([$o10->id]);
        $o12->linksTo()->sync([$o13->id]);
        $o14->linksTo()->sync([$o15->id]);
        $o16->linksTo()->sync([$o17->id]);

        // ══════════════════════════════════════════════════════════════════
        // RECIPIENT
        // ══════════════════════════════════════════════════════════════════

        $r01 = $this->seed($module->id, $rcp->id, 'PRF-RCP-01', 'Carian Recipient',
            'pages/profiling/recipient/carian/index.vue', 10, $staff,
            $this->itemsRcpCarian(), [
                ['method' => 'GET', 'endpoint' => '/profiling/recipient/carian',                            'description' => 'Search recipients (params: jenisRecipient, nama, jenisPengenalan, noPengenalan)'],
                ['method' => 'GET', 'endpoint' => '/profiling/pendaftaran-lengkap/individu/check-existing', 'description' => 'Check if individual already exists before new registration'],
            ]);

        $r02 = $this->seed($module->id, $rcp->id, 'PRF-RCP-02', 'Senarai Recipient',
            'pages/profiling/recipient/senarai/index.vue', 20, $staff,
            $this->itemsSenarai('Senarai Recipient'), [
                ['method' => 'GET', 'endpoint' => '/profiling/recipient', 'description' => 'Fetch all recipients with status filter'],
            ]);

        $r03 = $this->seed($module->id, $rcp->id, 'PRF-RCP-03', 'Pendaftaran Recipient',
            'pages/profiling/recipient/pendaftaran/index.vue', 30, $staff,
            $this->itemsRcpPendaftaran(), [
                ['method' => 'GET',  'endpoint' => '/kod',                                                  'description' => 'Fetch reference codes (jenis recipient, pengenalan)'],
                ['method' => 'POST', 'endpoint' => '/profiling/recipient',                                  'description' => 'Submit new recipient registration'],
                ['method' => 'GET',  'endpoint' => '/profiling/recipient/check-ic',                        'description' => 'Validate IC against database (background check)'],
                ['method' => 'GET',  'endpoint' => '/profiling/recipient/check-email',                     'description' => 'Validate email uniqueness (background check)'],
                ['method' => 'GET',  'endpoint' => '/kod/getSub/NEGERI',                                   'description' => 'Fetch state list'],
                ['method' => 'GET',  'endpoint' => '/kod/getSub/DAERAH',                                   'description' => 'Fetch district list'],
            ]);

        $r04 = $this->seed($module->id, $rcp->id, 'PRF-RCP-04', 'Pengesahan Recipient',
            'pages/profiling/recipient/pengesahan/[id].vue', 40, $staff,
            $this->itemsRcpDetail('Pengesahan'), [
                ['method' => 'GET',  'endpoint' => '/profiling/recipient/{id}',           'description' => 'Fetch recipient detail for verification'],
                ['method' => 'POST', 'endpoint' => '/profiling/recipient/{id}/pengesahan','description' => 'Submit verification decision'],
            ]);

        $r05 = $this->seed($module->id, $rcp->id, 'PRF-RCP-05', 'Kemaskini Recipient',
            'pages/profiling/recipient/kemaskini/[id].vue', 50, $staff,
            $this->itemsRcpDetail('Kemaskini'), [
                ['method' => 'GET',   'endpoint' => '/profiling/recipient/{id}',          'description' => 'Fetch recipient for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/recipient/{id}',          'description' => 'Update recipient record'],
            ]);

        $r06 = $this->seed($module->id, $rcp->id, 'PRF-RCP-06', 'Kemaskini Profil Recipient',
            'pages/profiling/recipient/kemaskini-profil/[id].vue', 60, $staff,
            $this->itemsRcpDetail('Kemaskini Profil'), [
                ['method' => 'GET',   'endpoint' => '/profiling/recipient/{id}',          'description' => 'Fetch recipient profile for editing'],
                ['method' => 'PATCH', 'endpoint' => '/profiling/recipient/{id}',          'description' => 'Update recipient profile'],
            ]);

        $r07 = $this->seed($module->id, $rcp->id, 'PRF-RCP-07', 'Lihat Profil Recipient',
            'pages/profiling/recipient/view/[id].vue', 70, $all,
            $this->itemsRcpDetail('Lihat'), [
                ['method' => 'GET', 'endpoint' => '/profiling/recipient/{id}',            'description' => 'Fetch full recipient profile (read-only)'],
            ]);

        $r08 = $this->seed($module->id, $rcp->id, 'PRF-RCP-08', 'Lihat Profil Recipient (Variant)',
            'pages/profiling/recipient/view-profile/[id].vue', 80, $all,
            $this->itemsRcpDetail('Lihat'), [
                ['method' => 'GET', 'endpoint' => '/profiling/recipient/{id}',            'description' => 'Fetch full recipient profile (variant view)'],
            ]);

        $r09 = $this->seed($module->id, $rcp->id, 'PRF-RCP-09', 'Lihat Profil Recipient Tidak Sah',
            'pages/profiling/recipient/view-tidak-sah/[id].vue', 90, $all,
            $this->itemsRcpDetail('Tidak Sah'), [
                ['method' => 'GET', 'endpoint' => '/profiling/recipient/{id}',            'description' => 'Fetch invalid/rejected recipient profile'],
            ]);

        $r10 = $this->seed($module->id, $rcp->id, 'PRF-RCP-10', 'Senarai Kelulusan Recipient',
            'pages/profiling/recipient/kelulusan/index.vue', 100, $approve,
            $this->itemsSenarai('Menunggu Kelulusan'), [
                ['method' => 'GET', 'endpoint' => '/profiling/recipient/menunggu-kelulusan', 'description' => 'Fetch recipients pending approval'],
            ]);

        $r11 = $this->seed($module->id, $rcp->id, 'PRF-RCP-11', 'Butiran Kelulusan Recipient',
            'pages/profiling/recipient/kelulusan/[id].vue', 110, $approve,
            $this->itemsRcpDetail('Kelulusan'), [
                ['method' => 'GET',  'endpoint' => '/profiling/recipient/{id}',           'description' => 'Fetch recipient detail for approval'],
                ['method' => 'POST', 'endpoint' => '/profiling/recipient/{id}/kelulusan', 'description' => 'Submit approval decision (Lulus/Tolak)'],
            ]);

        $r12 = $this->seed($module->id, $rcp->id, 'PRF-RCP-12', 'Pembangunan',
            'pages/profiling/recipient/pembangunan.vue', 120, $staff,
            [['screen_name' => 'Halaman Utama', 'id_fr' => 'PRF-RCP-12-FR-001', 'type' => 'Display',
              'label' => 'Halaman Pembangunan (Under Construction)', 'mandatory' => false,
              'table_fieldname' => null, 'condition' => 'Placeholder page', 'validation' => null, 'status' => 'missing']],
            []);

        // Recipient page links
        $r01->linksTo()->sync([$r07->id, $r08->id, $r03->id]);
        $r02->linksTo()->sync([$r07->id, $r08->id]);
        $r10->linksTo()->sync([$r11->id]);
    }

    // ── Helper ─────────────────────────────────────────────────────────────

    private function seed(int $moduleId, int $subModuleId, string $specId, string $title,
        string $vuePath, int $sortOrder, array $actorIds, array $items, array $endpoints): RtmfFrontend
    {
        $fe = RtmfFrontend::updateOrCreate(
            ['spec_id' => $specId],
            ['module_id' => $moduleId, 'sub_module_id' => $subModuleId, 'tab_code' => $specId,
             'vue_path' => $vuePath, 'title' => $title, 'is_done' => false, 'sort_order' => $sortOrder],
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

    // ── Shared helpers ─────────────────────────────────────────────────────

    private function itemsSenarai(string $label): array
    {
        return [
            ['screen_name' => 'Senarai', 'id_fr' => 'PLACEHOLDER-1', 'type' => 'Table', 'label' => $label,
             'mandatory' => false, 'table_fieldname' => null, 'condition' => null, 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tindakan', 'id_fr' => 'PLACEHOLDER-2', 'type' => 'Button', 'label' => 'Butang Tindakan (Lihat / Proses)',
             'mandatory' => false, 'table_fieldname' => null, 'condition' => null, 'validation' => null, 'status' => 'missing'],
        ];
    }

    private function itemsKelulusanSenarai(): array
    {
        return [
            ['screen_name' => 'Tab: Menunggu Kelulusan', 'id_fr' => 'PLACEHOLDER-1', 'type' => 'Table',
             'label' => 'Menunggu Kelulusan', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => null, 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Diluluskan', 'id_fr' => 'PLACEHOLDER-2', 'type' => 'Table',
             'label' => 'Diluluskan', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => null, 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Tidak Diluluskan', 'id_fr' => 'PLACEHOLDER-3', 'type' => 'Table',
             'label' => 'Tidak Diluluskan', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => null, 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tindakan', 'id_fr' => 'PLACEHOLDER-4', 'type' => 'Button',
             'label' => 'Kelulusan / Lihat Profil', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => null, 'validation' => null, 'status' => 'missing'],
        ];
    }

    private function itemsBantuanSenarai(): array
    {
        return [
            ['screen_name' => 'Tab: Menunggu Kelulusan', 'id_fr' => 'PLACEHOLDER-1', 'type' => 'Table',
             'label' => 'Bantuan Menunggu Kelulusan', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => null, 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Selesai', 'id_fr' => 'PLACEHOLDER-2', 'type' => 'Table',
             'label' => 'Bantuan Selesai', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => null, 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tab: Semua', 'id_fr' => 'PLACEHOLDER-3', 'type' => 'Table',
             'label' => 'Semua Bantuan', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => null, 'validation' => null, 'status' => 'missing'],
        ];
    }

    private function itemsBantuanDetail(): array
    {
        return [
            ['screen_name' => 'Maklumat Organisasi', 'id_fr' => 'PLACEHOLDER-1', 'type' => 'Display',
             'label' => 'Nama Organisasi', 'mandatory' => false, 'table_fieldname' => 'organisasi.nama',
             'condition' => 'Baca sahaja', 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Bantuan', 'id_fr' => 'PLACEHOLDER-2', 'type' => 'Table',
             'label' => 'Senarai Bantuan Dipohon', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => null, 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tindakan', 'id_fr' => 'PLACEHOLDER-3', 'type' => 'Button',
             'label' => 'Butang Lulus / Tolak', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => null, 'validation' => null, 'status' => 'missing'],
        ];
    }

    // ── Organisasi FR Items ────────────────────────────────────────────────

    private function itemsOrgCarian(): array
    {
        return [
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-ORG-01-FR-001', 'type' => 'Select',
             'label' => 'Jenis Organisasi', 'mandatory' => true, 'table_fieldname' => 'jenis_organisasi',
             'condition' => null, 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-ORG-01-FR-002', 'type' => 'Text',
             'label' => 'Nama Organisasi', 'mandatory' => false, 'table_fieldname' => 'nama_organisasi',
             'condition' => null, 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-ORG-01-FR-003', 'type' => 'Select',
             'label' => 'Daerah', 'mandatory' => false, 'table_fieldname' => 'daerah',
             'condition' => 'Dipapar untuk jenis tertentu', 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-ORG-01-FR-004', 'type' => 'Select',
             'label' => 'Jenis ID', 'mandatory' => false, 'table_fieldname' => 'jenis_id',
             'condition' => null, 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-ORG-01-FR-005', 'type' => 'Text',
             'label' => 'Nombor Pendaftaran', 'mandatory' => false, 'table_fieldname' => 'no_pendaftaran',
             'condition' => null, 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Keputusan', 'id_fr' => 'PRF-ORG-01-FR-006', 'type' => 'Table',
             'label' => 'Keputusan Carian Organisasi', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => 'Kolum: Nama, No. Pendaftaran, Jenis, Status, Tindakan', 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tindakan', 'id_fr' => 'PRF-ORG-01-FR-007', 'type' => 'Button',
             'label' => 'Tambah Bantuan', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => 'Dari senarai hasil carian', 'validation' => null, 'status' => 'missing'],
        ];
    }

    private function itemsOrgPendaftaran(): array
    {
        return [
            ['screen_name' => 'Langkah 1: Maklumat Pendaftaran', 'id_fr' => 'PRF-ORG-03-FR-001', 'type' => 'Select',
             'label' => 'Jenis Organisasi', 'mandatory' => true, 'table_fieldname' => 'organization_type',
             'condition' => null, 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Pendaftaran', 'id_fr' => 'PRF-ORG-03-FR-002', 'type' => 'Text',
             'label' => 'Nama Organisasi', 'mandatory' => true, 'table_fieldname' => 'organization_name',
             'condition' => null, 'validation' => 'required|max:255', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Pendaftaran', 'id_fr' => 'PRF-ORG-03-FR-003', 'type' => 'Text',
             'label' => 'Nombor Pendaftaran', 'mandatory' => true, 'table_fieldname' => 'registration_number',
             'condition' => null, 'validation' => 'required|max:50', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Pendaftaran', 'id_fr' => 'PRF-ORG-03-FR-004', 'type' => 'Select',
             'label' => 'Jenis Masjid', 'mandatory' => false, 'table_fieldname' => 'jenis_masjid',
             'condition' => 'Dipapar jika Jenis Organisasi = Masjid', 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Pendaftaran', 'id_fr' => 'PRF-ORG-03-FR-005', 'type' => 'Select',
             'label' => 'Jenis Institusi', 'mandatory' => false, 'table_fieldname' => 'jenis_institusi',
             'condition' => 'Dipapar jika Jenis Organisasi = Institusi', 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Pendaftaran', 'id_fr' => 'PRF-ORG-03-FR-006', 'type' => 'Select',
             'label' => 'Struktur', 'mandatory' => false, 'table_fieldname' => 'structure',
             'condition' => 'Conditional display', 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Maklumat Alamat', 'id_fr' => 'PRF-ORG-03-FR-007', 'type' => 'Text',
             'label' => 'Alamat 1', 'mandatory' => true, 'table_fieldname' => 'alamat_1',
             'condition' => null, 'validation' => 'required|max:100|uppercase', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Maklumat Alamat', 'id_fr' => 'PRF-ORG-03-FR-008', 'type' => 'Text',
             'label' => 'Poskod', 'mandatory' => true, 'table_fieldname' => 'poskod',
             'condition' => 'Mencetuskan carian negeri/daerah', 'validation' => 'required|digits:5', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Maklumat Alamat', 'id_fr' => 'PRF-ORG-03-FR-009', 'type' => 'Select',
             'label' => 'Negeri', 'mandatory' => true, 'table_fieldname' => 'kod_negeri',
             'condition' => 'Auto-isi dari poskod', 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Maklumat Alamat', 'id_fr' => 'PRF-ORG-03-FR-010', 'type' => 'Select',
             'label' => 'Daerah', 'mandatory' => true, 'table_fieldname' => 'kod_daerah',
             'condition' => 'Terkait dengan negeri', 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Maklumat Alamat', 'id_fr' => 'PRF-ORG-03-FR-011', 'type' => 'Select',
             'label' => 'Kariah', 'mandatory' => false, 'table_fieldname' => 'kod_kariah',
             'condition' => 'Terkait dengan daerah', 'validation' => 'nullable', 'status' => 'missing'],
        ];
    }

    private function itemsOrgDetail(string $context): array
    {
        $readonly = in_array($context, ['Lihat', 'Pengesahan', 'Kelulusan']);
        $cond = $readonly ? 'Baca sahaja' : 'Boleh diedit';
        return [
            ['screen_name' => 'Maklumat Asas', 'id_fr' => 'PLACEHOLDER-1', 'type' => 'Text',
             'label' => 'Nama Organisasi', 'mandatory' => !$readonly, 'table_fieldname' => 'organisasi.nama',
             'condition' => $cond, 'validation' => $readonly ? null : 'required|max:255', 'status' => 'missing'],
            ['screen_name' => 'Maklumat Asas', 'id_fr' => 'PLACEHOLDER-2', 'type' => 'Select',
             'label' => 'Jenis Organisasi', 'mandatory' => !$readonly, 'table_fieldname' => 'organisasi.kod_jenis_organisasi',
             'condition' => $cond, 'validation' => $readonly ? null : 'required', 'status' => 'missing'],
            ['screen_name' => 'Maklumat Asas', 'id_fr' => 'PLACEHOLDER-3', 'type' => 'Text',
             'label' => 'Nombor Pendaftaran', 'mandatory' => !$readonly, 'table_fieldname' => 'organisasi.no_pendaftaran',
             'condition' => $cond, 'validation' => $readonly ? null : 'required|max:50', 'status' => 'missing'],
            ['screen_name' => 'Maklumat Asas', 'id_fr' => 'PLACEHOLDER-4', 'type' => 'Text',
             'label' => 'No. Rujukan SAP', 'mandatory' => false, 'table_fieldname' => 'organisasi.no_rujukan_sap',
             'condition' => 'Baca sahaja; dijana sistem', 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Maklumat Alamat', 'id_fr' => 'PLACEHOLDER-5', 'type' => 'Text',
             'label' => 'Alamat 1', 'mandatory' => !$readonly, 'table_fieldname' => 'organisasi.alamat_1',
             'condition' => $cond, 'validation' => $readonly ? null : 'required|max:100', 'status' => 'missing'],
            ['screen_name' => 'Maklumat Alamat', 'id_fr' => 'PLACEHOLDER-6', 'type' => 'Select',
             'label' => 'Negeri', 'mandatory' => !$readonly, 'table_fieldname' => 'organisasi.kod_negeri',
             'condition' => $cond, 'validation' => $readonly ? null : 'required', 'status' => 'missing'],
            ['screen_name' => 'Maklumat Alamat', 'id_fr' => 'PLACEHOLDER-7', 'type' => 'Select',
             'label' => 'Daerah', 'mandatory' => !$readonly, 'table_fieldname' => 'organisasi.kod_daerah',
             'condition' => $cond, 'validation' => $readonly ? null : 'required', 'status' => 'missing'],
            ['screen_name' => 'Tindakan', 'id_fr' => 'PLACEHOLDER-8', 'type' => 'Button',
             'label' => "Butang {$context}", 'mandatory' => false, 'table_fieldname' => null,
             'condition' => "Tindakan {$context}", 'validation' => null, 'status' => 'missing'],
        ];
    }

    // ── Recipient FR Items ─────────────────────────────────────────────────

    private function itemsRcpCarian(): array
    {
        return [
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-RCP-01-FR-001', 'type' => 'Select',
             'label' => 'Jenis Recipient', 'mandatory' => true, 'table_fieldname' => 'jenis_recipient',
             'condition' => 'Individu atau Syarikat — mengubah label medan berikut', 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-RCP-01-FR-002', 'type' => 'Text',
             'label' => 'Nama Individu / Syarikat', 'mandatory' => false, 'table_fieldname' => 'nama',
             'condition' => 'Label berubah mengikut Jenis Recipient', 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-RCP-01-FR-003', 'type' => 'Select',
             'label' => 'Jenis Pengenalan', 'mandatory' => false, 'table_fieldname' => 'jenis_pengenalan',
             'condition' => null, 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-RCP-01-FR-004', 'type' => 'Text',
             'label' => 'No. Pengenalan', 'mandatory' => false, 'table_fieldname' => 'no_pengenalan',
             'condition' => 'Placeholder & validasi bergantung pada Jenis Pengenalan', 'validation' => 'nullable|max:20', 'status' => 'missing'],
            ['screen_name' => 'Keputusan', 'id_fr' => 'PRF-RCP-01-FR-005', 'type' => 'Table',
             'label' => 'Keputusan Carian Recipient', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => 'Kolum: Nama, Jenis Pengenalan, ID, Status, Tindakan', 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Tindakan', 'id_fr' => 'PRF-RCP-01-FR-006', 'type' => 'Button',
             'label' => 'Daftar Recipient Baharu', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => 'Jika IC tidak ditemui dalam sistem', 'validation' => null, 'status' => 'missing'],
        ];
    }

    private function itemsRcpPendaftaran(): array
    {
        return [
            ['screen_name' => 'Langkah 1: Maklumat Recipient', 'id_fr' => 'PRF-RCP-03-FR-001', 'type' => 'Select',
             'label' => 'Jenis Recipient', 'mandatory' => true, 'table_fieldname' => 'jenis_recipient',
             'condition' => 'Individu atau Syarikat', 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Recipient', 'id_fr' => 'PRF-RCP-03-FR-002', 'type' => 'Text',
             'label' => 'Nama Penuh / Nama Syarikat', 'mandatory' => true, 'table_fieldname' => 'nama_penuh',
             'condition' => 'Label conditional; nama_syarikat jika syarikat', 'validation' => 'required|max:255', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Recipient', 'id_fr' => 'PRF-RCP-03-FR-003', 'type' => 'Select',
             'label' => 'Jenis Pengenalan', 'mandatory' => true, 'table_fieldname' => 'jenis_pengenalan',
             'condition' => null, 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Recipient', 'id_fr' => 'PRF-RCP-03-FR-004', 'type' => 'Text',
             'label' => 'No. Pengenalan / No. Syarikat', 'mandatory' => true, 'table_fieldname' => 'id_pengenalan',
             'condition' => 'Format validation bergantung pada jenis; semak keunikan IC/emel di background', 'validation' => 'required|max:20', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Recipient', 'id_fr' => 'PRF-RCP-03-FR-005', 'type' => 'Text',
             'label' => 'No. Telefon', 'mandatory' => true, 'table_fieldname' => 'no_telefon',
             'condition' => null, 'validation' => 'required|regex:phone', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Recipient', 'id_fr' => 'PRF-RCP-03-FR-006', 'type' => 'Email',
             'label' => 'Emel', 'mandatory' => true, 'table_fieldname' => 'emel',
             'condition' => null, 'validation' => 'required|email', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Maklumat Alamat', 'id_fr' => 'PRF-RCP-03-FR-007', 'type' => 'Text',
             'label' => 'Alamat 1', 'mandatory' => true, 'table_fieldname' => 'alamat_1',
             'condition' => null, 'validation' => 'required|max:100|uppercase', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Maklumat Alamat', 'id_fr' => 'PRF-RCP-03-FR-008', 'type' => 'Text',
             'label' => 'Poskod', 'mandatory' => true, 'table_fieldname' => 'poskod',
             'condition' => 'Mencetuskan carian negeri/daerah', 'validation' => 'required|digits:5', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Maklumat Alamat', 'id_fr' => 'PRF-RCP-03-FR-009', 'type' => 'Select',
             'label' => 'Negeri', 'mandatory' => true, 'table_fieldname' => 'kod_negeri',
             'condition' => 'Auto-isi dari poskod', 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Maklumat Alamat', 'id_fr' => 'PRF-RCP-03-FR-010', 'type' => 'Select',
             'label' => 'Daerah', 'mandatory' => true, 'table_fieldname' => 'kod_daerah',
             'condition' => 'Terkait dengan negeri', 'validation' => 'required', 'status' => 'missing'],
        ];
    }

    private function itemsRcpDetail(string $context): array
    {
        $readonly = in_array($context, ['Lihat', 'Pengesahan', 'Kelulusan', 'Tidak Sah']);
        $cond = $readonly ? 'Baca sahaja' : 'Boleh diedit';
        return [
            ['screen_name' => 'Maklumat Peribadi', 'id_fr' => 'PLACEHOLDER-1', 'type' => 'Text',
             'label' => 'Nama Penuh / Syarikat', 'mandatory' => !$readonly, 'table_fieldname' => 'recipient.nama_penuh',
             'condition' => $cond, 'validation' => $readonly ? null : 'required|max:255', 'status' => 'missing'],
            ['screen_name' => 'Maklumat Peribadi', 'id_fr' => 'PLACEHOLDER-2', 'type' => 'Text',
             'label' => 'No. Pengenalan', 'mandatory' => !$readonly, 'table_fieldname' => 'recipient.id_pengenalan',
             'condition' => $cond, 'validation' => $readonly ? null : 'required', 'status' => 'missing'],
            ['screen_name' => 'Maklumat Peribadi', 'id_fr' => 'PLACEHOLDER-3', 'type' => 'Text',
             'label' => 'No. Telefon', 'mandatory' => !$readonly, 'table_fieldname' => 'recipient.no_telefon',
             'condition' => $cond, 'validation' => $readonly ? null : 'required|regex:phone', 'status' => 'missing'],
            ['screen_name' => 'Maklumat Peribadi', 'id_fr' => 'PLACEHOLDER-4', 'type' => 'Email',
             'label' => 'Emel', 'mandatory' => !$readonly, 'table_fieldname' => 'recipient.emel',
             'condition' => $cond, 'validation' => $readonly ? null : 'required|email', 'status' => 'missing'],
            ['screen_name' => 'Maklumat Alamat', 'id_fr' => 'PLACEHOLDER-5', 'type' => 'Text',
             'label' => 'Alamat 1', 'mandatory' => !$readonly, 'table_fieldname' => 'recipient.alamat_1',
             'condition' => $cond, 'validation' => $readonly ? null : 'required|max:100', 'status' => 'missing'],
            ['screen_name' => 'Maklumat Alamat', 'id_fr' => 'PLACEHOLDER-6', 'type' => 'Select',
             'label' => 'Negeri', 'mandatory' => !$readonly, 'table_fieldname' => 'recipient.kod_negeri',
             'condition' => $cond, 'validation' => $readonly ? null : 'required', 'status' => 'missing'],
            ['screen_name' => 'Tindakan', 'id_fr' => 'PLACEHOLDER-7', 'type' => 'Button',
             'label' => "Butang {$context}", 'mandatory' => false, 'table_fieldname' => null,
             'condition' => "Tindakan {$context}", 'validation' => null, 'status' => 'missing'],
        ];
    }
}
