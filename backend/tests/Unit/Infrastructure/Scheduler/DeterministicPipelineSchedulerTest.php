<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Scheduler;

use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Optimization\OptimizationParameter;
use App\Domain\Optimization\OptimizationParameterCollection;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Optimization\OptimizationStageConfiguration;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Scheduler\ResourceType;
use App\Domain\Scheduler\ScheduledStageStatus;
use App\Domain\Scheduler\SchedulingStrategy;
use App\Domain\VideoIntelligence\AudioCharacteristics;
use App\Domain\VideoIntelligence\AudioNoiseLevel;
use App\Domain\VideoIntelligence\BackgroundMusic;
use App\Domain\VideoIntelligence\LightingCondition;
use App\Domain\VideoIntelligence\LipVisibility;
use App\Domain\VideoIntelligence\SpeechCharacteristics;
use App\Domain\VideoIntelligence\SpeechConfidence;
use App\Domain\VideoIntelligence\SpeechSpeed;
use App\Domain\VideoIntelligence\VideoEmotion;
use App\Domain\VideoIntelligence\VideoIntelligence;
use App\Domain\VideoIntelligence\VideoIntelligenceId;
use App\Domain\VideoIntelligence\VideoScene;
use App\Domain\VideoIntelligence\VideoSpeakerCollection;
use App\Domain\VideoIntelligence\VisualCharacteristics;
use App\Infrastructure\Scheduler\DeterministicPipelineScheduler;
use PHPUnit\Framework\TestCase;

final class DeterministicPipelineSchedulerTest extends TestCase
{
    private DeterministicPipelineScheduler $scheduler;

    protected function setUp(): void
    {
        $this->scheduler = new DeterministicPipelineScheduler();
    }

    public function testGpuStagesAreSequentialWithConcurrencyOne(): void
    {
        $schedule = $this->scheduler->schedule(
            $this->sampleIntelligence(),
            $this->sampleOptimization(OptimizationProfile::Balanced),
        );

        $gpu = $schedule->resourceFor(ResourceType::Gpu);
        self::assertNotNull($gpu);
        self::assertSame(1, $gpu->maxConcurrency());
        self::assertSame(4, $gpu->pending());

        $gpuGroups = [];

        foreach ($schedule->stages()->all() as $stage) {
            if (ResourceType::Gpu === $stage->primaryResource()) {
                $gpuGroups[] = $stage->parallelGroup();
            }
        }

        self::assertSame([1, 2, 3, 4], $gpuGroups);
    }

    public function testBalancedStrategyAllowsLimitedCpuParallelism(): void
    {
        $schedule = $this->scheduler->schedule(
            $this->sampleIntelligence(),
            $this->sampleOptimization(OptimizationProfile::Balanced),
        );

        $cpu = $schedule->resourceFor(ResourceType::Cpu);
        self::assertNotNull($cpu);
        self::assertSame(2, $cpu->maxConcurrency());
        self::assertSame(SchedulingStrategy::Balanced, $schedule->strategy());
    }

    public function testLowMemoryStrategyReducesConcurrency(): void
    {
        $schedule = $this->scheduler->schedule(
            $this->sampleIntelligence(),
            $this->sampleOptimization(OptimizationProfile::LowMemory),
        );

        self::assertSame(SchedulingStrategy::LowMemory, $schedule->strategy());
        self::assertSame(1, $schedule->resourceFor(ResourceType::Gpu)?->maxConcurrency());
        self::assertSame(1, $schedule->resourceFor(ResourceType::Cpu)?->maxConcurrency());
        self::assertSame(2, $schedule->resourceFor(ResourceType::Io)?->maxConcurrency());
    }

    public function testStageOrderIsPreserved(): void
    {
        $schedule = $this->scheduler->schedule(
            $this->sampleIntelligence(),
            $this->sampleOptimization(OptimizationProfile::Balanced),
        );

        $expected = [
            PipelineStageType::SpeechToText,
            PipelineStageType::Translation,
            PipelineStageType::TextToSpeech,
            PipelineStageType::VoiceClone,
            PipelineStageType::LipSync,
            PipelineStageType::VideoRender,
        ];

        $actual = array_map(
            static fn ($stage) => $stage->stage(),
            $schedule->stages()->all(),
        );

        self::assertSame($expected, $actual);
    }

