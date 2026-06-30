<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\TTS;

use App\Application\TTS\DTO\VideoAudioResult;

final readonly class VideoAudioResponse
{
    public function __construct(
        public string $videoId,
        public string $audioId,
        public string $translationId,
        public string $targetLanguage,
        public string $provider,
        public string $voiceId,
        public string $voiceDisplayName,
        public string $voiceLanguage,
        public string $voiceGender,
        public float $duration,
        public string $format,
        public string $downloadUrl,
    ) {
    }

    public static function fromResult(VideoAudioResult $result): self
    {
        return new self(
            videoId: $result->videoId,
            audioId: $result->audioId,
            translationId: $result->translationId,
            targetLanguage: $result->targetLanguage,
            provider: $result->provider,
            voiceId: $result->voiceId,
            voiceDisplayName: $result->voiceDisplayName,
            voiceLanguage: $result->voiceLanguage,
            voiceGender: $result->voiceGender,
            duration: $result->duration,
            format: $result->format,
            downloadUrl: $result->downloadUrl,
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
            'voiceLanguage' => $this->voiceLanguage,
            'voiceGender' => $this->voiceGender,
            'duration' => $this->duration,
            'format' => $this->format,
            'downloadUrl' => $this->downloadUrl,
        ];
    }
}
