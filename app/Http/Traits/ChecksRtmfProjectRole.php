<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ChecksRtmfProjectRole
{
    /**
     * Returns a 403 JsonResponse if the authenticated user cannot edit
     * the given project, or null if access is allowed.
     */
    protected function denyIfCannotEdit(Request $request, ?int $projectId): ?JsonResponse
    {
        $user        = $request->user();
        $isSystemAdmin = strtolower($user->role ?? '') === 'admin';

        if ($projectId === null) {
            // Without a project context only system admins may write
            return $isSystemAdmin
                ? null
                : $this->sendError(403, 'FORBIDDEN', 'A project context is required.');
        }

        if (! $user->canEditRtmfProject($projectId)) {
            return $this->sendError(403, 'FORBIDDEN', 'You do not have edit access to this project.');
        }

        return null;
    }
}
