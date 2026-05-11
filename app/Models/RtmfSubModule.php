<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RtmfSubModule extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = ['module_id', 'parent_id', 'code', 'name', 'description', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(RtmfModule::class, 'module_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RtmfSubModule::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RtmfSubModule::class, 'parent_id');
    }
}
