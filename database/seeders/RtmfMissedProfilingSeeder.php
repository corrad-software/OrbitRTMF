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

class RtmfMissedProfilingSeeder extends Seeder
{
    public function run(): void
    {
        $project = RtmfProject::first();

        $module = RtmfModule::firstOrCreate(
            ['code' => 'PRF'],
            ['name' => 'Profiling', 'project_id' => $project->id, 'sort_order' => 10],
        );
        if (! $module->project_id) {
            $module->update(['project_id' => $project->id]);
        }

        $eoad   = RtmfActor::firstOrCreate(['name' => 'EOAD']);
        $koad   = RtmfActor::firstOrCreate(['name' => 'KOAD']);
        $epoad  = RtmfActor::firstOrCreate(['name' => 'EPOAD']);

        // ── Sub-module: WKF — Workflow Profiling ──────────────────────────────
        $wkf = RtmfSubModule::firstOrCreate(
            ['code' => 'PRF-WKF'],
            ['name' => 'Workflow Profiling', 'module_id' => $module->id, 'sort_order' => 60],
        );

        $pages = [];

        // These 3 pages are workflow iframe wrappers — they load dynamic Flowable forms
        $pages[] = $this->seed($module->id, $wkf->id, 'PRF-WKF-01', 'Pendaftaran Lengkap (Workflow)',
            'pages/profiling/pendaftaran-lengkap/index.vue', 10,
            [$eoad->id, $koad->id],
            [
                ['label' => 'Workflow Borang Pendaftaran Lengkap', 'type' => 'Iframe'],
                ['label' => 'Status Proses', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/profiling/workflow/pendaftaran-lengkap', 'description' => 'Muatkan URL workflow borang pendaftaran lengkap']],
        );

        $pages[] = $this->seed($module->id, $wkf->id, 'PRF-WKF-02', 'Pengesahan Profil (Workflow)',
            'pages/profiling/pengesahan/index.vue', 20,
            [$eoad->id, $koad->id, $epoad->id],
            [
                ['label' => 'Workflow Pengesahan Profil', 'type' => 'Iframe'],
                ['label' => 'Status Proses', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/profiling/workflow/pengesahan', 'description' => 'Muatkan URL workflow pengesahan profil asnaf']],
        );

        $pages[] = $this->seed($module->id, $wkf->id, 'PRF-WKF-03', 'Semakan Profil (Workflow)',
            'pages/profiling/semakan/index.vue', 30,
            [$eoad->id, $koad->id],
            [
                ['label' => 'Workflow Semakan Profil', 'type' => 'Iframe'],
                ['label' => 'Status Proses', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/profiling/workflow/semakan', 'description' => 'Muatkan URL workflow semakan profil']],
        );

        // ── Sub-module: SIM — Simulasi & Kaunter ──────────────────────────────
        $sim = RtmfSubModule::firstOrCreate(
            ['code' => 'PRF-SIM'],
            ['name' => 'Simulasi & Kaunter', 'module_id' => $module->id, 'sort_order' => 70],
        );

        $pages[] = $this->seed($module->id, $sim->id, 'PRF-SIM-01', 'Simulasi Pengiraan Asnaf',
            'pages/profiling/simulasi-asnaf/index.vue', 10,
            [$eoad->id, $koad->id, $epoad->id],
            [
                ['label' => 'No. Kad Pengenalan / Nama', 'type' => 'Text'],
                ['label' => 'Cari', 'type' => 'Button'],
                ['screen_name' => 'Keputusan', 'label' => 'Nama', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'No. Kad Pengenalan', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Had Kifayah (RM)', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Skor Multidimensi', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Status Asnaf', 'type' => 'Badge'],
                ['screen_name' => 'Keputusan', 'label' => 'Lihat Butiran', 'type' => 'Button'],
            ],
            [['method' => 'GET', 'endpoint' => '/profiling/simulasi-asnaf', 'description' => 'Carian individu untuk simulasi pengiraan Had Kifayah dan Multidimensi']],
        );

        $pages[] = $this->seed($module->id, $sim->id, 'PRF-SIM-02', 'Butiran Simulasi Pengiraan Asnaf',
            'pages/profiling/simulasi-asnaf/[id].vue', 20,
            [$eoad->id, $koad->id, $epoad->id],
            [
                ['label' => 'Nama', 'type' => 'Text'],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text'],
                ['label' => 'Pengiraan Had Kifayah', 'type' => 'Section'],
                ['label' => 'Had Kifayah Individu (RM)', 'type' => 'Text'],
                ['label' => 'Had Kifayah Keluarga (RM)', 'type' => 'Text'],
                ['label' => 'Pengiraan Multidimensi', 'type' => 'Section'],
                ['label' => 'Skor Multidimensi', 'type' => 'Text'],
                ['label' => 'Dimensi Kemiskinan', 'type' => 'Table'],
                ['label' => 'Keputusan Akhir', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/profiling/simulasi-asnaf/{id}', 'description' => 'Butiran simulasi pengiraan Had Kifayah dan Multidimensi']],
        );

        $pages[] = $this->seed($module->id, $sim->id, 'PRF-SIM-03', 'Penilaian Awal Kaunter',
            'pages/profiling/penilaian-awal/kaunter/index.vue', 30,
            [$eoad->id, $koad->id],
            [
                ['screen_name' => 'Menunggu', 'label' => 'No. Giliran', 'type' => 'Text'],
                ['screen_name' => 'Menunggu', 'label' => 'Nama Pemohon', 'type' => 'Text'],
                ['screen_name' => 'Menunggu', 'label' => 'Masa Datang', 'type' => 'Date'],
                ['screen_name' => 'Menunggu', 'label' => 'Jenis Permohonan', 'type' => 'Badge'],
                ['screen_name' => 'Menunggu', 'label' => 'Mula Penilaian', 'type' => 'Button'],
                ['screen_name' => 'Selesai', 'label' => 'No. Giliran', 'type' => 'Text'],
                ['screen_name' => 'Selesai', 'label' => 'Keputusan', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/profiling/penilaian-awal/kaunter', 'description' => 'Senarai kes penilaian awal di kaunter']],
        );

        $pages[] = $this->seed($module->id, $sim->id, 'PRF-SIM-04', 'Butiran Penilaian Awal Kaunter',
            'pages/profiling/penilaian-awal/kaunter/[id].vue', 40,
            [$eoad->id, $koad->id],
            [
                ['label' => 'No. Giliran', 'type' => 'Text'],
                ['label' => 'Nama Pemohon', 'type' => 'Text'],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text'],
                ['label' => 'Jenis Permohonan', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Penilaian Awal', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Catatan', 'type' => 'Textarea'],
                ['label' => 'Simpan & Selesai', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profiling/penilaian-awal/kaunter/{id}', 'description' => 'Muatkan data kes kaunter'],
                ['method' => 'POST', 'endpoint' => '/profiling/penilaian-awal/kaunter/{id}', 'description' => 'Simpan keputusan penilaian awal kaunter'],
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
            ['PRF-SIM-01', 'PRF-SIM-02'],
            ['PRF-SIM-03', 'PRF-SIM-04'],
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
