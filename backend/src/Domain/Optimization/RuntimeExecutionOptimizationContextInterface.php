<?php

declare(strict_types=1);

namespace App\Domain\Optimization;

interface RuntimeExecutionOptimizationContextInterface
{
    public function set(?ExecutionOptimization $optimization): void;

    public function get(): ?ExecutionOptimization;

    public function clear(): void;
}
