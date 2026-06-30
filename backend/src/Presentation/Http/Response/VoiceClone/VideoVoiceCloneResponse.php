<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\VoiceClone;

use App\Application\VoiceClone\DTO\VideoVoiceCloneResult;

final readonly class VideoVoiceCloneResponse
{
    public function __construct(
        public string $videoId,
        public string $artifactId,
        public string $sourceAudioId,
        public string $clonedAudioId,
        public string $targetLanguage,
        public string $provider,
        public string $sourceLanguage,
        public float $duration,
        public int $sampleRate,
        public string $originalAudioUrl,
        public string $clonedAudioUrl,
    ) {
    }

    public static function fromResult(VideoVoiceCloneResult $result): self
    {
        return new self(
            videoId: $result->videoId,
            artifactId: $result->artifactId,
            sourceAudioId: $result->sourceAudioId,
            clonedAudioId: $result->clonedAudioId,
            targetLanguage: $result->targetLanguage,
            provider: $result->provider,
            sourceLanguage: $result->sourceLanguage,
            duration: $result->duration,
            sampleRate: $result->sampleRate,
            originalAudioUrl: sprintf(
                '/api/videos/%s/audio/%s/stream',
                $result->videoId,
                $result->targetLanguage,
            ),
            clonedAudioUrl: sprintf(
                '/api/videos/%s/voice-clone/%s/stream',
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
            'sourceAudioId' => $this->sourceAudioId,
            'clonedAudioId' => $this->clonedAudioId,
            'targetLanguage' => $this->targetLanguage,
            'provider' => $this->provider,
            'sourceLanguage' => $this->sourceLanguage,
            'duration' => $this->duration,
            'sampleRate' => $this->sampleRate,
            'originalAudioUrl' => $this->originalAudioUrl,
            'clonedAudioUrl' => $this->clonedAudioUrl,
        ];
    }
}
