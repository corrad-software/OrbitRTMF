<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DefectController extends Controller
{
    use ApiResponse;

    private const STATUS_FEEDBACK     = 20;
    private const STATUS_RESOLVED     = 80;
    private const STATUS_CLOSED       = 90;

    // All statuses shown in QA report (exclude closed=90 only)
    private const QA_STATUSES = [10, 20, 30, 40, 50, 80];
    // "Open" = not yet resolved/closed
    private const OPEN_STATUSES = [10, 20, 30, 40, 50];

    private const SEVERITY_LABEL = [
        10 => 'feature', 20 => 'trivial', 30 => 'text', 40 => 'tweak',
        50 => 'minor',   60 => 'major',   70 => 'crash', 80 => 'block',
    ];
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

    // Exact category IDs matching QA filter (Defect / SFT / UI-UX / With IT for Review)
    private const QA_CATEGORY_IDS = [
        2,  // Pre-UAT1 - Defect
        4,  // Pre-UAT2 - Defect
        6,  // Pre-UAT3 - Defect
        57, // SIT1 - Defect
        8,  // System Functional Testing (Internal) for Pre-UAT1
        18, // System Functional Testing (Internal) for Pre-UAT2
        43, // System Functional Testing (Internal) for Pre-UAT3
        53, // System Functional Testing (Internal) for SIT
        19, // System Functional Testing (Internal) for UAT1
        20, // System Functional Testing (Internal) for UAT2
        44, // System Functional Testing (Internal) for UAT3
        3,  // UAT1 - Defect
        5,  // UAT2 - Defect
        7,  // UAT3 - Defect
        13, // UI/UX Testing (Internal) for Pre-UAT1
        21, // UI/UX Testing (Internal) for Pre-UAT2
        49, // UI/UX Testing (Internal) for Pre-UAT3
        55, // UI/UX Testing (Internal) for SIT
        22, // UI/UX Testing (Internal) for UAT1
        23, // UI/UX Testing (Internal) for UAT2
        50, // UI/UX Testing (Internal) for UAT3
        59, // With IT for Review 1 - Defect
        60, // With IT for Review 2
    ];

    private function db()
    {
        return DB::connection('mantis');
    }

    private function mapSeverity(int $sev): string
    {
        return match (true) {
            $sev >= 70 => 'Kritikal',
            $sev === 60 => 'High',
            $sev === 50 => 'Medium',
            default => 'Low',
        };
    }

    private function startOfTodayTs(): int
    {
        return strtotime('today 00:00:00');
    }

    /** Base bug query with QA filter: correct categories + status != closed */
    private function qaBugs()
    {
        return $this->db()->table('mantis_bug_table')
            ->whereIn('category_id', self::QA_CATEGORY_IDS)
            ->where('status', '!=', self::STATUS_CLOSED);
    }

    public function dashboard(): JsonResponse
    {
        $db = $this->db();
        $todayStart = $this->startOfTodayTs();

        $newToday = (int) $this->qaBugs()
            ->where('date_submitted', '>=', $todayStart)->count();

        $resolvedToday = (int) $db->table('mantis_bug_history_table as h')
            ->join('mantis_bug_table as b', 'b.id', '=', 'h.bug_id')
            ->whereIn('b.category_id', self::QA_CATEGORY_IDS)
            ->where('h.field_name', 'status')
            ->where('h.new_value', (string) self::STATUS_RESOLVED)
            ->where('h.date_modified', '>=', $todayStart)->count();

        $reopenedToday = (int) $db->table('mantis_bug_history_table as h')
            ->join('mantis_bug_table as b', 'b.id', '=', 'h.bug_id')
            ->whereIn('b.category_id', self::QA_CATEGORY_IDS)
            ->where('h.field_name', 'status')
            ->whereIn('h.old_value', [(string) self::STATUS_RESOLVED, (string) self::STATUS_CLOSED])
            ->whereNotIn('h.new_value', [(string) self::STATUS_RESOLVED, (string) self::STATUS_CLOSED])
            ->where('h.date_modified', '>=', $todayStart)->count();

        $kritikalOpen = (int) $this->qaBugs()
            ->whereIn('status', self::OPEN_STATUSES)
            ->where('severity', '>=', 70)->count();

        $highOpen = (int) $this->qaBugs()
            ->whereIn('status', self::OPEN_STATUSES)
            ->where('severity', 60)->count();

        $feedback = (int) $this->qaBugs()
            ->where('status', self::STATUS_FEEDBACK)->count();

        $totalActive = (int) $this->qaBugs()->count();

        $activeYesterday = $totalActive - $newToday + $resolvedToday;

        $newDefects = $db->table('mantis_bug_table as b')
            ->leftJoin('mantis_project_table as p', 'p.id', '=', 'b.project_id')
            ->leftJoin('mantis_user_table as u', 'u.id', '=', 'b.handler_id')
            ->whereIn('b.category_id', self::QA_CATEGORY_IDS)
            ->where('b.date_submitted', '>=', $todayStart)
            ->orderByDesc('b.id')->limit(20)
            ->get(['b.id', 'b.summary', 'p.name as project', 'b.severity', 'b.priority', 'u.username as assigned', 'b.date_submitted']);

        $resolvedList = $db->table('mantis_bug_history_table as h')
            ->join('mantis_bug_table as b', 'b.id', '=', 'h.bug_id')
            ->leftJoin('mantis_project_table as p', 'p.id', '=', 'b.project_id')
            ->leftJoin('mantis_user_table as u', 'u.id', '=', 'h.user_id')
            ->whereIn('b.category_id', self::QA_CATEGORY_IDS)
            ->where('h.field_name', 'status')
            ->where('h.new_value', (string) self::STATUS_RESOLVED)
            ->where('h.date_modified', '>=', $todayStart)
            ->orderByDesc('h.date_modified')->limit(20)
            ->get(['b.id', 'b.summary', 'p.name as project', 'b.severity', 'u.username as resolver', 'h.date_modified']);

        $reopenedList = $db->table('mantis_bug_history_table as h')
            ->join('mantis_bug_table as b', 'b.id', '=', 'h.bug_id')
            ->leftJoin('mantis_project_table as p', 'p.id', '=', 'b.project_id')
            ->leftJoin('mantis_user_table as u', 'u.id', '=', 'h.user_id')
            ->whereIn('b.category_id', self::QA_CATEGORY_IDS)
            ->where('h.field_name', 'status')
            ->whereIn('h.old_value', [(string) self::STATUS_RESOLVED, (string) self::STATUS_CLOSED])
            ->whereNotIn('h.new_value', [(string) self::STATUS_RESOLVED, (string) self::STATUS_CLOSED])
            ->where('h.date_modified', '>=', $todayStart)
            ->orderByDesc('h.date_modified')->limit(20)
            ->get(['b.id', 'b.summary', 'p.name as project', 'b.severity', 'u.username as user', 'h.date_modified']);

        return $this->sendOk([
            'kpi' => [
                'new_today'            => $newToday,
                'resolved_today'       => $resolvedToday,
                'reopened_today'       => $reopenedToday,
                'kritikal_open'        => $kritikalOpen,
                'high_open'            => $highOpen,
                'feedback_open'        => $feedback,
                'total_active'         => $totalActive,
                'active_yesterday'     => $activeYesterday,
                'delta_vs_yesterday'   => $totalActive - $activeYesterday,
            ],
            'new_defects' => $newDefects->map(fn ($r) => [
                'id'        => (int) $r->id,
                'ringkasan' => $r->summary,
                'modul'     => $r->project,
                'tahap'     => $this->mapSeverity((int) $r->severity),
                'priority'  => self::PRIORITY_LABEL[(int) $r->priority] ?? '—',
                'assigned'  => $r->assigned,
                'tarikh'    => date('d/m/Y', (int) $r->date_submitted),
            ]),
            'resolved_today_list' => $resolvedList->map(fn ($r) => [
                'id'        => (int) $r->id,
                'ringkasan' => $r->summary,
                'modul'     => $r->project,
                'tahap'     => $this->mapSeverity((int) $r->severity),
                'resolver'  => $r->resolver,
                'tarikh'    => date('d/m/Y', (int) $r->date_modified),
            ]),
            'reopened_today_list' => $reopenedList->map(fn ($r) => [
                'id'        => (int) $r->id,
                'ringkasan' => $r->summary,
                'modul'     => $r->project,
                'tahap'     => $this->mapSeverity((int) $r->severity),
                'user'      => $r->user,
                'tarikh'    => date('d/m/Y', (int) $r->date_modified),
            ]),
        ]);
    }

    public function log(Request $request): JsonResponse
    {
        $page    = max(1, (int) $request->input('page', 1));
        $limit   = min(200, max(1, (int) $request->input('limit', 50)));
        $q       = trim((string) $request->input('q', ''));
        $sortBy  = $request->input('sort_by', 'id');
        $sortDir = strtolower($request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Multi-select filters
        $tahapFilter  = array_intersect((array) $request->input('tahap', []),  ['Kritikal', 'High', 'Medium', 'Low']);
        $statusFilter = array_intersect((array) $request->input('status', []), array_values(self::STATUS_LABEL));

        $allowedSort = ['id', 'date_submitted', 'last_updated', 'severity', 'priority', 'status'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'id';
        }

        $base = $this->db()->table('mantis_bug_table as b')
            ->leftJoin('mantis_project_table as p', 'p.id', '=', 'b.project_id')
            ->leftJoin('mantis_category_table as c', 'c.id', '=', 'b.category_id')
            ->leftJoin('mantis_user_table as ur', 'ur.id', '=', 'b.reporter_id')
            ->leftJoin('mantis_user_table as uh', 'uh.id', '=', 'b.handler_id')
            ->whereIn('b.category_id', self::QA_CATEGORY_IDS)
            ->where('b.status', '!=', self::STATUS_CLOSED);

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('b.summary', 'like', "%{$q}%")
                  ->orWhere('p.name', 'like', "%{$q}%")
                  ->orWhere('c.name', 'like', "%{$q}%")
                  ->orWhere('b.id', $q);
            });
        }

        if (! empty($tahapFilter)) {
            $base->where(function ($w) use ($tahapFilter) {
                foreach ($tahapFilter as $t) {
                    $w->orWhere(function ($q) use ($t) {
                        if ($t === 'Kritikal') $q->where('b.severity', '>=', 70);
                        elseif ($t === 'High')   $q->where('b.severity', 60);
                        elseif ($t === 'Medium') $q->where('b.severity', 50);
                        else                     $q->where('b.severity', '<', 50);
                    });
                }
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

        $total = (clone $base)->count('b.id');

        $rows = $base->orderBy('b.' . $sortBy, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get([
                'b.id', 'p.name as project', 'c.name as category', 'b.summary',
                'b.severity', 'b.priority', 'b.status', 'b.resolution',
                'ur.username as reporter', 'uh.username as handler',
                'b.date_submitted', 'b.last_updated',
            ]);

        $now = time();
        $data = $rows->map(fn ($r) => [
            'id'         => (int) $r->id,
            'projek'     => $r->project,
            'kategori'   => $r->category,
            'ringkasan'  => $r->summary,
            'severity'   => self::SEVERITY_LABEL[(int) $r->severity] ?? (string) $r->severity,
            'priority'   => self::PRIORITY_LABEL[(int) $r->priority] ?? (string) $r->priority,
            'tahap'      => $this->mapSeverity((int) $r->severity),
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
        $db = $this->db();

        $total    = (int) $this->qaBugs()->count();
        $open     = (int) $this->qaBugs()->whereIn('status', self::OPEN_STATUSES)->count();
        $resolved = (int) $this->qaBugs()->where('status', self::STATUS_RESOLVED)->count();
        $closed   = 0; // excluded by QA filter

        $bySeverity = $this->qaBugs()
            ->whereIn('status', self::OPEN_STATUSES)
            ->select('severity', DB::raw('COUNT(*) as c'))
            ->groupBy('severity')->get();

        $severityBuckets = ['Kritikal' => 0, 'High' => 0, 'Medium' => 0, 'Low' => 0];
        foreach ($bySeverity as $r) {
            $severityBuckets[$this->mapSeverity((int) $r->severity)] += (int) $r->c;
        }

        $topAssignees = $this->db()->table('mantis_bug_table as b')
            ->leftJoin('mantis_user_table as u', 'u.id', '=', 'b.handler_id')
            ->whereIn('b.category_id', self::QA_CATEGORY_IDS)
            ->where('b.status', '!=', self::STATUS_CLOSED)
            ->whereIn('b.status', self::OPEN_STATUSES)
            ->where('b.handler_id', '>', 0)
            ->select('u.username', DB::raw('COUNT(*) as open_count'))
            ->groupBy('u.username')
            ->orderByDesc('open_count')
            ->limit(10)->get();

        $topModules = $this->db()->table('mantis_bug_table as b')
            ->leftJoin('mantis_project_table as p', 'p.id', '=', 'b.project_id')
            ->whereIn('b.category_id', self::QA_CATEGORY_IDS)
            ->whereIn('b.status', self::OPEN_STATUSES)
            ->select('p.name', DB::raw('COUNT(*) as open_count'))
            ->groupBy('p.name')
            ->orderByDesc('open_count')
            ->limit(10)->get();

        return $this->sendOk([
            'totals' => compact('total', 'open', 'resolved', 'closed'),
            'severity' => [
                ['label' => 'Kritikal', 'count' => $severityBuckets['Kritikal'], 'color' => 'rose'],
                ['label' => 'High',     'count' => $severityBuckets['High'],     'color' => 'orange'],
                ['label' => 'Medium',   'count' => $severityBuckets['Medium'],   'color' => 'amber'],
                ['label' => 'Low',      'count' => $severityBuckets['Low'],      'color' => 'slate'],
            ],
            'assignees' => $topAssignees->map(fn ($r) => [
                'name'     => $r->username ?: '(none)',
                'open'     => (int) $r->open_count,
                'resolved' => 0,
            ]),
            'modules' => $topModules->map(fn ($r) => [
                'name'     => $r->name ?: '(none)',
                'open'     => (int) $r->open_count,
                'resolved' => 0,
            ]),
        ]);
    }

    public function categories(): JsonResponse
    {
        $rows = $this->db()->table('mantis_bug_table as b')
            ->leftJoin('mantis_category_table as c', 'c.id', '=', 'b.category_id')
            ->whereIn('b.category_id', self::QA_CATEGORY_IDS)
            ->where('b.status', '!=', self::STATUS_CLOSED)
            ->select(
                'c.name',
                DB::raw('COUNT(*) as jumlah'),
                DB::raw('SUM(CASE WHEN b.status IN (10,20,30,40,50) THEN 1 ELSE 0 END) as open_count'),
                DB::raw('SUM(CASE WHEN b.status = 80 THEN 1 ELSE 0 END) as resolved_count'),
                DB::raw('SUM(CASE WHEN b.resolution = 30 THEN 1 ELSE 0 END) as reopened_count'),
                DB::raw('SUM(CASE WHEN b.severity >= 60 AND b.status IN (10,20,30,40,50) THEN 1 ELSE 0 END) as kritikal_high')
            )
            ->groupBy('c.name')
            ->orderByDesc('jumlah')
            ->get();

        $data = $rows->map(function ($r) {
            $jumlah = (int) $r->jumlah;
            $open   = (int) $r->open_count;
            return [
                'label'        => $r->name ?: '(uncategorised)',
                'jumlah'       => $jumlah,
                'open'         => $open,
                'resolved'     => (int) $r->resolved_count,
                'reopened'     => (int) $r->reopened_count,
                'kritikalHigh' => (int) $r->kritikal_high,
                'pctOpen'      => $jumlah > 0 ? round($open / $jumlah * 100, 1) : 0,
            ];
        });

        return $this->sendOk($data);
    }

    public function trend(Request $request): JsonResponse
    {
        $days = max(3, min(60, (int) $request->input('days', 14)));
        $db   = $this->db();
        $today = strtotime('today 00:00:00');

        $bakiNow = (int) $this->qaBugs()->count();

        $dailyStats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $start = $today - ($i * 86400);
            $end   = $start + 86400;

            $baru = (int) $db->table('mantis_bug_table')
                ->whereIn('category_id', self::QA_CATEGORY_IDS)
                ->whereBetween('date_submitted', [$start, $end - 1])->count();

            $resolved = (int) $db->table('mantis_bug_history_table as h')
                ->join('mantis_bug_table as b', 'b.id', '=', 'h.bug_id')
                ->whereIn('b.category_id', self::QA_CATEGORY_IDS)
                ->where('h.field_name', 'status')
                ->where('h.new_value', (string) self::STATUS_RESOLVED)
                ->whereBetween('h.date_modified', [$start, $end - 1])->count();

            $reopened = (int) $db->table('mantis_bug_history_table as h')
                ->join('mantis_bug_table as b', 'b.id', '=', 'h.bug_id')
                ->whereIn('b.category_id', self::QA_CATEGORY_IDS)
                ->where('h.field_name', 'status')
                ->whereIn('h.old_value', [(string) self::STATUS_RESOLVED, (string) self::STATUS_CLOSED])
                ->whereNotIn('h.new_value', [(string) self::STATUS_RESOLVED, (string) self::STATUS_CLOSED])
                ->whereBetween('h.date_modified', [$start, $end - 1])->count();

            $dailyStats[] = compact('start', 'baru', 'resolved', 'reopened');
        }

        // Walk backwards from current baki to reconstruct historical balance
        $bakiAtEnd = $bakiNow;
        $bakiMap = [];
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
