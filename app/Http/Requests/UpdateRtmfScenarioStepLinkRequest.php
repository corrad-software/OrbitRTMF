<?php

namespace App\Http\Requests;

class UpdateRtmfScenarioStepLinkRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_step_id' => 'nullable|integer|exists:rtmf_scenario_steps,id',
            'condition'  => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ];
    }
}
