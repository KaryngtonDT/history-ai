<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Scheduler\Handlers;

use App\Application\Scheduler\Handlers\GetExecutionScheduleHandler;
use App\Application\Scheduler\Queries\GetExecutionScheduleQuery;
use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Optimization\OptimizationParameter;
use App\Domain\Optimization\OptimizationParameterCollection;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Optimization\OptimizationStageConfiguration;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Scheduler\ExecutionResource;
use App\Domain\Scheduler\ExecutionSchedule;
use App\Domain\Scheduler\ExecutionScheduleId;
use App\Domain\Scheduler\PipelineSchedulerInterface;
use App\Domain\Scheduler\ResourceRequirement;
use App\Domain\Scheduler\ResourceRequirementCollection;
use App\Domain\Scheduler\ResourceType;
use App\Domain\Scheduler\RuntimeExecutionScheduleContextInterface;
use App\Domain\Scheduler\ScheduledStage;
use App\Domain\Scheduler\ScheduledStageCollection;
use App\Domain\Scheduler\SchedulingStrategy;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
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
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;
use App\Domain\VideoIntelligence\VideoIntelligenceId;
use App\Domain\VideoIntelligence\VideoScene;
use App\Domain\VideoIntelligence\VideoSpeakerCollection;
use App\Domain\VideoIntelligence\VisualCharacteristics;
use PHPUnit\Framework\TestCase;

final class GetExecutionScheduleHandlerTest extends TestCase
{
    public function testReturnsRuntimeScheduleWhenAvailable(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $job = VideoJob::createUploaded($videoId, 'clip.mp4', VideoLanguage::English);
        $runtimeSchedule = $this->sampleSchedule(PipelineStageType::VoiceClone, ResourceType::Gpu);

        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn($job);

        $runtimeContext = $this->createMock(RuntimeExecutionScheduleContextInterface::class);
        $runtimeContext->method('get')->willReturn($runtimeSchedule);

        $handler = new GetExecutionScheduleHandler(
            $videoRepository,
            $this->createMock(VideoIntelligenceFactoryInterface::class),
            $this->createMock(ExecutionOptimizerInterface::class),
            $this->createMock(PipelineSchedulerInterface::class),
            $runtimeContext,
        );

        $result = $handler(new GetExecutionScheduleQuery($videoId->value));

        self::assertSame('voice_clone', $result->currentStage);
        self::assertSame('gpu', $result->currentResource);
    }

    public function testGeneratesScheduleWhenRuntimeContextIsEmpty(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $job = VideoJob::createUploaded($videoId, 'clip.mp4', VideoLanguage::English);
        $intelligence = $this->intelligence();
        $optimization = $this->optimization();
        $schedule = $this->sampleSchedule(null, null);

        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn($job);

        $intelligenceFactory = $this->createMock(VideoIntelligenceFactoryInterface::class);
        $intelligenceFactory->method('fromVideoJob')->willReturn($intelligence);

        $optimizer = $this->createMock(ExecutionOptimizerInterface::class);
        $optimizer->method('optimize')->willReturn($optimization);

        $scheduler = $this->createMock(PipelineSchedulerInterface::class);
        $scheduler->method('schedule')->willReturn($schedule);

        $runtimeContext = $this->createMock(RuntimeExecutionScheduleContextInterface::class);
        $runtimeContext->method('get')->willReturn(null);

        $handler = new GetExecutionScheduleHandler(
            $videoRepository,
            $intelligenceFactory,
            $optimizer,
            $scheduler,
            $runtimeContext,
        );

        $result = $handler(new GetExecutionScheduleQuery($videoId->value));

        self::assertSame($videoId->value, $result->videoId);
        self::assertSame('balanced', $result->strategy);
        self::assertCount(2, $result->stages);
    }

    public function testThrowsWhenVideoMissing(): void
    {
        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn(null);

        $handler = new GetExecutionScheduleHandler(
            $videoRepository,
            $this->createMock(VideoIntelligenceFactoryInterface::class),
            $this->createMock(ExecutionOptimizerInterface::class),
            $this->createMock(PipelineSchedulerInterface::class),
            $this->createMock(RuntimeExecutionScheduleContextInterface::class),
        );

        $this->expectException(InvalidVideoJobException::class);

        $handler(new GetExecutionScheduleQuery('550e8400-e29b-41d4-a716-446655440099'));
    }

    private function intelligence(): VideoIntelligence
    {
        return VideoIntelligence::create(
            VideoIntelligenceId::generate(),
            120.0,
            VideoScene::Interview,
            AudioCharacteristics::create('english', 1, AudioNoiseLevel::Low, BackgroundMusic::NotDetected, SpeechSpeed::Normal, SpeechConfidence::create(74)),
            VisualCharacteristics::create('1920x1080', 30.0, LightingCondition::Good, LipVisibility::Excellent, 1),
            SpeechCharacteristics::create(VideoEmotion::Neutral, 140.0, 5, false),
            VideoSpeakerCollection::empty(),
            true,
            8.0,
        );
    }

    private function optimization(): ExecutionOptimization
    {
        return ExecutionOptimization::create(
            ExecutionOptimizationId::generate(),
            OptimizationProfile::Balanced,
            new OptimizationStageCollection([
                OptimizationStageConfiguration::create(
                    OptimizationStage::SpeechToText,
                    new OptimizationParameterCollection([
                        OptimizationParameter::create('beamSize', '3'),
                    ]),
                ),
            ]),
            'Balanced optimization.',
            4,
        );
    }

    private function sampleSchedule(
        ?PipelineStageType $currentStage,
        ?ResourceType $currentResource,
    ): ExecutionSchedule {
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
                ExecutionResource::create(ResourceType::Gpu, 1, 0, 1),
                ExecutionResource::create(ResourceType::Cpu, 0, 1, 2),
            ],
            120,
            $currentStage,
            $currentResource,
        );
    }
}
