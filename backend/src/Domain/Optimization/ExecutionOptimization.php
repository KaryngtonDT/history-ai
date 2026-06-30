<?php

declare(strict_types=1);

namespace App\Domain\Optimization;

use App\Domain\Optimization\Exception\InvalidExecutionOptimizationException;

final readonly class ExecutionOptimization
{
    /**
     * @param list<string> $explanations
     */
    public function __construct(
        private ExecutionOptimizationId $id,
        private OptimizationProfile $profile,
        private OptimizationStageCollection $stages,
        private string $summary,
        private int $estimatedImpact,
        private array $explanations = [],
    ) {
        if ('' === trim($this->summary)) {
            throw new InvalidExecutionOptimizationException('Optimization summary must not be empty.');
        }

        if ($this->estimatedImpact < 1 || $this->estimatedImpact > 5) {
            throw new InvalidExecutionOptimizationException('Estimated impact must be between 1 and 5.');
        }
    }

    /**
     * @param list<string> $explanations
     */
    public static function create(
        ExecutionOptimizationId $id,
        OptimizationProfile $profile,
        OptimizationStageCollection $stages,
        string $summary,
        int $estimatedImpact,
        array $explanations = [],
    ): self {
        return new self(
            $id,
            $profile,
            $stages,
            $summary,
            $estimatedImpact,
            array_values($explanations),
        );
    }

    public function id(): ExecutionOptimizationId
    {
        return $this->id;
    }

    public function profile(): OptimizationProfile
    {
        return $this->profile;
    }

    public function stages(): OptimizationStageCollection
    {
        return $this->stages;
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function estimatedImpact(): int
    {
        return $this->estimatedImpact;
    }

    /**
     * @return list<string>
     */
    public function explanations(): array
    {
        return $this->explanations;
    }
}
