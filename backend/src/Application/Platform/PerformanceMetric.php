<?php

declare(strict_types=1);

namespace App\Application\Platform;

final readonly class PerformanceMetric
{
    public function __construct(
        public string $name,
        public int $durationMs,
    ) {
        if ($durationMs < 0) {
            throw new \InvalidArgumentException('Duration must be non-negative.');
        }
    }
}
