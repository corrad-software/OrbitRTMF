<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtmfFrontendFeedback extends Model
{
    use Auditable;

    protected $table = 'rtmf_frontend_feedbacks';

    protected $fillable = [
        'rtmf_frontend_id',
        'role',
        'status',
        'comment',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function frontend(): BelongsTo
    {
        return $this->belongsTo(RtmfFrontend::class, 'rtmf_frontend_id');
    }
}
