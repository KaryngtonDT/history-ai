<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Scheduler;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Scheduler\Exception\InvalidExecutionScheduleException;
use App\Domain\Scheduler\ResourceRequirement;
use App\Domain\Scheduler\ResourceRequirementCollection;
use App\Domain\Scheduler\ResourceType;
use App\Domain\Scheduler\ScheduledStage;
use App\Domain\Scheduler\ScheduledStageStatus;
use PHPUnit\Framework\TestCase;

final class ScheduledStageTest extends TestCase
{
    public function testCreateStoresStageMetadata(): void
    {
        $stage = ScheduledStage::create(
            PipelineStageType::VoiceClone,
            4,
            new ResourceRequirementCollection([
                ResourceRequirement::create(ResourceType::Gpu),
            ]),
            120,
            3,
        );

        self::assertSame(PipelineStageType::VoiceClone, $stage->stage());
        self::assertSame(4, $stage->order());
        self::assertSame(120, $stage->estimatedDurationSeconds());
        self::assertSame(3, $stage->parallelGroup());
        self::assertSame(ScheduledStageStatus::Pending, $stage->status());
        self::assertSame(ResourceType::Gpu, $stage->primaryResource());
    }

    public function testVideoRenderSupportsCpuAndIoRequirements(): void
    {
        $stage = ScheduledStage::create(
            PipelineStageType::VideoRender,
            6,
            new ResourceRequirementCollection([
                ResourceRequirement::create(ResourceType::Cpu),
                ResourceRequirement::create(ResourceType::Io),
            ]),
            90,
            4,
        );

        self::assertSame(2, $stage->requirements()->count());
        self::assertSame(ResourceType::Cpu, $stage->primaryResource());
    }

    public function testWithStatusReturnsImmutableCopy(): void
    {
        $stage = ScheduledStage::create(
            PipelineStageType::Translation,
            2,
            new ResourceRequirementCollection([
                ResourceRequirement::create(ResourceType::Cpu),
            ]),
            45,
            2,
        )->withStatus(ScheduledStageStatus::Running);

        self::assertSame(ScheduledStageStatus::Running, $stage->status());
    }

    public function testInvalidOrderThrows(): void
    {
        $this->expectException(InvalidExecutionScheduleException::class);

        ScheduledStage::create(
            PipelineStageType::Translation,
            0,
            new ResourceRequirementCollection([
                ResourceRequirement::create(ResourceType::Cpu),
            ]),
            45,
            1,
        );
    }
}
