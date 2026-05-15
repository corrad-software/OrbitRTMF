<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RtmfScenario extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'sort_order',
        'is_done',
        'assignees',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_done'    => 'boolean',
            'assignees'  => 'array',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RtmfScenarioStep::class, 'rtmf_scenario_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
