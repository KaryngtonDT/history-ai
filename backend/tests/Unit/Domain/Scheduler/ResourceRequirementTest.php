<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Scheduler;

use App\Domain\Scheduler\Exception\InvalidExecutionScheduleException;
use App\Domain\Scheduler\ResourceRequirement;
use App\Domain\Scheduler\ResourceRequirementCollection;
use App\Domain\Scheduler\ResourceType;
use PHPUnit\Framework\TestCase;

final class ResourceRequirementTest extends TestCase
{
    public function testCreateStoresTypeAndWeight(): void
    {
        $requirement = ResourceRequirement::create(ResourceType::Gpu, 2);

        self::assertSame(ResourceType::Gpu, $requirement->type());
        self::assertSame(2, $requirement->weight());
    }

    public function testCollectionExposesPrimaryAndTypes(): void
    {
        $collection = new ResourceRequirementCollection([
            ResourceRequirement::create(ResourceType::Cpu),
            ResourceRequirement::create(ResourceType::Io),
        ]);

        self::assertSame(ResourceType::Cpu, $collection->primary()->type());
        self::assertSame([ResourceType::Cpu, ResourceType::Io], $collection->types());
    }

    public function testEmptyCollectionThrows(): void
    {
        $this->expectException(InvalidExecutionScheduleException::class);

        new ResourceRequirementCollection([]);
    }

    public function testInvalidWeightThrows(): void
    {
        $this->expectException(InvalidExecutionScheduleException::class);

        ResourceRequirement::create(ResourceType::Cpu, 0);
    }
}
