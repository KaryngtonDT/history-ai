<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\Exception\InvalidVideoIntelligenceException;

final readonly class VideoAnalyzerInput
{
    public function __construct(
        private string $language,
        private float $durationSeconds,
        private string $resolution,
        private float $fps,
        private int $segmentCount,
        private string $transcriptText,
        private bool $gpuAvailable,
        private float $estimatedVramGb,
        private bool $hasSlidesHint = false,
    ) {
        if ('' === trim($this->language)) {
            throw new InvalidVideoIntelligenceException('Language must not be empty.');
        }

        if ($this->durationSeconds < 0) {
            throw new InvalidVideoIntelligenceException('Duration cannot be negative.');
        }

        if ('' === trim($this->resolution)) {
            throw new InvalidVideoIntelligenceException('Resolution must not be empty.');
        }

        if ($this->fps <= 0) {
            throw new InvalidVideoIntelligenceException('FPS must be greater than zero.');
        }

        if ($this->segmentCount < 0) {
            throw new InvalidVideoIntelligenceException('Segment count cannot be negative.');
        }

        if ($this->estimatedVramGb < 0) {
            throw new InvalidVideoIntelligenceException('Estimated VRAM cannot be negative.');
        }
    }

    public static function create(
        string $language,
        float $durationSeconds,
        string $resolution,
        float $fps,
        int $segmentCount,
        string $transcriptText,
        bool $gpuAvailable,
        float $estimatedVramGb,
        bool $hasSlidesHint = false,
    ): self {
        return new self(
            $language,
            $durationSeconds,
            $resolution,
            $fps,
            $segmentCount,
            $transcriptText,
            $gpuAvailable,
            $estimatedVramGb,
            $hasSlidesHint,
        );
    }

    public function language(): string
    {
        return $this->language;
    }

    public function durationSeconds(): float
    {
        return $this->durationSeconds;
    }

    public function resolution(): string
    {
        return $this->resolution;
    }

    public function fps(): float
    {
        return $this->fps;
    }

    public function segmentCount(): int
    {
        return $this->segmentCount;
    }

    public function transcriptText(): string
    {
        return $this->transcriptText;
    }

    public function gpuAvailable(): bool
    {
        return $this->gpuAvailable;
    }

    public function estimatedVramGb(): float
    {
        return $this->estimatedVramGb;
    }

    public function hasSlidesHint(): bool
    {
        return $this->hasSlidesHint;
    }
}
