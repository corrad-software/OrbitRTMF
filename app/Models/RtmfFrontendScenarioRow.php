<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtmfFrontendScenarioRow extends Model
{
    use Auditable;
    protected $fillable = [
        'rtmf_frontend_scenario_group_id',
        'step',
        'fasa',
        'role',
        'aktiviti',
        'sort_order',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(RtmfFrontendScenarioGroup::class, 'rtmf_frontend_scenario_group_id');
    }
}
