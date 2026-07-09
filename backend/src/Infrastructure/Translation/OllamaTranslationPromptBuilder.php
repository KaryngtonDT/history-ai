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
        $clean = $this->extractJson($responseJson);

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
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

        // Build a sequential fallback: if model reindexed from 0, remap by position
        $translatedSequential = array_values($translatedByIndex);
        $allSegments = $transcript->segments()->all();

        foreach ($allSegments as $position => $segment) {
            $translatedText = $translatedByIndex[$segment->index()]
                ?? $translatedSequential[$position]
                ?? $segment->text(); // fallback: keep source text if translation missing

            $mapped[] = [
                'index' => $segment->index(),
                'sourceText' => $segment->text(),
                'translatedText' => $translatedText,
            ];
        }

        return $mapped;
    }

    private function extractJson(string $text): string
    {
        // Strip <think>...</think> blocks (used by reasoning models like DeepSeek, Qwen3)
        $text = preg_replace('/<think>.*?<\/think>/s', '', $text) ?? $text;

        // Extract JSON from markdown code block ```json ... ``` or ``` ... ```
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $matches)) {
            return trim($matches[1]);
        }

        // Find the first { ... } JSON object in the text
        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if (false !== $start && false !== $end && $end > $start) {
            return substr($text, $start, $end - $start + 1);
        }

        return trim($text);
    }
}
