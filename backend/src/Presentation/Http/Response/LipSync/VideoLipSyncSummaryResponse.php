<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\LipSync;

use App\Application\LipSync\DTO\VideoLipSyncSummary;

final readonly class VideoLipSyncSummaryResponse
{
    public function __construct(
        public string $videoId,
        public string $artifactId,
        public string $clonedAudioId,
        public string $targetLanguage,
        public string $provider,
        public string $synchronizedVideoId,
        public float $duration,
        public string $syncedVideoUrl,
    ) {
    }

    public static function fromSummary(VideoLipSyncSummary $summary): self
    {
        return new self(
            videoId: $summary->videoId,
            artifactId: $summary->artifactId,
            clonedAudioId: $summary->clonedAudioId,
            targetLanguage: $summary->targetLanguage,
            provider: $summary->provider,
            synchronizedVideoId: $summary->synchronizedVideoId,
            duration: $summary->duration,
            syncedVideoUrl: sprintf(
                '/api/videos/%s/lip-sync/%s/stream',
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
            'artifactId' => $this->artifactId,
            'clonedAudioId' => $this->clonedAudioId,
            'targetLanguage' => $this->targetLanguage,
            'provider' => $this->provider,
            'synchronizedVideoId' => $this->synchronizedVideoId,
            'duration' => $this->duration,
            'syncedVideoUrl' => $this->syncedVideoUrl,
        ];
    }
}
