<?php

declare(strict_types=1);

namespace App\Infrastructure\Chat;

use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatResponse;
use App\Infrastructure\Chat\Exception\GeminiChatProviderException;
use Throwable;

final class GeminiChatProvider implements ChatProviderInterface
{
    public const string DEFAULT_MODEL = 'gemini-2.5-flash';

    public function __construct(
        private readonly GeminiChatTransportInterface $transport,
        private readonly string $apiKey,
        private readonly string $defaultModel = self::DEFAULT_MODEL,
    ) {
    }

    public function answer(ChatRequest $request): ChatResponse
    {
        if ('' === trim($this->apiKey)) {
            throw new GeminiChatProviderException('GEMINI_API_KEY is not configured.');
        }

        $model = $request->options()->model()?->value() ?? $this->defaultModel;

        try {
            $responsePayload = $this->transport->generateContent($this->buildPayload($request, $model));
        } catch (Throwable $exception) {
            throw new GeminiChatProviderException(
                'Gemini chat request failed.',
                0,
                $exception,
            );
        }

        return new ChatResponse(
            $this->extractAnswerText($responsePayload),
            $request->sources(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(ChatRequest $request, string $model): array
    {
        return [
            'model' => $model,
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $this->buildPromptText($request)],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $request->options()->temperature(),
                'maxOutputTokens' => $request->options()->maxTokens(),
            ],
        ];
    }

    private function buildPromptText(ChatRequest $request): string
    {
        $sections = [
            $request->prompt()->value(),
        ];

        if (!$request->sources()->isEmpty()) {
            $sections[] = '';
            $sections[] = 'Retrieved sources:';

            foreach ($request->sources()->sources() as $index => $source) {
                $sections[] = sprintf(
                    '[%d] artifact=%s chunk=%s score=%.4f text=%s',
                    $index + 1,
                    $source->artifactId()->value,
                    $source->chunkId()->value,
                    $source->score()->value(),
                    $source->text(),
                );
            }
        }

        return implode("\n", $sections);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractAnswerText(array $payload): string
    {
        $candidates = $payload['candidates'] ?? null;
        if (!is_array($candidates) || [] === $candidates) {
            throw new GeminiChatProviderException('Gemini API returned no candidates.');
        }

        $firstCandidate = $candidates[0] ?? null;
        if (!is_array($firstCandidate)) {
            throw new GeminiChatProviderException('Gemini API returned an invalid candidate.');
        }

        $content = $firstCandidate['content'] ?? null;
        if (!is_array($content)) {
            throw new GeminiChatProviderException('Gemini API returned no content.');
        }

        $parts = $content['parts'] ?? null;
        if (!is_array($parts) || [] === $parts) {
            throw new GeminiChatProviderException('Gemini API returned no content parts.');
        }

        $firstPart = $parts[0] ?? null;
        if (!is_array($firstPart)) {
            throw new GeminiChatProviderException('Gemini API returned an invalid content part.');
        }

        $text = $firstPart['text'] ?? null;
        if (!is_string($text) || '' === trim($text)) {
            throw new GeminiChatProviderException('Gemini API returned an empty answer.');
        }

        return $text;
    }
}
