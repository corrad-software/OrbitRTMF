<?php

namespace App\Http\Requests;

class StoreRtmfActorRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'  => 'nullable|integer|exists:rtmf_projects,id',
            'name'        => 'required|string|max:255|unique:rtmf_actors,name',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ];
    }
}
