<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\TTS;

use App\Application\TTS\DTO\VideoAudioResult;
use App\Application\TTS\DTO\VideoAudioSummary;

final readonly class VideoAudioSummaryResponse
{
    public function __construct(
        public string $videoId,
        public string $audioId,
        public string $translationId,
        public string $targetLanguage,
        public string $provider,
        public string $voiceId,
        public string $voiceDisplayName,
        public float $duration,
        public string $format,
    ) {
    }

    public static function fromSummary(VideoAudioSummary $summary): self
    {
        return new self(
            videoId: $summary->videoId,
            audioId: $summary->audioId,
            translationId: $summary->translationId,
            targetLanguage: $summary->targetLanguage,
            provider: $summary->provider,
            voiceId: $summary->voiceId,
            voiceDisplayName: $summary->voiceDisplayName,
            duration: $summary->duration,
            format: $summary->format,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'videoId' => $this->videoId,
            'audioId' => $this->audioId,
            'translationId' => $this->translationId,
            'targetLanguage' => $this->targetLanguage,
            'provider' => $this->provider,
            'voiceId' => $this->voiceId,
            'voiceDisplayName' => $this->voiceDisplayName,
            'duration' => $this->duration,
            'format' => $this->format,
        ];
    }
}
