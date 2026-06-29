<?php

declare(strict_types=1);

namespace App\Domain\Translation;

final readonly class Translation
{
    public function __construct(
        private TranslationId $translationId,
        private TranslationLanguage $sourceLanguage,
        private TranslationLanguage $targetLanguage,
        private TranslationProvider $provider,
        private TranslationSegmentCollection $segments,
    ) {
    }

    public static function create(
        TranslationId $translationId,
        TranslationLanguage $sourceLanguage,
        TranslationLanguage $targetLanguage,
        TranslationProvider $provider,
        TranslationSegmentCollection $segments,
    ): self {
        return new self($translationId, $sourceLanguage, $targetLanguage, $provider, $segments);
    }

    public function translationId(): TranslationId
    {
        return $this->translationId;
    }

    public function sourceLanguage(): TranslationLanguage
    {
        return $this->sourceLanguage;
    }

    public function targetLanguage(): TranslationLanguage
    {
        return $this->targetLanguage;
    }

    public function provider(): TranslationProvider
    {
        return $this->provider;
    }

    public function segments(): TranslationSegmentCollection
    {
        return $this->segments;
    }

    public function text(): string
    {
        if ($this->segments->isEmpty()) {
            return '';
        }

        return implode(
            ' ',
            array_map(
                static fn (TranslationSegment $segment): string => $segment->translatedText(),
                $this->segments->all(),
            ),
        );
    }

    public function segmentCount(): int
    {
        return $this->segments->count();
    }
}
