<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtmfFrontendApiEndpoint extends Model
{
    use Auditable;

    protected $fillable = [
        'rtmf_frontend_id',
        'method',
        'endpoint',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function frontend(): BelongsTo
    {
        return $this->belongsTo(RtmfFrontend::class, 'rtmf_frontend_id');
    }
}
