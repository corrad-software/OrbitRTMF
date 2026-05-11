<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RtmfActor extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = ['name', 'description', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function frontends(): BelongsToMany
    {
        return $this->belongsToMany(RtmfFrontend::class, 'rtmf_frontend_actor', 'rtmf_actor_id', 'rtmf_frontend_id');
    }
}
