<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\Exception\InvalidVideoIntelligenceException;

final readonly class SpeechCharacteristics
{
    public function __construct(
        private VideoEmotion $dominantEmotion,
        private float $averageSpeakingRate,
        private int $pauseCount,
        private bool $hasOverlaps,
    ) {
        if ($this->averageSpeakingRate < 0) {
            throw new InvalidVideoIntelligenceException('Average speaking rate cannot be negative.');
        }

        if ($this->pauseCount < 0) {
            throw new InvalidVideoIntelligenceException('Pause count cannot be negative.');
        }
    }

    public static function create(
        VideoEmotion $dominantEmotion,
        float $averageSpeakingRate,
        int $pauseCount,
        bool $hasOverlaps,
    ): self {
        return new self($dominantEmotion, $averageSpeakingRate, $pauseCount, $hasOverlaps);
    }

    public function dominantEmotion(): VideoEmotion
    {
        return $this->dominantEmotion;
    }

    public function averageSpeakingRate(): float
    {
        return $this->averageSpeakingRate;
    }

    public function pauseCount(): int
    {
        return $this->pauseCount;
    }

    public function hasOverlaps(): bool
    {
        return $this->hasOverlaps;
    }
}
