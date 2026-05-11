<?php

namespace Database\Seeders;

use App\Models\RtmfActor;
use App\Models\RtmfFrontend;
use App\Models\RtmfModule;
use App\Models\RtmfSubModule;
use Illuminate\Database\Seeder;

class RtmfFrontendSeeder extends Seeder
{
    public function run(): void
    {
        // ── Module: Profiling ──────────────────────────────────────────────
        $profiling = RtmfModule::firstOrCreate(
            ['code' => 'PRF'],
            ['name' => 'Profiling', 'sort_order' => 10],
        );

        // ── Sub-modules under Profiling ────────────────────────────────────
        $subModules = [];
        foreach ($this->subModuleDefs() as $code => $def) {
            $subModules[$code] = RtmfSubModule::firstOrCreate(
                ['module_id' => $profiling->id, 'code' => $code],
                ['name' => $def['name'], 'sort_order' => $def['sort_order']],
            );
        }

        // ── Frontend entries ───────────────────────────────────────────────
        $sort = 0;
        foreach ($this->dataset() as $row) {
            $sort += 10;

            $subModule = $subModules[$row['sub_module_code']] ?? null;

            $actor = null;
            if (! empty($row['actor'])) {
                $actor = RtmfActor::firstOrCreate(['name' => $row['actor']]);
            }

            $frontend = RtmfFrontend::updateOrCreate(
                ['spec_id' => $row['spec_id']],
                [
                    'module_id'               => $profiling->id,
                    'sub_module_id'           => $subModule?->id,
                    'tab_code'                => $row['tab_code'] ?? null,
                    'vue_path'                => $row['vue_path'] ?? null,
                    'title'                   => $row['title'],
                    'business_requirement'    => $row['business_requirement'] ?? null,
                    'stakeholder_requirement' => $row['stakeholder_requirement'] ?? null,
                    'description'             => $row['description'] ?? null,
                    'sort_order'              => $sort,
                ],
            );

            if ($actor && ! $frontend->actors()->where('rtmf_actor_id', $actor->id)->exists()) {
                $frontend->actors()->attach($actor->id);
            }
        }
    }

    /**
     * Sub-modules under the Profiling module.
     *
     * @return array<string, array{name: string, sort_order: int}>
     */
    private function subModuleDefs(): array
    {
        return [
            'QS' => ['name' => 'Pendaftaran Pantas Perseorangan', 'sort_order' => 10],
            'QB' => ['name' => 'Pendaftaran Pantas Pukal',        'sort_order' => 20],
            'FT' => ['name' => 'Pendaftaran Lengkap',             'sort_order' => 30],
        ];
    }

