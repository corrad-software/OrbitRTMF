<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRtmfActorRequest;
use App\Http\Requests\UpdateRtmfActorRequest;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfActor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtmfActorController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $q = $request->input('q');
        $query = RtmfActor::query()->withCount('frontends');
        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }
        $rows = $query->orderBy('sort_order')->orderBy('name')->get();

        return $this->sendOk($rows, ['total' => $rows->count()]);
    }

    public function store(StoreRtmfActorRequest $request): JsonResponse
    {
        $row = RtmfActor::create($request->validated());

        return $this->sendOk($row);
    }

    public function show(int $id): JsonResponse
    {
        $row = RtmfActor::withCount('frontends')->find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Actor not found');
        }

        return $this->sendOk($row);
    }

    public function update(UpdateRtmfActorRequest $request, int $id): JsonResponse
    {
        $row = RtmfActor::find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Actor not found');
        }
        $row->update($request->validated());

        return $this->sendOk($row);
    }

    public function destroy(int $id): JsonResponse
    {
        $row = RtmfActor::withCount('frontends')->find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Actor not found');
        }
        if ($row->frontends_count > 0) {
            return $this->sendError(422, 'IN_USE', "Actor is referenced by {$row->frontends_count} frontend entries.");
        }
        $row->delete();

        return $this->sendOk(['success' => true]);
    }
}
