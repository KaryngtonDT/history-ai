<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\VideoRender;

use App\Application\VideoRender\DTO\VideoRenderSummary;

final readonly class VideoRenderSummaryResponse
{
    public function __construct(
        public string $videoId,
        public string $finalVideoId,
        public string $targetLanguage,
        public string $provider,
        public string $format,
        public string $quality,
        public float $duration,
        public int $fileSizeBytes,
        public string $streamUrl,
    ) {
    }

    public static function fromSummary(VideoRenderSummary $summary): self
    {
        return new self(
            videoId: $summary->videoId,
            finalVideoId: $summary->finalVideoId,
            targetLanguage: $summary->targetLanguage,
            provider: $summary->provider,
            format: $summary->format,
            quality: $summary->quality,
            duration: $summary->duration,
            fileSizeBytes: $summary->fileSizeBytes,
            streamUrl: sprintf(
                '/api/videos/%s/render/%s/stream',
                $summary->videoId,
                $summary->targetLanguage,
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'videoId' => $this->videoId,
            'finalVideoId' => $this->finalVideoId,
            'targetLanguage' => $this->targetLanguage,
            'provider' => $this->provider,
            'format' => $this->format,
            'quality' => $this->quality,
            'duration' => $this->duration,
            'fileSizeBytes' => $this->fileSizeBytes,
            'streamUrl' => $this->streamUrl,
        ];
    }
}
