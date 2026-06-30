<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\Exception\InvalidVideoIntelligenceException;

final readonly class AudioCharacteristics
{
    public function __construct(
        private string $language,
        private int $speakerCount,
        private AudioNoiseLevel $backgroundNoise,
        private BackgroundMusic $backgroundMusic,
        private SpeechSpeed $speechSpeed,
        private SpeechConfidence $confidence,
    ) {
        if ('' === trim($this->language)) {
            throw new InvalidVideoIntelligenceException('Language must not be empty.');
        }

        if ($this->speakerCount < 0) {
            throw new InvalidVideoIntelligenceException('Speaker count cannot be negative.');
        }
    }

    public static function create(
        string $language,
        int $speakerCount,
        AudioNoiseLevel $backgroundNoise,
        BackgroundMusic $backgroundMusic,
        SpeechSpeed $speechSpeed,
        SpeechConfidence $confidence,
    ): self {
        return new self(
            $language,
            $speakerCount,
            $backgroundNoise,
            $backgroundMusic,
            $speechSpeed,
            $confidence,
        );
    }

    public function language(): string
    {
        return $this->language;
    }

    public function speakerCount(): int
    {
        return $this->speakerCount;
    }

    public function backgroundNoise(): AudioNoiseLevel
    {
        return $this->backgroundNoise;
    }

    public function backgroundMusic(): BackgroundMusic
    {
        return $this->backgroundMusic;
    }

    public function speechSpeed(): SpeechSpeed
    {
        return $this->speechSpeed;
    }

    public function confidence(): SpeechConfidence
    {
        return $this->confidence;
    }
}
