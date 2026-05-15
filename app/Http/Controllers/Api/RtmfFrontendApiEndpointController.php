<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendApiEndpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtmfFrontendApiEndpointController extends Controller
{
    use ApiResponse;

    public function index(int $frontendId): JsonResponse
    {
        if (! RtmfFrontend::find($frontendId)) {
            return $this->sendError(404, 'NOT_FOUND', 'Frontend entry not found');
        }

        $endpoints = RtmfFrontendApiEndpoint::where('rtmf_frontend_id', $frontendId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->sendOk($endpoints);
    }

    public function store(Request $request, int $frontendId): JsonResponse
    {
        if (! RtmfFrontend::find($frontendId)) {
            return $this->sendError(404, 'NOT_FOUND', 'Frontend entry not found');
        }

        $data = $request->validate([
            'method'      => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'endpoint'    => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'sort_order'  => 'nullable|integer',
        ]);

        $data['method']   = $data['method'] ?? 'GET';
        $data['endpoint'] = $data['endpoint'] ?? '';

        $maxSort = RtmfFrontendApiEndpoint::where('rtmf_frontend_id', $frontendId)->max('sort_order') ?? -1;
        $data['sort_order'] = $data['sort_order'] ?? ($maxSort + 1);

        $endpoint = RtmfFrontendApiEndpoint::create(['rtmf_frontend_id' => $frontendId] + $data);

        return $this->sendOk($endpoint);
    }

    public function update(Request $request, int $frontendId, int $id): JsonResponse
    {
        $endpoint = RtmfFrontendApiEndpoint::where('rtmf_frontend_id', $frontendId)->find($id);
        if (! $endpoint) {
            return $this->sendError(404, 'NOT_FOUND', 'Endpoint not found');
        }

        $data = $request->validate([
            'method'      => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'endpoint'    => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'sort_order'  => 'nullable|integer',
        ]);

        $endpoint->update($data);

        return $this->sendOk($endpoint);
    }

    public function destroy(int $frontendId, int $id): JsonResponse
    {
        $endpoint = RtmfFrontendApiEndpoint::where('rtmf_frontend_id', $frontendId)->find($id);
        if (! $endpoint) {
            return $this->sendError(404, 'NOT_FOUND', 'Endpoint not found');
        }

        $endpoint->delete();

        return $this->sendOk(['success' => true]);
    }
}
