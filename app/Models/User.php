<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo_url',
        'role',
        'role_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the role model that the user belongs to (for RBAC permission checks).
     */
    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Check if the user's role has a given permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Admin role always has full access regardless of role_id assignment
        if (strtolower($this->role ?? '') === 'admin') {
            return true;
        }

        $roleModel = $this->roleModel;

        return $roleModel
            && is_array($roleModel->permissions)
            && in_array($permission, $roleModel->permissions);
    }

    /**
     * Return this user's role within a specific RTMF project.
     * System admins always get 'admin'. Returns null if not a member.
     */
    public function rtmfProjectRole(int $projectId): ?string
    {
        if (strtolower($this->role ?? '') === 'admin') {
            return 'admin';
        }

        return \Illuminate\Support\Facades\DB::table('rtmf_project_users')
            ->where('project_id', $projectId)
            ->where('user_id', $this->id)
            ->value('role');
    }

    /**
     * Returns true if the user can edit content in the given project.
     */
    public function canEditRtmfProject(int $projectId): bool
    {
        return in_array($this->rtmfProjectRole($projectId), ['admin', 'business_analyst'], true);
    }
}
