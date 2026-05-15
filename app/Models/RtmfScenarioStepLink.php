<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtmfScenarioStepLink extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'from_step_id',
        'to_step_id',
        'condition',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function fromStep(): BelongsTo
    {
        return $this->belongsTo(RtmfScenarioStep::class, 'from_step_id');
    }

    public function toStep(): BelongsTo
    {
        return $this->belongsTo(RtmfScenarioStep::class, 'to_step_id');
    }
}
