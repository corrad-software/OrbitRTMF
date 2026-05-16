<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChangelogController extends Controller
{
    use ApiResponse;

    private function path(): string
    {
        return base_path('docs/CHANGELOG.md');
    }

    public function show(): JsonResponse
    {
        $path = $this->path();

        return $this->sendOk([
            'content' => file_exists($path) ? file_get_contents($path) : '',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|min:1',
        ]);

        $path = $this->path();
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, $request->input('content'));

        return $this->sendOk(['success' => true]);
    }
}
