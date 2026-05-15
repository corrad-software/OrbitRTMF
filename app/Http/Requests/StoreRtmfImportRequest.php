<?php

namespace App\Http\Requests;

class StoreRtmfImportRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Module
            'module'                              => 'required|array',
            'module.code'                         => 'required|string|max:16',
            'module.name'                         => 'required|string|max:255',
            'module.sort_order'                   => 'nullable|integer',

            // Sub-module
            'sub_module'                          => 'required|array',
            'sub_module.code'                     => 'required|string|max:32',
            'sub_module.name'                     => 'required|string|max:255',
            'sub_module.sort_order'               => 'nullable|integer',

            // Frontend entries
            'frontends'                           => 'required|array|min:1',
            'frontends.*.spec_id'                 => 'required|string|max:64',
            'frontends.*.title'                   => 'required|string|max:255',
            'frontends.*.tab_code'                => 'nullable|string|max:64',
            'frontends.*.vue_path'                => 'nullable|string|max:512',
            'frontends.*.business_requirement'    => 'nullable|string',
            'frontends.*.stakeholder_requirement' => 'nullable|string',
            'frontends.*.description'             => 'nullable|string',
            'frontends.*.actors'                  => 'nullable|array',
            'frontends.*.actors.*'                => 'string|max:255',

            // FR line items
            'frontends.*.items'                        => 'nullable|array',
            'frontends.*.items.*.id_fr'                => 'nullable|string|max:32',
            'frontends.*.items.*.type'                 => 'nullable|string|max:32',
            'frontends.*.items.*.label'                => 'nullable|string|max:255',
            'frontends.*.items.*.condition'            => 'nullable|string',
            'frontends.*.items.*.validation'           => 'nullable|string|max:255',
            'frontends.*.items.*.mandatory'            => 'nullable|boolean',
            'frontends.*.items.*.screen_name'          => 'nullable|string|max:128',
            'frontends.*.items.*.table_fieldname'      => 'nullable|string|max:255',
            'frontends.*.items.*.status'               => 'nullable|string|in:implemented,partial,missing',
            'frontends.*.items.*.sort_order'           => 'nullable|integer',

            // API endpoints
            'frontends.*.api_endpoints'                => 'nullable|array',
            'frontends.*.api_endpoints.*.method'       => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'frontends.*.api_endpoints.*.endpoint'     => 'nullable|string|max:255',
            'frontends.*.api_endpoints.*.description'  => 'nullable|string|max:255',
        ];
    }
}
