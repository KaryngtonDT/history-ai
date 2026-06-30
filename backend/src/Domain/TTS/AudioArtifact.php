<?php

declare(strict_types=1);

namespace App\Domain\TTS;

use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;

final readonly class AudioArtifact
{
    public function __construct(
        private AudioId $audioId,
        private TranslationId $translationId,
        private TextToSpeechProvider $provider,
        private Voice $voice,
        private float $duration,
        private FileFormat $format,
    ) {
        if ($this->duration < 0) {
            throw new InvalidAudioArtifactException('Audio duration cannot be negative.');
        }
    }

    public static function create(
        AudioId $audioId,
        TranslationId $translationId,
        TextToSpeechProvider $provider,
        Voice $voice,
        float $duration,
        FileFormat $format,
    ): self {
        return new self($audioId, $translationId, $provider, $voice, $duration, $format);
    }

    public function audioId(): AudioId
    {
        return $this->audioId;
    }

    public function translationId(): TranslationId
    {
        return $this->translationId;
    }

    public function provider(): TextToSpeechProvider
    {
        return $this->provider;
    }

    public function voice(): Voice
    {
        return $this->voice;
    }

    public function duration(): float
    {
        return $this->duration;
    }

    public function format(): FileFormat
    {
        return $this->format;
    }
}