    public function testLongGpuTasksIncreaseEstimatedDuration(): void
    {
        $longVideo = VideoIntelligence::create(
            VideoIntelligenceId::generate(),
            2000.0,
            VideoScene::Interview,
            AudioCharacteristics::create('english', 1, AudioNoiseLevel::Low, BackgroundMusic::NotDetected, SpeechSpeed::Normal, SpeechConfidence::create(90)),
            VisualCharacteristics::create('1920x1080', 30.0, LightingCondition::Good, LipVisibility::Excellent, 1),
            SpeechCharacteristics::create(VideoEmotion::Neutral, 140.0, 5, false),
            VideoSpeakerCollection::empty(),
            true,
            8.0,
        );

        $shortSchedule = $this->scheduler->schedule(
            $this->sampleIntelligence(),
            $this->sampleOptimization(OptimizationProfile::Balanced),
        );
        $longSchedule = $this->scheduler->schedule(
            $longVideo,
            $this->sampleOptimization(OptimizationProfile::Balanced),
        );

        $shortStt = $shortSchedule->stages()->forStage(PipelineStageType::SpeechToText);
        $longStt = $longSchedule->stages()->forStage(PipelineStageType::SpeechToText);

        self::assertNotNull($shortStt);
        self::assertNotNull($longStt);
        self::assertGreaterThan($shortStt->estimatedDurationSeconds(), $longStt->estimatedDurationSeconds());
        self::assertGreaterThan($shortSchedule->estimatedCompletionSeconds(), $longSchedule->estimatedCompletionSeconds());
    }

    public function testFailedStageDoesNotCorruptSchedule(): void
    {
        $schedule = $this->scheduler->schedule(
            $this->sampleIntelligence(),
            $this->sampleOptimization(OptimizationProfile::Balanced),
        );
        $failedStages = $schedule->stages()->markStage(
            PipelineStageType::VoiceClone,
            ScheduledStageStatus::Failed,
        );

        $updated = $schedule->withProgress(
            PipelineStageType::VoiceClone,
            ResourceType::Gpu,
            $failedStages,
            $schedule->resources(),
        );

        self::assertSame($schedule->id()->value, $updated->id()->value);
        self::assertSame(6, $updated->stages()->count());
        self::assertSame(
            ScheduledStageStatus::Failed,
            $updated->stages()->forStage(PipelineStageType::VoiceClone)?->status(),
        );
        self::assertSame(
            ScheduledStageStatus::Pending,
            $updated->stages()->forStage(PipelineStageType::Translation)?->status(),
        );
    }

    private function sampleIntelligence(): VideoIntelligence
    {
        return VideoIntelligence::create(
            VideoIntelligenceId::generate(),
            120.0,
            VideoScene::Interview,
            AudioCharacteristics::create('english', 1, AudioNoiseLevel::Low, BackgroundMusic::NotDetected, SpeechSpeed::Normal, SpeechConfidence::create(90)),
            VisualCharacteristics::create('1920x1080', 30.0, LightingCondition::Good, LipVisibility::Excellent, 1),
            SpeechCharacteristics::create(VideoEmotion::Neutral, 140.0, 5, false),
            VideoSpeakerCollection::empty(),
            true,
            8.0,
        );
    }

    private function sampleOptimization(OptimizationProfile $profile): ExecutionOptimization
    {
        $stages = [];

        foreach (OptimizationStage::all() as $stage) {
            $stages[] = OptimizationStageConfiguration::create(
                $stage,
                new OptimizationParameterCollection([
                    OptimizationParameter::create('mode', 'default'),
                ]),
            );
        }

        return ExecutionOptimization::create(
            ExecutionOptimizationId::generate(),
            $profile,
            new OptimizationStageCollection($stages),
            'Balanced optimization.',
            4,
        );
    }
}
