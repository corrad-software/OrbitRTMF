<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreRtmfSubModuleRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $moduleId = $this->route('rtmf_module');

        return [
            'code' => [
                'required', 'string', 'max:32',
                Rule::unique('rtmf_sub_modules', 'code')->where('module_id', $moduleId),
            ],
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:rtmf_sub_modules,id',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ];
    }
}
