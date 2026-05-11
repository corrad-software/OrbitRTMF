<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRtmfFrontendRequest;
use App\Http\Requests\UpdateRtmfFrontendRequest;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfFrontend;

use App\Services\VueSnapshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class RtmfFrontendController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 25);
        $q = $request->input('q');
        $moduleId = $request->input('module_id');
        $tabCode = $request->input('tab_code');
        $isDone = $request->input('is_done');
        $sortBy = $request->input('sort_by', 'spec_id');
        $sortDir = $request->input('sort_dir', 'asc');

        $allowedSort = ['spec_id', 'title', 'module_id', 'created_at', 'updated_at'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'spec_id';
        }

        $query = RtmfFrontend::query()->with([
            'module:id,code,name',
            'subModule:id,module_id,code,name',
            'actors:id,name',
            'linksFrom:id,spec_id,title',
            'linksTo:id,spec_id,title',
        ]);

        if ($moduleId) {
            $query->where('module_id', $moduleId);
        }

        if ($tabCode) {
            $query->where('tab_code', $tabCode);
        }

        if ($isDone !== null && $isDone !== '') {
            $query->where('is_done', filter_var($isDone, FILTER_VALIDATE_BOOLEAN));
        }

        if ($q) {
            $query->where(function ($b) use ($q) {
                $b->where('spec_id', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('business_requirement', 'like', "%{$q}%");
            });
        }

        $total = $query->count();

        $rows = $query->orderBy($sortBy, $sortDir)
            ->orderBy('id', 'asc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return $this->sendOk($rows, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $total > 0 ? (int) ceil($total / $limit) : 0,
        ]);
    }

    public function store(StoreRtmfFrontendRequest $request): JsonResponse
    {
        $data = $request->validated();
        $actorIds = $data['actor_ids'] ?? [];
        $fromIds = $data['from_ids'] ?? [];
        $toIds = $data['to_ids'] ?? [];
        unset($data['actor_ids'], $data['from_ids'], $data['to_ids']);

        $row = RtmfFrontend::create($data);
        $row->actors()->sync($actorIds);
        $row->linksFrom()->sync($fromIds);
        $row->linksTo()->sync($toIds);
        $row->load(['module', 'subModule', 'actors', 'linksFrom:id,spec_id,title', 'linksTo:id,spec_id,title']);

        return $this->sendOk($row);
    }

    public function show(int $id): JsonResponse
    {
        $row = RtmfFrontend::with(['module', 'subModule', 'actors', 'linksFrom:id,spec_id,title', 'linksTo:id,spec_id,title'])->find($id);

        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'RTMF frontend not found');
        }

        return $this->sendOk($row);
    }

    public function update(UpdateRtmfFrontendRequest $request, int $id): JsonResponse
    {
        $row = RtmfFrontend::find($id);

        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'RTMF frontend not found');
        }

        $data = $request->validated();
        $hasActors = array_key_exists('actor_ids', $data);
        $actorIds = $data['actor_ids'] ?? [];
        $hasFromIds = array_key_exists('from_ids', $data);
        $fromIds = $data['from_ids'] ?? [];
        $hasToIds = array_key_exists('to_ids', $data);
        $toIds = $data['to_ids'] ?? [];
        unset($data['actor_ids'], $data['from_ids'], $data['to_ids']);

        $row->update($data);
        if ($hasActors) $row->actors()->sync($actorIds);
        if ($hasFromIds) $row->linksFrom()->sync($fromIds);
        if ($hasToIds) $row->linksTo()->sync($toIds);
        $row->load(['module', 'subModule', 'actors', 'linksFrom:id,spec_id,title', 'linksTo:id,spec_id,title']);

        return $this->sendOk($row);
    }

    public function source(int $id): JsonResponse
    {
        $row = RtmfFrontend::find($id);

        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'RTMF frontend not found');
        }

        $vuePath = $row->vue_path;

        if (! $vuePath) {
            return $this->sendOk([
                'exists' => false,
                'path' => null,
                'content' => null,
                'line_count' => 0,
                'size_bytes' => 0,
            ]);
        }

        $abs = realpath(base_path('../nas-frontend/' . ltrim($vuePath, '/')));
        $root = realpath(base_path('../nas-frontend'));

        if (! $abs || ! $root || ! str_starts_with($abs, $root) || ! is_file($abs) || ! is_readable($abs)) {
            return $this->sendOk([
                'exists' => false,
                'path' => $vuePath,
                'content' => null,
                'line_count' => 0,
                'size_bytes' => 0,
            ]);
        }

        $content = (string) File::get($abs);

        return $this->sendOk([
            'exists' => true,
            'path' => $vuePath,
            'content' => $content,
            'line_count' => substr_count($content, "\n") + 1,
            'size_bytes' => strlen($content),
        ]);
    }

    public function getSnapshot(int $id): JsonResponse
    {
        $row = RtmfFrontend::find($id);

        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'RTMF frontend not found');
        }

        return $this->sendOk([
            'html' => $row->snapshot_html,
            'status' => $row->snapshot_status,
            'captured_at' => $row->snapshot_captured_at,
            'vue_path' => $row->vue_path,
            'url_dev' => $row->url_dev,
        ]);
    }

    public function captureSnapshot(int $id, VueSnapshotService $service): JsonResponse
    {
        $row = RtmfFrontend::find($id);

        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'RTMF frontend not found');
        }

        if (! $row->vue_path) {
            return $this->sendError(422, 'NO_VUE_PATH', 'Entry has no Vue path set.');
        }

        $result = $service->capture($row->vue_path);

        $row->update([
            'snapshot_html' => $result['html'],
            'snapshot_status' => $result['status'],
            'snapshot_captured_at' => now(),
        ]);

        return $this->sendOk([
            'html' => $row->snapshot_html,
            'status' => $row->snapshot_status,
            'captured_at' => $row->snapshot_captured_at,
            'vue_path' => $row->vue_path,
            'url_dev' => $row->url_dev,
        ]);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $moduleId = $request->input('module_id');
        $isDone   = $request->input('is_done');

        $query = RtmfFrontend::with(['module:id,code,name', 'subModule:id,code,name', 'actors:id,name'])
            ->orderBy('spec_id');

        if ($moduleId) {
            $query->where('module_id', $moduleId);
        }
        if ($isDone !== null && $isDone !== '') {
            $query->where('is_done', filter_var($isDone, FILTER_VALIDATE_BOOLEAN));
        }

        $rows = $query->get();

        $filename = 'page-catalog-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Page ID', 'Title', 'Module', 'Sub-module', 'Actors',
                'Vue Path', 'URL Dev', 'URL Staging', 'URL Prod',
                'Done', 'Business Requirement', 'Stakeholder Requirement', 'Description',
            ]);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->spec_id,
                    $row->title,
                    $row->module?->code . ' — ' . $row->module?->name,
                    $row->subModule?->code,
                    $row->actors->pluck('name')->join(', '),
                    $row->vue_path,
                    $row->url_dev,
                    $row->url_stg,
                    $row->url_prd,
                    $row->is_done ? 'Yes' : 'No',
                    $row->business_requirement,
                    $row->stakeholder_requirement,
                    $row->description,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function destroy(int $id): JsonResponse
    {
        RtmfFrontend::where('id', $id)->delete();

        return $this->sendOk(['success' => true]);
    }
}
