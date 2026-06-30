<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\Exception\InvalidVideoIntelligenceException;

final readonly class VisualCharacteristics
{
    public function __construct(
        private string $resolution,
        private float $fps,
        private LightingCondition $lighting,
        private LipVisibility $lipVisibility,
        private int $faceCount,
    ) {
        if ('' === trim($this->resolution)) {
            throw new InvalidVideoIntelligenceException('Resolution must not be empty.');
        }

        if ($this->fps <= 0) {
            throw new InvalidVideoIntelligenceException('FPS must be greater than zero.');
        }

        if ($this->faceCount < 0) {
            throw new InvalidVideoIntelligenceException('Face count cannot be negative.');
        }
    }

    public static function create(
        string $resolution,
        float $fps,
        LightingCondition $lighting,
        LipVisibility $lipVisibility,
        int $faceCount,
    ): self {
        return new self($resolution, $fps, $lighting, $lipVisibility, $faceCount);
    }

    public function resolution(): string
    {
        return $this->resolution;
    }

    public function fps(): float
    {
        return $this->fps;
    }

    public function lighting(): LightingCondition
    {
        return $this->lighting;
    }

    public function lipVisibility(): LipVisibility
    {
        return $this->lipVisibility;
    }

    public function faceCount(): int
    {
        return $this->faceCount;
    }
}
