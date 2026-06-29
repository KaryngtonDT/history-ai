<?php

declare(strict_types=1);

namespace App\Application\Translation\DTO;

use App\Domain\Translation\Translation;

final readonly class VideoTranslationResult
{
    /**
     * @param list<array{index: int, sourceText: string, translatedText: string}> $segments
     */
    public function __construct(
        public string $videoId,
        public string $translationId,
        public string $sourceLanguage,
        public string $targetLanguage,
        public string $provider,
        public string $text,
        public int $segmentCount,
        public array $segments,
    ) {
    }

    public static function fromDomain(string $videoId, Translation $translation): self
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

        return new self(
            videoId: $videoId,
            translationId: $translation->translationId()->value,
            sourceLanguage: $translation->sourceLanguage()->value,
            targetLanguage: $translation->targetLanguage()->value,
            provider: $translation->provider()->value,
            text: $translation->text(),
            segmentCount: $translation->segmentCount(),
            segments: $segments,
        );
    }
}
