<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendScenarioGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtmfFrontendScenarioGroupController extends Controller
{
    use ApiResponse;

    public function index(int $frontendId): JsonResponse
    {
        if (! RtmfFrontend::find($frontendId)) {
            return $this->sendError(404, 'NOT_FOUND', 'Frontend entry not found');
        }

        $groups = RtmfFrontendScenarioGroup::where('rtmf_frontend_id', $frontendId)
            ->with(['rows' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->sendOk($groups);
    }

    public function store(Request $request, int $frontendId): JsonResponse
    {
        if (! RtmfFrontend::find($frontendId)) {
            return $this->sendError(404, 'NOT_FOUND', 'Frontend entry not found');
        }

        $data = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ]);

        $maxSort = RtmfFrontendScenarioGroup::where('rtmf_frontend_id', $frontendId)->max('sort_order') ?? -1;
        $data['sort_order'] = $data['sort_order'] ?? ($maxSort + 1);

        $group = RtmfFrontendScenarioGroup::create(['rtmf_frontend_id' => $frontendId] + $data);
        $group->load('rows');

        return $this->sendOk($group);
    }

    public function update(Request $request, int $frontendId, int $groupId): JsonResponse
    {
        $group = RtmfFrontendScenarioGroup::where('rtmf_frontend_id', $frontendId)->find($groupId);
        if (! $group) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario group not found');
        }

        $data = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ]);

        $group->update($data);

        return $this->sendOk($group);
    }

    public function destroy(int $frontendId, int $groupId): JsonResponse
    {
        $group = RtmfFrontendScenarioGroup::where('rtmf_frontend_id', $frontendId)->find($groupId);
        if (! $group) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario group not found');
        }

        $group->delete();

        return $this->sendOk(['success' => true]);
    }
}
