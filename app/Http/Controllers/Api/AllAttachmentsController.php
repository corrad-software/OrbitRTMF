<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Media;
use App\Models\RtmfFrontendAttachment;
use App\Models\RtmfModulePhoto;
use App\Models\RtmfScenarioAttachment;
use App\Models\RtmfSubModulePhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AllAttachmentsController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $page    = (int) $request->input('page', 1);
        $limit   = (int) $request->input('limit', 50);
        $q       = $request->input('q');
        $source  = $request->input('source');
        $sortDir = $request->input('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $items = collect();

        // CMS Media (uploads/)
        if (!$source || $source === 'media') {
            $query = Media::query();
            if ($q) {
                $query->where(function ($b) use ($q) {
                    $b->where('original_name', 'ilike', "%{$q}%")
                      ->orWhere('title', 'ilike', "%{$q}%");
                });
            }
            $query->get()->each(function ($row) use ($items) {
                $items->push([
                    'id'           => 'media-' . $row->id,
                    'source'       => 'media',
                    'sourceLabel'  => 'CMS Media',
                    'filename'     => $row->filename,
                    'originalName' => $row->original_name,
                    'mimeType'     => $row->mime_type,
                    'size'         => $row->size,
                    'url'          => $row->url,
                    'createdAt'    => $row->created_at,
                ]);
            });
        }

        // RTMF Frontend Attachments (rtmf-attachments/)
        if (!$source || $source === 'frontend_attachment') {
            $query = RtmfFrontendAttachment::with('frontend:id,title');
            if ($q) {
                $query->where(function ($b) use ($q) {
                    $b->where('original_name', 'ilike', "%{$q}%")
                      ->orWhere('label', 'ilike', "%{$q}%");
                });
            }
            $query->get()->each(function ($row) use ($items) {
                $items->push([
                    'id'           => 'fa-' . $row->id,
                    'source'       => 'frontend_attachment',
                    'sourceLabel'  => 'Page Attachment',
                    'context'      => $row->frontend?->title,
                    'filename'     => $row->filename,
                    'originalName' => $row->original_name,
                    'label'        => $row->label,
                    'mimeType'     => $row->mime_type,
                    'size'         => $row->size,
                    'url'          => $row->url,
                    'createdAt'    => $row->created_at,
                ]);
            });
        }

        // RTMF Module Photos (rtmf-module-photos/)
        if (!$source || $source === 'module_photo') {
            $query = RtmfModulePhoto::with('module:id,title');
            if ($q) {
                $query->where('original_name', 'ilike', "%{$q}%");
            }
            $query->get()->each(function ($row) use ($items) {
                $items->push([
                    'id'           => 'mp-' . $row->id,
                    'source'       => 'module_photo',
                    'sourceLabel'  => 'Module Photo',
                    'context'      => $row->module?->title,
                    'filename'     => $row->filename,
                    'originalName' => $row->original_name,
                    'mimeType'     => $row->mime_type,
                    'size'         => $row->size,
                    'url'          => $row->url,
                    'createdAt'    => $row->created_at,
                ]);
            });
        }

        // RTMF Sub-Module Photos (rtmf-submodule-photos/)
        if (!$source || $source === 'submodule_photo') {
            $query = RtmfSubModulePhoto::with('subModule:id,title');
            if ($q) {
                $query->where('original_name', 'ilike', "%{$q}%");
            }
            $query->get()->each(function ($row) use ($items) {
                $items->push([
                    'id'           => 'smp-' . $row->id,
                    'source'       => 'submodule_photo',
                    'sourceLabel'  => 'Sub-Module Photo',
                    'context'      => $row->subModule?->title,
                    'filename'     => $row->filename,
                    'originalName' => $row->original_name,
                    'mimeType'     => $row->mime_type,
                    'size'         => $row->size,
                    'url'          => $row->url,
                    'createdAt'    => $row->created_at,
                ]);
            });
        }

        // RTMF Scenario Attachments
        if (!$source || $source === 'scenario_attachment') {
            $query = RtmfScenarioAttachment::with('scenario:id,title');
            if ($q) {
                $query->where(function ($b) use ($q) {
                    $b->where('original_name', 'ilike', "%{$q}%")
                      ->orWhere('label', 'ilike', "%{$q}%");
                });
            }
            $query->get()->each(function ($row) use ($items) {
                $items->push([
                    'id'           => 'sa-' . $row->id,
                    'source'       => 'scenario_attachment',
                    'sourceLabel'  => 'Scenario Attachment',
                    'context'      => $row->scenario?->title,
                    'filename'     => $row->filename,
                    'originalName' => $row->original_name,
                    'label'        => $row->label,
                    'mimeType'     => $row->mime_type,
                    'size'         => $row->size,
                    'url'          => $row->url,
                    'createdAt'    => $row->created_at,
                ]);
            });
        }

        $sorted = $sortDir === 'asc'
            ? $items->sortBy('createdAt')
            : $items->sortByDesc('createdAt');

        $total  = $sorted->count();
        $rows   = $sorted->slice(($page - 1) * $limit, $limit)->values();

        return $this->sendOk($rows, [
            'page'       => $page,
            'limit'      => $limit,
            'total'      => $total,
            'totalPages' => (int) ceil($total / max($limit, 1)),
        ]);
    }
}
