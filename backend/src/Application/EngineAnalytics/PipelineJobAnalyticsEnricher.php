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
        $hardware = $this->runtimePlatform->hardware();
        $profileType = is_string($hardware['profile']['type'] ?? null)
            ? (string) $hardware['profile']['type']
            : 'unknown';
        $gpuVendor = is_string($hardware['capabilities']['gpuVendor'] ?? null)
            ? strtolower((string) $hardware['capabilities']['gpuVendor'])
            : null;

        $payload['hardwareProfile'] = $execution?->hardwareProfile()
            ?? $this->resolveHardwareProfileCode($profileType, $gpuVendor);
        $payload['hardwareProfileLabel'] = is_string($hardware['profile']['label'] ?? null)
            ? (string) $hardware['profile']['label']
            : $profileType;

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
        $payload['estimatedCompletionAt'] = $estimatedCompletionAt?->format(DATE_ATOM);
        $payload['actualDurationSeconds'] = $actualDurationSeconds;
        $payload['estimationAccuracyPercent'] = $estimationAccuracyPercent;

        return $payload;
    }

    private function resolveHardwareProfileCode(string $profileType, ?string $gpuVendor): string
    {
        if (null !== $gpuVendor) {
            if (str_contains($gpuVendor, 'nvidia')) {
                return 'NVIDIA';
            }

            if (str_contains($gpuVendor, 'amd') || str_contains($gpuVendor, 'advanced micro')) {
                return 'AMD';
            }
        }

        return match ($profileType) {
            'cpu_only', 'low_end_local' => 'CPU_ONLY',
            'mid_range_nvidia', 'high_end_nvidia', 'enterprise_gpu' => 'NVIDIA',
            default => strtoupper(str_replace(' ', '_', $profileType)),
        };
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

        return is_string($profile['type'] ?? null)
            ? $profile['type']
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
