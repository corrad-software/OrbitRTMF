<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RtmfFrontendAttachmentController extends Controller
{
    use ApiResponse;

    public function index(int $frontendId): JsonResponse
    {
        $frontend = RtmfFrontend::find($frontendId);
        if (! $frontend) {
            return $this->sendError(404, 'NOT_FOUND', 'Frontend entry not found');
        }

        $attachments = RtmfFrontendAttachment::where('rtmf_frontend_id', $frontendId)
            ->orderBy('created_at')
            ->get();

        return $this->sendOk($attachments);
    }

    public function store(Request $request, int $frontendId): JsonResponse
    {
        $frontend = RtmfFrontend::find($frontendId);
        if (! $frontend) {
            return $this->sendError(404, 'NOT_FOUND', 'Frontend entry not found');
        }

        $request->validate([
            'file' => 'required|file|max:20480',
            'label' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $safeBase = preg_replace('/-+/', '-', preg_replace('/[^a-z0-9.\-_]/', '-', strtolower($originalName)));
        $ext = pathinfo($safeBase, PATHINFO_EXTENSION);
        $name = pathinfo($safeBase, PATHINFO_FILENAME);
        $filename = $name . '-' . time() . ($ext ? '.' . $ext : '');

        $path = $file->storeAs('rtmf-attachments', $filename, 'public');
        $url = Storage::disk('public')->url('rtmf-attachments/' . $filename);

        $attachment = RtmfFrontendAttachment::create([
            'rtmf_frontend_id' => $frontendId,
            'label' => $request->input('label') ?: null,
            'filename' => $filename,
            'original_name' => $originalName,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
            'path' => $path,
            'url' => $url,
        ]);

        return $this->sendOk($attachment);
    }

    public function update(Request $request, int $frontendId, int $id): JsonResponse
    {
        $attachment = RtmfFrontendAttachment::where('rtmf_frontend_id', $frontendId)->find($id);
        if (! $attachment) {
            return $this->sendError(404, 'NOT_FOUND', 'Attachment not found');
        }

        $request->validate(['label' => 'nullable|string|max:255']);
        $attachment->update(['label' => $request->input('label') ?: null]);

        return $this->sendOk($attachment);
    }

    public function destroy(int $frontendId, int $id): JsonResponse
    {
        $attachment = RtmfFrontendAttachment::where('rtmf_frontend_id', $frontendId)->find($id);
        if (! $attachment) {
            return $this->sendError(404, 'NOT_FOUND', 'Attachment not found');
        }

        Storage::disk('public')->delete('rtmf-attachments/' . $attachment->filename);
        $attachment->delete();

        return $this->sendOk(['success' => true]);
    }
}
