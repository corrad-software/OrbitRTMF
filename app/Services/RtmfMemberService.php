<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RtmfMemberService
{
    public function findOrProvisionByExternalId(string $externalId): ?User
    {
        $ext = DB::connection('mysql_external')
            ->table('User')
            ->where('id', $externalId)
            ->first();

        if (! $ext) {
            return null;
        }

        return User::firstOrCreate(
            ['email' => $ext->email],
            [
                'name'      => $ext->name,
                'role'      => $ext->role ?? 'tester',
                'password'  => Hash::make(Str::random(32)),
                'is_active' => true,
            ]
        );
    }
}
