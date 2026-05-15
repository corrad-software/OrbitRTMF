<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RtmfScenarioStep extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'rtmf_scenario_id',
        'rtmf_frontend_id',
        'note',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(RtmfScenario::class, 'rtmf_scenario_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(RtmfFrontend::class, 'rtmf_frontend_id');
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(RtmfActor::class, 'rtmf_scenario_step_actor', 'rtmf_scenario_step_id', 'rtmf_actor_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(RtmfScenarioStepLink::class, 'from_step_id')->orderBy('sort_order')->orderBy('id');
    }
}
