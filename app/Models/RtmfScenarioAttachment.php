<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtmfScenarioAttachment extends Model
{
    use Auditable;

    protected $fillable = [
        'rtmf_scenario_id',
        'label',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'path',
        'url',
    ];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(RtmfScenario::class, 'rtmf_scenario_id');
    }
}