    /**
     * Frontend entries.
     * `sub_module_code` maps to a key in subModuleDefs().
     *
     * @return array<int, array<string, mixed>>
     */
    private function dataset(): array
    {
        $ppp      = 'pages/profiling/asnaf/carian-profil-ppp/index.vue';
        $pppForm  = 'pages/profiling/asnaf/carian-profil-ppp/pendaftaran-pantas-perseorangan/index.vue';
        $semakan  = 'pages/profiling/asnaf/semakan-maklumat/index.vue';
        $kelulusan = 'pages/profiling/asnaf/kelulusan/index.vue';
        $siasatan = 'pages/profiling/asnaf/siasatan/[id].vue';
        $ftForm   = 'pages/profiling/asnaf/carian-profil/pendaftaran-lengkap/index.vue';
        $qbIndex  = 'pages/profiling/asnaf/pendaftaran-pantas-pukal/index.vue';
        $qbManual = 'pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/tambah.vue';
        $qbImport = 'pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/index.vue';
        $qbBatch  = 'pages/profiling/asnaf/pendaftaran-pantas-pukal/permohonan/[batchId]/index.vue';

        return [
            // ── QS: Pendaftaran Pantas Perseorangan ────────────────────────
            ['spec_id' => 'PRF-AS-QS-01_01',   'sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-01',  'title' => 'Carian Profil',                       'vue_path' => $ppp,      'actor' => 'Pendaftar',                           'business_requirement' => 'Skrin carian profil asnaf/bukan asnaf menggunakan Jenis Pengenalan + ID.',          ],
            [
                'spec_id'                 => 'PRF-AS-QS-01_02',
                'sub_module_code'         => 'QS',
                'tab_code'                => 'PRF-AS-QS-01',
                'title'                   => 'Paparan Hasil Carian',
                'vue_path'                => $ppp,
                'actor'                   => 'Pendaftar',
                'business_requirement'    => 'Paparan Hasil Carian oleh Pendaftar — senarai profil asnaf dengan sokongan sort dan bilangan data per skrin yang boleh diubah.',
                'stakeholder_requirement' => 'Pilih Profil Asnaf yang disenaraikan',
                'description'             => <<<'MD'
## Paparan Hasil Carian Maklumat Asnaf

**Screen ID:** EX-PRF-AS-QS-01_02

### Component (3.1) — Paparan Hasil Carian
- Skrin boleh disort menggunakan mana-mana field yang dipaparkan
- Pengguna boleh menukar bilangan data per skrin

### Fields (Read Only)

| ID_FR | Field |
|-------|-------|
| 3.1.1 | Jenis Pengenalan |
| 3.1.2 | Pengenalan ID Mengikut Dokumen Pengenalan |
| 3.1.3 | Nama |
| 3.1.4 | Kariah |
| 3.1.5 | Daerah |
| 3.1.6 | Kategori Asnaf |

### Buttons

| ID_FR | Label | Condition | Navigates To |
|-------|-------|-----------|--------------|
| 2.1 | Kemaskini | Rekod wujud, status **≠ DRAF** | EX-PRF-AS-UP-02_01_01 (Skrin Kemaskini) |
| 2.1 | Kemaskini | Rekod wujud, status **= DRAF** | EX-PRF-AS-QS-02_01_01 (Pendaftaran Pantas) |
| 2.2 | Lihat | Rekod wujud, status **≠ DRAF** | EX-PRF-AS-FR-02_02_01 (Skrin Paparan Profil) |
| 2.3 | Mohon Bantuan | — | Skrin Bantuan — Mohon Bantuan (Pegawai) |
| 2.4 | Kembali | — | Kembali ke skrin sebelum |
MD,
            ],
            ['spec_id' => 'PRF-AS-QS-02_01_01','sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-02',  'title' => 'Maklumat Peribadi',                    'vue_path' => $pppForm,  'actor' => 'Pemohon/Pendaftar',                   'business_requirement' => 'Personal info — Jenis Pengenalan, IC, etc. (Tab 1 → Step: Peribadi).',            ],
            ['spec_id' => 'PRF-AS-QS-02_01_02','sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-02',  'title' => 'Maklumat Alamat',                      'vue_path' => $pppForm,  'actor' => 'Pemohon/Pendaftar',                   'business_requirement' => 'Address details (Tab 1 → Step: Alamat).',                                          ],
            ['spec_id' => 'PRF-AS-QS-02_01_03A','sub_module_code'=> 'QS', 'tab_code' => 'PRF-AS-QS-02',  'title' => 'Maklumat Had Kifayah Pemohon',         'vue_path' => $pppForm,  'actor' => 'Pemohon/Pendaftar',                   'business_requirement' => 'Had Kifayah info for head of household (Tab 2 JadualPengiraanHadKifayah — variant A data entry).', ],
            ['spec_id' => 'PRF-AS-QS-02_01_03','sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-02',  'title' => 'Maklumat Perakuan Pemohon',            'vue_path' => $pppForm,  'actor' => 'Pemohon/Pendaftar',                   'business_requirement' => 'Applicant declaration/attestation (internal step: profilPerakuanId).',              ],
            ['spec_id' => 'PRF-AS-QS-02_01_04','sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-02',  'title' => 'Pengesahan Pendapatan',                'vue_path' => $pppForm,  'actor' => 'Pemohon/Pendaftar',                   'business_requirement' => 'Income verification fields (Tab 1 → Step: Pekerjaan & Pendapatan).',               ],
            ['spec_id' => 'PRF-AS-QS-02_01_05','sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-02',  'title' => 'Maklumat Pengesahan Permastautin',     'vue_path' => $pppForm,  'actor' => 'Pemohon/Pendaftar',                   'business_requirement' => 'Residency verification (Tab 5 Pengesahan → Step: Bermastautin).',                  ],
            ['spec_id' => 'PRF-AS-QS-02_01_06','sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-02',  'title' => 'Maklumat Pendaftar',                   'vue_path' => $pppForm,  'actor' => 'Pemohon/Pendaftar',                   'business_requirement' => 'Registrar info (Tab 5 Pengesahan → Step: Akhir).',                                  ],
            ['spec_id' => 'PRF-AS-QS-03',      'sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-03',  'title' => 'Semakan Maklumat Permohonan',          'vue_path' => $semakan,  'actor' => 'Pegawai LZS',                         'business_requirement' => 'Verify documents & attachments; ICU JPN integration for IC verification.',          ],
            ['spec_id' => 'PRF-AS-QS-04',      'sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-04',  'title' => 'Terima Notifikasi',                    'vue_path' => null,      'actor' => 'NAS / Pemohon / Pengesah Status Asnaf','business_requirement' => 'System sends notifications to Pemohon and Pengesah Status Asnaf.',               ],
            ['spec_id' => 'PRF-AS-QS_05',      'sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS_05',  'title' => 'Pengiraan Had Kifayah',                'vue_path' => $pppForm,  'actor' => 'NAS (System)',                        'business_requirement' => 'Auto-calculation of had kifayah per household.',                                    ],
            ['spec_id' => 'PRF-AS-QS-06',      'sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-06',  'title' => 'Syor Kategori',                        'vue_path' => null,      'actor' => 'NAS (System)',                        'business_requirement' => 'Display had kifayah calculation result; recommend family status.',                  ],
            ['spec_id' => 'PRF-AS-QS-07',      'sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-07',  'title' => 'Pengesahan Status Asnaf',              'vue_path' => $kelulusan,'actor' => 'Pegawai LZS',                         'business_requirement' => 'Had Kifayah + Multidimensi result; officer confirms/overrides.',                   ],
            ['spec_id' => 'PRF-AS-QS-07_01',   'sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-07',  'title' => 'Siasatan di Lapangan (Laporan)',       'vue_path' => $siasatan, 'actor' => 'Pengesah Status',                     'business_requirement' => 'Field investigation report by PAK/PAK+/Pegawai LZS.',                              ],
            ['spec_id' => 'PRF-AS-QS-09',      'sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-09',  'title' => 'Siasatan di Lapangan (Tindakan)',      'vue_path' => $siasatan, 'actor' => 'PAK / PAK+ / Pegawai LZS',           'business_requirement' => 'Field investigation action screen.',                                                ],
            ['spec_id' => 'PRF-AS-QS-10',      'sub_module_code' => 'QS', 'tab_code' => 'PRF-AS-QS-10',  'title' => 'Kelulusan Status Asnaf',               'vue_path' => $kelulusan,'actor' => 'Pelulus',                             'business_requirement' => 'Had Kifayah + Multidimensi; officer performs final approval of asnaf status.',     ],
            // ── QB: Pendaftaran Pantas Pukal ───────────────────────────────
            ['spec_id' => 'PRF-AS-QB-01',      'sub_module_code' => 'QB', 'tab_code' => 'PRF-AS-QB-01',  'title' => 'Senarai Pendaftaran Pantas Pukal',     'vue_path' => $qbIndex,  'actor' => 'Pendaftar',                           'business_requirement' => 'Senarai Bencana — dropdown Nama Bencana, date filter.',                             ],
            ['spec_id' => 'PRF-AS-QB-01_01',   'sub_module_code' => 'QB', 'tab_code' => 'PRF-AS-QB-01',  'title' => 'Maklumat Peribadi (Manual)',            'vue_path' => $qbManual, 'actor' => 'Pendaftar',                           'business_requirement' => 'Same field structure as QS-02_01_01 — manual entry per applicant.',                ],
            ['spec_id' => 'PRF-AS-QB-02_01',   'sub_module_code' => 'QB', 'tab_code' => 'PRF-AS-QB-02',  'title' => 'Maklumat Peribadi (Import)',            'vue_path' => $qbImport, 'actor' => 'Pendaftar',                           'business_requirement' => 'Import via Excel/CSV file — applicant data loaded into staging pool.',               ],
            ['spec_id' => 'PRF-AS-QB-03_01',   'sub_module_code' => 'QB', 'tab_code' => 'PRF-AS-QB-03',  'title' => 'Semakan by Kategori',                  'vue_path' => $qbImport, 'actor' => 'Pendaftar',                           'business_requirement' => 'Search/filter by kategori (Fakir/Miskin/Non-FM).',                                  ],
            ['spec_id' => 'PRF-AS-QB-03_02',   'sub_module_code' => 'QB', 'tab_code' => 'PRF-AS-QB-03',  'title' => 'Lihat Maklumat',                       'vue_path' => $qbBatch,  'actor' => 'Pendaftar',                           'business_requirement' => 'View applicant details — unified tabular batch layout.',                            ],
            ['spec_id' => 'PRF-AS-QB-03_03',   'sub_module_code' => 'QB', 'tab_code' => 'PRF-AS-QB-03',  'title' => 'Paparan Maklumat Pemohon (ReadOnly)',   'vue_path' => $qbBatch,  'actor' => 'Pendaftar',                           'business_requirement' => 'Read-only view of applicant + tanggungan details.',                                 ],
            ['spec_id' => 'PRF-AS-QB-04',      'sub_module_code' => 'QB', 'tab_code' => 'PRF-AS-QB-04',  'title' => 'Terima Notifikasi',                    'vue_path' => null,      'actor' => 'NAS / Pemohon / Pengesah Status Asnaf','business_requirement' => 'System-driven notifications — bulk variant of QS-04.',                            ],
            ['spec_id' => 'PRF-AS-QB-05',      'sub_module_code' => 'QB', 'tab_code' => 'PRF-AS-QB-05',  'title' => 'Syor Asnaf (Non-FM)',                  'vue_path' => $qbImport, 'actor' => 'EOAD',                                'business_requirement' => 'EOAD sends notification to eligible recipients to register as asnaf.',               ],
            // ── FT: Pendaftaran Lengkap ────────────────────────────────────
            ['spec_id' => 'PRF-AS-FT',         'sub_module_code' => 'FT', 'tab_code' => 'PRF-AS-FT',     'title' => 'Pendaftaran Lengkap',                  'vue_path' => $ftForm,   'actor' => 'Pemohon/Pendaftar',                   'business_requirement' => 'Full household tree for complete asnaf registration.',                              ],
        ];
    }
}
