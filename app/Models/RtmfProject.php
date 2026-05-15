<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RtmfProject extends Model
{
    use HasFactory, Auditable;

    public const MEMBER_ROLES = ['admin', 'business_analyst', 'qa', 'technical', 'developer', 'viewer'];

    protected $fillable = ['code', 'name', 'description', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'rtmf_project_users', 'project_id', 'user_id')->withTimestamps();
    }

    public function modules(): HasMany
    {
        return $this->hasMany(RtmfModule::class, 'project_id');
    }

    public function actors(): HasMany
    {
        return $this->hasMany(RtmfActor::class, 'project_id');
    }

    public function scenarios(): HasMany
    {
        return $this->hasMany(RtmfScenario::class, 'project_id');
    }
}
