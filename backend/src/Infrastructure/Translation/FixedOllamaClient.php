<?php

declare(strict_types=1);

namespace App\Infrastructure\Translation;

final class FixedOllamaClient implements OllamaClientInterface
{
    public function generate(array $payload): array
    {
        $prompt = $payload['prompt'] ?? '';

        if (!is_string($prompt)) {
            $prompt = '';
        }

        /** @var list<array{index: int, translatedText: string}> $segments */
        $segments = [];

        if (preg_match_all('/^(\d+): (.+)$/m', $prompt, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $index = (int) $match[1];
                $sourceText = $match[2];
                $translatedText = match (true) {
                    str_contains($prompt, 'french') => sprintf('[FR] %s', $sourceText),
                    str_contains($prompt, 'german') => sprintf('[DE] %s', $sourceText),
                    str_contains($prompt, 'spanish') => sprintf('[ES] %s', $sourceText),
                    str_contains($prompt, 'italian') => sprintf('[IT] %s', $sourceText),
                    default => sprintf('[TR] %s', $sourceText),
                };
                $segments[] = ['index' => $index, 'translatedText' => $translatedText];
            }
        }

        if ([] === $segments) {
            $segments[] = ['index' => 0, 'translatedText' => 'Translated text'];
        }

        return [
            'response' => json_encode(['segments' => $segments], JSON_THROW_ON_ERROR),
            'done' => true,
        ];
    }
}
