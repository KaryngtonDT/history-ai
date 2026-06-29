<?php

declare(strict_types=1);

namespace App\Infrastructure\Translation;

use App\Domain\Speech\Transcript;
use App\Domain\Translation\TranslationLanguage;
use App\Infrastructure\Translation\Exception\OllamaTranslationException;

final class OllamaTranslationPromptBuilder
{
    public function build(Transcript $transcript, TranslationLanguage $targetLanguage): string
    {
        $sourceLanguage = TranscriptLanguageMapper::toTranslationLanguage($transcript->language());
        $lines = [];

        foreach ($transcript->segments()->all() as $segment) {
            $lines[] = sprintf('%d: %s', $segment->index(), $segment->text());
        }

        return sprintf(
            "You are a professional translator.\nTranslate the following transcript segments from %s to %s.\nRules:\n- Translate only. Do not summarize.\n- Preserve paragraph structure.\n- Preserve timestamps by keeping the same number of segments.\n\nReturn JSON only with this shape:\n{\"segments\":[{\"index\":0,\"translatedText\":\"...\"}]}\n\nSource segments:\n%s",
            $sourceLanguage->value,
            $targetLanguage->value,
            implode("\n", $lines),
        );
    }

    /**
     * @return list<array{index: int, sourceText: string, translatedText: string}>
     */
    public function mapResponseToSegments(Transcript $transcript, string $responseJson): array
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new OllamaTranslationException('Ollama translation response is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !isset($decoded['segments']) || !is_array($decoded['segments'])) {
            throw new OllamaTranslationException('Ollama translation response must include segments array.');
        }

        /** @var array<int, string> $translatedByIndex */
        $translatedByIndex = [];

        foreach ($decoded['segments'] as $segment) {
            if (!is_array($segment)) {
                continue;
            }

            $index = $segment['index'] ?? null;
            $translatedText = $segment['translatedText'] ?? null;

            if (!is_int($index) || !is_string($translatedText) || '' === trim($translatedText)) {
                continue;
            }

            $translatedByIndex[$index] = trim($translatedText);
        }

        $mapped = [];

        foreach ($transcript->segments()->all() as $segment) {
            $translatedText = $translatedByIndex[$segment->index()] ?? null;

            if (null === $translatedText) {
                throw new OllamaTranslationException(sprintf(
                    'Ollama translation response is missing segment index %d.',
                    $segment->index(),
                ));
            }

            $mapped[] = [
                'index' => $segment->index(),
                'sourceText' => $segment->text(),
                'translatedText' => $translatedText,
            ];
        }

        return $mapped;
    }
}
