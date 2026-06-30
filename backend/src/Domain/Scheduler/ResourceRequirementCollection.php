<?php

declare(strict_types=1);

namespace App\Domain\Scheduler;

use App\Domain\Scheduler\Exception\InvalidExecutionScheduleException;

final readonly class ResourceRequirementCollection
{
    /** @var list<ResourceRequirement> */
    private array $requirements;

    /**
     * @param list<ResourceRequirement> $requirements
     */
    public function __construct(array $requirements)
    {
        if ([] === $requirements) {
            throw new InvalidExecutionScheduleException('Resource requirements cannot be empty.');
        }

        $this->requirements = array_values($requirements);
    }

    /**
     * @return list<ResourceRequirement>
     */
    public function all(): array
    {
        return $this->requirements;
    }

    public function count(): int
    {
        return count($this->requirements);
    }

    public function primary(): ResourceRequirement
    {
        return $this->requirements[0];
    }

    /**
     * @return list<ResourceType>
     */
    public function types(): array
    {
        return array_map(
            static fn (ResourceRequirement $requirement): ResourceType => $requirement->type(),
            $this->requirements,
        );
    }
}
