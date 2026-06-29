<?php

declare(strict_types=1);

namespace App\Application\Platform;

final class PerformanceTimer
{
    private ?float $startedAt = null;

    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function start(): void
    {
        $this->startedAt = $this->clock->now();
    }

    public function stop(string $name): PerformanceMetric
    {
        if (null === $this->startedAt) {
            throw new \LogicException('Performance timer was not started.');
        }

        $durationMs = max(0, (int) round(($this->clock->now() - $this->startedAt) * 1000));
        $this->startedAt = null;

        return new PerformanceMetric($name, $durationMs);
    }
}
