<?php

namespace App\Http\Requests;

use App\Models\RtmfProject;

class UpdateRtmfProjectMemberRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_role' => 'required|string|in:' . implode(',', RtmfProject::MEMBER_ROLES),
        ];
    }
}
