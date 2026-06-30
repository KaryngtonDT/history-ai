<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\VideoRender;

use App\Application\VideoRender\DTO\VideoRenderResult;

final readonly class VideoRenderResponse
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
        public string $downloadUrl,
    ) {
    }

    public static function fromResult(VideoRenderResult $result): self
    {
        $streamUrl = sprintf(
            '/api/videos/%s/render/%s/stream',
            $result->videoId,
            $result->targetLanguage,
        );

        return new self(
            videoId: $result->videoId,
            finalVideoId: $result->finalVideoId,
            targetLanguage: $result->targetLanguage,
            provider: $result->provider,
            format: $result->format,
            quality: $result->quality,
            duration: $result->duration,
            fileSizeBytes: $result->fileSizeBytes,
            streamUrl: $streamUrl,
            downloadUrl: $streamUrl,
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
            'downloadUrl' => $this->downloadUrl,
        ];
    }
}
