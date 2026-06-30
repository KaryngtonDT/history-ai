<?php

declare(strict_types=1);

namespace App\Infrastructure\Optimization;

use App\Domain\Optimization\ExecutionOptimization;

final class RuntimeExecutionOptimizationContext implements \App\Domain\Optimization\RuntimeExecutionOptimizationContextInterface
{
    private ?ExecutionOptimization $optimization = null;

    public function set(?ExecutionOptimization $optimization): void
    {
        $this->optimization = $optimization;
    }

    public function get(): ?ExecutionOptimization
    {
        return $this->optimization;
    }

    public function clear(): void
    {
        $this->optimization = null;
    }
}
