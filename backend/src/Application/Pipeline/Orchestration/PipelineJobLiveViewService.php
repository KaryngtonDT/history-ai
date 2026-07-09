<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobProgressDetail;
use App\Domain\PipelineJob\PipelineJobStatus;
use DateTimeImmutable;

final class PipelineJobLiveViewService
{
    private const WORKER_STALE_SECONDS = 15;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function enrich(PipelineJob $job, array $payload): array
    {
        $now = new DateTimeImmutable();
        $detail = PipelineJobProgressDetail::fromArray($job->progressDetail());
        $isRunning = PipelineJobStatus::Running === $job->status() || PipelineJobStatus::Queued === $job->status();
        $isTerminal = in_array($job->status(), [
            PipelineJobStatus::Completed,
            PipelineJobStatus::WaitingUserConfirmation,
            PipelineJobStatus::Failed,
            PipelineJobStatus::Cancelled,
        ], true);

        $payload['serverNow'] = $now->format(DATE_ATOM);
        $payload['isLive'] = $isRunning;
        $payload['progressDetail'] = $detail?->toArray();

        if ($isTerminal) {
            $payload['liveFrozen'] = true;
            $payload['workerStatus'] = 'completed';
            $payload['progressPercent'] = $job->progressPercent();

            return $payload;
        }

        if (!$isRunning) {
            return $payload;
        }

        $elapsed = $this->liveElapsedSeconds($job, $now);
        $checkpoint = PipelineSttCheckpointRegistry::resolve($job->currentStep());
        $progress = $this->resolveLiveProgress($job, $detail, $elapsed, $checkpoint);
        $remaining = $this->resolveRemainingSeconds($job, $elapsed, $progress);
        $completionAt = null !== $remaining
            ? $now->modify(sprintf('+%d seconds', $remaining))
            : null;
        $secondsSinceUpdate = max(0, $now->getTimestamp() - $job->updatedAt()->getTimestamp());
        $workerStale = $secondsSinceUpdate >= self::WORKER_STALE_SECONDS;

        $speed = $detail?->processingSpeedRatio;
        if (null === $speed && null !== $detail?->audioProcessedSeconds && $elapsed > 0) {
            $speed = round($detail->audioProcessedSeconds / $elapsed, 2);
        }

        $payload['progressPercent'] = $progress;
        $payload['elapsedSeconds'] = $elapsed;
        $payload['estimatedRemainingSeconds'] = $remaining;
        $payload['estimatedCompletionAt'] = $completionAt?->format(DATE_ATOM);
        $payload['checkpoint'] = $checkpoint['checkpoint'];
        $payload['checkpointLabel'] = $checkpoint['label'];
        $payload['workerStatus'] = $workerStale ? 'waiting_for_update' : 'active';
        $payload['workerStale'] = $workerStale;
        $payload['secondsSinceUpdate'] = $secondsSinceUpdate;
        $payload['processingSpeedRatio'] = $speed;
        $payload['currentSegment'] = $detail?->currentSegment;
        $payload['totalSegments'] = $detail?->totalSegments;
        $payload['audioProcessedSeconds'] = $detail?->audioProcessedSeconds;
        $payload['audioTotalSeconds'] = $detail?->audioTotalSeconds;
        $payload['engineVersion'] = $detail?->engineVersion;
        $payload['workerId'] = $detail?->workerId ?? 'symfony-worker';
        $payload['dockerContainer'] = $detail?->dockerContainer ?? (gethostname() ?: null);

        if (null !== $payload['hardwareProfile']) {
            $payload['hardwareProfileCode'] = strtoupper(str_replace(' ', '_', (string) $payload['hardwareProfile']));
        }

        return $payload;
    }

    private function liveElapsedSeconds(PipelineJob $job, DateTimeImmutable $now): int
    {
        $startedAt = $job->startedAt();

        if (null === $startedAt) {
            return max(0, (int) ($job->elapsedSeconds() ?? 0));
        }

        return max(0, $now->getTimestamp() - $startedAt->getTimestamp());
    }

    /**
     * @param array{checkpoint: string, label: string, minPercent: int, maxPercent: int} $checkpoint
     */
    private function resolveLiveProgress(
        PipelineJob $job,
        ?PipelineJobProgressDetail $detail,
        int $elapsed,
        array $checkpoint,
    ): int {
        $stored = $job->progressPercent();
        $min = $checkpoint['minPercent'];
        $max = $checkpoint['maxPercent'];

        if ('transcribing' === $checkpoint['checkpoint']) {
            if (
                null !== $detail?->audioProcessedSeconds
                && null !== $detail->audioTotalSeconds
                && $detail->audioTotalSeconds > 0
            ) {
                $ratio = min(1.0, $detail->audioProcessedSeconds / $detail->audioTotalSeconds);

                return (int) round($min + (($max - $min) * $ratio));
            }

            if (
                null !== $detail?->currentSegment
                && null !== $detail?->totalSegments
                && $detail->totalSegments > 0
            ) {
                $ratio = min(1.0, $detail->currentSegment / $detail->totalSegments);

                return (int) round($min + (($max - $min) * $ratio));
            }

            $estimated = $job->estimatedDurationSeconds();
            if (null !== $estimated && $estimated > 0) {
                $ratio = min(1.0, $elapsed / $estimated);

                return (int) round($min + (($max - $min) * $ratio));
            }
        }

        return max($stored, min($max, max($min, $stored)));
    }

    private function resolveRemainingSeconds(PipelineJob $job, int $elapsed, int $progress): ?int
    {
        $estimated = $job->estimatedDurationSeconds();

        if (null !== $estimated && $estimated > 0) {
            if ($progress > 5 && $progress < 99) {
                $projectedTotal = (int) round($elapsed / ($progress / 100));

                return max(0, $projectedTotal - $elapsed);
            }

            return max(0, $estimated - $elapsed);
        }

        return $job->estimatedRemainingSeconds();
    }
}
