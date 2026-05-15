<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRtmfScenarioStepRequest;
use App\Http\Requests\UpdateRtmfScenarioStepRequest;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfScenario;
use App\Models\RtmfScenarioStep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtmfScenarioStepController extends Controller
{
    use ApiResponse;

    private array $with = ['page:id,spec_id,title', 'actors:id,name', 'links.toStep.page:id,spec_id,title'];

    public function index(int $scenarioId): JsonResponse
    {
        if (! RtmfScenario::find($scenarioId)) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario not found');
        }

        $steps = RtmfScenarioStep::where('rtmf_scenario_id', $scenarioId)
            ->with($this->with)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->sendOk($steps);
    }

    public function store(StoreRtmfScenarioStepRequest $request, int $scenarioId): JsonResponse
    {
        if (! RtmfScenario::find($scenarioId)) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario not found');
        }

        $data = $request->validated();
        $actorIds = $data['actor_ids'] ?? [];
        unset($data['actor_ids']);

        $maxSort = RtmfScenarioStep::where('rtmf_scenario_id', $scenarioId)->max('sort_order') ?? -1;
        $data['sort_order'] = $data['sort_order'] ?? ($maxSort + 1);

        $step = RtmfScenarioStep::create(['rtmf_scenario_id' => $scenarioId] + $data);
        $step->actors()->sync($actorIds);
        $step->load($this->with);

        return $this->sendOk($step);
    }

    public function update(UpdateRtmfScenarioStepRequest $request, int $scenarioId, int $stepId): JsonResponse
    {
        $step = RtmfScenarioStep::where('rtmf_scenario_id', $scenarioId)->find($stepId);
        if (! $step) {
            return $this->sendError(404, 'NOT_FOUND', 'Step not found');
        }

        $data = $request->validated();
        $hasActorIds = array_key_exists('actor_ids', $data);
        $actorIds = $data['actor_ids'] ?? [];
        unset($data['actor_ids']);

        $step->update($data);
        if ($hasActorIds) {
            $step->actors()->sync($actorIds);
        }
        $step->load($this->with);

        return $this->sendOk($step);
    }

    public function destroy(int $scenarioId, int $stepId): JsonResponse
    {
        $step = RtmfScenarioStep::where('rtmf_scenario_id', $scenarioId)->find($stepId);
        if (! $step) {
            return $this->sendError(404, 'NOT_FOUND', 'Step not found');
        }

        $step->delete();

        return $this->sendOk(['success' => true]);
    }

    public function reorder(Request $request, int $scenarioId): JsonResponse
    {
        if (! RtmfScenario::find($scenarioId)) {
            return $this->sendError(404, 'NOT_FOUND', 'Scenario not found');
        }

        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        foreach ($ids as $index => $id) {
            RtmfScenarioStep::where('rtmf_scenario_id', $scenarioId)
                ->where('id', $id)
                ->update(['sort_order' => ($index + 1) * 10]);
        }

        return $this->sendOk(['success' => true]);
    }
}
