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

        $segments = [];

        if (str_contains($prompt, 'Hello world')) {
            if (str_contains($prompt, 'French') || str_contains($prompt, 'french')) {
                $segments[] = ['index' => 0, 'translatedText' => 'Bonjour le monde'];
            } elseif (str_contains($prompt, 'German') || str_contains($prompt, 'german')) {
                $segments[] = ['index' => 0, 'translatedText' => 'Hallo Welt'];
            } else {
                $segments[] = ['index' => 0, 'translatedText' => 'Translated text'];
            }
        } elseif (str_contains($prompt, 'First segment')) {
            if (str_contains($prompt, 'French') || str_contains($prompt, 'french')) {
                $segments = [
                    ['index' => 0, 'translatedText' => 'Premier segment.'],
                    ['index' => 1, 'translatedText' => 'Deuxième segment.'],
                ];
            } else {
                $segments = [
                    ['index' => 0, 'translatedText' => 'Erstes Segment.'],
                    ['index' => 1, 'translatedText' => 'Zweites Segment.'],
                ];
            }
        } else {
            $segments[] = ['index' => 0, 'translatedText' => 'Translated text'];
        }

        return [
            'response' => json_encode(['segments' => $segments], JSON_THROW_ON_ERROR),
            'done' => true,
        ];
    }
}
