<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Video;

use App\Application\EngineAnalytics\DurationPredictionEngine;
use App\Application\EngineAnalytics\EngineExecutionRecorder;
use App\Application\EngineAnalytics\EngineStatisticsAggregator;
use App\Application\EngineAnalytics\PipelineJobAnalyticsEnricher;
use App\Application\Pipeline\Estimation\HardwareAwareEstimateResolver;
use App\Application\Pipeline\Estimation\MediaDurationResolver;
use App\Application\Pipeline\Estimation\PipelineStageDurationEstimator;
use App\Application\Pipeline\Estimation\TranscriptionDurationEstimator;
use App\Application\Pipeline\Orchestration\PipelineDependencyResolver;
use App\Application\Pipeline\Orchestration\PipelineInvalidationService;
use App\Application\Pipeline\Orchestration\PipelineNotificationService;
use App\Application\Pipeline\Orchestration\PipelineJobLiveViewService;
use App\Application\Pipeline\Orchestration\PipelineOrchestrator;
use App\Application\Pipeline\Orchestration\PipelineProgressService;
use App\Application\Video\Handlers\ProcessVideoHandler;
use App\Application\Video\Messages\ProcessVideoMessage;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;
use App\Domain\PipelineJob\PipelineJobStatus;
use App\Domain\PipelineJob\PipelineSourceType;
use App\Application\Runtime\RuntimePlatformInterface;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Tests\Unit\Application\EngineAnalytics\InMemoryEngineExecutionHistoryRepository;
use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Video\VideoStatus;
use App\Application\Speech\TranscriptJsonMapper;
use App\Application\Translation\DefaultTranslationLanguagesProvider;
use App\Application\Translation\VideoTranslationGenerator;
use App\Application\TTS\GenerateAudioConfiguration;
use App\Application\TTS\VideoAudioGenerator;
use App\Application\LipSync\GenerateLipSyncConfiguration;
use App\Application\LipSync\VideoLipSyncGenerator;
use App\Application\VideoRender\GenerateFinalVideoConfiguration;
use App\Application\VideoRender\VideoFinalRenderGenerator;
use App\Application\VoiceClone\GenerateVoiceCloneConfiguration;
use App\Application\VoiceClone\VideoVoiceCloneGenerator;
use App\Application\Quality\QualityReportJsonMapper;
use App\Application\History\ExecutionHistoryRecorder;
use App\Application\History\RecordExecutionHistoryHandler;
use App\Tests\Unit\Application\History\InMemoryExecutionHistoryRepository;
use App\Tests\Unit\Application\History\InMemoryExecutionHistoryStore;
use App\Application\History\ExecutionOptimizationSnapshotMapper;
use App\Application\Pipeline\PipelineConfigurationJsonMapper;
use App\Application\Quality\VideoQualityAssessmentRunner;
use App\Application\Telemetry\CollectPipelineMetricsHandler;
use App\Application\Telemetry\PipelineTelemetryRecorder;
use App\Tests\Unit\Application\Telemetry\InMemoryPipelineTelemetryRepository;
use App\Application\Workspace\BatchJobProgressUpdater;
use App\Domain\Workspace\BatchJobRepositoryInterface;
use App\Domain\Orchestrator\PipelinePlannerInterface;
use App\Domain\Pipeline\RuntimePipelineConfigurationContextInterface;
use App\Infrastructure\History\ExecutionReplayContext;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Optimization\RuntimeExecutionOptimizationContextInterface;
use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Optimization\OptimizationParameter;
use App\Domain\Optimization\OptimizationParameterCollection;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Optimization\OptimizationStageConfiguration;
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
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;
use App\Domain\VideoRender\FinalVideoRepositoryInterface;
use App\Domain\Pipeline\PipelineConfigurationResolverInterface;
use App\Domain\PipelineJob\PipelineNotificationRepositoryInterface;
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
use App\Domain\Scheduler\ExecutionResource;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Quality\QualityEvaluatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ProcessVideoHandlerTest extends TestCase
{
    private VideoRepositoryInterface&MockObject $videoRepository;

    private AIProviderResolverInterface&MockObject $aiProviderResolver;

    private SpeechToTextProviderInterface&MockObject $speechToTextProvider;

    private TranscriptRepositoryInterface&MockObject $transcriptRepository;

    private ArtifactRepositoryInterface&MockObject $artifactRepository;

    private VideoTranslationGenerator&MockObject $videoTranslationGenerator;

    private VideoAudioGenerator&MockObject $videoAudioGenerator;

    private VideoVoiceCloneGenerator $videoVoiceCloneGenerator;

    private VideoLipSyncGenerator $videoLipSyncGenerator;

    private VideoFinalRenderGenerator $videoFinalRenderGenerator;

    private RuntimeExecutionScheduleContextInterface&MockObject $runtimeScheduleContext;

    private VideoQualityAssessmentRunner $qualityAssessmentRunner;

    private ProcessVideoHandler $handler;

    protected function setUp(): void
    {
        $this->videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $this->aiProviderResolver = $this->createMock(AIProviderResolverInterface::class);
        $this->speechToTextProvider = $this->createMock(SpeechToTextProviderInterface::class);
        $this->transcriptRepository = $this->createMock(TranscriptRepositoryInterface::class);
        $this->artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $this->videoTranslationGenerator = $this->createMock(VideoTranslationGenerator::class);
        $this->videoAudioGenerator = $this->createMock(VideoAudioGenerator::class);
        $this->videoVoiceCloneGenerator = $this->createStub(VideoVoiceCloneGenerator::class);
        $this->videoLipSyncGenerator = $this->createStub(VideoLipSyncGenerator::class);
        $this->videoFinalRenderGenerator = $this->createStub(VideoFinalRenderGenerator::class);
        $this->runtimeScheduleContext = $this->createMock(RuntimeExecutionScheduleContextInterface::class);

        $qualityVideoRepository = $this->createStub(VideoRepositoryInterface::class);
        $qualityVideoRepository->method('findById')->willReturn(null);

        $this->qualityAssessmentRunner = new VideoQualityAssessmentRunner(
            $qualityVideoRepository,
            $this->createStub(VideoIntelligenceFactoryInterface::class),
            $this->createStub(ExecutionOptimizerInterface::class),
            $this->createStub(QualityEvaluatorInterface::class),
            $this->createStub(FinalVideoRepositoryInterface::class),
            $this->createStub(ArtifactRepositoryInterface::class),
            new QualityReportJsonMapper(),
        );

        $batchJobProgressUpdater = new BatchJobProgressUpdater(
            $this->createStub(BatchJobRepositoryInterface::class),
        );

        $intelligenceFactory = $this->createStub(VideoIntelligenceFactoryInterface::class);
        $intelligenceFactory->method('fromVideoJob')->willReturn($this->sampleIntelligence());

        $optimizer = $this->createStub(ExecutionOptimizerInterface::class);
        $optimizer->method('optimize')->willReturn($this->sampleOptimization());

        $scheduler = $this->createStub(PipelineSchedulerInterface::class);
        $scheduler->method('schedule')->willReturn($this->sampleSchedule());

        $historyStore = new InMemoryExecutionHistoryStore();
        $recordHandler = new RecordExecutionHistoryHandler(
            new InMemoryExecutionHistoryRepository($historyStore),
            $historyStore,
            new PipelineConfigurationJsonMapper(),
            new ExecutionOptimizationSnapshotMapper(),
            new QualityReportJsonMapper(),
        );

        $executionHistoryRecorder = new ExecutionHistoryRecorder(
            $recordHandler,
            $this->createStub(RuntimePipelineConfigurationContextInterface::class),
            $this->createStub(RuntimeExecutionOptimizationContextInterface::class),
            $this->createStub(PipelineConfigurationResolverInterface::class),
            $this->createStub(FinalVideoRepositoryInterface::class),
        );

        $telemetryRecorder = new PipelineTelemetryRecorder(
            new CollectPipelineMetricsHandler(new InMemoryPipelineTelemetryRepository()),
            $this->createStub(\App\Domain\Workspace\ProjectRepositoryInterface::class),
            $this->createStub(RuntimePipelineConfigurationContextInterface::class),
            $this->runtimeScheduleContext,
        );

        $pipelineJobRepository = $this->createStub(PipelineJobRepositoryInterface::class);
        $pipelineNotificationRepository = $this->createStub(PipelineNotificationRepositoryInterface::class);
        $notificationService = new PipelineNotificationService($pipelineNotificationRepository);
        $dependencyResolver = new PipelineDependencyResolver();
        $invalidationService = new PipelineInvalidationService(
            $pipelineJobRepository,
            $dependencyResolver,
            $notificationService,
        );
        $progressService = new PipelineProgressService($pipelineJobRepository);
        $pipelineOrchestrator = $this->createPipelineOrchestrator($pipelineJobRepository);

        $this->handler = new ProcessVideoHandler(
            $this->videoRepository,
            $this->aiProviderResolver,
            $this->transcriptRepository,
            $this->artifactRepository,
            new TranscriptJsonMapper(),
            $this->videoTranslationGenerator,
            new DefaultTranslationLanguagesProvider(''),
            $this->videoAudioGenerator,
            new GenerateAudioConfiguration(false),
            $this->videoVoiceCloneGenerator,
            new GenerateVoiceCloneConfiguration(false),
            $this->videoLipSyncGenerator,
            new GenerateLipSyncConfiguration(false),
            $this->videoFinalRenderGenerator,
            new GenerateFinalVideoConfiguration(false),
            $this->createStub(PipelinePlannerInterface::class),
            $intelligenceFactory,
            $optimizer,
            $this->createStub(RuntimeExecutionOptimizationContextInterface::class),
            $this->createStub(RuntimePipelineConfigurationContextInterface::class),
            $scheduler,
            $this->runtimeScheduleContext,
            $this->qualityAssessmentRunner,
            $batchJobProgressUpdater,
            $executionHistoryRecorder,
            new ExecutionReplayContext(),
            $telemetryRecorder,
            $pipelineOrchestrator,
            $progressService,
        );
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
            ]),
            [
                ExecutionResource::create(ResourceType::Gpu, 0, 1, 1),
            ],
            60,
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

    private function sampleOptimization(): ExecutionOptimization
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

    public function testProcessesQueuedVideoIntoTranscriptArtifact(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $queued = VideoJob::createUploaded($videoId, 'lecture.mp4', VideoLanguage::English)
            ->withStoragePath('/var/video-storage/lecture.mp4')
            ->queue();

        $transcript = Transcript::create(
            new TranscriptId('550e8400-e29b-41d4-a716-446655440010'),
            TranscriptLanguage::English,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 2.0, 'Hello world'),
            ]),
        );

        $this->videoRepository
            ->expects(self::exactly(2))
            ->method('save')
            ->willReturnCallback(function (VideoJob $job) use ($videoId): void {
                static $call = 0;
                ++$call;

                if (1 === $call) {
                    self::assertSame(VideoStatus::Processing, $job->status());
                }

                if (2 === $call) {
                    self::assertSame(VideoStatus::Completed, $job->status());
                    self::assertTrue($job->id()->equals($videoId));
                }
            });

        $this->videoRepository
            ->expects(self::once())
            ->method('findById')
            ->with($videoId)
            ->willReturn($queued);

        $this->aiProviderResolver
            ->expects(self::once())
            ->method('resolveSpeechToText')
            ->willReturn($this->speechToTextProvider);

        $this->speechToTextProvider
            ->expects(self::once())
            ->method('transcribe')
            ->willReturn($transcript);

        $this->transcriptRepository
            ->expects(self::once())
            ->method('save')
            ->with($videoId, $transcript, self::anything());

        $this->artifactRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(function ($artifact) use ($videoId): bool {
                return $artifact->type() === ArtifactType::Transcript
                    && $artifact->contentId()->equals(new ContentId($videoId->value));
            }));

        $this->videoTranslationGenerator->expects(self::never())->method('generate');
        $this->videoAudioGenerator->expects(self::never())->method('generate');

        $this->runtimeScheduleContext
            ->expects(self::atLeastOnce())
            ->method('updateStage');

        ($this->handler)(new ProcessVideoMessage($videoId->value));
    }

    public function testMarksVideoFailedWhenTranscriptionFails(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $queued = VideoJob::createUploaded($videoId, 'lecture.mp4', VideoLanguage::English)
            ->withStoragePath('/var/video-storage/lecture.mp4')
            ->queue();

        $this->videoRepository
            ->expects(self::once())
            ->method('findById')
            ->willReturn($queued);

        $this->aiProviderResolver
            ->expects(self::once())
            ->method('resolveSpeechToText')
            ->willReturn($this->speechToTextProvider);

        $this->speechToTextProvider
            ->expects(self::once())
            ->method('transcribe')
            ->willThrowException(new \RuntimeException('transcription failed'));

        $this->videoTranslationGenerator->expects(self::never())->method('generate');
        $this->videoAudioGenerator->expects(self::never())->method('generate');
        $this->runtimeScheduleContext
            ->expects(self::atLeastOnce())
            ->method('updateStage');

        $this->videoRepository
            ->expects(self::exactly(2))
            ->method('save')
            ->willReturnCallback(function (VideoJob $job): void {
                static $call = 0;
                ++$call;

                if (2 === $call) {
                    self::assertSame(VideoStatus::Failed, $job->status());
                }
            });

        $this->transcriptRepository->expects(self::never())->method('save');
        $this->artifactRepository->expects(self::never())->method('save');

        ($this->handler)(new ProcessVideoMessage($videoId->value));
    }

    public function testProcessesOrchestratedTranslationStage(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $pipelineJobId = PipelineJobId::generate();
        $queued = VideoJob::createUploaded($videoId, 'lecture.mp4', VideoLanguage::English)
            ->withStoragePath('/var/video-storage/lecture.mp4')
            ->queue();
        $translationJob = PipelineJob::createQueued(
            $pipelineJobId,
            $videoId->value,
            PipelineSourceType::Video,
            PipelineStageType::Translation,
            $videoId->value,
        )->start('processing');

        $pipelineJobRepository = $this->createStub(PipelineJobRepositoryInterface::class);
        $pipelineJobRepository->method('findById')->willReturn($translationJob);
        $pipelineJobRepository->method('save')->willReturnCallback(
            static function (PipelineJob $job) use (&$translationJob): void {
                $translationJob = $job;
            },
        );

        $notificationService = new PipelineNotificationService(
            $this->createStub(PipelineNotificationRepositoryInterface::class),
        );
        $dependencyResolver = new PipelineDependencyResolver();
        $invalidationService = new PipelineInvalidationService(
            $pipelineJobRepository,
            $dependencyResolver,
            $notificationService,
        );
        $progressService = new PipelineProgressService($pipelineJobRepository);
        $pipelineOrchestrator = $this->createPipelineOrchestrator($pipelineJobRepository);

        $intelligenceFactory = $this->createStub(VideoIntelligenceFactoryInterface::class);
        $intelligenceFactory->method('fromVideoJob')->willReturn($this->sampleIntelligence());

        $optimizer = $this->createStub(ExecutionOptimizerInterface::class);
        $optimizer->method('optimize')->willReturn($this->sampleOptimization());

        $scheduler = $this->createStub(PipelineSchedulerInterface::class);
        $scheduler->method('schedule')->willReturn($this->sampleSchedule());

        $historyStore = new InMemoryExecutionHistoryStore();
        $recordHandler = new RecordExecutionHistoryHandler(
            new InMemoryExecutionHistoryRepository($historyStore),
            $historyStore,
            new PipelineConfigurationJsonMapper(),
            new ExecutionOptimizationSnapshotMapper(),
            new QualityReportJsonMapper(),
        );
        $executionHistoryRecorder = new ExecutionHistoryRecorder(
            $recordHandler,
            $this->createStub(RuntimePipelineConfigurationContextInterface::class),
            $this->createStub(RuntimeExecutionOptimizationContextInterface::class),
            $this->createStub(PipelineConfigurationResolverInterface::class),
            $this->createStub(FinalVideoRepositoryInterface::class),
        );
        $telemetryRecorder = new PipelineTelemetryRecorder(
            new CollectPipelineMetricsHandler(new InMemoryPipelineTelemetryRepository()),
            $this->createStub(\App\Domain\Workspace\ProjectRepositoryInterface::class),
            $this->createStub(RuntimePipelineConfigurationContextInterface::class),
            $this->runtimeScheduleContext,
        );

        $handler = new ProcessVideoHandler(
            $this->videoRepository,
            $this->aiProviderResolver,
            $this->transcriptRepository,
            $this->artifactRepository,
            new TranscriptJsonMapper(),
            $this->videoTranslationGenerator,
            new DefaultTranslationLanguagesProvider('french'),
            $this->videoAudioGenerator,
            new GenerateAudioConfiguration(false),
            $this->videoVoiceCloneGenerator,
            new GenerateVoiceCloneConfiguration(false),
            $this->videoLipSyncGenerator,
            new GenerateLipSyncConfiguration(false),
            $this->videoFinalRenderGenerator,
            new GenerateFinalVideoConfiguration(false),
            $this->createStub(PipelinePlannerInterface::class),
            $intelligenceFactory,
            $optimizer,
            $this->createStub(RuntimeExecutionOptimizationContextInterface::class),
            $this->createStub(RuntimePipelineConfigurationContextInterface::class),
            $scheduler,
            $this->runtimeScheduleContext,
            $this->qualityAssessmentRunner,
            new BatchJobProgressUpdater($this->createStub(BatchJobRepositoryInterface::class)),
            $executionHistoryRecorder,
            new ExecutionReplayContext(),
            $telemetryRecorder,
            $pipelineOrchestrator,
            $progressService,
        );

        $this->videoRepository
            ->expects(self::exactly(2))
            ->method('findById')
            ->with($videoId)
            ->willReturn($queued);

        $this->aiProviderResolver->expects(self::never())->method('resolveSpeechToText');
        $this->speechToTextProvider->expects(self::never())->method('transcribe');
        $this->transcriptRepository->expects(self::never())->method('save');
        $this->artifactRepository->expects(self::never())->method('save');

        $this->videoTranslationGenerator
            ->expects(self::once())
            ->method('generate')
            ->with($videoId, self::anything());

        $this->videoAudioGenerator->expects(self::never())->method('generate');

        $this->videoRepository
            ->expects(self::exactly(2))
            ->method('save')
            ->willReturnCallback(function (VideoJob $job) use ($videoId): void {
                static $call = 0;
                ++$call;

                if (2 === $call) {
                    self::assertSame(VideoStatus::Completed, $job->status());
                    self::assertTrue($job->id()->equals($videoId));
                }
            });

        $this->runtimeScheduleContext
            ->expects(self::atLeastOnce())
            ->method('updateStage');

        ($handler)(new ProcessVideoMessage(
            $videoId->value,
            ProcessingMode::Manual,
            null,
            null,
            PipelineStageType::Translation->value,
            $pipelineJobId->value,
        ));

        self::assertSame(
            PipelineJobStatus::WaitingUserConfirmation,
            $translationJob->status(),
        );
    }

    private function createPipelineOrchestrator(
        PipelineJobRepositoryInterface $pipelineJobRepository,
    ): PipelineOrchestrator {
        $dependencyResolver = new PipelineDependencyResolver();
        $notificationService = new PipelineNotificationService(
            $this->createStub(PipelineNotificationRepositoryInterface::class),
        );
        $invalidationService = new PipelineInvalidationService(
            $pipelineJobRepository,
            $dependencyResolver,
            $notificationService,
        );
        $progressService = new PipelineProgressService($pipelineJobRepository);
        $runtimePlatform = $this->createStub(RuntimePlatformInterface::class);
        $historyRepository = new InMemoryEngineExecutionHistoryRepository();
        $hardwareRepository = $this->createStub(\App\Domain\Hardware\HardwareRepositoryInterface::class);
        $hardwareRepository->method('detect')->willReturn(
            (new \App\Application\Hardware\HardwareReportBuilder(new \App\Application\Hardware\HardwareProfileClassifier()))
                ->build(new \App\Domain\Hardware\HardwareCapability()),
        );
        $fallbackEstimator = new PipelineStageDurationEstimator(
            new TranscriptionDurationEstimator(
                new MediaDurationResolver($this->videoRepository),
                new HardwareAwareEstimateResolver(false),
                'large-v3',
            ),
            new MediaDurationResolver($this->videoRepository),
        );

        $durationPredictionEngine = new DurationPredictionEngine($historyRepository, $fallbackEstimator, $hardwareRepository);

        return new PipelineOrchestrator(
            $pipelineJobRepository,
            $dependencyResolver,
            $invalidationService,
            $notificationService,
            $progressService,
            new PipelineJobLiveViewService(),
            $durationPredictionEngine,
            new EngineExecutionRecorder($historyRepository, $runtimePlatform, new MediaDurationResolver($this->videoRepository)),
            new EngineStatisticsAggregator($historyRepository, $durationPredictionEngine),
            new PipelineJobAnalyticsEnricher($historyRepository, $runtimePlatform),
            $this->createStub(VideoProcessingQueueInterface::class),
            $this->videoRepository,
        );
    }
}
