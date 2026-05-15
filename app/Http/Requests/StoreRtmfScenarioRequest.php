<?php

namespace App\Http\Requests;

class StoreRtmfScenarioRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'        => 'nullable|integer|exists:rtmf_projects,id',
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'is_done'           => 'nullable|boolean',
            'assignees'         => 'nullable|array',
            'assignees.*.id'    => 'required',
            'assignees.*.name'  => 'required|string',
        ];
    }
}
