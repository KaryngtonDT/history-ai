<?php

declare(strict_types=1);

namespace App\Application\EngineAnalytics;

use App\Application\Pipeline\Estimation\MediaDurationResolver;
use App\Application\Runtime\RuntimePlatformInterface;
use App\Domain\EngineAnalytics\EngineExecutionHistory;
use App\Domain\EngineAnalytics\EngineExecutionHistoryRepositoryInterface;
use App\Domain\EngineAnalytics\EngineExecutionStatus;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobStatus;
use App\Domain\Video\VideoId;
use DateTimeImmutable;

final class EngineExecutionRecorder
{
    public function __construct(
        private readonly EngineExecutionHistoryRepositoryInterface $historyRepository,
        private readonly RuntimePlatformInterface $runtimePlatform,
        private readonly MediaDurationResolver $mediaDurationResolver,
    ) {
    }

    public function recordFromJob(PipelineJob $job, EngineExecutionStatus $status, ?string $notes = null): ?EngineExecutionHistory
    {
        $startedAt = $job->startedAt();

        if (null === $startedAt) {
            return null;
        }

        $completedAt = $job->completedAt() ?? new DateTimeImmutable();
        $videoId = $job->videoId() ?? $job->sourceId();
        $mediaDuration = null !== $videoId
            ? $this->mediaDurationResolver->resolveForVideo(new VideoId($videoId))
            : null;

        $execution = EngineExecutionHistory::fromPipelineExecution(
            $job->jobId(),
            $job->sourceId(),
            $job->stage(),
            $this->resolveEngineId($job),
            $this->resolveHardwareProfile(),
            $status,
            $startedAt,
            $completedAt,
            $job->estimatedDurationSeconds() ?? max(1, $job->elapsedSeconds() ?? 1),
            $job->provider(),
            null,
            $this->resolveModel($job),
            null,
            $mediaDuration,
            notes: $notes,
        );

        $this->historyRepository->record($execution);

        return $execution;
    }

    public function recordTerminalJob(PipelineJob $job): ?EngineExecutionHistory
    {
        return match ($job->status()) {
            PipelineJobStatus::Completed,
            PipelineJobStatus::WaitingUserConfirmation => $this->recordFromJob($job, EngineExecutionStatus::Completed),
            PipelineJobStatus::Failed => $this->recordFromJob(
                $job,
                EngineExecutionStatus::Failed,
                $job->failureReason(),
            ),
            PipelineJobStatus::Cancelled => $this->recordFromJob(
                $job,
                EngineExecutionStatus::Cancelled,
                $job->cancellationReason(),
            ),
            default => null,
        };
    }

    private function resolveEngineId(PipelineJob $job): string
    {
        if (null !== $job->currentEngine() && '' !== trim($job->currentEngine())) {
            return $job->currentEngine();
        }

        if (null !== $job->provider() && '' !== trim($job->provider())) {
            return $job->provider();
        }

        return match ($job->stage()) {
            PipelineStageType::SpeechToText => 'faster_whisper',
            PipelineStageType::Translation => 'ollama',
            PipelineStageType::TextToSpeech => 'f5_tts',
            PipelineStageType::VoiceClone => 'openvoice',
            PipelineStageType::LipSync => 'latentsync',
            PipelineStageType::VideoRender => 'ffmpeg',
        };
    }

    private function resolveModel(PipelineJob $job): ?string
    {
        if (PipelineStageType::SpeechToText !== $job->stage()) {
            return null;
        }

        return $job->currentEngine();
    }

    private function resolveHardwareProfile(): string
    {
        $profile = $this->runtimePlatform->hardwareProfile();

        return is_string($profile['profile']['type'] ?? null)
            ? $profile['profile']['type']
            : 'unknown';
    }
}
