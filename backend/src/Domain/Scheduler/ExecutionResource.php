<?php

declare(strict_types=1);

namespace App\Domain\Scheduler;

use App\Domain\Scheduler\Exception\InvalidExecutionScheduleException;

final readonly class ExecutionResource
{
    public function __construct(
        private ResourceType $type,
        private int $running,
        private int $pending,
        private int $maxConcurrency,
    ) {
        if ($this->running < 0) {
            throw new InvalidExecutionScheduleException('Running resource count cannot be negative.');
        }

        if ($this->pending < 0) {
            throw new InvalidExecutionScheduleException('Pending resource count cannot be negative.');
        }

        if ($this->maxConcurrency < 1) {
            throw new InvalidExecutionScheduleException('Max concurrency must be at least 1.');
        }

        if ($this->running > $this->maxConcurrency) {
            throw new InvalidExecutionScheduleException('Running count cannot exceed max concurrency.');
        }
    }

    public static function create(
        ResourceType $type,
        int $running,
        int $pending,
        int $maxConcurrency,
    ): self {
        return new self($type, $running, $pending, $maxConcurrency);
    }

    public function type(): ResourceType
    {
        return $this->type;
    }

    public function running(): int
    {
        return $this->running;
    }

    public function pending(): int
    {
        return $this->pending;
    }

    public function maxConcurrency(): int
    {
        return $this->maxConcurrency;
    }

    public function withCounts(int $running, int $pending): self
    {
        return new self($this->type, $running, $pending, $this->maxConcurrency);
    }
}
