<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RtmfUrlPath extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'vue_path',
        'live_url',
        'description',
        'line_count',
        'file_size_kb',
        'shared_components',
        'snapshot_html',
        'snapshot_captured_at',
        'snapshot_status',
    ];

    protected function casts(): array
    {
        return [
            'shared_components' => 'array',
            'line_count' => 'integer',
            'file_size_kb' => 'integer',
            'snapshot_captured_at' => 'datetime',
        ];
    }

    public function frontends(): HasMany
    {
        return $this->hasMany(RtmfFrontend::class, 'url_path_id');
    }
}
