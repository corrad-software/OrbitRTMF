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
            ->orderByRaw("CASE role WHEN 'business_analyst' THEN 0 WHEN 'qa' THEN 1 WHEN 'technical' THEN 2 ELSE 3 END")
            ->get();

        return $this->sendOk($feedbacks);
    }

    public function upsert(Request $request, int $frontendId, string $role): JsonResponse
    {
        $frontend = RtmfFrontend::find($frontendId);
        if (! $frontend) {
            return $this->sendError(404, 'NOT_FOUND', 'Frontend not found');
        }

        if (! in_array($role, ['business_analyst', 'qa', 'technical'])) {
            return $this->sendError(422, 'VALIDATION_ERROR', 'Invalid role');
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
