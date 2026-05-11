<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtmfFrontendAttachment extends Model
{
    use Auditable;
    protected $fillable = [
        'rtmf_frontend_id',
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

    public function frontend(): BelongsTo
    {
        return $this->belongsTo(RtmfFrontend::class, 'rtmf_frontend_id');
    }
}
