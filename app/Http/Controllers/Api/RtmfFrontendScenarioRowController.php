<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfFrontendScenarioGroup;
use App\Models\RtmfFrontendScenarioRow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtmfFrontendScenarioRowController extends Controller
{
    use ApiResponse;

    public function store(Request $request, int $frontendId, int $groupId): JsonResponse
    {
        $group = RtmfFrontendScenarioGroup::where('rtmf_frontend_id', $frontendId)->find($groupId);
        if (! $group) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario group not found');
        }

        $data = $request->validate([
            'step'       => 'nullable|string|max:32',
            'fasa'       => 'nullable|string|max:128',
            'role'       => 'nullable|string|max:128',
            'aktiviti'   => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $maxSort = RtmfFrontendScenarioRow::where('rtmf_frontend_scenario_group_id', $groupId)->max('sort_order') ?? -1;
        $data['sort_order'] = $data['sort_order'] ?? ($maxSort + 1);

        $row = RtmfFrontendScenarioRow::create(['rtmf_frontend_scenario_group_id' => $groupId] + $data);

        return $this->sendOk($row);
    }

    public function update(Request $request, int $frontendId, int $groupId, int $rowId): JsonResponse
    {
        $group = RtmfFrontendScenarioGroup::where('rtmf_frontend_id', $frontendId)->find($groupId);
        if (! $group) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario group not found');
        }

        $row = RtmfFrontendScenarioRow::where('rtmf_frontend_scenario_group_id', $groupId)->find($rowId);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario row not found');
        }

        $data = $request->validate([
            'step'       => 'nullable|string|max:32',
            'fasa'       => 'nullable|string|max:128',
            'role'       => 'nullable|string|max:128',
            'aktiviti'   => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $row->update($data);

        return $this->sendOk($row);
    }

    public function destroy(int $frontendId, int $groupId, int $rowId): JsonResponse
    {
        $group = RtmfFrontendScenarioGroup::where('rtmf_frontend_id', $frontendId)->find($groupId);
        if (! $group) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario group not found');
        }

        $row = RtmfFrontendScenarioRow::where('rtmf_frontend_scenario_group_id', $groupId)->find($rowId);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario row not found');
        }

        $row->delete();

        return $this->sendOk(['success' => true]);
    }
}
