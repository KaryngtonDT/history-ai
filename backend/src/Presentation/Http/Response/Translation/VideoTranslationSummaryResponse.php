<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Translation;

use App\Application\Translation\DTO\VideoTranslationSummary;

final readonly class VideoTranslationSummaryResponse
{
    public function __construct(
        public string $videoId,
        public string $translationId,
        public string $sourceLanguage,
        public string $targetLanguage,
        public string $provider,
        public string $text,
        public int $segmentCount,
    ) {
    }

    public static function fromSummary(VideoTranslationSummary $summary): self
    {
        return new self(
            videoId: $summary->videoId,
            translationId: $summary->translationId,
            sourceLanguage: $summary->sourceLanguage,
            targetLanguage: $summary->targetLanguage,
            provider: $summary->provider,
            text: $summary->text,
            segmentCount: $summary->segmentCount,
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
        ];
    }
}
