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

class RtmfProfilingAsnafSeeder extends Seeder
{
    public function run(): void
    {
        $project = RtmfProject::firstOrFail();

        // ── Module ─────────────────────────────────────────────────────────
        $module = RtmfModule::firstOrCreate(
            ['code' => 'PRF'],
            ['name' => 'Profiling', 'sort_order' => 10, 'project_id' => $project->id],
        );
        if (! $module->project_id) {
            $module->update(['project_id' => $project->id]);
        }

        // ── Sub-module ─────────────────────────────────────────────────────
        $asn = RtmfSubModule::firstOrCreate(
            ['module_id' => $module->id, 'code' => 'ASN'],
            ['name' => 'Asnaf', 'sort_order' => 10],
        );

        // ── Actors ─────────────────────────────────────────────────────────
        $pegawai  = RtmfActor::firstOrCreate(['name' => 'Pegawai']);
        $penyelia = RtmfActor::firstOrCreate(['name' => 'Penyelia']);
        $pelulus  = RtmfActor::firstOrCreate(['name' => 'Pelulus']);

        $allActors      = [$pegawai->id, $penyelia->id, $pelulus->id];
        $pegawaiPenyelia = [$pegawai->id, $penyelia->id];
        $pegawaiOnly    = [$pegawai->id];
        $pelulusOnly    = [$pelulus->id];

        // ── Group A: Carian Profil ─────────────────────────────────────────
        $asn01 = $this->seed($module->id, $asn->id, 'PRF-ASN-01', 'Carian Profil Asnaf',
            'pages/profiling/asnaf/carian-profil/index.vue', 10, $pegawaiPenyelia,
            $this->itemsCarian01(), $this->epCarian());

        $asn02 = $this->seed($module->id, $asn->id, 'PRF-ASN-02', 'Lihat Profil Asnaf',
            'pages/profiling/asnaf/carian-profil/lihat/[id].vue', 20, $pegawaiPenyelia,
            $this->itemsLihat(), $this->epPendaftaranLengkap('/profiling/pendaftaran-lengkap/individu/{id}/lihat-lengkap'));

        $asn03 = $this->seed($module->id, $asn->id, 'PRF-ASN-03', 'Kemaskini Profil Asnaf',
            'pages/profiling/asnaf/carian-profil/kemaskini/[id].vue', 30, $pegawaiPenyelia,
            $this->itemsKemaskini(), $this->epPendaftaranLengkap('/profiling/pendaftaran-lengkap/individu/{id}'));

        $asn04 = $this->seed($module->id, $asn->id, 'PRF-ASN-04', 'Pendaftaran Lengkap Asnaf',
            'pages/profiling/asnaf/carian-profil/pendaftaran-lengkap/index.vue', 40, $pegawaiPenyelia,
            $this->itemsPendaftaran(), $this->epPendaftaranLengkap('/profiling/pendaftaran-lengkap/individu'));

        $asn05 = $this->seed($module->id, $asn->id, 'PRF-ASN-05', 'Carian Profil PPP',
            'pages/profiling/asnaf/carian-profil-ppp/index.vue', 50, $pegawaiOnly,
            $this->itemsCarian01(), $this->epCarian());

        $asn06 = $this->seed($module->id, $asn->id, 'PRF-ASN-06', 'Lihat Profil PPP',
            'pages/profiling/asnaf/carian-profil-ppp/lihat/[id].vue', 60, $pegawaiOnly,
            $this->itemsLihat(), $this->epPendaftaranLengkap('/profiling/pendaftaran-lengkap/individu/{id}/lihat-lengkap'));

        $asn07 = $this->seed($module->id, $asn->id, 'PRF-ASN-07', 'Kemaskini Profil PPP',
            'pages/profiling/asnaf/carian-profil-ppp/kemaskini/[id].vue', 70, $pegawaiOnly,
            $this->itemsKemaskini(), $this->epPendaftaranLengkap('/profiling/pendaftaran-lengkap/individu/{id}'));

        // ── Group B: Semakan Maklumat (peringkat 4012) ─────────────────────
        $asn08 = $this->seed($module->id, $asn->id, 'PRF-ASN-08', 'Senarai Semakan Maklumat',
            'pages/profiling/asnaf/semakan-maklumat/index.vue', 80, $pegawaiOnly,
            $this->itemsSenarai([
                ['Tab: Menunggu Semakan', 'Permohonan baru & tidak lengkap (peringkat 4012 + BARU/TIDAK_LENGKAP)'],
                ['Tab: Tidak Lengkap',   'Dikembalikan untuk dilengkapkan (tindakan 201)'],
                ['Tab: Selesai',         'Selesai semakan (peringkat ≥ 4013)'],
                ['Tab: Semua',           'Semua permohonan'],
            ]),
            $this->epSenarai());

        $asn09 = $this->seed($module->id, $asn->id, 'PRF-ASN-09', 'Semakan Data Lengkap',
            'pages/profiling/asnaf/semakan-maklumat/semakan-data-lengkap/index.vue', 90, $pegawaiOnly,
            $this->itemsWorkflowDetail('Semakan'), $this->epWorkflowDetail('semakan'));

        $asn10 = $this->seed($module->id, $asn->id, 'PRF-ASN-10', 'Keputusan Pengiraan',
            'pages/profiling/asnaf/semakan-maklumat/keputusan-pengiraan/[id].vue', 100, $pegawaiOnly,
            $this->itemsKeputusanPengiraan(), [
                ['method' => 'GET', 'endpoint' => '/profiling/pendaftaran-lengkap/had-kifayah-kiraan/by-individu/{id}', 'description' => 'Fetch had kifayah calculation result'],
                ['method' => 'GET', 'endpoint' => '/profiling/pendaftaran-lengkap/individu/{id}/lihat-lengkap', 'description' => 'Fetch full individual profile'],
            ]);

        // ── Group C: Siasatan (peringkat 4013) ────────────────────────────
        $asn11 = $this->seed($module->id, $asn->id, 'PRF-ASN-11', 'Senarai Siasatan',
            'pages/profiling/asnaf/siasatan/index.vue', 110, $pegawaiPenyelia,
            $this->itemsSenarai([
                ['Tab: Dalam Siasatan', 'Kes dalam proses siasatan (peringkat 4013)'],
                ['Tab: Selesai',        'Siasatan selesai (peringkat 4016/4017)'],
                ['Tab: Semua',          'Semua kes siasatan'],
            ]),
            $this->epSenarai());

        $asn12 = $this->seed($module->id, $asn->id, 'PRF-ASN-12', 'Butiran Siasatan',
            'pages/profiling/asnaf/siasatan/[id].vue', 120, $pegawaiPenyelia,
            $this->itemsWorkflowDetail('Siasatan'), $this->epWorkflowDetail('siasatan'));

        // ── Group D: Sokongan (peringkat 4014) ────────────────────────────
        $asn13 = $this->seed($module->id, $asn->id, 'PRF-ASN-13', 'Senarai Sokongan',
            'pages/profiling/asnaf/sokongan/index.vue', 130, $pegawaiPenyelia,
            $this->itemsSokonganSenarai(), $this->epSokonganSenarai());

        $asn14 = $this->seed($module->id, $asn->id, 'PRF-ASN-14', 'Butiran Sokongan',
            'pages/profiling/asnaf/sokongan/[id].vue', 140, $pegawaiPenyelia,
            $this->itemsWorkflowDetail('Sokongan'), $this->epWorkflowDetail('sokongan'));

        // ── Group E: Kelulusan (peringkat 4015) ───────────────────────────
        $asn15 = $this->seed($module->id, $asn->id, 'PRF-ASN-15', 'Senarai Kelulusan',
            'pages/profiling/asnaf/kelulusan/index.vue', 150, $pelulusOnly,
            $this->itemsSenarai([
                ['Tab: Menunggu Kelulusan', 'Menunggu keputusan pelulus (peringkat 4015)'],
                ['Tab: Selesai',            'Kelulusan selesai (peringkat 4016/4017)'],
                ['Tab: Semua',              'Semua kes kelulusan'],
            ]),
            $this->epSenarai());

        $asn16 = $this->seed($module->id, $asn->id, 'PRF-ASN-16', 'Butiran Kelulusan',
            'pages/profiling/asnaf/kelulusan/[id].vue', 160, $pelulusOnly,
            $this->itemsKelulusanDetail(), $this->epKelulusanDetail());

        $asn17 = $this->seed($module->id, $asn->id, 'PRF-ASN-17', 'Kelulusan Khas',
            'pages/profiling/asnaf/kelulusan-khas/index.vue', 170, $pelulusOnly,
            $this->itemsSenarai([
                ['Tab: Menunggu Kelulusan', 'Kes kelulusan khas menunggu tindakan'],
                ['Tab: Selesai',            'Kelulusan khas selesai'],
                ['Tab: Semua',              'Semua kes kelulusan khas'],
            ]),
            $this->epSenarai());

        // ── Group F: Penilaian Awal ────────────────────────────────────────
        $asn18 = $this->seed($module->id, $asn->id, 'PRF-ASN-18', 'Penilaian Awal',
            'pages/profiling/asnaf/penilaian-awal/index.vue', 180, $pegawaiOnly,
            [['screen_name' => 'Halaman Utama', 'id_fr' => 'PRF-ASN-18-FR-001', 'type' => 'Table',
              'label' => 'Senarai Penilaian Awal', 'mandatory' => false, 'table_fieldname' => null,
              'condition' => 'Stub page — under development', 'validation' => null, 'status' => 'missing']],
            [['method' => 'GET', 'endpoint' => '/profiling/penilaian-awal', 'description' => 'Fetch penilaian awal list']]);

        $asn19 = $this->seed($module->id, $asn->id, 'PRF-ASN-19', 'Senarai Aduan',
            'pages/profiling/asnaf/penilaian-awal/aduan/index.vue', 190, $pegawaiOnly,
            $this->itemsPenilaianAduan(), $this->epPenilaianAduan());

        $asn20 = $this->seed($module->id, $asn->id, 'PRF-ASN-20', 'Lihat Aduan',
            'pages/profiling/asnaf/penilaian-awal/aduan/lihat/[id].vue', 200, $pegawaiOnly,
            $this->itemsWorkflowDetail('Aduan'), [
                ['method' => 'GET', 'endpoint' => '/pengurusan-aduan/daftar-aduan/{id}', 'description' => 'Fetch complaint detail'],
            ]);

        $asn21 = $this->seed($module->id, $asn->id, 'PRF-ASN-21', 'Penilaian Kaunter',
            'pages/profiling/asnaf/penilaian-awal/kaunter/index.vue', 210, $pegawaiOnly,
            $this->itemsSenarai([
                ['Tab: Menunggu Penilaian', 'Kes baru menunggu penilaian kaunter'],
                ['Tab: Selesai',            'Penilaian kaunter selesai'],
                ['Tab: Semua',              'Semua kes kaunter'],
            ]),
            $this->epSenarai());

        // ── Group G: Carian Umum ───────────────────────────────────────────
        $asn22 = $this->seed($module->id, $asn->id, 'PRF-ASN-22', 'Carian Asnaf',
            'pages/profiling/asnaf/carian/index.vue', 220, $pegawaiOnly,
            [
                ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-ASN-22-FR-001', 'type' => 'Select',
                 'label' => 'Jenis Pengenalan', 'mandatory' => false, 'table_fieldname' => 'kod_jenis_pengenalan',
                 'condition' => null, 'validation' => 'nullable', 'status' => 'missing'],
                ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-ASN-22-FR-002', 'type' => 'Text',
                 'label' => 'No. Pengenalan', 'mandatory' => false, 'table_fieldname' => 'no_pengenalan',
                 'condition' => null, 'validation' => 'nullable|max:20', 'status' => 'missing'],
                ['screen_name' => 'Form Carian', 'id_fr' => 'PRF-ASN-22-FR-003', 'type' => 'Text',
                 'label' => 'Nama', 'mandatory' => false, 'table_fieldname' => 'nama',
                 'condition' => null, 'validation' => 'nullable|min:2', 'status' => 'missing'],
                ['screen_name' => 'Keputusan', 'id_fr' => 'PRF-ASN-22-FR-004', 'type' => 'Table',
                 'label' => 'Keputusan Carian', 'mandatory' => false, 'table_fieldname' => null,
                 'condition' => null, 'validation' => null, 'status' => 'missing'],
            ],
            [['method' => 'GET', 'endpoint' => '/profiling/individu/carian', 'description' => 'General Asnaf search by name or IC']]);

        // ── Page Links ─────────────────────────────────────────────────────
        $asn01->linksTo()->sync([$asn02->id, $asn03->id, $asn04->id]);
        $asn05->linksTo()->sync([$asn06->id, $asn07->id]);
        $asn08->linksTo()->sync([$asn09->id, $asn10->id]);
        $asn11->linksTo()->sync([$asn12->id]);
        $asn13->linksTo()->sync([$asn14->id]);
        $asn15->linksTo()->sync([$asn16->id]);
        $asn19->linksTo()->sync([$asn20->id]);
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

        $frontend->actors()->sync($actorIds);

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

    // ── FR Item Templates ──────────────────────────────────────────────────

    /** Build tab-based senarai items from [[screen_name, condition], ...] */
    private function itemsSenarai(array $tabs): array
    {
        $items = [];
        foreach ($tabs as $i => [$screen, $condition]) {
            $n       = $i + 1;
            $specRef = 'FR-00' . $n;
            $items[] = [
                'screen_name'     => $screen,
                'id_fr'           => "PLACEHOLDER-{$n}",
                'type'            => 'Table',
                'label'           => "Senarai ({$screen})",
                'mandatory'       => false,
                'table_fieldname' => null,
                'condition'       => $condition,
                'validation'      => null,
                'status'          => 'missing',
            ];
        }
        $items[] = [
            'screen_name'     => 'Tindakan',
            'id_fr'           => 'PLACEHOLDER-ACT',
            'type'            => 'Button',
            'label'           => 'Butang Tindakan (Lihat / Proses)',
            'mandatory'       => false,
            'table_fieldname' => null,
            'condition'       => null,
            'validation'      => null,
            'status'          => 'missing',
        ];
        return $items;
    }

    /** Shared 7-tab structure for all workflow detail pages */
    private function itemsWorkflowDetail(string $stage): array
    {
        $tabs = [
            ['Aduan',           'Senarai bendera/aduan berkaitan profil (baca sahaja)'],
            ['Maklumat Pemohon','Maklumat peribadi, alamat, pendidikan, pekerjaan, pendapatan — stepper 4-5 langkah'],
            ['Tanggungan',      'Senarai tanggungan/isi rumah'],
            ['Status Asnaf',    'Kategori asnaf semasa dan sejarah'],
            ['Bantuan',         'Permohonan dan agihan bantuan'],
            ['Pengesahan',      'Perakuan dan dokumen pengesahan'],
            ['Status Tracking', 'Log aliran kerja dan sejarah tindakan'],
        ];

        $items = [];
        foreach ($tabs as $i => [$screen, $condition]) {
            $items[] = [
                'screen_name'     => "Tab: {$screen}",
                'id_fr'           => 'PLACEHOLDER-' . ($i + 1),
                'type'            => 'Table',
                'label'           => $screen,
                'mandatory'       => false,
                'table_fieldname' => null,
                'condition'       => $condition,
                'validation'      => null,
                'status'          => 'missing',
            ];
        }

        // Workflow action buttons for this stage
        $actions = match ($stage) {
            'Semakan'  => [['Butang Hantar Semakan', 'Menghantar semakan ke peringkat Siasatan'],
                           ['Butang Kembalikan',     'Kembalikan permohonan tidak lengkap']],
            'Siasatan' => [['Butang Simpan',         'Simpan draf siasatan'],
                           ['Butang Hantar Siasatan','Hantar keputusan ke Sokongan'],
                           ['Keputusan Siasatan',    'Lulus / Pindaan / Tolak']],
            'Sokongan' => [['Butang Simpan',         'Simpan draf sokongan'],
                           ['Butang Sokong',         'Sokong permohonan ke Kelulusan'],
                           ['Butang Tidak Sokong',   'Tolak sokongan'],
                           ['Butang Batal Bantuan',  'Batal bantuan (modal pilih sebab)']],
            'Kelulusan'=> [['Butang Lulus',          'Luluskan permohonan'],
                           ['Butang Pindaan',        'Pindaan — kembalikan ke Sokongan'],
                           ['Butang Tolak',          'Tolak permohonan']],
            default    => [['Butang Tindakan',       "Tindakan {$stage}"]],
        };

        foreach ($actions as $j => [$label, $condition]) {
            $items[] = [
                'screen_name'     => 'Tindakan',
                'id_fr'           => 'PLACEHOLDER-ACT-' . ($j + 1),
                'type'            => 'Button',
                'label'           => $label,
                'mandatory'       => false,
                'table_fieldname' => null,
                'condition'       => $condition,
                'validation'      => null,
                'status'          => 'missing',
            ];
        }

        return $items;
    }

    private function itemsCarian01(): array
    {
        return [
            ['screen_name' => 'Form Carian', 'id_fr' => 'PLACEHOLDER-1', 'type' => 'Select',
             'label' => 'Jenis Pendaftaran', 'mandatory' => false, 'table_fieldname' => 'kod_jenis_pendaftaran',
             'condition' => 'Default: 1 (Pendaftaran Lengkap)', 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PLACEHOLDER-2', 'type' => 'Select',
             'label' => 'Jenis Pengenalan', 'mandatory' => false, 'table_fieldname' => 'kod_jenis_pengenalan',
             'condition' => null, 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PLACEHOLDER-3', 'type' => 'Text',
             'label' => 'No. Pengenalan', 'mandatory' => false, 'table_fieldname' => 'no_pengenalan',
             'condition' => null, 'validation' => 'nullable|max:20', 'status' => 'missing'],
            ['screen_name' => 'Form Carian', 'id_fr' => 'PLACEHOLDER-4', 'type' => 'Text',
             'label' => 'Nama', 'mandatory' => false, 'table_fieldname' => 'nama',
             'condition' => null, 'validation' => 'nullable|min:2', 'status' => 'missing'],
            ['screen_name' => 'Keputusan', 'id_fr' => 'PLACEHOLDER-5', 'type' => 'Table',
             'label' => 'Keputusan Carian Profil', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => 'Kolum: Nama, No. Pengenalan, Status, Tindakan', 'validation' => null, 'status' => 'missing'],
        ];
    }

    private function itemsLihat(): array
    {
        $tabs = [
            ['Tab: Aduan',             'Bendera / aduan berkaitan profil (baca sahaja)'],
            ['Tab: Maklumat Pemohon',  'Stepper: Peribadi/Perbankan → Pendidikan/Kemahiran → Pinjaman/Aset → Pekerjaan/Pendapatan'],
            ['Tab: Tanggungan',        'Senarai tanggungan dengan penapis status/umur'],
            ['Tab: Bantuan',           'Sejarah bantuan dan agihan (baca sahaja)'],
            ['Tab: Pengesahan',        'Dokumen dan perakuan (baca sahaja)'],
            ['Tab: Sejarah Kifayah',   'Sejarah had kifayah dan kiraan'],
            ['Tab: Status Tracking',   'Log aliran kerja dan sejarah tindakan'],
        ];

        $items = [];
        foreach ($tabs as $i => [$screen, $condition]) {
            $items[] = [
                'screen_name'     => $screen,
                'id_fr'           => 'PLACEHOLDER-' . ($i + 1),
                'type'            => 'Table',
                'label'           => str_replace('Tab: ', '', $screen),
                'mandatory'       => false,
                'table_fieldname' => null,
                'condition'       => $condition,
                'validation'      => null,
                'status'          => 'missing',
            ];
        }

        // Key fields in Maklumat Pemohon tab
        $fields = [
            ['individu.nama',                    'Nama',                  'Text'],
            ['individu.no_pengenalan',           'No. Pengenalan',        'Text'],
            ['individu.no_telefon',              'No. Telefon',           'Text'],
            ['individu.kod_jantina',             'Jantina',               'Select'],
            ['individu.kod_agama',               'Agama',                 'Select'],
            ['individu.kod_status_perkahwinan',  'Status Perkahwinan',    'Select'],
            ['individu.tarikh_lahir',            'Tarikh Lahir',          'Date'],
            ['individu.kod_warganegara',         'Warganegara',           'Select'],
            ['individu.kod_bangsa',              'Bangsa',                'Select'],
            ['individu.pendapatan_bulanan',      'Pendapatan Bulanan',    'Display'],
            ['individu.bil_tanggungan',          'Bil. Tanggungan',       'Display'],
        ];

        foreach ($fields as $j => [$field, $label, $type]) {
            $items[] = [
                'screen_name'     => 'Tab: Maklumat Pemohon',
                'id_fr'           => 'PLACEHOLDER-F' . ($j + 1),
                'type'            => $type,
                'label'           => $label,
                'mandatory'       => false,
                'table_fieldname' => $field,
                'condition'       => 'Baca sahaja',
                'validation'      => null,
                'status'          => 'missing',
            ];
        }

        return $items;
    }

    private function itemsKemaskini(): array
    {
        // Same tabs as lihat but editable
        $items = $this->itemsLihat();
        foreach ($items as &$item) {
            if ($item['condition'] === 'Baca sahaja') {
                $item['condition'] = 'Boleh diedit';
            }
        }
        $items[] = [
            'screen_name'     => 'Tindakan',
            'id_fr'           => 'PLACEHOLDER-SAVE',
            'type'            => 'Button',
            'label'           => 'Butang Kemaskini',
            'mandatory'       => false,
            'table_fieldname' => null,
            'condition'       => 'Simpan perubahan profil',
            'validation'      => null,
            'status'          => 'missing',
        ];
        return $items;
    }

    private function itemsPendaftaran(): array
    {
        return [
            ['screen_name' => 'Langkah 1: Maklumat Peribadi', 'id_fr' => 'PLACEHOLDER-1', 'type' => 'Text',
             'label' => 'Nama Penuh', 'mandatory' => true, 'table_fieldname' => 'individu.nama',
             'condition' => null, 'validation' => 'required|max:100|uppercase', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Peribadi', 'id_fr' => 'PLACEHOLDER-2', 'type' => 'Select',
             'label' => 'Jenis Pengenalan', 'mandatory' => true, 'table_fieldname' => 'individu.kod_jenis_pengenalan',
             'condition' => null, 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Peribadi', 'id_fr' => 'PLACEHOLDER-3', 'type' => 'Text',
             'label' => 'No. Pengenalan', 'mandatory' => true, 'table_fieldname' => 'individu.no_pengenalan',
             'condition' => null, 'validation' => 'required|max:20', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Peribadi', 'id_fr' => 'PLACEHOLDER-4', 'type' => 'Date',
             'label' => 'Tarikh Lahir', 'mandatory' => true, 'table_fieldname' => 'individu.tarikh_lahir',
             'condition' => null, 'validation' => 'required|date', 'status' => 'missing'],
            ['screen_name' => 'Langkah 1: Maklumat Peribadi', 'id_fr' => 'PLACEHOLDER-5', 'type' => 'Select',
             'label' => 'Status Perkahwinan', 'mandatory' => true, 'table_fieldname' => 'individu.kod_status_perkahwinan',
             'condition' => null, 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Alamat', 'id_fr' => 'PLACEHOLDER-6', 'type' => 'Text',
             'label' => 'Alamat 1', 'mandatory' => true, 'table_fieldname' => 'individu.alamat_1',
             'condition' => null, 'validation' => 'required|max:100|uppercase', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Alamat', 'id_fr' => 'PLACEHOLDER-7', 'type' => 'Text',
             'label' => 'Poskod', 'mandatory' => true, 'table_fieldname' => 'individu.poskod',
             'condition' => 'Mencetuskan carian negeri/daerah/kariah', 'validation' => 'required|digits:5', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Alamat', 'id_fr' => 'PLACEHOLDER-8', 'type' => 'Select',
             'label' => 'Negeri', 'mandatory' => true, 'table_fieldname' => 'individu.kod_negeri',
             'condition' => 'Auto-isi dari poskod', 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Alamat', 'id_fr' => 'PLACEHOLDER-9', 'type' => 'Select',
             'label' => 'Daerah', 'mandatory' => true, 'table_fieldname' => 'individu.kod_daerah',
             'condition' => 'Terkait dengan negeri', 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Langkah 2: Alamat', 'id_fr' => 'PLACEHOLDER-10', 'type' => 'Select',
             'label' => 'Kariah', 'mandatory' => true, 'table_fieldname' => 'individu.kod_kariah',
             'condition' => 'Terkait dengan daerah', 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Langkah 3: Tanggungan', 'id_fr' => 'PLACEHOLDER-11', 'type' => 'Table',
             'label' => 'Senarai Tanggungan', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => 'Boleh tambah/edit ahli isi rumah', 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Langkah 4: Pendapatan', 'id_fr' => 'PLACEHOLDER-12', 'type' => 'Text',
             'label' => 'Jumlah Pendapatan Bulanan', 'mandatory' => true, 'table_fieldname' => 'individu.jumlah_pendapatan',
             'condition' => null, 'validation' => 'required|numeric|min:0', 'status' => 'missing'],
        ];
    }

    private function itemsSokonganSenarai(): array
    {
        $items = $this->itemsSenarai([
            ['Tab: Menunggu Sokongan', 'Kes menunggu sokongan pegawai (peringkat 4014)'],
            ['Tab: Selesai',           'Sokongan selesai (peringkat 4015/4016/4017)'],
            ['Tab: Semua',             'Semua kes sokongan'],
        ]);

        // Assignment modal fields
        $items[] = [
            'screen_name'     => 'Modal: Agihan Kes',
            'id_fr'           => 'PLACEHOLDER-M1',
            'type'            => 'Text',
            'label'           => 'Kariah',
            'mandatory'       => true,
            'table_fieldname' => 'kod_kariah',
            'condition'       => 'Auto-isi berdasarkan lokasi kes; baca sahaja',
            'validation'      => 'required',
            'status'          => 'missing',
        ];
        $items[] = [
            'screen_name'     => 'Modal: Agihan Kes',
            'id_fr'           => 'PLACEHOLDER-M2',
            'type'            => 'Select',
            'label'           => 'Nama Pegawai',
            'mandatory'       => true,
            'table_fieldname' => 'id_pegawai',
            'condition'       => 'Senarai pegawai EOAD/ETD/PAK aktif mengikut kariah',
            'validation'      => 'required',
            'status'          => 'missing',
        ];

        return $items;
    }

    private function itemsKeputusanPengiraan(): array
    {
        return [
            ['screen_name' => 'Ringkasan Had Kifayah', 'id_fr' => 'PLACEHOLDER-1', 'type' => 'Display',
             'label' => 'Jumlah Had Kifayah Kiraan', 'mandatory' => false, 'table_fieldname' => 'had_kifayah_kiraan.jumlah',
             'condition' => null, 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Ringkasan Had Kifayah', 'id_fr' => 'PLACEHOLDER-2', 'type' => 'Display',
             'label' => 'Jumlah Pendapatan Isi Rumah', 'mandatory' => false, 'table_fieldname' => 'individu.jumlah_pendapatan',
             'condition' => null, 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Ringkasan Had Kifayah', 'id_fr' => 'PLACEHOLDER-3', 'type' => 'Display',
             'label' => 'Status Asnaf Semasa', 'mandatory' => false, 'table_fieldname' => 'status_asnaf.kod',
             'condition' => null, 'validation' => null, 'status' => 'missing'],
            ['screen_name' => 'Butiran Kiraan', 'id_fr' => 'PLACEHOLDER-4', 'type' => 'Table',
             'label' => 'Komponen Had Kifayah (per tanggungan)', 'mandatory' => false, 'table_fieldname' => null,
             'condition' => null, 'validation' => null, 'status' => 'missing'],
        ];
    }

    private function itemsKelulusanDetail(): array
    {
        $items = $this->itemsWorkflowDetail('Kelulusan');

        // SAP integration fields
        $items[] = [
            'screen_name'     => 'Integrasi SAP',
            'id_fr'           => 'PLACEHOLDER-SAP1',
            'type'            => 'Button',
            'label'           => 'Cipta Vendor SAP',
            'mandatory'       => false,
            'table_fieldname' => null,
            'condition'       => 'Hantar kepada SAP Master Vendor Creation setelah lulus',
            'validation'      => null,
            'status'          => 'missing',
        ];
        $items[] = [
            'screen_name'     => 'Integrasi SAP',
            'id_fr'           => 'PLACEHOLDER-SAP2',
            'type'            => 'Button',
            'label'           => 'Request Fund SAP',
            'mandatory'       => false,
            'table_fieldname' => null,
            'condition'       => 'Hantar request dana kepada SAP selepas vendor dicipta',
            'validation'      => null,
            'status'          => 'missing',
        ];

        return $items;
    }

    private function itemsPenilaianAduan(): array
    {
        return [
            // Seksyen A: Maklumat Pengadu (Wakil)
            ['screen_name' => 'Seksyen A: Pengadu', 'id_fr' => 'PLACEHOLDER-1', 'type' => 'Text',
             'label' => 'Nama Penuh Pengadu', 'mandatory' => false, 'table_fieldname' => 'pengadu.nama_penuh',
             'condition' => 'Dipapar jika bukan diri sendiri', 'validation' => 'nullable|max:100', 'status' => 'missing'],
            ['screen_name' => 'Seksyen A: Pengadu', 'id_fr' => 'PLACEHOLDER-2', 'type' => 'Select',
             'label' => 'Jenis Pengenalan Pengadu', 'mandatory' => false, 'table_fieldname' => 'pengadu.jenis_pengenalan',
             'condition' => 'Dipapar jika bukan diri sendiri', 'validation' => 'nullable', 'status' => 'missing'],
            ['screen_name' => 'Seksyen A: Pengadu', 'id_fr' => 'PLACEHOLDER-3', 'type' => 'Text',
             'label' => 'No. Pengenalan Pengadu', 'mandatory' => false, 'table_fieldname' => 'pengadu.id_pengenalan',
             'condition' => 'Dipapar jika bukan diri sendiri', 'validation' => 'nullable|max:20', 'status' => 'missing'],
            ['screen_name' => 'Seksyen A: Pengadu', 'id_fr' => 'PLACEHOLDER-4', 'type' => 'Email',
             'label' => 'Emel Pengadu', 'mandatory' => false, 'table_fieldname' => 'pengadu.email',
             'condition' => null, 'validation' => 'nullable|email', 'status' => 'missing'],
            ['screen_name' => 'Seksyen A: Pengadu', 'id_fr' => 'PLACEHOLDER-5', 'type' => 'Text',
             'label' => 'No. Telefon Pengadu', 'mandatory' => false, 'table_fieldname' => 'pengadu.no_telefon',
             'condition' => null, 'validation' => 'nullable|regex:phone', 'status' => 'missing'],
            // Seksyen A: Maklumat Individu Diadukan
            ['screen_name' => 'Seksyen A: Individu Diadukan', 'id_fr' => 'PLACEHOLDER-6', 'type' => 'Text',
             'label' => 'Nama Penuh Individu', 'mandatory' => true, 'table_fieldname' => 'aduan_individu.nama_penuh',
             'condition' => null, 'validation' => 'required|max:100', 'status' => 'missing'],
            ['screen_name' => 'Seksyen A: Individu Diadukan', 'id_fr' => 'PLACEHOLDER-7', 'type' => 'Text',
             'label' => 'No. Pengenalan Individu', 'mandatory' => true, 'table_fieldname' => 'aduan_individu.id_pengenalan',
             'condition' => 'Disabled selepas submit', 'validation' => 'required|max:20', 'status' => 'missing'],
            ['screen_name' => 'Seksyen A: Individu Diadukan', 'id_fr' => 'PLACEHOLDER-8', 'type' => 'Text',
             'label' => 'Poskod', 'mandatory' => true, 'table_fieldname' => 'aduan_individu.poskod',
             'condition' => 'Mencetuskan carian negeri/daerah/kariah', 'validation' => 'required|digits:5', 'status' => 'missing'],
            ['screen_name' => 'Seksyen A: Individu Diadukan', 'id_fr' => 'PLACEHOLDER-9', 'type' => 'Select',
             'label' => 'Kariah', 'mandatory' => true, 'table_fieldname' => 'aduan_individu.kariah',
             'condition' => 'Terkait dengan daerah', 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Seksyen A: Individu Diadukan', 'id_fr' => 'PLACEHOLDER-10', 'type' => 'Map',
             'label' => 'Lokasi Individu (Peta)', 'mandatory' => true, 'table_fieldname' => 'picker_geolokasi',
             'condition' => 'Pilih dari peta atau geocode', 'validation' => 'required', 'status' => 'missing'],
            // Seksyen B: Masalah
            ['screen_name' => 'Seksyen B: Masalah', 'id_fr' => 'PLACEHOLDER-11', 'type' => 'Radio',
             'label' => 'Tahap Keperluan Bantuan', 'mandatory' => true, 'table_fieldname' => 'masalah.tahap_keperluan',
             'condition' => 'Pilihan dari knf_tahap_aduan', 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Seksyen B: Masalah', 'id_fr' => 'PLACEHOLDER-12', 'type' => 'Radio',
             'label' => 'Sub-kategori Masalah', 'mandatory' => true, 'table_fieldname' => 'masalah.tahap_keperluan_sub',
             'condition' => 'Terkait dengan tahap keperluan', 'validation' => 'required', 'status' => 'missing'],
            ['screen_name' => 'Seksyen B: Masalah', 'id_fr' => 'PLACEHOLDER-13', 'type' => 'Textarea',
             'label' => 'Pernyataan Masalah', 'mandatory' => true, 'table_fieldname' => 'masalah.pernyataan_masalah',
             'condition' => null, 'validation' => 'required|min:5|max:255', 'status' => 'missing'],
            ['screen_name' => 'Seksyen B: Masalah', 'id_fr' => 'PLACEHOLDER-14', 'type' => 'FileUpload',
             'label' => 'Lampiran', 'mandatory' => false, 'table_fieldname' => 'masalah.lampiran_files',
             'condition' => 'Wajib untuk kategori Bencana/Perubatan; JPG/PDF', 'validation' => 'nullable|mimes:jpg,png,pdf|max:10240', 'status' => 'missing'],
            // Seksyen C: Pengesahan
            ['screen_name' => 'Seksyen C: Pengesahan', 'id_fr' => 'PLACEHOLDER-15', 'type' => 'Checkbox',
             'label' => 'Persetujuan & Perisytiharan', 'mandatory' => true, 'table_fieldname' => 'consent',
             'condition' => 'Mesti ditanda sebelum submit', 'validation' => 'required|accepted', 'status' => 'missing'],
        ];
    }

    // ── API Endpoint Templates ─────────────────────────────────────────────

    private function epCarian(): array
    {
        return [
            ['method' => 'GET', 'endpoint' => '/profiling/pendaftaran-lengkap/individu', 'description' => 'Search profiles (params: kodJenisPengenalan, noPengenalan, nama, kodJenisPendaftaran)'],
        ];
    }

    private function epPendaftaranLengkap(string $path): array
    {
        return [
            ['method' => 'GET',   'endpoint' => $path, 'description' => 'Fetch full individual profile'],
            ['method' => 'PATCH', 'endpoint' => $path, 'description' => 'Update individual profile section'],
            ['method' => 'GET',   'endpoint' => '/profiling/pendaftaran-lengkap/individu/{id}/sejarah', 'description' => 'Fetch workflow history'],
            ['method' => 'GET',   'endpoint' => '/profiling/pendaftaran-lengkap/isirumah/household-lengkap', 'description' => 'Fetch household members'],
        ];
    }

    private function epSenarai(): array
    {
        return [
            ['method' => 'GET', 'endpoint' => '/profiling/pendaftaran-lengkap/individu', 'description' => 'List workflow cases (params: kodPeringkatProsesIn, adalahKetuaKeluarga, isPemohon, pageSize)'],
        ];
    }

    private function epWorkflowDetail(string $stage): array
    {
        return [
            ['method' => 'GET',   'endpoint' => '/profiling/pendaftaran-lengkap/individu/{id}/lihat-lengkap', 'description' => "Fetch {$stage} case full profile"],
            ['method' => 'PATCH', 'endpoint' => '/profiling/pendaftaran-lengkap/individu/{id}',              'description' => 'Save profile section changes'],
            ['method' => 'POST',  'endpoint' => "/profiling/pendaftaran-lengkap/workflow/{$stage}",           'description' => "Submit {$stage} workflow decision"],
            ['method' => 'GET',   'endpoint' => '/profiling/pendaftaran-lengkap/had-kifayah-kiraan/by-individu/{id}', 'description' => 'Fetch had kifayah calculation'],
            ['method' => 'GET',   'endpoint' => '/profiling/pendaftaran-lengkap/individu/{id}/sejarah',      'description' => 'Fetch workflow history log'],
        ];
    }

    private function epSokonganSenarai(): array
    {
        return [
            ['method' => 'GET', 'endpoint' => '/profiling/pendaftaran-lengkap/individu', 'description' => 'List sokongan cases (kodPeringkatProsesIn=4014)'],
            ['method' => 'GET', 'endpoint' => '/organisasi/kariah', 'description' => 'Fetch kariah list for assignment modal'],
            ['method' => 'GET', 'endpoint' => '/kod/pegawai-by-lokasi', 'description' => 'Fetch officers by role and location (params: kod_peranan, page, pageSize)'],
            ['method' => 'POST','endpoint' => '/profiling/pendaftaran-lengkap/workflow/sokongan/assign', 'description' => 'Assign case to officer (EOAD/ETD/PAK)'],
        ];
    }

    private function epKelulusanDetail(): array
    {
        return [
            ['method' => 'GET',  'endpoint' => '/profiling/pendaftaran-lengkap/individu/{id}/lihat-lengkap', 'description' => 'Fetch full kelulusan profile'],
            ['method' => 'POST', 'endpoint' => '/profiling/pendaftaran-lengkap/workflow/kelulusan',          'description' => 'Submit kelulusan decision (Lulus/Pindaan/Tolak)'],
            ['method' => 'GET',  'endpoint' => '/profiling/pendaftaran-lengkap/had-kifayah-kiraan/by-individu/{id}', 'description' => 'Had kifayah calculation for approval screen'],
            ['method' => 'POST', 'endpoint' => '/integration/sap/master-vendor-creation',                   'description' => 'Create SAP master vendor after approval'],
            ['method' => 'POST', 'endpoint' => '/integration/sap/request-fund',                             'description' => 'Request fund from SAP after vendor creation'],
        ];
    }

    private function epPenilaianAduan(): array
    {
        return [
            ['method' => 'GET',  'endpoint' => '/rujukan/jenis-pengenalan',  'description' => 'ID type reference options'],
            ['method' => 'GET',  'endpoint' => '/rujukan/negeri',            'description' => 'State list'],
            ['method' => 'GET',  'endpoint' => '/rujukan/daerah',            'description' => 'District list (by state)'],
            ['method' => 'GET',  'endpoint' => '/rujukan/kariah',            'description' => 'Kariah list (by district)'],
            ['method' => 'GET',  'endpoint' => '/knf/tahap-aduan',           'description' => 'Tahap keperluan bantuan options'],
            ['method' => 'GET',  'endpoint' => '/knf/tahap-aduan-masalah',   'description' => 'Sub-category options (by tahap)'],
            ['method' => 'GET',  'endpoint' => '/knf/bencana',               'description' => 'Disaster options for Bencana category'],
            ['method' => 'GET',  'endpoint' => '/knf/validasi-input',        'description' => 'Validation rules from config'],
            ['method' => 'POST', 'endpoint' => '/pengurusan-aduan/daftar-aduan', 'description' => 'Submit new complaint/aduan'],
        ];
    }
}
