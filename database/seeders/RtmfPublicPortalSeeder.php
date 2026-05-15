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

class RtmfPublicPortalSeeder extends Seeder
{
    public function run(): void
    {
        $project = RtmfProject::first();

        $module = RtmfModule::firstOrCreate(
            ['code' => 'PUB'],
            ['name' => 'Portal Awam', 'project_id' => $project->id, 'sort_order' => 70],
        );
        if (! $module->project_id) {
            $module->update(['project_id' => $project->id]);
        }

        $awam    = RtmfActor::firstOrCreate(['name' => 'ORANG_AWAM']);
        $asnaf   = RtmfActor::firstOrCreate(['name' => 'ASNAF']);
        $eoad    = RtmfActor::firstOrCreate(['name' => 'EOAD']);
        $pa      = RtmfActor::firstOrCreate(['name' => 'PENOLONG_AMIL']);

        // ── Sub-module: DAF — Daftar Aduan Awam ───────────────────────────────
        $daf = RtmfSubModule::firstOrCreate(
            ['code' => 'PUB-DAF'],
            ['name' => 'Daftar Aduan Awam', 'module_id' => $module->id, 'sort_order' => 10],
        );

        $pages = [];

        $pages[] = $this->seed($module->id, $daf->id, 'PUB-DAF-01', 'Borang Aduan Awam',
            'pages/public/daftar-aduan/index.vue', 10,
            [$awam->id, $asnaf->id],
            [
                ['label' => 'Nama Pengadu', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Telefon', 'type' => 'Tel', 'mandatory' => true],
                ['label' => 'E-mel', 'type' => 'Email'],
                ['label' => 'Kategori Masalah', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Penerangan Aduan', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Alamat', 'type' => 'Textarea'],
                ['label' => 'Lampiran', 'type' => 'File'],
                ['label' => 'Hantar Aduan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/public/kategori-masalah', 'description' => 'Senarai kategori masalah untuk borang awam'],
                ['method' => 'POST', 'endpoint' => '/public/daftar-aduan', 'description' => 'Hantar aduan awam'],
            ],
        );

        $pages[] = $this->seed($module->id, $daf->id, 'PUB-DAF-02', 'Berjaya Daftar Aduan Awam',
            'pages/public/daftar-aduan/berjaya-daftar/index.vue', 20,
            [$awam->id, $asnaf->id],
            [
                ['label' => 'No. Rujukan Aduan', 'type' => 'Text'],
                ['label' => 'Mesej Pengesahan', 'type' => 'Text'],
                ['label' => 'Semak Status Aduan', 'type' => 'Button'],
                ['label' => 'Kembali ke Laman Utama', 'type' => 'Button'],
            ],
            [],
        );

        // ── Sub-module: FAQ — FAQ Awam ─────────────────────────────────────────
        $faq = RtmfSubModule::firstOrCreate(
            ['code' => 'PUB-FAQ'],
            ['name' => 'FAQ Awam', 'module_id' => $module->id, 'sort_order' => 20],
        );

        $pages[] = $this->seed($module->id, $faq->id, 'PUB-FAQ-01', 'Soalan Lazim (FAQ)',
            'pages/public/faq/index.vue', 10,
            [$awam->id, $asnaf->id],
            [
                ['label' => 'Cari Soalan', 'type' => 'Text'],
                ['label' => 'Kategori FAQ', 'type' => 'Select'],
                ['screen_name' => 'Senarai FAQ', 'label' => 'Soalan', 'type' => 'Text'],
                ['screen_name' => 'Senarai FAQ', 'label' => 'Jawapan', 'type' => 'Textarea'],
            ],
            [['method' => 'GET', 'endpoint' => '/public/faq', 'description' => 'Senarai soalan lazim portal awam']],
        );

        // ── Sub-module: PKT — PA Kad Tauliah ──────────────────────────────────
        $pkt = RtmfSubModule::firstOrCreate(
            ['code' => 'PUB-PKT'],
            ['name' => 'PA Kad Tauliah', 'module_id' => $module->id, 'sort_order' => 30],
        );

        $pages[] = $this->seed($module->id, $pkt->id, 'PUB-PKT-01', 'Semak Kad Tauliah Penolong Amil',
            'pages/public/pa-kad-tauliah/index.vue', 10,
            [$awam->id, $pa->id],
            [
                ['label' => 'No. Kad Tauliah / Nama PA', 'type' => 'Text'],
                ['label' => 'Semak', 'type' => 'Button'],
                ['screen_name' => 'Keputusan', 'label' => 'Nama PA', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'No. Kad Tauliah', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Kariah', 'type' => 'Text'],
                ['screen_name' => 'Keputusan', 'label' => 'Tarikh Tamat', 'type' => 'Date'],
                ['screen_name' => 'Keputusan', 'label' => 'Status', 'type' => 'Badge'],
            ],
            [['method' => 'GET', 'endpoint' => '/public/pa-kad-tauliah', 'description' => 'Semak kesahihan kad tauliah Penolong Amil']],
        );

        // ── Sub-module: BNC — Pendaftaran Bencana ─────────────────────────────
        $bnc = RtmfSubModule::firstOrCreate(
            ['code' => 'PUB-BNC'],
            ['name' => 'Pendaftaran Bencana', 'module_id' => $module->id, 'sort_order' => 40],
        );

        $pages[] = $this->seed($module->id, $bnc->id, 'PUB-BNC-01', 'Borang Pendaftaran Bencana',
            'pages/public/pendaftaran-bencana/index.vue', 10,
            [$awam->id, $asnaf->id],
            [
                ['label' => 'Nama', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Telefon', 'type' => 'Tel', 'mandatory' => true],
                ['label' => 'Jenis Bencana', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Alamat Terlibat', 'type' => 'Textarea', 'mandatory' => true],
                ['label' => 'Bilangan Mangsa', 'type' => 'Number'],
                ['label' => 'Kerosakan (Anggarkan RM)', 'type' => 'Number'],
                ['label' => 'Lampiran', 'type' => 'File'],
                ['label' => 'Daftar', 'type' => 'Button'],
            ],
            [['method' => 'POST', 'endpoint' => '/public/pendaftaran-bencana', 'description' => 'Daftar kes bencana secara awam']],
        );

        $pages[] = $this->seed($module->id, $bnc->id, 'PUB-BNC-02', 'Berjaya Daftar Bencana',
            'pages/public/pendaftaran-bencana/berjaya/index.vue', 20,
            [$awam->id, $asnaf->id],
            [
                ['label' => 'No. Rujukan Pendaftaran', 'type' => 'Text'],
                ['label' => 'Mesej Pengesahan', 'type' => 'Text'],
                ['label' => 'Kembali ke Laman Utama', 'type' => 'Button'],
            ],
            [],
        );

        // ── Sub-module: PRG — Program Awam ────────────────────────────────────
        $prg = RtmfSubModule::firstOrCreate(
            ['code' => 'PUB-PRG'],
            ['name' => 'Program Awam', 'module_id' => $module->id, 'sort_order' => 50],
        );

        $pages[] = $this->seed($module->id, $prg->id, 'PUB-PRG-01', 'Kehadiran Program (Awam)',
            'pages/public/program/kehadiran/[id].vue', 10,
            [$awam->id, $asnaf->id, $pa->id],
            [
                ['label' => 'Nama Program', 'type' => 'Text'],
                ['label' => 'Tarikh & Masa', 'type' => 'Date'],
                ['label' => 'Lokasi', 'type' => 'Text'],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Sahkan Kehadiran', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/public/program/{id}/kehadiran', 'description' => 'Muatkan maklumat program untuk pendaftaran kehadiran'],
                ['method' => 'POST', 'endpoint' => '/public/program/{id}/kehadiran', 'description' => 'Daftar kehadiran program'],
            ],
        );

        $pages[] = $this->seed($module->id, $prg->id, 'PUB-PRG-02', 'Tuntutan Program (Awam)',
            'pages/public/program/tuntutan/[id].vue', 20,
            [$awam->id, $asnaf->id],
            [
                ['label' => 'Nama Program', 'type' => 'Text'],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Jenis Tuntutan', 'type' => 'Select', 'mandatory' => true],
                ['label' => 'Amaun Dituntut (RM)', 'type' => 'Number'],
                ['label' => 'Lampiran Resit', 'type' => 'File'],
                ['label' => 'Hantar Tuntutan', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/public/program/{id}/tuntutan', 'description' => 'Muatkan maklumat program untuk tuntutan'],
                ['method' => 'POST', 'endpoint' => '/public/program/{id}/tuntutan', 'description' => 'Hantar tuntutan program'],
            ],
        );

        // ── Sub-module: TWR — Terima Tawaran ──────────────────────────────────
        $twr = RtmfSubModule::firstOrCreate(
            ['code' => 'PUB-TWR'],
            ['name' => 'Terima Tawaran', 'module_id' => $module->id, 'sort_order' => 60],
        );

        $pages[] = $this->seed($module->id, $twr->id, 'PUB-TWR-01', 'Borang Terima Tawaran Bantuan',
            'pages/public/terima-tawaran/index.vue', 10,
            [$awam->id, $asnaf->id],
            [
                ['label' => 'No. Rujukan Tawaran', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'No. Kad Pengenalan', 'type' => 'Text', 'mandatory' => true],
                ['label' => 'Jenis Bantuan Ditawarkan', 'type' => 'Text'],
                ['label' => 'Amaun Bantuan (RM)', 'type' => 'Text'],
                ['label' => 'Persetujuan Terma & Syarat', 'type' => 'Checkbox', 'mandatory' => true],
                ['label' => 'Terima Tawaran', 'type' => 'Button'],
                ['label' => 'Tolak Tawaran', 'type' => 'Button'],
            ],
            [
                ['method' => 'GET', 'endpoint' => '/public/terima-tawaran', 'description' => 'Muatkan maklumat tawaran bantuan'],
                ['method' => 'POST', 'endpoint' => '/public/terima-tawaran/terima', 'description' => 'Sahkan penerimaan tawaran bantuan'],
                ['method' => 'POST', 'endpoint' => '/public/terima-tawaran/tolak', 'description' => 'Tolak tawaran bantuan'],
            ],
        );

        $pages[] = $this->seed($module->id, $twr->id, 'PUB-TWR-02', 'Berjaya Terima Tawaran',
            'pages/public/terima-tawaran/berjaya/index.vue', 20,
            [$awam->id, $asnaf->id],
            [
                ['label' => 'No. Rujukan Tawaran', 'type' => 'Text'],
                ['label' => 'Status Penerimaan', 'type' => 'Badge'],
                ['label' => 'Maklumat Pembayaran', 'type' => 'Text'],
                ['label' => 'Kembali ke Laman Utama', 'type' => 'Button'],
            ],
            [],
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
            ['PUB-DAF-01', 'PUB-DAF-02'],
            ['PUB-BNC-01', 'PUB-BNC-02'],
            ['PUB-TWR-01', 'PUB-TWR-02'],
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
