<?php

declare(strict_types=1);

namespace App\Infrastructure\Scheduler;

use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Scheduler\ExecutionResource;
use App\Domain\Scheduler\ExecutionSchedule;
use App\Domain\Scheduler\ExecutionScheduleId;
use App\Domain\Scheduler\PipelineSchedulerInterface;
use App\Domain\Scheduler\ResourceRequirement;
use App\Domain\Scheduler\ResourceRequirementCollection;
use App\Domain\Scheduler\ResourceType;
use App\Domain\Scheduler\ScheduledStage;
use App\Domain\Scheduler\ScheduledStageCollection;
use App\Domain\Scheduler\SchedulingStrategy;
use App\Domain\VideoIntelligence\VideoIntelligence;

final class DeterministicPipelineScheduler implements PipelineSchedulerInterface
{
    private const float LONG_DURATION_SECONDS = 1800.0;

    public function schedule(
        VideoIntelligence $intelligence,
        ExecutionOptimization $optimization,
    ): ExecutionSchedule {
        $strategy = $this->resolveStrategy($optimization);
        $limits = $this->concurrencyLimits($strategy);
        $stages = $this->buildStages($intelligence, $optimization, $strategy);
        $resources = $this->buildResources($stages, $limits);

        return ExecutionSchedule::create(
            ExecutionScheduleId::generate(),
            $strategy,
            new ScheduledStageCollection($stages),
            $resources,
            $this->estimateCompletion($stages, $strategy),
        );
    }

    private function resolveStrategy(ExecutionOptimization $optimization): SchedulingStrategy
    {
        return match ($optimization->profile()) {
            OptimizationProfile::Quality => SchedulingStrategy::Quality,
            OptimizationProfile::Speed => SchedulingStrategy::Speed,
            OptimizationProfile::LowMemory => SchedulingStrategy::LowMemory,
            OptimizationProfile::Balanced => SchedulingStrategy::Balanced,
        };
    }

    /**
     * @return array{gpu: int, cpu: int, io: int}
     */
    private function concurrencyLimits(SchedulingStrategy $strategy): array
    {
        return match ($strategy) {
            SchedulingStrategy::LowMemory => ['gpu' => 1, 'cpu' => 1, 'io' => 2],
            SchedulingStrategy::Speed => ['gpu' => 1, 'cpu' => 3, 'io' => 4],
            SchedulingStrategy::Quality => ['gpu' => 1, 'cpu' => 1, 'io' => 2],
            SchedulingStrategy::Balanced => ['gpu' => 1, 'cpu' => 2, 'io' => 4],
        };
    }

    /**
     * @return list<ScheduledStage>
     */
    private function buildStages(
        VideoIntelligence $intelligence,
        ExecutionOptimization $optimization,
        SchedulingStrategy $strategy,
    ): array {
        $order = 1;
        $gpuGroup = 1;
        $cpuGroup = 1;
        $stages = [];

        foreach ($this->stageDefinitions() as $definition) {
            if (null === $optimization->stages()->forStage($definition['optimization'])) {
                continue;
            }

            $duration = $this->estimateDuration(
                $definition['pipeline'],
                $intelligence,
                $strategy,
            );
            $parallelGroup = ResourceType::Gpu === $definition['primary']
                ? $gpuGroup++
                : $this->resolveCpuParallelGroup($definition['pipeline'], $strategy, $cpuGroup);

            if (ResourceType::Cpu === $definition['primary']) {
                ++$cpuGroup;
            }

            $stages[] = ScheduledStage::create(
                $definition['pipeline'],
                $order++,
                new ResourceRequirementCollection($definition['requirements']),
                $duration,
                $parallelGroup,
            );
        }

        return $stages;
    }

