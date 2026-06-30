<?php

declare(strict_types=1);

namespace App\Domain\Optimization;

use App\Domain\Optimization\Exception\InvalidExecutionOptimizationException;

final readonly class OptimizationStageCollection
{
    /** @var list<OptimizationStageConfiguration> */
    private array $stages;

    /**
     * @param list<OptimizationStageConfiguration> $stages
     */
    public function __construct(array $stages)
    {
        $seen = [];

        foreach ($stages as $stage) {
            $key = $stage->stage()->value;

            if (isset($seen[$key])) {
                throw new InvalidExecutionOptimizationException(sprintf(
                    'Duplicate optimization stage "%s".',
                    $key,
                ));
            }

            $seen[$key] = true;
        }

        $this->stages = array_values($stages);
    }

    /**
     * @return list<OptimizationStageConfiguration>
     */
    public function all(): array
    {
        return $this->stages;
    }

    public function count(): int
    {
        return count($this->stages);
    }

    public function forStage(OptimizationStage $stage): ?OptimizationStageConfiguration
    {
        foreach ($this->stages as $configuration) {
            if ($configuration->stage() === $stage) {
                return $configuration;
            }
        }

        return null;
    }
}
