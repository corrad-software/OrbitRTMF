<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateRtmfActorRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('rtmf_actor');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('rtmf_actors', 'name')->ignore($id)],
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ];
    }
}
