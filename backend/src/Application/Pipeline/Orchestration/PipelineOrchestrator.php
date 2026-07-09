<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Application\EngineAnalytics\DurationPredictionEngine;
use App\Application\EngineAnalytics\EngineExecutionRecorder;
use App\Application\EngineAnalytics\EngineStatisticsAggregator;
use App\Application\EngineAnalytics\PipelineJobAnalyticsEnricher;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;
use App\Domain\PipelineJob\PipelineJobStatus;
use App\Domain\PipelineJob\PipelineSourceType;
use App\Domain\PipelineJob\TranscriptUserChoice;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Video\VideoStatus;

final class PipelineOrchestrator
{
    public function __construct(
        private readonly PipelineJobRepositoryInterface $jobRepository,
        private readonly PipelineDependencyResolver $dependencyResolver,
        private readonly PipelineInvalidationService $invalidationService,
        private readonly PipelineNotificationService $notificationService,
        private readonly PipelineProgressService $progressService,
        private readonly PipelineJobLiveViewService $liveViewService,
        private readonly DurationPredictionEngine $durationPredictionEngine,
        private readonly EngineExecutionRecorder $executionRecorder,
        private readonly EngineStatisticsAggregator $statisticsAggregator,
        private readonly PipelineJobAnalyticsEnricher $jobAnalyticsEnricher,
        private readonly VideoProcessingQueueInterface $videoProcessingQueue,
        private readonly VideoRepositoryInterface $videoRepository,
    ) {
    }

    public function getOrCreateJob(
        string $sourceId,
        PipelineStageType $stage,
        PipelineSourceType $sourceType = PipelineSourceType::Video,
        ?string $videoId = null,
        ?string $provider = null,
        ?string $currentEngine = null,
        bool $forceRestart = false,
    ): PipelineJob {
        $existing = $this->jobRepository->findActiveBySourceAndStage($sourceId, $stage);

        if (null !== $existing && !$forceRestart) {
            return $existing;
        }

        if (null !== $existing && $forceRestart) {
            $this->invalidationService->invalidateDependentStages($existing);
            $cancelled = $existing->cancel('Restarted by user');
            $this->jobRepository->save($cancelled);
        }

        $estimate = null !== ($videoId ?? $sourceId)
            ? $this->durationPredictionEngine->estimateForStage(
                new VideoId($videoId ?? $sourceId),
                $stage,
                $currentEngine,
            )
            : ['maxSeconds' => null, 'confidence' => null];

        $job = PipelineJob::createQueued(
            PipelineJobId::generate(),
            $sourceId,
            $sourceType,
            $stage,
            $videoId ?? $sourceId,
            null,
            $videoId ?? $sourceId,
            $provider,
            $currentEngine,
            $estimate['maxSeconds'] ?? null,
            invalidatesStages: $this->dependencyResolver->invalidatesStages($stage),
        );

        $this->jobRepository->save($job);

        return $job;
    }

    public function startStage(PipelineJobId $jobId): PipelineJob
    {
        $job = $this->requireJob($jobId);
        $existing = $this->jobRepository->findActiveBySourceAndStage($job->sourceId(), $job->stage());

        if (null !== $existing && $existing->jobId()->value !== $jobId->value) {
            return $existing;
        }

        if (PipelineJobStatus::Running === $job->status()) {
            return $job;
        }

        $videoId = new VideoId($job->videoId() ?? $job->sourceId());

        if (null === $job->estimatedDurationSeconds()) {
            $estimate = $this->durationPredictionEngine->estimateForStage($videoId, $job->stage(), $job->currentEngine());
            $job = $job->withDurationEstimate($estimate['maxSeconds']);
        }

        $started = $job->start('processing');
        $this->jobRepository->save($started);
        $this->dispatchStageProcessing($started);

        return $started;
    }

    private function dispatchStageProcessing(PipelineJob $started): void
    {
        $videoId = new VideoId($started->videoId() ?? $started->sourceId());

        if (PipelineStageType::SpeechToText === $started->stage()) {
            $videoJob = $this->videoRepository->findById($videoId);

            if (null !== $videoJob && VideoStatus::Uploaded === $videoJob->status()) {
                $queued = $videoJob->queue();
                $this->videoRepository->save($queued);
            }
        }

        $this->videoProcessingQueue->enqueue(
            $videoId,
            ProcessingMode::Manual,
            null,
            null,
            $started->stage(),
            $started->jobId()->value,
        );
    }

