<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\LipSync;

use App\Application\LipSync\DTO\VideoLipSyncResult;

final readonly class VideoLipSyncResponse
{
    public function __construct(
        public string $videoId,
        public string $artifactId,
        public string $clonedAudioId,
        public string $targetLanguage,
        public string $provider,
        public string $synchronizedVideoId,
        public float $duration,
        public string $originalVideoUrl,
        public string $syncedVideoUrl,
    ) {
    }

    public static function fromResult(VideoLipSyncResult $result): self
    {
        return new self(
            videoId: $result->videoId,
            artifactId: $result->artifactId,
            clonedAudioId: $result->clonedAudioId,
            targetLanguage: $result->targetLanguage,
            provider: $result->provider,
            synchronizedVideoId: $result->synchronizedVideoId,
            duration: $result->duration,
            originalVideoUrl: sprintf('/api/videos/%s/stream', $result->videoId),
            syncedVideoUrl: sprintf(
                '/api/videos/%s/lip-sync/%s/stream',
                $result->videoId,
                $result->targetLanguage,
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
            'artifactId' => $this->artifactId,
            'clonedAudioId' => $this->clonedAudioId,
            'targetLanguage' => $this->targetLanguage,
            'provider' => $this->provider,
            'synchronizedVideoId' => $this->synchronizedVideoId,
            'duration' => $this->duration,
            'originalVideoUrl' => $this->originalVideoUrl,
            'syncedVideoUrl' => $this->syncedVideoUrl,
        ];
    }
}
