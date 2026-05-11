<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateRtmfSubModuleRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $moduleId = $this->route('rtmf_module');
        $id = $this->route('sub_module');

        return [
            'code' => [
                'sometimes', 'required', 'string', 'max:32',
                Rule::unique('rtmf_sub_modules', 'code')->where('module_id', $moduleId)->ignore($id),
            ],
            'name' => 'sometimes|required|string|max:255',
            'parent_id' => 'nullable|integer|exists:rtmf_sub_modules,id',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ];
    }
}
