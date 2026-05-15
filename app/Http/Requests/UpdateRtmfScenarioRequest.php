<?php

namespace App\Http\Requests;

class UpdateRtmfScenarioRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'is_done'           => 'nullable|boolean',
            'assignees'         => 'nullable|array',
            'assignees.*.id'    => 'required',
            'assignees.*.name'  => 'required|string',
        ];
    }
}
