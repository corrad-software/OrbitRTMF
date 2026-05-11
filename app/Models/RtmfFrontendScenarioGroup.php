<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RtmfFrontendScenarioGroup extends Model
{
    use Auditable;
    protected $fillable = [
        'rtmf_frontend_id',
        'title',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function frontend(): BelongsTo
    {
        return $this->belongsTo(RtmfFrontend::class, 'rtmf_frontend_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(RtmfFrontendScenarioRow::class, 'rtmf_frontend_scenario_group_id')
                    ->orderBy('sort_order')
                    ->orderBy('id');
    }
}