    /**
     * @return list<array{
     *     pipeline: PipelineStageType,
     *     optimization: OptimizationStage,
     *     primary: ResourceType,
     *     requirements: list<ResourceRequirement>
     * }>
     */
    private function stageDefinitions(): array
    {
        return [
            [
                'pipeline' => PipelineStageType::SpeechToText,
                'optimization' => OptimizationStage::SpeechToText,
                'primary' => ResourceType::Gpu,
                'requirements' => [ResourceRequirement::create(ResourceType::Gpu)],
            ],
            [
                'pipeline' => PipelineStageType::Translation,
                'optimization' => OptimizationStage::Translation,
                'primary' => ResourceType::Cpu,
                'requirements' => [ResourceRequirement::create(ResourceType::Cpu)],
            ],
            [
                'pipeline' => PipelineStageType::TextToSpeech,
                'optimization' => OptimizationStage::TextToSpeech,
                'primary' => ResourceType::Gpu,
                'requirements' => [ResourceRequirement::create(ResourceType::Gpu)],
            ],
            [
                'pipeline' => PipelineStageType::VoiceClone,
                'optimization' => OptimizationStage::VoiceClone,
                'primary' => ResourceType::Gpu,
                'requirements' => [ResourceRequirement::create(ResourceType::Gpu)],
            ],
            [
                'pipeline' => PipelineStageType::LipSync,
                'optimization' => OptimizationStage::LipSync,
                'primary' => ResourceType::Gpu,
                'requirements' => [ResourceRequirement::create(ResourceType::Gpu)],
            ],
            [
                'pipeline' => PipelineStageType::VideoRender,
                'optimization' => OptimizationStage::VideoRender,
                'primary' => ResourceType::Cpu,
                'requirements' => [
                    ResourceRequirement::create(ResourceType::Cpu),
                    ResourceRequirement::create(ResourceType::Io),
                ],
            ],
        ];
    }

    private function resolveCpuParallelGroup(
        PipelineStageType $stage,
        SchedulingStrategy $strategy,
        int $cpuGroup,
    ): int {
        if (SchedulingStrategy::Balanced === $strategy && PipelineStageType::Translation === $stage) {
            return $cpuGroup;
        }

        if (SchedulingStrategy::Balanced === $strategy && PipelineStageType::VideoRender === $stage) {
            return $cpuGroup;
        }

        return $cpuGroup;
    }

    private function estimateDuration(
        PipelineStageType $stage,
        VideoIntelligence $intelligence,
        SchedulingStrategy $strategy,
    ): int {
        $duration = $intelligence->durationSeconds();
        $longMultiplier = $duration > self::LONG_DURATION_SECONDS ? 1.5 : 1.0;
        $qualityMultiplier = SchedulingStrategy::Quality === $strategy ? 1.25 : 1.0;

        $base = match ($stage) {
            PipelineStageType::SpeechToText => max(30, (int) ceil($duration / 10)),
            PipelineStageType::Translation => max(20, (int) ceil($duration / 20)),
            PipelineStageType::TextToSpeech => max(30, (int) ceil($duration / 15)),
            PipelineStageType::VoiceClone => max(60, (int) ceil($duration / 8)),
            PipelineStageType::LipSync => max(60, (int) ceil($duration / 6)),
            PipelineStageType::VideoRender => max(45, (int) ceil($duration / 12)),
        };

        if (PipelineStageType::VoiceClone === $stage && $intelligence->audio()->speakerCount() >= 2) {
            $base = (int) ceil($base * 1.2);
        }

        return (int) ceil($base * $longMultiplier * $qualityMultiplier);
    }

    /**
     * @param list<ScheduledStage> $stages
     * @param array{gpu: int, cpu: int, io: int} $limits
     * @return list<ExecutionResource>
     */
    private function buildResources(array $stages, array $limits): array
    {
        $pending = [
            ResourceType::Gpu->value => 0,
            ResourceType::Cpu->value => 0,
            ResourceType::Io->value => 0,
        ];

        foreach ($stages as $stage) {
            foreach ($stage->requirements()->types() as $type) {
                ++$pending[$type->value];
            }
        }

        return [
            ExecutionResource::create(ResourceType::Gpu, 0, $pending[ResourceType::Gpu->value], $limits['gpu']),
            ExecutionResource::create(ResourceType::Cpu, 0, $pending[ResourceType::Cpu->value], $limits['cpu']),
            ExecutionResource::create(ResourceType::Io, 0, $pending[ResourceType::Io->value], $limits['io']),
        ];
    }

    /**
     * @param list<ScheduledStage> $stages
     */
    private function estimateCompletion(array $stages, SchedulingStrategy $strategy): int
    {
        $gpuTotal = 0;
        $cpuTotal = 0;
        $ioTotal = 0;

        foreach ($stages as $stage) {
            $duration = $stage->estimatedDurationSeconds();

            if (ResourceType::Gpu === $stage->primaryResource()) {
                $gpuTotal += $duration;
                continue;
            }

            if (ResourceType::Cpu === $stage->primaryResource()) {
                $cpuTotal += $duration;

                if ($stage->requirements()->types() !== [ResourceType::Cpu]) {
                    $ioTotal += (int) ceil($duration * 0.5);
                }
            }
        }

        if (SchedulingStrategy::Balanced === $strategy) {
            $cpuTotal = (int) ceil($cpuTotal / 2);
        }

        return max(60, $gpuTotal + $cpuTotal + $ioTotal);
    }
}
