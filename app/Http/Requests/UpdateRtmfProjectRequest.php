<?php

namespace App\Http\Requests;

class UpdateRtmfProjectRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('rtmf_project');

        return [
            'code'        => "required|string|max:32|unique:rtmf_projects,code,{$id}",
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ];
    }
}
