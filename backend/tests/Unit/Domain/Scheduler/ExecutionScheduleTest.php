<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Scheduler;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Scheduler\Exception\InvalidExecutionScheduleException;
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
use PHPUnit\Framework\TestCase;

final class ExecutionScheduleTest extends TestCase
{
    public function testCreateStoresScheduleFields(): void
    {
        $schedule = $this->createSchedule();

        self::assertTrue(ExecutionScheduleId::isValid($schedule->id()->value));
        self::assertSame(SchedulingStrategy::Balanced, $schedule->strategy());
        self::assertSame(6, $schedule->stages()->count());
        self::assertSame(360, $schedule->estimatedCompletionSeconds());
        self::assertSame(3, count($schedule->resources()));
        self::assertNotNull($schedule->resourceFor(ResourceType::Gpu));
    }

    public function testStagesMustBeOrdered(): void
    {
        $this->expectException(InvalidExecutionScheduleException::class);

        new ScheduledStageCollection([
            $this->stage(PipelineStageType::SpeechToText, 1, ResourceType::Gpu, 60, 1),
            $this->stage(PipelineStageType::Translation, 1, ResourceType::Cpu, 30, 1),
        ]);
    }

    public function testDuplicateStagesThrow(): void
    {
        $this->expectException(InvalidExecutionScheduleException::class);

        new ScheduledStageCollection([
            $this->stage(PipelineStageType::SpeechToText, 1, ResourceType::Gpu, 60, 1),
            $this->stage(PipelineStageType::SpeechToText, 2, ResourceType::Gpu, 60, 2),
        ]);
    }

    public function testWithProgressUpdatesCurrentStageAndResources(): void
    {
        $schedule = $this->createSchedule();
        $stages = $schedule->stages()->markStage(
            PipelineStageType::VoiceClone,
            ScheduledStageStatus::Running,
        );
        $resources = [
            ExecutionResource::create(ResourceType::Gpu, 1, 1, 1),
            ExecutionResource::create(ResourceType::Cpu, 0, 2, 2),
            ExecutionResource::create(ResourceType::Io, 0, 1, 4),
        ];

        $updated = $schedule->withProgress(
            PipelineStageType::VoiceClone,
            ResourceType::Gpu,
            $stages,
            $resources,
        );

        self::assertSame(PipelineStageType::VoiceClone, $updated->currentStage());
        self::assertSame(ResourceType::Gpu, $updated->currentResource());
        self::assertSame(
            ScheduledStageStatus::Running,
            $updated->stages()->forStage(PipelineStageType::VoiceClone)?->status(),
        );
    }

    private function createSchedule(): ExecutionSchedule
    {
        return ExecutionSchedule::create(
            ExecutionScheduleId::generate(),
            SchedulingStrategy::Balanced,
            new ScheduledStageCollection([
                $this->stage(PipelineStageType::SpeechToText, 1, ResourceType::Gpu, 60, 1),
                $this->stage(PipelineStageType::Translation, 2, ResourceType::Cpu, 30, 2),
                $this->stage(PipelineStageType::TextToSpeech, 3, ResourceType::Gpu, 45, 3),
                $this->stage(PipelineStageType::VoiceClone, 4, ResourceType::Gpu, 120, 3),
                $this->stage(PipelineStageType::LipSync, 5, ResourceType::Gpu, 90, 3),
                $this->stage(PipelineStageType::VideoRender, 6, ResourceType::Cpu, 90, 4, ResourceType::Io),
            ]),
            [
                ExecutionResource::create(ResourceType::Gpu, 0, 3, 1),
                ExecutionResource::create(ResourceType::Cpu, 0, 2, 2),
                ExecutionResource::create(ResourceType::Io, 0, 1, 4),
            ],
            360,
        );
    }

    private function stage(
        PipelineStageType $stage,
        int $order,
        ResourceType $primary,
        int $duration,
        int $group,
        ?ResourceType $secondary = null,
    ): ScheduledStage {
        $requirements = [ResourceRequirement::create($primary)];

        if (null !== $secondary) {
            $requirements[] = ResourceRequirement::create($secondary);
        }

        return ScheduledStage::create(
            $stage,
            $order,
            new ResourceRequirementCollection($requirements),
            $duration,
            $group,
        );
    }
}
