<?php

namespace App\Services;

use App\Models\RtmfFrontend;
use App\Models\RtmfFrontendItem;
use App\Models\RtmfFrontendFeedback;
use App\Models\RtmfModule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AiChatService
{
    private const MODEL = 'claude-haiku-4-5-20251001';

    // Haiku pricing per million tokens (USD)
    private const COST_INPUT_PER_M  = 0.80;
    private const COST_OUTPUT_PER_M = 4.00;

    public function ask(string $question, int $userId): array
    {
        $apiKey = app(SettingService::class)->get('aiApiKey');

        if (empty($apiKey)) {
            return [
                'response' => 'AI chat is not configured yet. Please ask an administrator to add an Anthropic API key in Settings → AI.',
                'input_tokens' => 0,
                'output_tokens' => 0,
                'cost_usd' => 0,
            ];
        }

        $context = $this->buildContext();
        $systemPrompt = $this->buildSystemPrompt($context);

        $httpResponse = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => self::MODEL,
            'max_tokens' => 300,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $question],
            ],
        ]);

        if ($httpResponse->failed()) {
            $error = $httpResponse->json('error.message') ?? 'Unknown error';
            return [
                'response' => "AI service error: {$error}",
                'input_tokens' => 0,
                'output_tokens' => 0,
                'cost_usd' => 0,
            ];
        }

        $body         = $httpResponse->json();
        $responseText = $body['content'][0]['text'] ?? 'No response';
        $inputTokens  = $body['usage']['input_tokens'] ?? 0;
        $outputTokens = $body['usage']['output_tokens'] ?? 0;
        $costUsd      = ($inputTokens / 1_000_000 * self::COST_INPUT_PER_M)
                      + ($outputTokens / 1_000_000 * self::COST_OUTPUT_PER_M);

        return [
            'response'      => $responseText,
            'input_tokens'  => $inputTokens,
            'output_tokens' => $outputTokens,
            'cost_usd'      => round($costUsd, 8),
        ];
    }

    public function fetchBalance(string $apiKey): array
    {
        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])->get('https://api.anthropic.com/v1/organizations/billing/credits');

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        // Credits endpoint may require an Admin API key — return empty gracefully
        return [];
    }

    // -----------------------------------------------------------------------
    // Context builder — fetches compact RTMF summary for the last 7 days
    // -----------------------------------------------------------------------

    private function buildContext(): array
    {
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();
        $weekStart = Carbon::now()->startOfWeek();

        // Pages (rtmf_frontends) stats
        $totalPages     = RtmfFrontend::whereNull('deleted_at')->count();
        $donePages      = RtmfFrontend::whereNull('deleted_at')->where('is_done', true)->count();
        $notDonePages   = $totalPages - $donePages;

        $doneToday = RtmfFrontend::whereNull('deleted_at')
            ->where('is_done', true)
            ->whereDate('updated_at', $today)
            ->count();

        $doneYesterday = RtmfFrontend::whereNull('deleted_at')
            ->where('is_done', true)
            ->whereDate('updated_at', $yesterday)
            ->count();

        $doneThisWeek = RtmfFrontend::whereNull('deleted_at')
            ->where('is_done', true)
            ->where('updated_at', '>=', $weekStart)
            ->count();

        // Daily breakdown for last 7 days
        $dailyDone = RtmfFrontend::whereNull('deleted_at')
            ->where('is_done', true)
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw("DATE(updated_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($r) => ['date' => $r->date, 'done' => $r->count])
            ->toArray();

        // Frontend items by status
        $itemsByStatus = RtmfFrontendItem::selectRaw("status, COUNT(*) as count")
            ->whereNotNull('status')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Recent feedback (last 7 days)
        $recentFeedback = RtmfFrontendFeedback::where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        // Module count
        $moduleCount = RtmfModule::count();

        // Pages per module (top 10 by page count)
        $pagesByModule = DB::table('rtmf_frontends')
            ->join('rtmf_modules', 'rtmf_frontends.module_id', '=', 'rtmf_modules.id')
            ->whereNull('rtmf_frontends.deleted_at')
            ->selectRaw('rtmf_modules.name as module, COUNT(*) as total, SUM(CASE WHEN rtmf_frontends.is_done THEN 1 ELSE 0 END) as done')
            ->groupBy('rtmf_modules.id', 'rtmf_modules.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'generated_at'  => now()->toIso8601String(),
            'pages'         => [
                'total'          => $totalPages,
                'done'           => $donePages,
                'not_done'       => $notDonePages,
                'done_today'     => $doneToday,
                'done_yesterday' => $doneYesterday,
                'done_this_week' => $doneThisWeek,
                'daily_last_7d'  => $dailyDone,
            ],
            'frontend_items' => $itemsByStatus,
            'feedback'       => ['last_7_days' => $recentFeedback],
            'modules'        => [
                'total'       => $moduleCount,
                'by_module'   => $pagesByModule,
            ],
        ];
    }

    private function buildSystemPrompt(array $context): string
    {
        $json = json_encode($context, JSON_PRETTY_PRINT);

        return <<<PROMPT
You are AIRA, an AI assistant for the Page Catalog system.
Answer the user's question using ONLY the data snapshot below. Be concise and direct — one to three sentences.
If the answer is not in the data, say "I don't have that information in the current snapshot."
Do not make up numbers or details not present in the data.

Data snapshot:
{$json}
PROMPT;
    }
}
