<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChangelogController extends Controller
{
    use ApiResponse;

    public function __construct(protected SettingService $settings) {}

    public function show(): JsonResponse
    {
        return $this->sendOk([
            'content' => $this->settings->get('changelog', ''),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|min:1',
        ]);

        $this->settings->set('changelog', $request->input('content'));

        return $this->sendOk(['success' => true]);
    }
}
