<?php

declare(strict_types=1);

namespace App\Application\Video\Handlers;

use App\Application\History\ExecutionHistoryRecorder;
use App\Application\Quality\VideoQualityAssessmentRunner;
use App\Application\Telemetry\PipelineTelemetryRecorder;
use App\Application\Workspace\BatchJobProgressUpdater;
use App\Application\LipSync\GenerateLipSyncConfiguration;
use App\Application\LipSync\VideoLipSyncGenerator;
use App\Application\Speech\TranscriptJsonMapper;
use App\Application\Translation\DefaultTranslationLanguagesProvider;
use App\Application\Translation\VideoTranslationGenerator;
use App\Application\TTS\GenerateAudioConfiguration;
use App\Application\TTS\VideoAudioGenerator;
use App\Application\Video\Messages\ProcessVideoMessage;
use App\Application\VideoRender\GenerateFinalVideoConfiguration;
use App\Application\VideoRender\VideoFinalRenderGenerator;
use App\Application\VoiceClone\GenerateVoiceCloneConfiguration;
use App\Application\VoiceClone\VideoVoiceCloneGenerator;
use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Orchestrator\PipelinePlannerInterface;
use App\Domain\History\ExecutionReplayContextInterface;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Optimization\RuntimeExecutionOptimizationContextInterface;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Pipeline\RuntimePipelineConfigurationContextInterface;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Scheduler\PipelineSchedulerInterface;
use App\Domain\Scheduler\RuntimeExecutionScheduleContextInterface;
use App\Domain\Scheduler\ScheduledStageStatus;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VideoIntelligence\VideoIntelligence;
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;
use Throwable;

final class ProcessVideoHandler
{
    private float $pipelineStartedAt = 0.0;

    /** @var array<string, float> */
    private array $stageDurations = [];

    private int $retryCount = 0;

    private float $initialQueueTimeSeconds = 0.0;

