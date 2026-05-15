<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtmfFrontendFeedbackController extends Controller
{
    use ApiResponse;

    public function index(int $frontendId): JsonResponse
    {
        $frontend = RtmfFrontend::find($frontendId);
        if (! $frontend) {
            return $this->sendError(404, 'NOT_FOUND', 'Frontend not found');
        }

        $feedbacks = RtmfFrontendFeedback::where('rtmf_frontend_id', $frontendId)
            ->orderByRaw("CASE role WHEN 'business_analyst' THEN 0 WHEN 'qa' THEN 1 WHEN 'technical' THEN 2 WHEN 'developer' THEN 3 ELSE 4 END")
            ->get();

        return $this->sendOk($feedbacks);
    }

    public function upsert(Request $request, int $frontendId, string $role): JsonResponse
    {
        $frontend = RtmfFrontend::with('module')->find($frontendId);
        if (! $frontend) {
            return $this->sendError(404, 'NOT_FOUND', 'Frontend not found');
        }

        if (! in_array($role, ['business_analyst', 'qa', 'technical', 'developer'])) {
            return $this->sendError(422, 'VALIDATION_ERROR', 'Invalid role');
        }

        // Enforce that users may only update their own role's feedback.
        $projectId   = $frontend->module?->project_id;
        $projectRole = $projectId ? $request->user()->rtmfProjectRole($projectId) : null;
        if ($projectRole !== 'admin' && $projectRole !== $role) {
            return $this->sendError(403, 'FORBIDDEN', 'You may only update feedback for your own role');
        }

        $data = $request->validate([
            'status'  => 'nullable|in:open,reviewed,approved',
            'comment' => 'nullable|string',
        ]);

        $feedback = RtmfFrontendFeedback::updateOrCreate(
            ['rtmf_frontend_id' => $frontendId, 'role' => $role],
            $data
        );

        return $this->sendOk($feedback);
    }
}
