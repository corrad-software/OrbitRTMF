<?php

namespace App\Http\Requests;

class StoreRtmfScenarioStepRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rtmf_frontend_id' => 'nullable|integer|exists:rtmf_frontends,id',
            'actor_ids'        => 'nullable|array',
            'actor_ids.*'      => 'integer|exists:rtmf_actors,id',
            'note'             => 'nullable|string|max:255',
            'sort_order'       => 'nullable|integer',
        ];
    }
}
