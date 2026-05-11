<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateRtmfModuleRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('rtmf_module');

        return [
            'code' => ['sometimes', 'required', 'string', 'max:16', Rule::unique('rtmf_modules', 'code')->ignore($id)],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ];
    }
}
