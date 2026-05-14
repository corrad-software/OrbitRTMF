<?php

namespace App\Http\Requests;

class UpdateRtmfFrontendFeedbackRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_checked' => 'nullable|boolean',
            'comment'    => 'nullable|string',
        ];
    }
}
