<?php

declare(strict_types=1);

namespace App\Domain\Orchestrator;

use App\Domain\Orchestrator\Exception\InvalidPipelineRecommendationException;
use App\Domain\Pipeline\PipelineConfiguration;

final readonly class PipelineRecommendation
{
    public function __construct(
        private PipelineRecommendationId $id,
        private ProcessingStrategy $strategy,
        private PipelineConfiguration $pipelineConfiguration,
        private string $explanation,
        private int $estimatedDurationSeconds,
        private int $estimatedQuality,
        private float $estimatedVramGb,
    ) {
        if ('' === trim($this->explanation)) {
            throw new InvalidPipelineRecommendationException('Recommendation explanation must not be empty.');
        }

        if ($this->estimatedDurationSeconds < 0) {
            throw new InvalidPipelineRecommendationException('Estimated duration cannot be negative.');
        }

        if ($this->estimatedQuality < 1 || $this->estimatedQuality > 5) {
            throw new InvalidPipelineRecommendationException('Estimated quality must be between 1 and 5.');
        }

        if ($this->estimatedVramGb < 0) {
            throw new InvalidPipelineRecommendationException('Estimated VRAM cannot be negative.');
        }
    }

    public static function create(
        PipelineRecommendationId $id,
        ProcessingStrategy $strategy,
        PipelineConfiguration $pipelineConfiguration,
        string $explanation,
        int $estimatedDurationSeconds,
        int $estimatedQuality,
        float $estimatedVramGb,
    ): self {
        return new self(
            $id,
            $strategy,
            $pipelineConfiguration,
            $explanation,
            $estimatedDurationSeconds,
            $estimatedQuality,
            $estimatedVramGb,
        );
    }

    public function id(): PipelineRecommendationId
    {
        return $this->id;
    }

    public function strategy(): ProcessingStrategy
    {
        return $this->strategy;
    }

    public function pipelineConfiguration(): PipelineConfiguration
    {
        return $this->pipelineConfiguration;
    }

    public function explanation(): string
    {
        return $this->explanation;
    }

    public function estimatedDurationSeconds(): int
    {
        return $this->estimatedDurationSeconds;
    }

    public function estimatedQuality(): int
    {
        return $this->estimatedQuality;
    }

    public function estimatedVramGb(): float
    {
        return $this->estimatedVramGb;
    }
}
