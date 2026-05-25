<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\AiChatLog;
use App\Services\AiChatService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiChatController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AiChatService $aiChatService,
        protected SettingService $settingService,
    ) {}

    public function chat(Request $request): JsonResponse
    {
        $request->validate(['message' => 'required|string|max:500']);

        $enabled = $this->settingService->get('aiChatEnabled') ?? 'true';
        if ($enabled === 'false' || $enabled === false) {
            return $this->sendError(403, 'FORBIDDEN', 'AI chat is currently disabled by the administrator.');
        }

        $userId = Auth::id();
        $result = $this->aiChatService->ask($request->input('message'), $userId);

        AiChatLog::create([
            'user_id'       => $userId,
            'question'      => $request->input('message'),
            'response'      => $result['response'],
            'model'         => 'claude-haiku-4-5-20251001',
            'input_tokens'  => $result['input_tokens'],
            'output_tokens' => $result['output_tokens'],
            'cost_usd'      => $result['cost_usd'],
        ]);

        return $this->sendOk(['reply' => $result['response']]);
    }

    public function logs(Request $request): JsonResponse
    {
        $page    = (int) $request->input('page', 1);
        $limit   = (int) $request->input('limit', 20);
        $q       = $request->input('q');

        $query = AiChatLog::with('user:id,name')->orderByDesc('created_at');

        if ($q) {
            $query->where(function ($b) use ($q) {
                $b->where('question', 'like', "%{$q}%")
                  ->orWhere('response', 'like', "%{$q}%");
            });
        }

        $total = $query->count();
        $rows  = $query->skip(($page - 1) * $limit)->take($limit)->get();

        return $this->sendOk($rows, [
            'page'       => $page,
            'limit'      => $limit,
            'total'      => $total,
            'totalPages' => (int) ceil($total / $limit),
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $page  = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 30);

        $rows = AiChatLog::selectRaw(
            "DATE(created_at) as date,
             COUNT(*) as messages,
             SUM(input_tokens) as input_tokens,
             SUM(output_tokens) as output_tokens,
             SUM(cost_usd) as cost_usd"
        )
        ->groupByRaw('DATE(created_at)')
        ->orderByDesc('date')
        ->skip(($page - 1) * $limit)
        ->take($limit)
        ->get();

        $total = AiChatLog::selectRaw('COUNT(DISTINCT DATE(created_at)) as cnt')
            ->value('cnt') ?? 0;

        $totalCost   = AiChatLog::sum('cost_usd');
        $totalTokens = AiChatLog::sum('input_tokens') + AiChatLog::sum('output_tokens');

        return $this->sendOk($rows, [
            'page'        => $page,
            'limit'       => $limit,
            'total'       => $total,
            'totalPages'  => (int) ceil($total / $limit),
            'summary'     => [
                'total_cost_usd'  => round((float) $totalCost, 6),
                'total_tokens'    => (int) $totalTokens,
                'total_messages'  => AiChatLog::count(),
            ],
        ]);
    }

    public function balance(): JsonResponse
    {
        $apiKey = $this->settingService->get('aiApiKey');

        if (empty($apiKey)) {
            return $this->sendOk(['available' => false, 'message' => 'API key not configured']);
        }

        $data = $this->aiChatService->fetchBalance($apiKey);

        if (empty($data)) {
            return $this->sendOk([
                'available' => false,
                'message'   => 'Balance info requires an Admin API key or is not available via API. Check console.anthropic.com.',
            ]);
        }

        return $this->sendOk(['available' => true, 'data' => $data]);
    }

    public function getSettings(): JsonResponse
    {
        $key = $this->settingService->get('aiApiKey') ?? '';
        $enabled = $this->settingService->get('aiChatEnabled') ?? 'true';

        return $this->sendOk([
            'ai_api_key'   => $key ? str_repeat('*', max(0, strlen($key) - 8)) . substr($key, -8) : '',
            'has_key'      => !empty($key),
            'model'        => 'claude-haiku-4-5-20251001',
            'max_tokens'   => 300,
            'chat_enabled' => $enabled !== 'false',
        ]);
    }

    public function saveSettings(Request $request): JsonResponse
    {
        $request->validate([
            'ai_api_key'   => 'nullable|string|max:200',
            'chat_enabled' => 'nullable|boolean',
        ]);

        $key = $request->input('ai_api_key');
        if ($key !== null && !str_contains($key, '***')) {
            $this->settingService->set('aiApiKey', $key);
        }

        if ($request->has('chat_enabled')) {
            $this->settingService->set('aiChatEnabled', $request->boolean('chat_enabled') ? 'true' : 'false');
        }

        return $this->sendOk(['success' => true]);
    }
}
