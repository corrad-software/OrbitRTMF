<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRtmfUrlPathRequest;
use App\Http\Requests\UpdateRtmfUrlPathRequest;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfUrlPath;
use App\Services\VueSnapshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtmfUrlPathController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $q = $request->input('q');
        $query = RtmfUrlPath::query()
            ->select(['id', 'vue_path', 'live_url', 'description', 'line_count', 'file_size_kb', 'snapshot_status', 'snapshot_captured_at', 'created_at', 'updated_at'])
            ->withCount('frontends');
        if ($q) {
            $query->where(function ($b) use ($q) {
                $b->where('vue_path', 'like', "%{$q}%")->orWhere('live_url', 'like', "%{$q}%");
            });
        }
        $rows = $query->orderBy('vue_path')->get();

        return $this->sendOk($rows, ['total' => $rows->count()]);
    }

    public function store(StoreRtmfUrlPathRequest $request): JsonResponse
    {
        $row = RtmfUrlPath::create($request->validated());

        return $this->sendOk($row);
    }

    public function show(int $id): JsonResponse
    {
        $row = RtmfUrlPath::withCount('frontends')->find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'URL Path not found');
        }

        return $this->sendOk($row);
    }

    public function update(UpdateRtmfUrlPathRequest $request, int $id): JsonResponse
    {
        $row = RtmfUrlPath::find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'URL Path not found');
        }
        $row->update($request->validated());

        return $this->sendOk($row);
    }

    public function destroy(int $id): JsonResponse
    {
        $row = RtmfUrlPath::withCount('frontends')->find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'URL Path not found');
        }
        if ($row->frontends_count > 0) {
            return $this->sendError(422, 'IN_USE', "URL Path is referenced by {$row->frontends_count} frontend entries.");
        }
        $row->delete();

        return $this->sendOk(['success' => true]);
    }

    public function captureSnapshot(int $id, VueSnapshotService $service): JsonResponse
    {
        $row = RtmfUrlPath::find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'URL Path not found');
        }

        $result = $service->capture($row->vue_path);

        // Also re-extract metadata (line_count, file_size_kb, shared_components) on capture.
        $meta = $service->extractMetadata($row->vue_path);

        $row->update(array_merge([
            'snapshot_html' => $result['html'],
            'snapshot_status' => $result['status'],
            'snapshot_captured_at' => now(),
        ], $meta));

        return $this->sendOk($row);
    }
}