    public function completeStage(PipelineJobId $jobId, ?string $resultArtifactId = null): PipelineJob
    {
        $job = $this->requireJob($jobId);
        $completed = $job->complete($resultArtifactId);
        $this->jobRepository->save($completed);
        $this->notificationService->notifyStageCompleted($completed);
        $this->recordExecution($completed);

        return $completed;
    }

    public function failStage(PipelineJobId $jobId, string $reason): PipelineJob
    {
        $job = $this->requireJob($jobId);
        $failed = $job->fail($reason);
        $this->jobRepository->save($failed);
        $this->notificationService->notifyStageFailed($failed);
        $this->recordExecution($failed);

        return $failed;
    }

    public function continueToNextStage(PipelineJobId $jobId): ?PipelineJob
    {
        $job = $this->requireJob($jobId);

        if (PipelineJobStatus::WaitingUserConfirmation !== $job->status()) {
            throw new \RuntimeException('Stage is not waiting for user confirmation.');
        }

        $confirmed = $job->confirmContinue();
        $this->jobRepository->save($confirmed);

        $next = $this->dependencyResolver->nextStage($job->stage());

        if (null === $next) {
            return null;
        }

        $next = $this->getOrCreateJob(
            $job->sourceId(),
            $next,
            $job->sourceType(),
            $job->videoId(),
        );

        return $this->startStage($next->jobId());
    }

    public function requireUserTranscriptChoice(PipelineJobId $jobId): PipelineJob
    {
        $job = $this->requireJob($jobId);
        $waiting = $job->requireUserChoice([
            TranscriptUserChoice::YoutubeTranscript->value,
            TranscriptUserChoice::LocalEngine->value,
        ], 'Original YouTube transcript found.');
        $this->jobRepository->save($waiting);
        $this->notificationService->notifyOriginalYoutubeTranscriptFound($job->sourceId());
        $this->notificationService->notifyUserChoiceRequired($job->sourceId());

        return $waiting;
    }

