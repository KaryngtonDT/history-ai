<?php

declare(strict_types=1);

namespace App\Domain\Scheduler;

use App\Domain\Scheduler\Exception\InvalidExecutionScheduleException;

final readonly class ResourceRequirement
{
    public function __construct(
        private ResourceType $type,
        private int $weight = 1,
    ) {
        if ($this->weight < 1) {
            throw new InvalidExecutionScheduleException('Resource requirement weight must be at least 1.');
        }
    }

    public static function create(ResourceType $type, int $weight = 1): self
    {
        return new self($type, $weight);
    }

    public function type(): ResourceType
    {
        return $this->type;
    }

    public function weight(): int
    {
        return $this->weight;
    }
}
