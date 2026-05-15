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

class RtmfSistemGlobalSeeder extends Seeder
{
    public function run(): void
    {
        $project = RtmfProject::first();

        $module = RtmfModule::firstOrCreate(
            ['code' => 'SYS'],
            ['name' => 'Sistem Global', 'project_id' => $project->id, 'sort_order' => 5],
        );
        if (! $module->project_id) {
            $module->update(['project_id' => $project->id]);
        }

        $superAdmin = RtmfActor::firstOrCreate(['name' => 'SUPER_ADMIN']);
        $pnt        = RtmfActor::firstOrCreate(['name' => 'PENTADBIR_SISTEM']);
        $eoad       = RtmfActor::firstOrCreate(['name' => 'EOAD']);
        $koad       = RtmfActor::firstOrCreate(['name' => 'KOAD']);
        $epoad      = RtmfActor::firstOrCreate(['name' => 'EPOAD']);
        $awam       = RtmfActor::firstOrCreate(['name' => 'ORANG_AWAM']);
        $asnaf      = RtmfActor::firstOrCreate(['name' => 'ASNAF']);

        $allStaff = [$superAdmin->id, $pnt->id, $eoad->id, $koad->id, $epoad->id];
        $allUsers = array_merge($allStaff, [$awam->id, $asnaf->id]);

        // ── Sub-module: GLB — Global ───────────────────────────────────────────
        $glb = RtmfSubModule::firstOrCreate(
            ['code' => 'SYS-GLB'],
            ['name' => 'Global', 'module_id' => $module->id, 'sort_order' => 10],
        );

        $pages = [];

        $pages[] = $this->seed($module->id, $glb->id, 'SYS-GLB-01', 'Dashboard Utama',
            'pages/dashboard/index.vue', 10,
            $allStaff,
            [
                ['label' => 'Jumlah Permohonan Hari Ini', 'type' => 'Counter'],
                ['label' => 'Jumlah Kes Dalam Proses', 'type' => 'Counter'],
                ['label' => 'Jumlah Kes Selesai', 'type' => 'Counter'],
                ['label' => 'Graf Trend Bulanan', 'type' => 'Chart'],
                ['label' => 'Kes Terkini Perlu Tindakan', 'type' => 'Table'],
                ['label' => 'Notifikasi Terkini', 'type' => 'Table'],
            ],
            [['method' => 'GET', 'endpoint' => '/dashboard', 'description' => 'Dashboard utama sistem — ringkasan merentas semua modul']],
        );

        $pages[] = $this->seed($module->id, $glb->id, 'SYS-GLB-02', 'Notifikasi',
            'pages/notifikasi/index.vue', 20,
            $allUsers,
            [
                ['screen_name' => 'Belum Dibaca', 'label' => 'Tajuk Notifikasi', 'type' => 'Text'],
                ['screen_name' => 'Belum Dibaca', 'label' => 'Modul', 'type' => 'Badge'],
                ['screen_name' => 'Belum Dibaca', 'label' => 'Tarikh Masa', 'type' => 'Date'],
                ['screen_name' => 'Belum Dibaca', 'label' => 'Tandakan Dibaca', 'type' => 'Button'],
                ['screen_name' => 'Semua', 'label' => 'Tajuk Notifikasi', 'type' => 'Text'],
                ['screen_name' => 'Semua', 'label' => 'Status Baca', 'type' => 'Badge'],
                ['screen_name' => 'Semua', 'label' => 'Tarikh Masa', 'type' => 'Date'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/notifikasi', 'description' => 'Senarai notifikasi pengguna semasa'],
                ['method' => 'PUT', 'endpoint' => '/notifikasi/{id}/baca', 'description' => 'Tandakan notifikasi sebagai dibaca'],
                ['method' => 'PUT', 'endpoint' => '/notifikasi/baca-semua', 'description' => 'Tandakan semua notifikasi sebagai dibaca'],
            ],
        );

        $pages[] = $this->seed($module->id, $glb->id, 'SYS-GLB-03', 'Kemaskini Profil Saya',
            'pages/profil-saya/kemaskini/index.vue', 30,
            $allUsers,
            [
                ['label' => 'Nama Penuh', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Telefon', 'type' => 'Tel'],
                ['label' => 'E-mel', 'type' => 'Email'],
                ['label' => 'Foto Profil', 'type' => 'File'],
                ['label' => 'Kata Laluan Semasa', 'type' => 'Password'],
                ['label' => 'Kata Laluan Baharu', 'type' => 'Password'],
                ['label' => 'Sahkan Kata Laluan Baharu', 'type' => 'Password'],
                ['label' => 'Simpan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/profil-saya', 'description' => 'Muatkan maklumat profil pengguna semasa'],
                ['method' => 'PUT', 'endpoint' => '/profil-saya', 'description' => 'Kemaskini profil pengguna semasa'],
                ['method' => 'PUT', 'endpoint' => '/profil-saya/kata-laluan', 'description' => 'Tukar kata laluan'],
            ],
        );

        $pages[] = $this->seed($module->id, $glb->id, 'SYS-GLB-04', 'Tetapan Profil',
            'pages/tetapan/profil/index.vue', 40,
            $allUsers,
            [
                ['label' => 'Bahasa Antaramuka', 'type' => 'Select'],
                ['label' => 'Zon Masa', 'type' => 'Select'],
                ['label' => 'Notifikasi E-mel', 'type' => 'Toggle'],
                ['label' => 'Notifikasi SMS', 'type' => 'Toggle'],
                ['label' => 'Simpan Tetapan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/tetapan/profil', 'description' => 'Muatkan tetapan profil pengguna'],
                ['method' => 'PUT', 'endpoint' => '/tetapan/profil', 'description' => 'Simpan tetapan profil'],
            ],
        );

        $pages[] = $this->seed($module->id, $glb->id, 'SYS-GLB-05', 'Paparan Aliran Proses',
            'pages/process-flow/view/[id].vue', 50,
            $allStaff,
            [
                ['label' => 'Tajuk Aliran Proses', 'type' => 'Text'],
                ['label' => 'Modul', 'type' => 'Badge'],
                ['label' => 'Diagram Aliran', 'type' => 'Diagram'],
                ['label' => 'Peringkat Semasa', 'type' => 'Badge'],
                ['label' => 'Sejarah Tindakan', 'type' => 'Table'],
            ],
            [['method' => 'GET', 'endpoint' => '/process-flow/{id}', 'description' => 'Paparan diagram aliran proses sesuatu permohonan']],
        );

        $pages[] = $this->seed($module->id, $glb->id, 'SYS-GLB-06', 'Paparan Workflow',
            'pages/workflow/[id].vue', 60,
            $allStaff,
            [
                ['label' => 'Tajuk Workflow', 'type' => 'Text'],
                ['label' => 'No. Rujukan', 'type' => 'Text'],
                ['label' => 'Peringkat Semasa', 'type' => 'Badge'],
                ['label' => 'Borang Workflow', 'type' => 'Iframe'],
                ['label' => 'Sejarah Kelulusan', 'type' => 'Table'],
            ],
            [['method' => 'GET', 'endpoint' => '/workflow/{id}', 'description' => 'Paparan dan tindakan workflow Flowable']],
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
            ['SYS-GLB-03', 'SYS-GLB-04'],
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
