<?php

namespace App\Services;

use App\Support\AiModel;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Dunne laag om de OpenAI Chat Completions API. Geeft altijd een antwoord terug in dezelfde
 * vorm, ook bij een fout, zodat de aanroepers de kosten kunnen vastleggen voordat ze verder
 * beslissen. De token-aantallen worden vertaald naar input_tokens/output_tokens, de vorm die
 * AiSpendGuard verwacht.
 */
class OpenAiClient
{
    public const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    /**
     * @return array{ok: bool, status: int, model: string, text: ?string, usage: array{input_tokens: int, output_tokens: int}|null, error: ?string}
     */
    public function json(string $system, string $user, int $maxTokens = 1000, int $timeout = 30): array
    {
        $model = AiModel::resolve();

        try {
            $response = Http::withToken((string) config('services.openai.key'))
                ->timeout($timeout)
                ->post(self::ENDPOINT, [
                    'model' => $model,
                    'max_completion_tokens' => $maxTokens,
                    // Weinig nadenken is genoeg voor deze taken en houdt de kosten laag:
                    // redeneertokens tellen mee als uitvoertokens.
                    'reasoning_effort' => 'low',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                ]);
        } catch (ConnectionException $exception) {
            return ['ok' => false, 'status' => 0, 'model' => $model, 'text' => null, 'usage' => null, 'error' => $exception->getMessage()];
        }

        $usage = $response->json('usage');

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'model' => $model,
            'text' => $response->json('choices.0.message.content'),
            'usage' => is_array($usage) ? [
                'input_tokens' => (int) ($usage['prompt_tokens'] ?? 0),
                'output_tokens' => (int) ($usage['completion_tokens'] ?? 0),
            ] : null,
            'error' => $response->successful() ? null : ($response->json('error.message') ?? 'HTTP '.$response->status()),
        ];
    }
}
