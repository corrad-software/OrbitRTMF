<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRtmfModuleRequest;
use App\Http\Requests\UpdateRtmfModuleRequest;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\ChecksRtmfProjectRole;
use App\Models\RtmfModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtmfModuleController extends Controller
{
    use ApiResponse, ChecksRtmfProjectRole;

    public function index(Request $request): JsonResponse
    {
        $q = $request->input('q');
        $projectId = $request->integer('project_id') ?: null;

        $query = RtmfModule::query()->withCount(['frontends', 'subModules']);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($q) {
            $query->where(function ($b) use ($q) {
                $b->where('code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%");
            });
        }
        $rows = $query->orderBy('sort_order')->orderBy('code')->get();

        return $this->sendOk($rows, ['total' => $rows->count()]);
    }

    public function store(StoreRtmfModuleRequest $request): JsonResponse
    {
        $projectId = $request->integer('project_id') ?: null;
        if ($deny = $this->denyIfCannotEdit($request, $projectId)) return $deny;

        $row = RtmfModule::create($request->validated());

        return $this->sendOk($row);
    }

    public function show(int $id): JsonResponse
    {
        $row = RtmfModule::withCount('frontends')->with('subModules')->find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Module not found');
        }

        return $this->sendOk($row);
    }

    public function update(UpdateRtmfModuleRequest $request, int $id): JsonResponse
    {
        $row = RtmfModule::find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Module not found');
        }
        if ($deny = $this->denyIfCannotEdit($request, $row->project_id)) return $deny;

        $row->update($request->validated());

        return $this->sendOk($row);
    }

    public function destroy(int $id): JsonResponse
    {
        $row = RtmfModule::withCount('frontends')->find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Module not found');
        }
        if ($deny = $this->denyIfCannotEdit(request(), $row->project_id)) return $deny;

        if ($row->frontends_count > 0) {
            return $this->sendError(422, 'IN_USE', "Module is referenced by {$row->frontends_count} frontend entries.");
        }
        $row->delete();

        return $this->sendOk(['success' => true]);
    }
}
