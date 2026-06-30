<?php

declare(strict_types=1);

namespace App\Domain\Orchestrator;

use App\Domain\Orchestrator\Exception\InvalidPipelineRecommendationException;

final readonly class VideoAnalysis
{
    public function __construct(
        private string $detectedLanguage,
        private float $durationSeconds,
        private string $resolution,
        private float $fps,
        private bool $gpuAvailable,
        private float $estimatedVramGb,
    ) {
        if ('' === trim($this->detectedLanguage)) {
            throw new InvalidPipelineRecommendationException('Detected language must not be empty.');
        }

        if ($this->durationSeconds < 0) {
            throw new InvalidPipelineRecommendationException('Duration cannot be negative.');
        }

        if ('' === trim($this->resolution)) {
            throw new InvalidPipelineRecommendationException('Resolution must not be empty.');
        }

        if ($this->fps <= 0) {
            throw new InvalidPipelineRecommendationException('FPS must be greater than zero.');
        }

        if ($this->estimatedVramGb < 0) {
            throw new InvalidPipelineRecommendationException('Estimated VRAM cannot be negative.');
        }
    }

    public static function create(
        string $detectedLanguage,
        float $durationSeconds,
        string $resolution,
        float $fps,
        bool $gpuAvailable,
        float $estimatedVramGb,
    ): self {
        return new self(
            $detectedLanguage,
            $durationSeconds,
            $resolution,
            $fps,
            $gpuAvailable,
            $estimatedVramGb,
        );
    }

    public function detectedLanguage(): string
    {
        return $this->detectedLanguage;
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

    public function gpuAvailable(): bool
    {
        return $this->gpuAvailable;
    }

    public function estimatedVramGb(): float
    {
        return $this->estimatedVramGb;
    }
}
