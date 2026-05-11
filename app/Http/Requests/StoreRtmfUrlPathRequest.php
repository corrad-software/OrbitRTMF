<?php

namespace App\Http\Requests;

class StoreRtmfUrlPathRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vue_path' => 'nullable|string|max:512|unique:rtmf_url_paths,vue_path',
            'live_url' => 'nullable|string|max:1024|url',
            'description' => 'nullable|string',
        ];
    }
}
