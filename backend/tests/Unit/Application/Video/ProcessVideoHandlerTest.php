<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Video;

use App\Application\Video\Handlers\ProcessVideoHandler;
use App\Application\Video\Messages\ProcessVideoMessage;
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
use App\Application\Quality\VideoQualityAssessmentRunner;
use App\Domain\Orchestrator\PipelinePlannerInterface;
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
use App\Domain\Pipeline\RuntimePipelineConfigurationContextInterface;
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

    private VideoVoiceCloneGenerator&MockObject $videoVoiceCloneGenerator;

    private VideoLipSyncGenerator&MockObject $videoLipSyncGenerator;

    private VideoFinalRenderGenerator&MockObject $videoFinalRenderGenerator;

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
        $this->videoVoiceCloneGenerator = $this->createMock(VideoVoiceCloneGenerator::class);
        $this->videoLipSyncGenerator = $this->createMock(VideoLipSyncGenerator::class);
        $this->videoFinalRenderGenerator = $this->createMock(VideoFinalRenderGenerator::class);
        $this->runtimeScheduleContext = $this->createMock(RuntimeExecutionScheduleContextInterface::class);

        $qualityVideoRepository = $this->createMock(VideoRepositoryInterface::class);
        $qualityVideoRepository->method('findById')->willReturn(null);

        $this->qualityAssessmentRunner = new VideoQualityAssessmentRunner(
            $qualityVideoRepository,
            $this->createMock(VideoIntelligenceFactoryInterface::class),
            $this->createMock(ExecutionOptimizerInterface::class),
            $this->createMock(QualityEvaluatorInterface::class),
            $this->createMock(FinalVideoRepositoryInterface::class),
            $this->createMock(ArtifactRepositoryInterface::class),
            new QualityReportJsonMapper(),
        );

        $intelligenceFactory = $this->createMock(VideoIntelligenceFactoryInterface::class);
        $intelligenceFactory->method('fromVideoJob')->willReturn($this->sampleIntelligence());

        $optimizer = $this->createMock(ExecutionOptimizerInterface::class);
        $optimizer->method('optimize')->willReturn($this->sampleOptimization());

        $scheduler = $this->createMock(PipelineSchedulerInterface::class);
        $scheduler->method('schedule')->willReturn($this->sampleSchedule());

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
            $this->createMock(PipelinePlannerInterface::class),
            $intelligenceFactory,
            $optimizer,
            $this->createMock(RuntimeExecutionOptimizationContextInterface::class),
            $this->createMock(RuntimePipelineConfigurationContextInterface::class),
            $scheduler,
            $this->runtimeScheduleContext,
            $this->qualityAssessmentRunner,
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
            ->with($videoId, $transcript);

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
            ->method('findById')
            ->willReturn($queued);

        $this->aiProviderResolver
            ->method('resolveSpeechToText')
            ->willReturn($this->speechToTextProvider);

        $this->speechToTextProvider
            ->method('transcribe')
            ->willThrowException(new \RuntimeException('transcription failed'));

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
}
