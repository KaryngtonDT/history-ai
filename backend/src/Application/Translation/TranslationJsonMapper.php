<?php

declare(strict_types=1);

namespace App\Application\Translation;

use App\Domain\Translation\Exception\InvalidTranslationException;
use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use JsonException;

final class TranslationJsonMapper
{
    /**
     * @return array{
     *     translationId: string,
     *     sourceLanguage: string,
     *     targetLanguage: string,
     *     provider: string,
     *     text: string,
     *     segmentCount: int,
     *     segments: list<array{index: int, sourceText: string, translatedText: string}>
     * }
     */
    public function toArray(Translation $translation): array
    {
        /** @var list<array{index: int, sourceText: string, translatedText: string}> $segments */
        $segments = [];

        foreach ($translation->segments()->all() as $segment) {
            $segments[] = [
                'index' => $segment->index(),
                'sourceText' => $segment->sourceText(),
                'translatedText' => $segment->translatedText(),
            ];
        }

        return [
            'translationId' => $translation->translationId()->value,
            'sourceLanguage' => $translation->sourceLanguage()->value,
            'targetLanguage' => $translation->targetLanguage()->value,
            'provider' => $translation->provider()->value,
            'text' => $translation->text(),
            'segmentCount' => $translation->segmentCount(),
            'segments' => $segments,
        ];
    }

    public function toJson(Translation $translation): string
    {
        return json_encode($this->toArray($translation), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): Translation
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidTranslationException('Stored translation is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidTranslationException('Stored translation must be a JSON object.');
        }

        $translationId = is_string($decoded['translationId'] ?? null) ? $decoded['translationId'] : null;
        $sourceLanguageValue = is_string($decoded['sourceLanguage'] ?? null)
            ? $decoded['sourceLanguage']
            : TranslationLanguage::Unknown->value;
        $targetLanguageValue = is_string($decoded['targetLanguage'] ?? null)
            ? $decoded['targetLanguage']
            : TranslationLanguage::Unknown->value;
        $providerValue = is_string($decoded['provider'] ?? null)
            ? $decoded['provider']
            : TranslationProvider::Mock->value;

        if (null === $translationId) {
            throw new InvalidTranslationException('Stored translation must include translationId.');
        }

        $sourceLanguage = TranslationLanguage::tryFrom($sourceLanguageValue) ?? TranslationLanguage::Unknown;
        $targetLanguage = TranslationLanguage::tryFrom($targetLanguageValue) ?? TranslationLanguage::Unknown;
        $provider = TranslationProvider::tryFrom($providerValue) ?? TranslationProvider::Mock;
        $rawSegments = $decoded['segments'] ?? [];

        if (!is_array($rawSegments)) {
            throw new InvalidTranslationException('Stored translation must include segments array.');
        }

        /** @var list<TranslationSegment> $segments */
        $segments = [];

        foreach (array_values($rawSegments) as $position => $rawSegment) {
            if (!is_array($rawSegment)) {
                continue;
            }

            $translatedText = is_string($rawSegment['translatedText'] ?? null)
                ? trim($rawSegment['translatedText'])
                : '';
            $sourceText = is_string($rawSegment['sourceText'] ?? null)
                ? trim($rawSegment['sourceText'])
                : '';

            if ('' === $translatedText) {
                continue;
            }

            $index = is_int($rawSegment['index'] ?? null) ? $rawSegment['index'] : $position;
            $segments[] = TranslationSegment::create($index, $sourceText, $translatedText);
        }

        return Translation::create(
            new TranslationId($translationId),
            $sourceLanguage,
            $targetLanguage,
            $provider,
            new TranslationSegmentCollection($segments),
        );
    }
}
