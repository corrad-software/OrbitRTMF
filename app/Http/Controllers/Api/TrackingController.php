<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendFeedback;
use App\Models\RtmfFrontendItem;
use App\Models\RtmfModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingController extends Controller
{
    use ApiResponse;

    private const ROLES = ['business_analyst', 'qa', 'technical', 'developer'];

    public function overview(Request $request): JsonResponse
    {
        $moduleId = $request->input('module_id');

        $frontendQuery = RtmfFrontend::query();
        if ($moduleId) {
            $frontendQuery->where('module_id', $moduleId);
        }

        $frontendIds  = $frontendQuery->pluck('id');
        $total        = $frontendIds->count();
        $done         = RtmfFrontend::whereIn('id', $frontendIds)->where('is_done', true)->count();

        // Item status breakdown
        $statusRows = RtmfFrontendItem::select('status', DB::raw('count(*) as c'))
            ->whereIn('rtmf_frontend_id', $frontendIds)
            ->groupBy('status')
            ->get();

        $statusMap   = $statusRows->whereNotNull('status')->pluck('c', 'status')->toArray();
        $unsetCount  = (int) ($statusRows->whereStrict('status', null)->first()?->c ?? 0);
        $totalItems  = array_sum($statusMap) + $unsetCount;
        $implemented = (int) ($statusMap['implemented'] ?? 0);
        $partial     = (int) ($statusMap['partial'] ?? 0);
        $missing     = (int) ($statusMap['missing'] ?? 0);

        // Feedback breakdown per role
        $feedbackRows = RtmfFrontendFeedback::select('role', 'status', DB::raw('count(*) as c'))
            ->whereIn('rtmf_frontend_id', $frontendIds)
            ->groupBy('role', 'status')
            ->get();

        $byReview = [];
        foreach (self::ROLES as $role) {
            $rows = $feedbackRows->where('role', $role);
            $byReview[$role] = [
                'approved' => (int) ($rows->where('status', 'approved')->first()?->c ?? 0),
                'reviewed' => (int) ($rows->where('status', 'reviewed')->first()?->c ?? 0),
                'open'     => (int) ($rows->where('status', 'open')->first()?->c ?? 0),
            ];
        }

        // Pages with all roles approved — wrap as derived table to avoid PostgreSQL 42803
        $approvedAll = DB::table(
            DB::table('rtmf_frontend_feedbacks')
                ->whereIn('rtmf_frontend_id', $frontendIds)
                ->where('status', 'approved')
                ->whereIn('role', self::ROLES)
                ->select('rtmf_frontend_id')
                ->groupBy('rtmf_frontend_id')
                ->havingRaw('COUNT(DISTINCT role) = ?', [count(self::ROLES)]),
            'approved_sub'
        )->count();

        return $this->sendOk([
            'totals' => [
                'pages'       => $total,
                'done'        => $done,
                'pending'     => $total - $done,
                'approvedAll' => $approvedAll,
                'donePct'     => $total > 0 ? round($done / $total * 100) : 0,
                'approvedPct' => $total > 0 ? round($approvedAll / $total * 100) : 0,
            ],
            'byReview' => $byReview,
            'items' => [
                'total'       => $totalItems,
                'implemented' => $implemented,
                'partial'     => $partial,
                'missing'     => $missing,
                'unset'       => $unsetCount,
                'pct'         => $totalItems > 0 ? round($implemented / $totalItems * 100) : 0,
            ],
        ]);
    }

    public function byModule(Request $request): JsonResponse
    {
        $moduleId = $request->input('module_id');

        $frontendStats = RtmfFrontend::select(
            'module_id',
            DB::raw('count(*) as pages_count'),
            DB::raw('sum(case when is_done then 1 else 0 end) as done_count')
        )
            ->when($moduleId, fn ($q) => $q->where('module_id', $moduleId))
            ->groupBy('module_id')
            ->get()
            ->keyBy('module_id');

        $frontendIds = $moduleId
            ? RtmfFrontend::where('module_id', $moduleId)->pluck('id')
            : RtmfFrontend::pluck('id');

        $itemStats = RtmfFrontendItem::select(
            'rtmf_frontends.module_id',
            DB::raw('count(*) as items_count'),
            DB::raw("sum(case when rtmf_frontend_items.status = 'implemented' then 1 else 0 end) as impl_count"),
            DB::raw("sum(case when rtmf_frontend_items.status = 'partial'     then 1 else 0 end) as partial_count"),
            DB::raw("sum(case when rtmf_frontend_items.status = 'missing'     then 1 else 0 end) as missing_count")
        )
            ->join('rtmf_frontends', 'rtmf_frontend_items.rtmf_frontend_id', '=', 'rtmf_frontends.id')
            ->whereIn('rtmf_frontend_items.rtmf_frontend_id', $frontendIds)
            ->groupBy('rtmf_frontends.module_id')
            ->get()
            ->keyBy('module_id');

        $feedbackByModule = DB::table('rtmf_frontend_feedbacks as f')
            ->join('rtmf_frontends as fe', 'fe.id', '=', 'f.rtmf_frontend_id')
            ->whereIn('f.rtmf_frontend_id', $frontendIds)
            ->select('fe.module_id', 'f.role', 'f.status', DB::raw('count(*) as c'))
            ->groupBy('fe.module_id', 'f.role', 'f.status')
            ->get();

        // Two-step: find fully-approved frontend_ids, then count per module
        $approvedAllByModule = DB::table(
            DB::table('rtmf_frontend_feedbacks as f')
                ->join('rtmf_frontends as fe', 'fe.id', '=', 'f.rtmf_frontend_id')
                ->whereIn('f.rtmf_frontend_id', $frontendIds)
                ->where('f.status', 'approved')
                ->whereIn('f.role', self::ROLES)
                ->select('fe.module_id', 'f.rtmf_frontend_id')
                ->groupBy('fe.module_id', 'f.rtmf_frontend_id')
                ->havingRaw('COUNT(DISTINCT f.role) = ?', [count(self::ROLES)]),
            'approved_sub'
        )
            ->select('module_id', DB::raw('count(*) as cnt'))
            ->groupBy('module_id')
            ->pluck('cnt', 'module_id');

        $modulesQuery = RtmfModule::select('id', 'code', 'name')->orderBy('sort_order')->orderBy('id');
        if ($moduleId) {
            $modulesQuery->where('id', $moduleId);
        }

        $data = $modulesQuery->get()->map(function ($module) use ($frontendStats, $itemStats, $feedbackByModule, $approvedAllByModule) {
            $fs = $frontendStats->get($module->id);
            $is = $itemStats->get($module->id);
            $pages      = (int) ($fs?->pages_count ?? 0);
            $doneCt     = (int) ($fs?->done_count ?? 0);
            $itemsTotal = (int) ($is?->items_count ?? 0);
            $implCt     = (int) ($is?->impl_count ?? 0);
            $partCt     = (int) ($is?->partial_count ?? 0);
            $missCt     = (int) ($is?->missing_count ?? 0);

            $review = [];
            foreach (self::ROLES as $role) {
                $rows = $feedbackByModule->where('module_id', $module->id)->where('role', $role);
                $review[$role] = [
                    'approved' => (int) ($rows->where('status', 'approved')->first()?->c ?? 0),
                    'reviewed' => (int) ($rows->where('status', 'reviewed')->first()?->c ?? 0),
                    'open'     => (int) ($rows->where('status', 'open')->first()?->c ?? 0),
                ];
            }

            return [
                'id'          => $module->id,
                'code'        => $module->code,
                'name'        => $module->name,
                'pages'       => $pages,
                'done'        => $doneCt,
                'donePct'     => $pages > 0 ? round($doneCt / $pages * 100) : 0,
                'approvedAll' => (int) ($approvedAllByModule->get($module->id) ?? 0),
                'items'       => [
                    'total'       => $itemsTotal,
                    'implemented' => $implCt,
                    'partial'     => $partCt,
                    'missing'     => $missCt,
                    'unset'       => $itemsTotal - $implCt - $partCt - $missCt,
                    'pct'         => $itemsTotal > 0 ? round($implCt / $itemsTotal * 100) : 0,
                ],
                'review' => $review,
            ];
        });

        return $this->sendOk($data);
    }

    public function pages(Request $request): JsonResponse
    {
        $page     = max(1, (int) $request->input('page', 1));
        $limit    = min(100, max(1, (int) $request->input('limit', 50)));
        $q        = trim((string) $request->input('q', ''));
        $moduleId = $request->input('module_id');
        $isDone   = $request->input('is_done'); // '1', '0', or null

        $query = RtmfFrontend::with(['module:id,code,name', 'feedbacks:rtmf_frontend_id,role,status'])
            ->withCount([
                'items',
                'items as implemented_count' => fn ($q) => $q->where('status', 'implemented'),
                'items as partial_count'     => fn ($q) => $q->where('status', 'partial'),
                'items as missing_count'     => fn ($q) => $q->where('status', 'missing'),
            ])
            ->orderBy('module_id')
            ->orderBy('id');

        if ($q !== '') {
            $query->where(fn ($w) => $w
                ->where('spec_id', 'like', "%{$q}%")
                ->orWhere('title', 'like', "%{$q}%")
            );
        }

        if ($moduleId) {
            $query->where('module_id', $moduleId);
        }

        if ($isDone !== null) {
            $query->where('is_done', (bool)(int)$isDone);
        }

        // Use a clean query without withCount() to avoid PostgreSQL 42803 on count()
        $countQuery = RtmfFrontend::query();
        if ($q !== '') {
            $countQuery->where(fn ($w) => $w
                ->where('spec_id', 'like', "%{$q}%")
                ->orWhere('title', 'like', "%{$q}%")
            );
        }
        if ($moduleId) {
            $countQuery->where('module_id', $moduleId);
        }
        if ($isDone !== null) {
            $countQuery->where('is_done', (bool)(int)$isDone);
        }
        $total = $countQuery->count();
        $rows  = $query->skip(($page - 1) * $limit)->take($limit)->get();

        $data = $rows->map(function ($row) {
            $reviewMap = [];
            foreach (self::ROLES as $role) {
                $reviewMap[match($role) {
                    'business_analyst' => 'ba',
                    'qa'               => 'qa',
                    'technical'        => 'tech',
                    'developer'        => 'dev',
                }] = $row->feedbacks->firstWhere('role', $role)?->status ?? null;
            }

            $itemsTotal  = (int) ($row->items_count ?? 0);
            $implemented = (int) ($row->implemented_count ?? 0);
            $partial     = (int) ($row->partial_count ?? 0);
            $missing     = (int) ($row->missing_count ?? 0);

            return [
                'id'        => $row->id,
                'specId'    => $row->spec_id,
                'title'     => $row->title,
                'module'    => $row->module ? [
                    'id'   => $row->module->id,
                    'code' => $row->module->code,
                    'name' => $row->module->name,
                ] : null,
                'isDone'    => $row->is_done,
                'review'    => $reviewMap,
                'items'     => [
                    'total'       => $itemsTotal,
                    'implemented' => $implemented,
                    'partial'     => $partial,
                    'missing'     => $missing,
                    'unset'       => $itemsTotal - $implemented - $partial - $missing,
                    'pct'         => $itemsTotal > 0 ? round($implemented / $itemsTotal * 100) : null,
                ],
                'assignees' => is_array($row->assignees)
                    ? array_values(array_filter(array_column($row->assignees, 'name')))
                    : [],
            ];
        });

        return $this->sendOk($data, [
            'page' => $page, 'limit' => $limit,
            'total' => $total, 'totalPages' => (int) ceil($total / $limit),
        ]);
    }

    public function trend(Request $request): JsonResponse
    {
        $days     = min(30, max(7, (int) $request->input('days', 14)));
        $moduleId = $request->input('module_id');

        $frontendIds = $moduleId
            ? RtmfFrontend::where('module_id', $moduleId)->pluck('id')
            : null; // null = no filter (all)

        // Daily done pages (using updated_at as proxy for when is_done was set)
        $doneByDay = DB::table('rtmf_frontends')
            ->where('is_done', true)
            ->when($frontendIds, fn ($q) => $q->whereIn('id', $frontendIds))
            ->whereNull('deleted_at')
            ->selectRaw("DATE(updated_at AT TIME ZONE 'UTC') as tarikh, COUNT(*) as cnt")
            ->groupByRaw("DATE(updated_at AT TIME ZONE 'UTC')")
            ->pluck('cnt', 'tarikh')
            ->map(fn ($v) => (int) $v);

        // Daily approvals across all roles
        $approvedByDay = DB::table('rtmf_frontend_feedbacks')
            ->where('status', 'approved')
            ->when($frontendIds, fn ($q) => $q->whereIn('rtmf_frontend_id', $frontendIds))
            ->selectRaw("DATE(updated_at AT TIME ZONE 'UTC') as tarikh, COUNT(*) as cnt")
            ->groupByRaw("DATE(updated_at AT TIME ZONE 'UTC')")
            ->pluck('cnt', 'tarikh')
            ->map(fn ($v) => (int) $v);

        // Cumulative totals as of start of window
        $windowStart = now()->subDays($days)->startOfDay();

        $cumulativeDone = DB::table('rtmf_frontends')
            ->where('is_done', true)
            ->when($frontendIds, fn ($q) => $q->whereIn('id', $frontendIds))
            ->whereNull('deleted_at')
            ->where('updated_at', '<', $windowStart)
            ->count();

        $cumulativeApproved = DB::table('rtmf_frontend_feedbacks')
            ->where('status', 'approved')
            ->when($frontendIds, fn ($q) => $q->whereIn('rtmf_frontend_id', $frontendIds))
            ->where('updated_at', '<', $windowStart)
            ->count();

        $rows = [];
        $runningDone     = $cumulativeDone;
        $runningApproved = $cumulativeApproved;

        for ($i = $days - 1; $i >= 0; $i--) {
            $date        = now()->subDays($i)->format('Y-m-d');
            $doneDelta   = $doneByDay->get($date, 0);
            $approvedDelta = $approvedByDay->get($date, 0);
            $runningDone     += $doneDelta;
            $runningApproved += $approvedDelta;

            $rows[] = [
                'tarikh'          => $date,
                'halamanSelesai'  => $doneDelta,
                'reviewLulus'     => $approvedDelta,
                'jumlahSelesai'   => $runningDone,
                'jumlahLulus'     => $runningApproved,
            ];
        }

        return $this->sendOk($rows);
    }

    public function modules(): JsonResponse
    {
        $data = RtmfModule::select('id', 'code', 'name')
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        return $this->sendOk($data);
    }
}
