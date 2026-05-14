<?php

namespace Database\Seeders;

use App\Models\RtmfActor;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendItem;
use App\Models\RtmfModule;
use App\Models\RtmfSubModule;
use Illuminate\Database\Seeder;

class RtmfAduanSeeder extends Seeder
{
    public function run(): void
    {
        // ── Module: Aduan ──────────────────────────────────────────────────
        $aduan = RtmfModule::firstOrCreate(
            ['code' => 'ADN'],
            ['name' => 'Aduan', 'sort_order' => 20],
        );

        // ── Sub-module: Daftar Aduan ───────────────────────────────────────
        $daftarAduan = RtmfSubModule::firstOrCreate(
            ['module_id' => $aduan->id, 'code' => 'DA'],
            ['name' => 'Daftar Aduan', 'sort_order' => 10],
        );

        // ── Actors ─────────────────────────────────────────────────────────
        $pengguna  = RtmfActor::firstOrCreate(['name' => 'Pengguna']);
        $orangAwam = RtmfActor::firstOrCreate(['name' => 'Orang Awam']);

        // ── Frontend entry ─────────────────────────────────────────────────
        $frontend = RtmfFrontend::updateOrCreate(
            ['spec_id' => 'ADN-DA-01'],
            [
                'module_id'               => $aduan->id,
                'sub_module_id'           => $daftarAduan->id,
                'tab_code'                => 'ADN-DA-01',
                'vue_path'                => 'pages/pengurusan-aduan/daftar-aduan/index.vue',
                'title'                   => 'Daftar Aduan',
                'business_requirement'    => 'Sistem membenarkan pengguna dan orang awam mendaftar aduan baharu berkaitan asnaf zakat. Maklumat wakil (Seksyen A), maklumat individu yang diadukan (Seksyen B), dan kategori masalah dihadapi (Seksyen B) ditangkap dan disimpan bersama lampiran sokongan.',
                'stakeholder_requirement' => 'Pengguna boleh mengemukakan aduan bagi pihak diri sendiri atau orang lain (pola wakil). IC divalidasi melalui JPN dan pangkalan data Asnaf. Poskod mencari negeri/daerah secara automatik dan menetapkan kariah. Lampiran wajib untuk kategori Bencana dan Perubatan. Notifikasi e-mel dihantar kepada semua pihak selepas aduan berjaya didaftarkan.',
                'description'             => $this->description(),
                'is_done'                 => false,
                'sort_order'              => 10,
            ],
        );

        // Attach actors (skip if already attached)
        foreach ([$pengguna, $orangAwam] as $actor) {
            if (! $frontend->actors()->where('rtmf_actor_id', $actor->id)->exists()) {
                $frontend->actors()->attach($actor->id);
            }
        }

        // ── FR line items — wipe and re-seed ──────────────────────────────
        RtmfFrontendItem::where('rtmf_frontend_id', $frontend->id)->delete();

        foreach ($this->frItems() as $i => $item) {
            RtmfFrontendItem::create([
                'rtmf_frontend_id' => $frontend->id,
                'sort_order'       => $i,
                ...$item,
            ]);
        }
    }