    public function beginLocalStt(PipelineJobId $jobId, string $estimateMessage): PipelineJob
    {
        $job = $this->requireJob($jobId);

        if (PipelineJobStatus::WaitingUserChoice === $job->status()
            || (PipelineJobStatus::Queued === $job->status() && $job->userChoiceRequired())) {
            $job = $job->acceptLocalSttChoice();
        }

        $started = PipelineJobStatus::Queued === $job->status() ? $job->start('local_stt') : $job;
        $this->jobRepository->save($started);
        $this->notificationService->notifyLocalSttStarted($job->sourceId(), $estimateMessage);

        $videoId = new VideoId($started->videoId() ?? $started->sourceId());
        $this->videoProcessingQueue->enqueue(
            $videoId,
            ProcessingMode::Manual,
            null,
            null,
            PipelineStageType::SpeechToText,
            $started->jobId()->value,
        );

        return $started;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSourceStatus(string $sourceId): array
    {
        $jobs = $this->jobRepository->findBySourceId($sourceId);
        $activeJobs = [];
        $completedJobs = [];
        $jobsWaitingUserChoice = [];
        $jobsWaitingConfirmation = [];
        $failedJobs = [];
        $cancelledJobs = [];
        $staleArtifacts = [];

        foreach ($jobs as $job) {
            $payload = $this->serializeJob($job);

            if ($this->isWaitingForTranscriptChoice($job)) {
                $jobsWaitingUserChoice[] = $payload;
            } else {
                match ($job->status()) {
                    PipelineJobStatus::Queued, PipelineJobStatus::Running => $activeJobs[] = $payload,
                    PipelineJobStatus::Completed => $completedJobs[] = $payload,
                    PipelineJobStatus::WaitingUserChoice => $jobsWaitingUserChoice[] = $payload,
                    PipelineJobStatus::WaitingUserConfirmation => $jobsWaitingConfirmation[] = $payload,
                    PipelineJobStatus::Failed => $failedJobs[] = $payload,
                    PipelineJobStatus::Cancelled => $cancelledJobs[] = $payload,
                };
            }

            $staleArtifacts = [...$staleArtifacts, ...$job->staleArtifactIds()];
        }

        $nextPossibleStage = null;
        $blockedStages = [];
        $requiresUserAction = [] !== $jobsWaitingUserChoice || [] !== $jobsWaitingConfirmation;

        if ([] !== $jobsWaitingUserChoice) {
            $blockedStages = array_map(static fn (PipelineStageType $s): string => $s->value, PipelineStageType::cases());
        }

        return [
            'sourceId' => $sourceId,
            'serverNow' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'activeJobs' => $activeJobs,
            'completedJobs' => $completedJobs,
            'jobsWaitingUserChoice' => $jobsWaitingUserChoice,
            'jobsWaitingConfirmation' => $jobsWaitingConfirmation,
            'failedJobs' => $failedJobs,
            'cancelledJobs' => $cancelledJobs,
            'staleArtifacts' => array_values(array_unique($staleArtifacts)),
            'nextPossibleStage' => $nextPossibleStage,
            'blockedStages' => $blockedStages,
            'requiresUserAction' => $requiresUserAction,
            'message' => $this->buildStatusMessage($activeJobs, $jobsWaitingUserChoice, $jobsWaitingConfirmation),
            'notification' => [] !== $jobsWaitingUserChoice
                ? 'Original YouTube transcript found. Choose how to proceed.'
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeJob(PipelineJob $job): array
    {
        $videoId = $job->videoId() ?? $job->sourceId();
        $estimate = null !== $videoId
            ? $this->durationPredictionEngine->estimateForStage(
                new VideoId($videoId),
                $job->stage(),
                $job->currentEngine(),
            )
            : ['confidence' => null];

        return $this->liveViewService->enrich(
            $job,
            $this->jobAnalyticsEnricher->enrich($job, [
                'jobId' => $job->jobId()->value,
                'sourceId' => $job->sourceId(),
                'videoId' => $job->videoId(),
                'stage' => $job->stage()->value,
                'status' => $job->status()->value,
                'progressPercent' => $job->progressPercent(),
                'currentStep' => $job->currentStep(),
                'currentEngine' => $job->currentEngine(),
                'provider' => $job->provider(),
                'startedAt' => $job->startedAt()?->format(DATE_ATOM),
                'updatedAt' => $job->updatedAt()->format(DATE_ATOM),
                'completedAt' => $job->completedAt()?->format(DATE_ATOM),
                'estimatedDurationSeconds' => $job->estimatedDurationSeconds(),
                'estimatedRemainingSeconds' => $job->estimatedRemainingSeconds(),
                'elapsedSeconds' => $job->elapsedSeconds(),
                'failureReason' => $job->failureReason(),
                'cancellationReason' => $job->cancellationReason(),
                'transcriptSource' => $job->transcriptSource()?->value,
                'userChoiceRequired' => $job->userChoiceRequired(),
                'userChoiceOptions' => $job->userChoiceOptions(),
                'staleArtifactIds' => $job->staleArtifactIds(),
                'predictionConfidence' => $estimate['confidence'] ?? null,
                'estimationSource' => $estimate['source'] ?? null,
            ]),
        );
    }

    private function recordExecution(PipelineJob $job): void
    {
        if (null !== $this->executionRecorder->recordTerminalJob($job)) {
            $this->statisticsAggregator->refreshAfterExecution();
        }
    }

    private function isWaitingForTranscriptChoice(PipelineJob $job): bool
    {
        if (PipelineJobStatus::WaitingUserChoice === $job->status()) {
            return true;
        }

        return PipelineJobStatus::Queued === $job->status() && $job->userChoiceRequired();
    }

    private function requireJob(PipelineJobId $jobId): PipelineJob
    {
        $job = $this->jobRepository->findById($jobId);

        if (null === $job) {
            throw new \RuntimeException('Pipeline job not found.');
        }

        return $job;
    }

    /**
     * @param list<array<string, mixed>> $activeJobs
     * @param list<array<string, mixed>> $waitingChoice
     * @param list<array<string, mixed>> $waitingConfirmation
     */
    private function buildStatusMessage(array $activeJobs, array $waitingChoice, array $waitingConfirmation): string
    {
        if ([] !== $waitingChoice) {
            return 'Original YouTube transcript found. Choose whether to use it or run the local transcription engine.';
        }

        if ([] !== $waitingConfirmation) {
            return 'A pipeline stage completed. Review the result and continue when ready.';
        }

        if ([] !== $activeJobs) {
            return 'Background processing is running. You can safely leave this page — refreshing will not restart the job.';
        }

        return 'Pipeline idle.';
    }
}
