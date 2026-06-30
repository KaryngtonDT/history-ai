<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Scheduler;

use App\Domain\Scheduler\Exception\InvalidExecutionScheduleException;
use App\Domain\Scheduler\ExecutionResource;
use App\Domain\Scheduler\ResourceType;
use PHPUnit\Framework\TestCase;

final class ExecutionResourceTest extends TestCase
{
    public function testCreateStoresQueueMetrics(): void
    {
        $resource = ExecutionResource::create(ResourceType::Gpu, 1, 2, 1);

        self::assertSame(ResourceType::Gpu, $resource->type());
        self::assertSame(1, $resource->running());
        self::assertSame(2, $resource->pending());
        self::assertSame(1, $resource->maxConcurrency());
    }

    public function testWithCountsReturnsUpdatedResource(): void
    {
        $resource = ExecutionResource::create(ResourceType::Cpu, 0, 4, 2)
            ->withCounts(2, 1);

        self::assertSame(2, $resource->running());
        self::assertSame(1, $resource->pending());
    }

    public function testRunningCannotExceedMaxConcurrency(): void
    {
        $this->expectException(InvalidExecutionScheduleException::class);

        ExecutionResource::create(ResourceType::Gpu, 2, 0, 1);
    }
}
