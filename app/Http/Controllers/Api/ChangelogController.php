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
        $content = trim((string) $this->settings->get('changelog', ''));

        if ($content === '' || $content === 'null') {
            $content = trim($this->readBundledChangelog());
        }

        return $this->sendOk([
            'content' => $content,
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

    /**
     * Default changelog shipped with the app (survives redeploy when settings row is empty).
     */
    private function readBundledChangelog(): string
    {
        foreach ([
            base_path('CHANGELOG.md'),
            base_path('docs/CHANGELOG.md'),
        ] as $path) {
            if (is_file($path) && is_readable($path)) {
                return (string) file_get_contents($path);
            }
        }

        return '';
    }
}
