<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrController extends Controller
{
    use ApiResponse;

    private const STATUS_RESOLVED = 80;
    private const STATUS_CLOSED   = 90;

    private const OPEN_STATUSES = [10, 20, 30, 40, 50];

    private const PRIORITY_LABEL = [
        10 => 'none', 20 => 'low', 30 => 'normal',
        40 => 'high', 50 => 'urgent', 60 => 'immediate',
    ];

    private const STATUS_LABEL = [
        10 => 'new', 20 => 'feedback', 30 => 'acknowledged',
        40 => 'confirmed', 50 => 'assigned', 80 => 'resolved', 90 => 'closed',
    ];

    private const RESOLUTION_LABEL = [
        10 => 'open', 20 => 'fixed', 30 => 'reopened', 40 => 'unable to reproduce',
        50 => 'not fixable', 60 => 'duplicate', 70 => 'not a bug', 80 => "won't fix",
        90 => 'suspended',
    ];

    private function db()
    {
        return DB::connection('mantis');
    }

    private function crCategoryIds(): array
    {
        return $this->db()
            ->table('mantis_category_table')
            ->where(function ($q) {
                $q->whereRaw("LOWER(name) LIKE '%cr%'")
                  ->orWhereRaw("LOWER(name) LIKE '%change request%'");
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function crBugs(array $crIds)
    {
        return $this->db()->table('mantis_bug_table')
            ->whereIn('category_id', $crIds)
            ->where('status', '!=', self::STATUS_CLOSED);
    }

    public function filters(): JsonResponse
    {
        $crIds = $this->crCategoryIds();
        $empty = ['projek' => [], 'kategori' => [], 'priority' => [], 'by' => [], 'assigned' => []];
        if (empty($crIds)) {
            return $this->sendOk($empty);
        }

        $db = $this->db();

        $projek = $db->table('mantis_bug_table as b')
            ->leftJoin('mantis_project_table as p', 'p.id', '=', 'b.project_id')
            ->whereIn('b.category_id', $crIds)
            ->where('b.status', '!=', self::STATUS_CLOSED)
            ->whereNotNull('p.name')
            ->orderBy('p.name')
            ->distinct()->pluck('p.name')->values();

        $kategori = $db->table('mantis_bug_table as b')
            ->leftJoin('mantis_category_table as c', 'c.id', '=', 'b.category_id')
            ->whereIn('b.category_id', $crIds)
            ->where('b.status', '!=', self::STATUS_CLOSED)
            ->whereNotNull('c.name')
            ->orderBy('c.name')
            ->distinct()->pluck('c.name')->values();

        $priorityCodes = $db->table('mantis_bug_table')
            ->whereIn('category_id', $crIds)
            ->where('status', '!=', self::STATUS_CLOSED)
            ->distinct()->pluck('priority')
            ->map(fn ($p) => self::PRIORITY_LABEL[(int) $p] ?? null)
            ->filter()->unique()->values();

        $by = $db->table('mantis_bug_table as b')
            ->leftJoin('mantis_user_table as ur', 'ur.id', '=', 'b.reporter_id')
            ->whereIn('b.category_id', $crIds)
            ->where('b.status', '!=', self::STATUS_CLOSED)
            ->whereNotNull('ur.username')
            ->orderBy('ur.username')
            ->distinct()->pluck('ur.username')->values();

        $assigned = $db->table('mantis_bug_table as b')
            ->leftJoin('mantis_user_table as uh', 'uh.id', '=', 'b.handler_id')
            ->whereIn('b.category_id', $crIds)
            ->where('b.status', '!=', self::STATUS_CLOSED)
            ->where('b.handler_id', '>', 0)
            ->whereNotNull('uh.username')
            ->orderBy('uh.username')
            ->distinct()->pluck('uh.username')->values();

        return $this->sendOk(compact('projek', 'kategori', 'by', 'assigned') + ['priority' => $priorityCodes]);
    }

    public function log(Request $request): JsonResponse
    {
        $crIds = $this->crCategoryIds();
        if (empty($crIds)) {
            return $this->sendOk([], ['page' => 1, 'limit' => 50, 'total' => 0, 'totalPages' => 0]);
        }

        $page    = max(1, (int) $request->input('page', 1));
        $limit   = min(200, max(1, (int) $request->input('limit', 50)));
        $q       = trim((string) $request->input('q', ''));
        $sortBy  = $request->input('sort_by', 'id');
        $sortDir = strtolower($request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $statusFilter   = array_intersect((array) $request->input('status',   []), array_values(self::STATUS_LABEL));
        $priorityFilter = array_intersect((array) $request->input('priority', []), array_values(self::PRIORITY_LABEL));
        $projekFilter   = (array) $request->input('projek',   []);
        $kategoriFilter = (array) $request->input('kategori', []);
        $byFilter       = (array) $request->input('by',       []);
        $assignedFilter = (array) $request->input('assigned', []);
        $dateFrom       = $request->input('date_from'); // YYYY-MM-DD
        $dateTo         = $request->input('date_to');   // YYYY-MM-DD

        $allowedSort = ['id', 'date_submitted', 'last_updated', 'priority', 'status'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'id';
        }

        $base = $this->db()->table('mantis_bug_table as b')
            ->leftJoin('mantis_project_table as p', 'p.id', '=', 'b.project_id')
            ->leftJoin('mantis_category_table as c', 'c.id', '=', 'b.category_id')
            ->leftJoin('mantis_user_table as ur', 'ur.id', '=', 'b.reporter_id')
            ->leftJoin('mantis_user_table as uh', 'uh.id', '=', 'b.handler_id')
            ->whereIn('b.category_id', $crIds)
            ->where('b.status', '!=', self::STATUS_CLOSED);

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('b.summary', 'like', "%{$q}%")
                  ->orWhere('p.name', 'like', "%{$q}%")
                  ->orWhere('b.id', $q);
            });
        }

        if (! empty($statusFilter)) {
            $labelToCode = array_flip(self::STATUS_LABEL);
            $codes = array_values(array_filter(
                array_map(fn ($s) => $labelToCode[$s] ?? null, $statusFilter),
                fn ($v) => $v !== null,
            ));
            if (! empty($codes)) {
                $base->whereIn('b.status', $codes);
            }
        }

        if (! empty($priorityFilter)) {
            $labelToCode = array_flip(self::PRIORITY_LABEL);
            $codes = array_values(array_filter(
                array_map(fn ($p) => $labelToCode[$p] ?? null, $priorityFilter),
                fn ($v) => $v !== null,
            ));
            if (! empty($codes)) {
                $base->whereIn('b.priority', $codes);
            }
        }

        if (! empty($projekFilter)) {
            $base->whereIn('p.name', $projekFilter);
        }

        if (! empty($kategoriFilter)) {
            $base->whereIn('c.name', $kategoriFilter);
        }

        if (! empty($byFilter)) {
            $base->whereIn('ur.username', $byFilter);
        }

        if (! empty($assignedFilter)) {
            $base->whereIn('uh.username', $assignedFilter);
        }

        if ($dateFrom) {
            $ts = strtotime($dateFrom . ' 00:00:00');
            if ($ts !== false) {
                $base->where('b.date_submitted', '>=', $ts);
            }
        }

        if ($dateTo) {
            $ts = strtotime($dateTo . ' 23:59:59');
            if ($ts !== false) {
                $base->where('b.date_submitted', '<=', $ts);
            }
        }

        $total = (clone $base)->count('b.id');

        $rows = $base->orderBy('b.' . $sortBy, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get([
                'b.id', 'p.name as project', 'c.name as category', 'b.summary',
                'b.priority', 'b.status', 'b.resolution',
                'ur.username as reporter', 'uh.username as handler',
                'b.date_submitted', 'b.last_updated',
            ]);

        $now = time();
        $data = $rows->map(fn ($r) => [
            'id'         => (int) $r->id,
            'projek'     => $r->project,
            'kategori'   => $r->category,
            'ringkasan'  => $r->summary,
            'priority'   => self::PRIORITY_LABEL[(int) $r->priority] ?? (string) $r->priority,
            'status'     => self::STATUS_LABEL[(int) $r->status] ?? (string) $r->status,
            'resolution' => self::RESOLUTION_LABEL[(int) $r->resolution] ?? (string) $r->resolution,
            'by'         => $r->reporter,
            'assigned'   => $r->handler,
            'tarikh'     => date('d/m/Y', (int) $r->date_submitted),
            'kemaskini'  => date('d/m/Y', (int) $r->last_updated),
            'umur'       => max(0, (int) floor(($now - (int) $r->date_submitted) / 86400)),
        ]);

        return $this->sendOk($data, [
            'page' => $page, 'limit' => $limit,
            'total' => $total, 'totalPages' => (int) ceil($total / $limit),
        ]);
    }

    public function summary(): JsonResponse
    {
        $crIds = $this->crCategoryIds();

        if (empty($crIds)) {
            return $this->sendOk([
                'totals'    => ['total' => 0, 'open' => 0, 'resolved' => 0],
                'assignees' => [],
                'modules'   => [],
            ]);
        }

        $db = $this->db();

        $total    = (int) $this->crBugs($crIds)->count();
        $open     = (int) $this->crBugs($crIds)->whereIn('status', self::OPEN_STATUSES)->count();
        $resolved = (int) $this->crBugs($crIds)->where('status', self::STATUS_RESOLVED)->count();

        $topAssignees = $db->table('mantis_bug_table as b')
            ->leftJoin('mantis_user_table as u', 'u.id', '=', 'b.handler_id')
            ->whereIn('b.category_id', $crIds)
            ->where('b.status', '!=', self::STATUS_CLOSED)
            ->whereIn('b.status', self::OPEN_STATUSES)
            ->where('b.handler_id', '>', 0)
            ->select('u.username', DB::raw('COUNT(*) as open_count'))
            ->groupBy('u.username')
            ->orderByDesc('open_count')
            ->limit(10)->get();

        $topModules = $db->table('mantis_bug_table as b')
            ->leftJoin('mantis_project_table as p', 'p.id', '=', 'b.project_id')
            ->whereIn('b.category_id', $crIds)
            ->whereIn('b.status', self::OPEN_STATUSES)
            ->select('p.name', DB::raw('COUNT(*) as open_count'))
            ->groupBy('p.name')
            ->orderByDesc('open_count')
            ->limit(10)->get();

        return $this->sendOk([
            'totals'    => compact('total', 'open', 'resolved'),
            'assignees' => $topAssignees->map(fn ($r) => [
                'name' => $r->username ?: '(none)',
                'open' => (int) $r->open_count,
            ]),
            'modules'   => $topModules->map(fn ($r) => [
                'name' => $r->name ?: '(none)',
                'open' => (int) $r->open_count,
            ]),
        ]);
    }

    public function trend(Request $request): JsonResponse
    {
        $crIds = $this->crCategoryIds();
        $days  = max(3, min(60, (int) $request->input('days', 14)));
        $db    = $this->db();
        $today = strtotime('today 00:00:00');

        if (empty($crIds)) {
            return $this->sendOk([]);
        }

        $bakiNow = (int) $this->crBugs($crIds)->count();

        $dailyStats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $start = $today - ($i * 86400);
            $end   = $start + 86400;

            $baru = (int) $db->table('mantis_bug_table')
                ->whereIn('category_id', $crIds)
                ->whereBetween('date_submitted', [$start, $end - 1])->count();

            $resolved = (int) $db->table('mantis_bug_history_table as h')
                ->join('mantis_bug_table as b', 'b.id', '=', 'h.bug_id')
                ->whereIn('b.category_id', $crIds)
                ->where('h.field_name', 'status')
                ->where('h.new_value', (string) self::STATUS_RESOLVED)
                ->whereBetween('h.date_modified', [$start, $end - 1])->count();

            $reopened = (int) $db->table('mantis_bug_history_table as h')
                ->join('mantis_bug_table as b', 'b.id', '=', 'h.bug_id')
                ->whereIn('b.category_id', $crIds)
                ->where('h.field_name', 'status')
                ->whereIn('h.old_value', [(string) self::STATUS_RESOLVED, (string) self::STATUS_CLOSED])
                ->whereNotIn('h.new_value', [(string) self::STATUS_RESOLVED, (string) self::STATUS_CLOSED])
                ->whereBetween('h.date_modified', [$start, $end - 1])->count();

            $dailyStats[] = compact('start', 'baru', 'resolved', 'reopened');
        }

        $bakiAtEnd = $bakiNow;
        $bakiMap   = [];
        for ($i = count($dailyStats) - 1; $i >= 0; $i--) {
            $bakiMap[$i] = $bakiAtEnd;
            $d = $dailyStats[$i];
            $bakiAtEnd = $bakiAtEnd - $d['baru'] + $d['resolved'];
        }

        $rows = [];
        foreach ($dailyStats as $i => $d) {
            $baki = $bakiMap[$i];
            $kadarResolve = ($d['resolved'] + $baki) > 0
                ? round($d['resolved'] / ($d['resolved'] + $baki) * 100, 1) : 0;
            $kadarReopen = $d['resolved'] > 0
                ? round($d['reopened'] / $d['resolved'] * 100, 1) : 0;
            $prevBaki = $i > 0 ? $bakiMap[$i - 1] : $baki;
            $tren = $i === 0 ? '—'
                : ($baki > $prevBaki ? '↑ Perhatian' : ($baki < $prevBaki ? '↓' : '—'));

            $rows[] = [
                'tarikh'       => date('d/m/Y', $d['start']),
                'baru'         => $d['baru'],
                'resolved'     => $d['resolved'],
                'reopened'     => $d['reopened'],
                'baki'         => $baki,
                'kadarResolve' => $kadarResolve,
                'kadarReopen'  => $kadarReopen,
                'tren'         => $tren,
                'catatan'      => $i === 0 ? 'Baseline' : '',
            ];
        }

        return $this->sendOk($rows);
    }
}
