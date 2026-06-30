<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Scheduler;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Scheduler\ExecutionResource;
use App\Domain\Scheduler\ExecutionSchedule;
use App\Domain\Scheduler\ExecutionScheduleId;
use App\Domain\Scheduler\ResourceRequirement;
use App\Domain\Scheduler\ResourceRequirementCollection;
use App\Domain\Scheduler\ResourceType;
use App\Domain\Scheduler\ScheduledStage;
use App\Domain\Scheduler\ScheduledStageCollection;
use App\Domain\Scheduler\ScheduledStageStatus;
use App\Domain\Scheduler\SchedulingStrategy;
use App\Infrastructure\Scheduler\RuntimeExecutionScheduleContext;
use PHPUnit\Framework\TestCase;

final class RuntimeExecutionScheduleContextTest extends TestCase
{
    public function testUpdateStageTracksRunningAndCompletedProgress(): void
    {
        $context = new RuntimeExecutionScheduleContext();
        $context->set($this->sampleSchedule());

        $context->updateStage(PipelineStageType::SpeechToText, ScheduledStageStatus::Running);

        $running = $context->get();
        self::assertNotNull($running);
        self::assertSame(PipelineStageType::SpeechToText, $running->currentStage());
        self::assertSame(ResourceType::Gpu, $running->currentResource());
        self::assertSame(1, $running->resourceFor(ResourceType::Gpu)?->running());

        $context->updateStage(PipelineStageType::SpeechToText, ScheduledStageStatus::Completed);

        $completed = $context->get();
        self::assertNotNull($completed);
        self::assertSame(
            ScheduledStageStatus::Completed,
            $completed->stages()->forStage(PipelineStageType::SpeechToText)?->status(),
        );
        self::assertSame(0, $completed->resourceFor(ResourceType::Gpu)?->running());
    }

    public function testClearRemovesSchedule(): void
    {
        $context = new RuntimeExecutionScheduleContext();
        $context->set($this->sampleSchedule());
        $context->clear();

        self::assertNull($context->get());
    }

    private function sampleSchedule(): ExecutionSchedule
    {
        return ExecutionSchedule::create(
            ExecutionScheduleId::generate(),
            SchedulingStrategy::Balanced,
            new ScheduledStageCollection([
                ScheduledStage::create(
                    PipelineStageType::SpeechToText,
                    1,
                    new ResourceRequirementCollection([
                        ResourceRequirement::create(ResourceType::Gpu),
                    ]),
                    60,
                    1,
                ),
                ScheduledStage::create(
                    PipelineStageType::Translation,
                    2,
                    new ResourceRequirementCollection([
                        ResourceRequirement::create(ResourceType::Cpu),
                    ]),
                    30,
                    2,
                ),
            ]),
            [
                ExecutionResource::create(ResourceType::Gpu, 0, 1, 1),
                ExecutionResource::create(ResourceType::Cpu, 0, 1, 2),
            ],
            120,
        );
    }
}
