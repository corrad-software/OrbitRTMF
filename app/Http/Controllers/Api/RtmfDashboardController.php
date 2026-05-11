<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfActor;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendItem;
use App\Models\RtmfFrontendScenarioGroup;
use App\Models\RtmfModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RtmfDashboardController extends Controller
{
    use ApiResponse;

    public function summary(): JsonResponse
    {
        // Totals
        $totalFrontends    = RtmfFrontend::count();
        $totalDone         = RtmfFrontend::where('is_done', true)->count();
        $totalModules      = RtmfModule::count();
        $totalActors       = RtmfActor::count();
        $totalItems        = RtmfFrontendItem::count();
        $totalScenarios    = RtmfFrontendScenarioGroup::count();

        // Item status breakdown
        $statusCounts = RtmfFrontendItem::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $itemsByStatus = [
            'implemented' => (int) ($statusCounts['implemented'] ?? 0),
            'partial'     => (int) ($statusCounts['partial'] ?? 0),
            'missing'     => (int) ($statusCounts['missing'] ?? 0),
            'unset'       => (int) ($statusCounts[''] ?? $statusCounts[null] ?? 0),
        ];

        // Fix null key from pluck
        $nullCount = RtmfFrontendItem::whereNull('status')->count();
        $itemsByStatus['unset'] = $nullCount;

        // Per-module breakdown — aggregated in 2 queries instead of N*3
        $frontendStats = RtmfFrontend::select(
            'module_id',
            DB::raw('count(*) as frontends_count'),
            DB::raw('sum(case when is_done then 1 else 0 end) as done_count')
        )->groupBy('module_id')->get()->keyBy('module_id');

        $itemStats = RtmfFrontendItem::select(
            'rtmf_frontends.module_id',
            DB::raw('count(*) as items_count'),
            DB::raw("sum(case when rtmf_frontend_items.status = 'implemented' then 1 else 0 end) as implemented_count")
        )->join('rtmf_frontends', 'rtmf_frontend_items.rtmf_frontend_id', '=', 'rtmf_frontends.id')
        ->groupBy('rtmf_frontends.module_id')
        ->get()->keyBy('module_id');

        $byModule = RtmfModule::select('id', 'code', 'name')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($module) use ($frontendStats, $itemStats) {
                $fs = $frontendStats->get($module->id);
                $is = $itemStats->get($module->id);

                return [
                    'id'               => $module->id,
                    'code'             => $module->code,
                    'name'             => $module->name,
                    'frontendsCount'   => (int) ($fs?->frontends_count ?? 0),
                    'doneCount'        => (int) ($fs?->done_count ?? 0),
                    'itemsCount'       => (int) ($is?->items_count ?? 0),
                    'implementedCount' => (int) ($is?->implemented_count ?? 0),
                ];
            });

        // Per-actor breakdown — aggregated in 1 query
        $actorStats = DB::table('rtmf_frontend_actor')
            ->select('rtmf_actor_id', DB::raw('count(*) as frontends_count'))
            ->groupBy('rtmf_actor_id')
            ->pluck('frontends_count', 'rtmf_actor_id');

        $byActor = RtmfActor::select('id', 'name')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($actor) => [
                'id'             => $actor->id,
                'name'           => $actor->name,
                'frontendsCount' => (int) ($actorStats->get($actor->id) ?? 0),
            ]);

        return $this->sendOk([
            'totals' => [
                'frontends'  => $totalFrontends,
                'done'       => $totalDone,
                'modules'    => $totalModules,
                'actors'     => $totalActors,
                'items'      => $totalItems,
                'scenarios'  => $totalScenarios,
            ],
            'itemsByStatus' => $itemsByStatus,
            'byModule'      => $byModule,
            'byActor'       => $byActor,
        ]);
    }
}
