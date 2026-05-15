<?php

namespace App\Http\Requests;

use App\Models\RtmfSubModule;
use Illuminate\Validation\Rule;

class StoreRtmfSubModuleRequest extends BaseFormRequest
{
    public const MAX_DEPTH = 8;

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
            'name'        => 'required|string|max:255',
            'parent_id'   => [
                'nullable', 'integer',
                Rule::exists('rtmf_sub_modules', 'id')->where('module_id', $moduleId),
                function ($attribute, $value, $fail) {
                    if ($value === null) return;
                    if ($this->depthOf($value) >= self::MAX_DEPTH) {
                        $fail("Maximum nesting depth of " . self::MAX_DEPTH . " tiers exceeded.");
                    }
                },
            ],
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ];
    }

    private function depthOf(int $parentId): int
    {
        $depth  = 1;
        $cursor = RtmfSubModule::find($parentId);
        while ($cursor && $cursor->parent_id) {
            $depth++;
            $cursor = RtmfSubModule::find($cursor->parent_id);
        }
        return $depth;
    }
}
