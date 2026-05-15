<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RtmfFrontend extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'spec_id',
        'module_id',
        'sub_module_id',
        'tab_code',
        'vue_path',
        'url_dev',
        'url_stg',
        'url_prd',
        'title',
        'business_requirement',
        'stakeholder_requirement',
        'description',
        'is_done',
        'assignees',
        'snapshot_html',
        'snapshot_status',
        'snapshot_captured_at',
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
            'assignees' => 'array',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(RtmfModule::class, 'module_id');
    }

    public function subModule(): BelongsTo
    {
        return $this->belongsTo(RtmfSubModule::class, 'sub_module_id');
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(RtmfActor::class, 'rtmf_frontend_actor', 'rtmf_frontend_id', 'rtmf_actor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RtmfFrontendItem::class, 'rtmf_frontend_id')->orderBy('sort_order')->orderBy('id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(RtmfFrontendFeedback::class, 'rtmf_frontend_id');
    }

    public function linksFrom(): BelongsToMany
    {
        return $this->belongsToMany(
            RtmfFrontend::class,
            'rtmf_frontend_links',
            'to_frontend_id',
            'from_frontend_id'
        );
    }

    public function linksTo(): BelongsToMany
    {
        return $this->belongsToMany(
            RtmfFrontend::class,
            'rtmf_frontend_links',
            'from_frontend_id',
            'to_frontend_id'
        );
    }
}