    private function description(): string
    {
        return <<<'MD'
## Daftar Aduan

Borang pendaftaran aduan baharu untuk asnaf zakat. Pengguna atau orang awam boleh mengemukakan aduan bagi pihak diri sendiri atau mewakili orang lain.

### Seksyen A — Maklumat Wakil
Dipaparkan hanya apabila pengadu bukan diri sendiri (`isDiriSendiri === false`). Merakam maklumat identiti wakil (nama, jenis pengenalan, IC, emel, telefon).

### Seksyen B — Maklumat Individu yang Diadukan
Maklumat peribadi dan alamat individu yang diadukan. Poskod memicu carian automatik negeri dan daerah. IC disemak melalui endpoint JPN dan pangkalan data Asnaf (butang **Semak**). Peta Google Maps dijana secara automatik daripada alamat yang diisi.

### Seksyen B — Masalah Dihadapi
Pemilihan tahap keperluan bantuan (hierarki dua peringkat dari `knf_tahap_aduan` → `knf_tahap_aduan_masalah`). Kategori Bantuan/Bencana menggunakan `knf_bencana`. Lampiran **wajib** untuk kategori Bencana dan Perubatan.

### Seksyen C — Pengesahan
Kotak semak persetujuan terma dan notis privasi wajib ditanda sebelum borang boleh dihantar.

### Aliran Kerja
`BARU` → `DALAM_TINDAKAN` → `SELESAI` / `DITUTUP`

### Jadual Utama
`adn_aduan_asnaf` (individu yang diadukan), `adn_aduan_wakil` (wakil pengadu)

### Endpoint API Utama
- `POST /aduan/daftar-aduan/aduan-asnaf/public` — hantar aduan (orang awam)
- `GET  /aduan/daftar-aduan/aduan-asnaf/public/check-individu-asnaf` — semak IC & status Asnaf
- `GET  /aduan/daftar-aduan/aduan-asnaf/public/validate` — validasi negeri & had aduan per IC
- `POST /aduan/daftar-aduan/aduan-asnaf/upload-lampiran/public` — muat naik lampiran
MD;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function frItems(): array
    {
        return [
            // ── Seksyen A: Maklumat Wakil (dipapar jika bukan diri sendiri) ──
            [
                'screen_name'     => 'Maklumat Wakil',
                'id_fr'           => 'ADN-DA-01-FR-001',
                'type'            => 'Text',
                'label'           => 'Nama Penuh (Wakil)',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_wakil.nama_penuh',
                'condition'       => 'Dipapar jika bukan diri sendiri',
                'validation'      => 'required|max:100|uppercase',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Wakil',
                'id_fr'           => 'ADN-DA-01-FR-002',
                'type'            => 'Select',
                'label'           => 'Jenis Pengenalan (Wakil)',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_wakil.kod_jenis_pengenalan',
                'condition'       => 'Dipapar jika bukan diri sendiri',
                'validation'      => 'required; pilihan dari gbl_rujukan',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Wakil',
                'id_fr'           => 'ADN-DA-01-FR-003',
                'type'            => 'Text',
                'label'           => 'No. Pengenalan (Wakil)',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_wakil.no_pengenalan',
                'condition'       => 'Dipapar jika bukan diri sendiri',
                'validation'      => 'required|max:20',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Wakil',
                'id_fr'           => 'ADN-DA-01-FR-004',
                'type'            => 'Email',
                'label'           => 'Emel (Wakil)',
                'mandatory'       => false,
                'table_fieldname' => 'adn_aduan_wakil.emel',
                'condition'       => 'Dipapar jika bukan diri sendiri',
                'validation'      => 'nullable|email',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Wakil',
                'id_fr'           => 'ADN-DA-01-FR-005',
                'type'            => 'Text',
                'label'           => 'No. Telefon (Wakil)',
                'mandatory'       => false,
                'table_fieldname' => 'adn_aduan_wakil.no_telefon',
                'condition'       => 'Dipapar jika bukan diri sendiri',
                'validation'      => 'nullable|regex:^(01[0-46-9]\d{7,8})$',
                'status'          => 'missing',
            ],

            // ── Seksyen B: Maklumat Individu ──────────────────────────────
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-006',
                'type'            => 'Text',
                'label'           => 'Nama Penuh',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.nama_penuh',
                'condition'       => null,
                'validation'      => 'required|min:5|max:100|uppercase',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-007',
                'type'            => 'Select',
                'label'           => 'Jenis Pengenalan',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.kod_jenis_pengenalan',
                'condition'       => null,
                'validation'      => 'required; MYKAD/MYKID sahaja',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-008',
                'type'            => 'Text+Button',
                'label'           => 'No. Pengenalan',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.no_pengenalan',
                'condition'       => null,
                'validation'      => 'required|max:20; butang Semak mencetuskan semakan JPN & Asnaf',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-009',
                'type'            => 'Email',
                'label'           => 'Emel',
                'mandatory'       => false,
                'table_fieldname' => 'adn_aduan_asnaf.emel',
                'condition'       => null,
                'validation'      => 'nullable|email',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-010',
                'type'            => 'Text',
                'label'           => 'No. Telefon',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.no_telefon',
                'condition'       => null,
                'validation'      => 'required|regex:^(01[0-46-9]\d{7,8})$',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-011',
                'type'            => 'Select',
                'label'           => 'Status Perkahwinan',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.kod_status_kahwin',
                'condition'       => null,
                'validation'      => 'required; pilihan dari gbl_rujukan',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-012',
                'type'            => 'Text',
                'label'           => 'Alamat 1',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.alamat_1',
                'condition'       => null,
                'validation'      => 'required|max:100|uppercase',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-013',
                'type'            => 'Text',
                'label'           => 'Alamat 2',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.alamat_2',
                'condition'       => null,
                'validation'      => 'required|max:100|uppercase',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-014',
                'type'            => 'Text',
                'label'           => 'Alamat 3',
                'mandatory'       => false,
                'table_fieldname' => 'adn_aduan_asnaf.alamat_3',
                'condition'       => null,
                'validation'      => 'nullable|max:100|uppercase',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-015',
                'type'            => 'Text',
                'label'           => 'Poskod',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.poskod',
                'condition'       => null,
                'validation'      => 'required|tepat 5 digit; mencetuskan carian negeri & daerah',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-016',
                'type'            => 'Select',
                'label'           => 'Negeri',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.kod_negeri',
                'condition'       => 'Auto-isi dari poskod; terhad oleh validasi-input',
                'validation'      => 'required',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-017',
                'type'            => 'Select',
                'label'           => 'Daerah',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.kod_daerah',
                'condition'       => 'Terkait dengan negeri/poskod',
                'validation'      => 'required',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-018',
                'type'            => 'Text',
                'label'           => 'Bandar',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.kod_bandar',
                'condition'       => null,
                'validation'      => 'required; abjad sahaja (tiada nombor/simbol)',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-019',
                'type'            => 'Select',
                'label'           => 'Kariah',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.kod_kariah',
                'condition'       => 'Diambil secara dinamik mengikut daerah',
                'validation'      => 'required',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Maklumat Individu',
                'id_fr'           => 'ADN-DA-01-FR-020',
                'type'            => 'Map',
                'label'           => 'Lokasi (Peta)',
                'mandatory'       => false,
                'table_fieldname' => 'adn_aduan_asnaf.geolokasi',
                'condition'       => null,
                'validation'      => 'nullable; dijana automatik dari alamat (Google Maps iframe)',
                'status'          => 'missing',
            ],

            // ── Seksyen B: Masalah Dihadapi ───────────────────────────────
            [
                'screen_name'     => 'Masalah Dihadapi',
                'id_fr'           => 'ADN-DA-01-FR-021',
                'type'            => 'Radio',
                'label'           => 'Tahap Keperluan Bantuan',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.id_knf_tahap_aduan',
                'condition'       => null,
                'validation'      => 'required; pilihan dari knf_tahap_aduan',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Masalah Dihadapi',
                'id_fr'           => 'ADN-DA-01-FR-022',
                'type'            => 'Radio',
                'label'           => 'Masalah / Sub-kategori',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.id_knf_tahap_aduan_masalah',
                'condition'       => 'Terkait dengan tahap keperluan; Bantuan/Bencana guna knf_bencana (id_knf_bantuan)',
                'validation'      => 'required kecuali Bantuan/Bencana dipilih',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Masalah Dihadapi',
                'id_fr'           => 'ADN-DA-01-FR-023',
                'type'            => 'Textarea',
                'label'           => 'Pernyataan Masalah',
                'mandatory'       => true,
                'table_fieldname' => 'adn_aduan_asnaf.kenyataan_masalah',
                'condition'       => null,
                'validation'      => 'required|min:5|max:255',
                'status'          => 'missing',
            ],
            [
                'screen_name'     => 'Masalah Dihadapi',
                'id_fr'           => 'ADN-DA-01-FR-024',
                'type'            => 'FileUpload',
                'label'           => 'Lampiran / Gambar',
                'mandatory'       => false,
                'table_fieldname' => 'adn_aduan_asnaf.pautan_lampiran',
                'condition'       => 'Wajib untuk kategori Bencana & Perubatan',
                'validation'      => 'nullable; JPG/PNG/PDF; max 10MB setiap fail',
                'status'          => 'missing',
            ],

            // ── Seksyen C: Pengesahan ─────────────────────────────────────
            [
                'screen_name'     => 'Pengesahan',
                'id_fr'           => 'ADN-DA-01-FR-025',
                'type'            => 'Checkbox',
                'label'           => 'Persetujuan & Perisytiharan',
                'mandatory'       => true,
                'table_fieldname' => null,
                'condition'       => null,
                'validation'      => 'required; mesti ditanda sebelum borang boleh dihantar',
                'status'          => 'missing',
            ],
        ];
    }
}
