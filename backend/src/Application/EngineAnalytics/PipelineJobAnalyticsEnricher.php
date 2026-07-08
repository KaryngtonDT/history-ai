<?php

declare(strict_types=1);

namespace App\Application\EngineAnalytics;

use App\Application\Runtime\RuntimePlatformInterface;
use App\Domain\EngineAnalytics\EngineExecutionHistory;
use App\Domain\EngineAnalytics\EngineExecutionHistoryId;
use App\Domain\EngineAnalytics\EngineExecutionHistoryRepositoryInterface;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobStatus;
use DateTimeImmutable;

final class PipelineJobAnalyticsEnricher
{
    public function __construct(
        private readonly EngineExecutionHistoryRepositoryInterface $historyRepository,
        private readonly RuntimePlatformInterface $runtimePlatform,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function enrich(PipelineJob $job, array $payload): array
    {
        $execution = $this->historyRepository->findLatestByPipelineJobId($job->jobId());
        $engineId = $execution?->engineId()
            ?? $job->currentEngine()
            ?? $job->provider();
        $hardwareProfile = $execution?->hardwareProfile() ?? $this->resolveHardwareProfile();
        $estimationAccuracyPercent = $execution?->estimationAccuracyPercent();
        $actualDurationSeconds = $execution?->actualDurationSeconds()
            ?? $this->resolveActualDuration($job);

        $estimatedDurationSeconds = $job->estimatedDurationSeconds();
        $estimatedCompletionAt = null;

        if (null !== $estimatedDurationSeconds) {
            $base = $job->startedAt() ?? new DateTimeImmutable();

            if (PipelineJobStatus::Queued === $job->status()) {
                $base = new DateTimeImmutable();
            }

            $estimatedCompletionAt = $base->modify(sprintf('+%d seconds', $estimatedDurationSeconds));
        }

        $payload['engineId'] = $engineId;
        $payload['hardwareProfile'] = $hardwareProfile;
        $payload['estimatedCompletionAt'] = $estimatedCompletionAt?->format(DATE_ATOM);
        $payload['actualDurationSeconds'] = $actualDurationSeconds;
        $payload['estimationAccuracyPercent'] = $estimationAccuracyPercent;

        return $payload;
    }

    private function resolveActualDuration(PipelineJob $job): ?int
    {
        if (null !== $job->elapsedSeconds() && $job->elapsedSeconds() > 0) {
            return $job->elapsedSeconds();
        }

        $startedAt = $job->startedAt();
        $completedAt = $job->completedAt();

        if (null === $startedAt || null === $completedAt) {
            return null;
        }

        return max(1, $completedAt->getTimestamp() - $startedAt->getTimestamp());
    }

    private function resolveHardwareProfile(): string
    {
        $profile = $this->runtimePlatform->hardwareProfile();

        return is_string($profile['profile']['type'] ?? null)
            ? $profile['profile']['type']
            : 'unknown';
    }

    public function serializeExecution(EngineExecutionHistory $execution): array
    {
        return [
            'executionId' => $execution->executionId()->value,
            'pipelineJobId' => $execution->pipelineJobId()->value,
            'sourceId' => $execution->sourceId(),
            'stage' => $execution->stage()->value,
            'engineId' => $execution->engineId(),
            'engineVersion' => $execution->engineVersion(),
            'provider' => $execution->provider(),
            'hardwareProfile' => $execution->hardwareProfile(),
            'model' => $execution->model(),
            'language' => $execution->language(),
            'mediaDurationSeconds' => $execution->mediaDurationSeconds(),
            'estimatedDurationSeconds' => $execution->estimatedDurationSeconds(),
            'actualDurationSeconds' => $execution->actualDurationSeconds(),
            'estimationErrorSeconds' => $execution->estimationErrorSeconds(),
            'estimationAccuracyPercent' => $execution->estimationAccuracyPercent(),
            'startedAt' => $execution->startedAt()->format(DATE_ATOM),
            'completedAt' => $execution->completedAt()->format(DATE_ATOM),
            'status' => $execution->status()->value,
            'benchmarkScore' => $execution->benchmarkScore(),
            'notes' => $execution->notes(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listExecutions(
        ?PipelineStageType $stage,
        ?string $engineId,
        ?string $hardwareProfile,
        int $limit,
    ): array {
        return array_map(
            fn (EngineExecutionHistory $execution): array => $this->serializeExecution($execution),
            $this->historyRepository->findRecent($stage, $engineId, $hardwareProfile, $limit),
        );
    }

    public function getExecution(EngineExecutionHistoryId $executionId): ?array
    {
        $execution = $this->historyRepository->findById($executionId);

        return null === $execution ? null : $this->serializeExecution($execution);
    }
}
