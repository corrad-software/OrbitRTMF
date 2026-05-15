<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRtmfScenarioRequest;
use App\Http\Requests\UpdateRtmfScenarioRequest;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\ChecksRtmfProjectRole;
use App\Models\RtmfScenario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtmfScenarioController extends Controller
{
    use ApiResponse, ChecksRtmfProjectRole;

    public function index(Request $request): JsonResponse
    {
        $page    = (int) $request->input('page', 1);
        $limit   = (int) $request->input('limit', 20);
        $q       = $request->input('q');
        $sortBy  = $request->input('sort_by', 'sort_order');
        $sortDir = $request->input('sort_dir', 'asc');

        $isDone    = $request->input('is_done');
        $projectId = $request->integer('project_id') ?: null;

        $query = RtmfScenario::query();

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($q) {
            $query->where('title', 'ilike', "%{$q}%");
        }

        if ($isDone !== null && $isDone !== '') {
            $query->where('is_done', (bool) $isDone);
        }

        $total = $query->count();
        $rows  = $query->orderBy($sortBy, $sortDir)
            ->orderBy('id')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->withCount('steps')
            ->get();

        return $this->sendOk($rows, [
            'page'       => $page,
            'limit'      => $limit,
            'total'      => $total,
            'totalPages' => (int) ceil($total / $limit),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $scenario = RtmfScenario::with(['steps.page:id,spec_id,title', 'steps.actors:id,name', 'steps.links.toStep.page:id,spec_id,title'])->find($id);
        if (! $scenario) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario not found');
        }

        return $this->sendOk($scenario);
    }

    public function store(StoreRtmfScenarioRequest $request): JsonResponse
    {
        $projectId = $request->integer('project_id') ?: null;
        if ($deny = $this->denyIfCannotEdit($request, $projectId)) return $deny;

        $maxSort = RtmfScenario::max('sort_order') ?? -1;
        $scenario = RtmfScenario::create($request->validated() + ['sort_order' => $maxSort + 1]);

        return $this->sendCreated($scenario);
    }

    public function update(UpdateRtmfScenarioRequest $request, int $id): JsonResponse
    {
        $scenario = RtmfScenario::find($id);
        if (! $scenario) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario not found');
        }
        if ($deny = $this->denyIfCannotEdit($request, $scenario->project_id)) return $deny;

        $scenario->update($request->validated());

        return $this->sendOk($scenario);
    }

    public function destroy(int $id): JsonResponse
    {
        $scenario = RtmfScenario::find($id);
        if (! $scenario) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario not found');
        }
        if ($deny = $this->denyIfCannotEdit(request(), $scenario->project_id)) return $deny;

        $scenario->delete();

        return $this->sendOk(['success' => true]);
    }
}
