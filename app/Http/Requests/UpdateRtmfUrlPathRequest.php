<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateRtmfUrlPathRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('rtmf_url_path');

        return [
            'vue_path' => ['nullable', 'string', 'max:512', Rule::unique('rtmf_url_paths', 'vue_path')->ignore($id)],
            'live_url' => 'nullable|string|max:1024|url',
            'description' => 'nullable|string',
        ];
    }
}