    private ?PipelineStageType $currentStage = null;

    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly AIProviderResolverInterface $aiProviderResolver,
        private readonly TranscriptRepositoryInterface $transcriptRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly TranscriptJsonMapper $transcriptJsonMapper,
        private readonly VideoTranslationGenerator $videoTranslationGenerator,
        private readonly DefaultTranslationLanguagesProvider $defaultTranslationLanguages,
        private readonly VideoAudioGenerator $videoAudioGenerator,
        private readonly GenerateAudioConfiguration $generateAudioConfiguration,
        private readonly VideoVoiceCloneGenerator $videoVoiceCloneGenerator,
        private readonly GenerateVoiceCloneConfiguration $generateVoiceCloneConfiguration,
        private readonly VideoLipSyncGenerator $videoLipSyncGenerator,
        private readonly GenerateLipSyncConfiguration $generateLipSyncConfiguration,
        private readonly VideoFinalRenderGenerator $videoFinalRenderGenerator,
        private readonly GenerateFinalVideoConfiguration $generateFinalVideoConfiguration,
        private readonly PipelinePlannerInterface $pipelinePlanner,
        private readonly VideoIntelligenceFactoryInterface $videoIntelligenceFactory,
        private readonly ExecutionOptimizerInterface $executionOptimizer,
        private readonly RuntimeExecutionOptimizationContextInterface $runtimeOptimizationContext,
        private readonly RuntimePipelineConfigurationContextInterface $runtimePipelineContext,
        private readonly PipelineSchedulerInterface $pipelineScheduler,
        private readonly RuntimeExecutionScheduleContextInterface $runtimeScheduleContext,
        private readonly VideoQualityAssessmentRunner $qualityAssessmentRunner,
        private readonly BatchJobProgressUpdater $batchJobProgressUpdater,
        private readonly ExecutionHistoryRecorder $executionHistoryRecorder,
        private readonly ExecutionReplayContextInterface $executionReplayContext,
        private readonly PipelineTelemetryRecorder $pipelineTelemetryRecorder,
    ) {
    }

    public function __invoke(ProcessVideoMessage $message): void
    {
        $videoId = new VideoId($message->videoId);
        $job = $this->videoRepository->findById($videoId);

        if (null === $job) {
            return;
        }

        $processing = $job->startProcessing();
        $this->videoRepository->save($processing);

        $succeeded = false;
        $failureMessage = null;
        $qualityReport = null;
        $this->stageDurations = [];
        $this->retryCount = 0;
        $this->pipelineStartedAt = microtime(true);

        try {
            $this->configurePipelineForMessage($message, $processing);
            $this->initialQueueTimeSeconds = $this->pipelineTelemetryRecorder->resolveInitialQueueTimeSeconds();

            $this->runScheduledStage(PipelineStageType::SpeechToText, function () use ($processing, $videoId): void {
                $transcript = $this->aiProviderResolver
                    ->resolveSpeechToText()
                    ->transcribe($processing);
                $this->transcriptRepository->save($videoId, $transcript);

                $artifact = Artifact::create(
                    ArtifactId::generate(),
                    new ContentId($videoId->value),
                    new ProcessingJobId($videoId->value),
                    ArtifactType::Transcript,
                    ArtifactContent::fromString($this->transcriptJsonMapper->toJson($transcript)),
                );
                $this->artifactRepository->save($artifact);
            });

            if ([] !== $this->defaultTranslationLanguages->all()) {
                $this->runScheduledStage(
                    PipelineStageType::Translation,
                    fn (): mixed => $this->videoTranslationGenerator->generate(
                        $videoId,
                        $this->defaultTranslationLanguages->all(),
                    ),
                );
            }

            if ($this->generateAudioConfiguration->isEnabled()) {
                $this->runScheduledStage(
                    PipelineStageType::TextToSpeech,
                    fn (): mixed => $this->videoAudioGenerator->generate($videoId),
                );
            }

            if ($this->generateVoiceCloneConfiguration->isEnabled()) {
                $this->runScheduledStage(
                    PipelineStageType::VoiceClone,
                    fn (): mixed => $this->videoVoiceCloneGenerator->generate($videoId),
                );
            }

            if ($this->generateLipSyncConfiguration->isEnabled()) {
                $this->runScheduledStage(
                    PipelineStageType::LipSync,
                    fn (): mixed => $this->videoLipSyncGenerator->generate($videoId),
                );
            }

            if ($this->generateFinalVideoConfiguration->isEnabled()) {
                $this->runScheduledStage(
                    PipelineStageType::VideoRender,
                    fn (): mixed => $this->videoFinalRenderGenerator->generate($videoId),
                );
            }

            $qualityReport = $this->qualityAssessmentRunner->assess(
                $videoId,
                $this->runtimeOptimizationContext->get(),
            );

            $this->executionHistoryRecorder->recordCompletedExecution($videoId, $qualityReport);

            $this->videoRepository->save($processing->complete());
            $succeeded = true;
        } catch (Throwable $throwable) {
            $failureMessage = $throwable->getMessage();
            $this->videoRepository->save($processing->fail(
                $failureMessage,
                $this->currentStage?->value,
                microtime(true) - $this->pipelineStartedAt,
            ));
        } finally {
            $this->pipelineTelemetryRecorder->record(
                $videoId,
                $message,
                $succeeded,
                microtime(true) - $this->pipelineStartedAt,
                $this->stageDurations,
                $qualityReport,
                $failureMessage,
                $this->retryCount,
                $this->initialQueueTimeSeconds,
            );
            $this->batchJobProgressUpdater->recordOutcome($message->batchJobId, $videoId, $succeeded);
            $this->executionReplayContext->clear($videoId);
            $this->runtimePipelineContext->clear();
            $this->runtimeOptimizationContext->clear();
            $this->runtimeScheduleContext->clear();
        }
    }

    private function configurePipelineForMessage(ProcessVideoMessage $message, VideoJob $job): void
    {
        $intelligence = $this->videoIntelligenceFactory->fromVideoJob($job);
        $optimization = $this->executionOptimizer->optimize($intelligence);
        $this->runtimeOptimizationContext->set($optimization);
        $this->configureSchedule($intelligence, $optimization);

        $replayConfiguration = $this->executionReplayContext->consume(new VideoId($message->videoId));

        if (null !== $replayConfiguration) {
            $this->retryCount = 1;
            $this->runtimePipelineContext->set($replayConfiguration);

            return;
        }

        if (ProcessingMode::Manual === $message->processingMode) {
            $this->runtimePipelineContext->clear();

            return;
        }

        $recommendation = null !== $message->strategy
            ? $this->pipelinePlanner->recommendWithStrategy($intelligence, $message->strategy)
            : $this->pipelinePlanner->recommend($intelligence);

        $this->runtimePipelineContext->set($recommendation->pipelineConfiguration());
    }

    private function configureSchedule(VideoIntelligence $intelligence, ExecutionOptimization $optimization): void
    {
        try {
            $this->runtimeScheduleContext->set(
                $this->pipelineScheduler->schedule($intelligence, $optimization),
            );
        } catch (Throwable) {
            $this->runtimeScheduleContext->clear();
        }
    }

    /**
     * @param callable(): mixed $callback
     */
    private function runScheduledStage(PipelineStageType $stage, callable $callback): void
    {
        $this->currentStage = $stage;
        $this->runtimeScheduleContext->updateStage($stage, ScheduledStageStatus::Running);
        $startedAt = microtime(true);

        try {
            $callback();
            $this->runtimeScheduleContext->updateStage($stage, ScheduledStageStatus::Completed);
            $this->stageDurations[$stage->value] = microtime(true) - $startedAt;
        } catch (Throwable $throwable) {
            $this->runtimeScheduleContext->updateStage($stage, ScheduledStageStatus::Failed);
            $this->stageDurations[$stage->value] = microtime(true) - $startedAt;
            throw $throwable;
        }
    }
}
