<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Estimation;

final readonly class HardwareAwareEstimateResolver
{
    public function __construct(
        private readonly bool $hasGpu,
    ) {
    }

    public function hasGpu(): bool
    {
        return $this->hasGpu;
    }
}
