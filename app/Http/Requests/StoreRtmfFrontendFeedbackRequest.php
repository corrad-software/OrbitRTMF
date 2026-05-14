<?php

namespace App\Http\Requests;

class StoreRtmfFrontendFeedbackRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role'       => 'required|in:business_analyst,qa,technical',
            'is_checked' => 'nullable|boolean',
            'comment'    => 'nullable|string',
        ];
    }
}
