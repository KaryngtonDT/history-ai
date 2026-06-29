<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Translation;

use App\Application\Translation\DTO\VideoTranslationResult;

final readonly class VideoTranslationResponse
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

    public static function fromResult(VideoTranslationResult $result): self
    {
        return new self(
            videoId: $result->videoId,
            translationId: $result->translationId,
            sourceLanguage: $result->sourceLanguage,
            targetLanguage: $result->targetLanguage,
            provider: $result->provider,
            text: $result->text,
            segmentCount: $result->segmentCount,
            segments: $result->segments,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'videoId' => $this->videoId,
            'translationId' => $this->translationId,
            'sourceLanguage' => $this->sourceLanguage,
            'targetLanguage' => $this->targetLanguage,
            'provider' => $this->provider,
            'text' => $this->text,
            'segmentCount' => $this->segmentCount,
            'segments' => $this->segments,
        ];
    }
}
