<?php

declare(strict_types=1);

namespace App\Domain\Optimization;

final readonly class OptimizationStageConfiguration
{
    public function __construct(
        private OptimizationStage $stage,
        private OptimizationParameterCollection $parameters,
    ) {
    }

    public static function create(
        OptimizationStage $stage,
        OptimizationParameterCollection $parameters,
    ): self {
        return new self($stage, $parameters);
    }

    public function stage(): OptimizationStage
    {
        return $this->stage;
    }

    public function parameters(): OptimizationParameterCollection
    {
        return $this->parameters;
    }
}
