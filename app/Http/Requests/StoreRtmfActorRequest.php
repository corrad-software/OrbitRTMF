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
            'name' => 'required|string|max:255|unique:rtmf_actors,name',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ];
    }
}
