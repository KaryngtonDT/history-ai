<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\VoiceClone;

use App\Application\VoiceClone\DTO\VideoVoiceCloneResult;
use App\Application\VoiceClone\DTO\VideoVoiceCloneSummary;

final readonly class VideoVoiceCloneSummaryResponse
{
    public function __construct(
        public string $videoId,
        public string $artifactId,
        public string $sourceAudioId,
        public string $clonedAudioId,
        public string $targetLanguage,
        public string $provider,
        public float $duration,
        public int $sampleRate,
    ) {
    }

    public static function fromSummary(VideoVoiceCloneSummary $summary): self
    {
        return new self(
            videoId: $summary->videoId,
            artifactId: $summary->artifactId,
            sourceAudioId: $summary->sourceAudioId,
            clonedAudioId: $summary->clonedAudioId,
            targetLanguage: $summary->targetLanguage,
            provider: $summary->provider,
            duration: $summary->duration,
            sampleRate: $summary->sampleRate,
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
            'sourceAudioId' => $this->sourceAudioId,
            'clonedAudioId' => $this->clonedAudioId,
            'targetLanguage' => $this->targetLanguage,
            'provider' => $this->provider,
            'duration' => $this->duration,
            'sampleRate' => $this->sampleRate,
        ];
    }
}
