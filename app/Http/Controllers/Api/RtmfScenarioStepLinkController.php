<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRtmfScenarioStepLinkRequest;
use App\Http\Requests\UpdateRtmfScenarioStepLinkRequest;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfScenarioStep;
use App\Models\RtmfScenarioStepLink;
use Illuminate\Http\JsonResponse;

class RtmfScenarioStepLinkController extends Controller
{
    use ApiResponse;

    public function store(StoreRtmfScenarioStepLinkRequest $request, int $scenarioId, int $stepId): JsonResponse
    {
        $step = RtmfScenarioStep::where('rtmf_scenario_id', $scenarioId)->find($stepId);
        if (! $step) {
            return $this->sendError(404, 'NOT_FOUND', 'Step not found');
        }

        $data = $request->validated();
        $maxSort = RtmfScenarioStepLink::where('from_step_id', $stepId)->max('sort_order') ?? -1;
        $data['sort_order'] = $data['sort_order'] ?? ($maxSort + 1);

        $link = RtmfScenarioStepLink::create(['from_step_id' => $stepId] + $data);
        $link->load('toStep.page:id,spec_id,title');

        return $this->sendCreated($link);
    }

    public function update(UpdateRtmfScenarioStepLinkRequest $request, int $scenarioId, int $stepId, int $linkId): JsonResponse
    {
        $step = RtmfScenarioStep::where('rtmf_scenario_id', $scenarioId)->find($stepId);
        if (! $step) {
            return $this->sendError(404, 'NOT_FOUND', 'Step not found');
        }

        $link = RtmfScenarioStepLink::where('from_step_id', $stepId)->find($linkId);
        if (! $link) {
            return $this->sendError(404, 'NOT_FOUND', 'Link not found');
        }

        $link->update($request->validated());
        $link->load('toStep.page:id,spec_id,title');

        return $this->sendOk($link);
    }

    public function destroy(int $scenarioId, int $stepId, int $linkId): JsonResponse
    {
        $step = RtmfScenarioStep::where('rtmf_scenario_id', $scenarioId)->find($stepId);
        if (! $step) {
            return $this->sendError(404, 'NOT_FOUND', 'Step not found');
        }

        RtmfScenarioStepLink::where('from_step_id', $stepId)->where('id', $linkId)->delete();

        return $this->sendOk(['success' => true]);
    }
}
