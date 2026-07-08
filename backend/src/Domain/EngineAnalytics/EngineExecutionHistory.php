<?php

declare(strict_types=1);

namespace App\Domain\EngineAnalytics;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobId;
use DateTimeImmutable;

final readonly class EngineExecutionHistory
{
    public function __construct(
        private EngineExecutionHistoryId $executionId,
        private PipelineJobId $pipelineJobId,
        private string $sourceId,
        private PipelineStageType $stage,
        private string $engineId,
        private ?string $engineVersion,
        private ?string $provider,
        private string $hardwareProfile,
        private ?string $model,
        private ?string $language,
        private ?int $mediaDurationSeconds,
        private ?int $inputSizeBytes,
        private ?int $outputSizeBytes,
        private int $estimatedDurationSeconds,
        private int $actualDurationSeconds,
        private int $estimationErrorSeconds,
        private float $estimationAccuracyPercent,
        private DateTimeImmutable $startedAt,
        private DateTimeImmutable $completedAt,
        private EngineExecutionStatus $status,
        private ?float $benchmarkScore,
        private ?string $notes,
    ) {
    }

    public static function fromPipelineExecution(
        PipelineJobId $pipelineJobId,
        string $sourceId,
        PipelineStageType $stage,
        string $engineId,
        string $hardwareProfile,
        EngineExecutionStatus $status,
        DateTimeImmutable $startedAt,
        DateTimeImmutable $completedAt,
        int $estimatedDurationSeconds,
        ?string $provider = null,
        ?string $engineVersion = null,
        ?string $model = null,
        ?string $language = null,
        ?int $mediaDurationSeconds = null,
        ?int $inputSizeBytes = null,
        ?int $outputSizeBytes = null,
        ?float $benchmarkScore = null,
        ?string $notes = null,
    ): self {
        $actualDurationSeconds = max(1, $completedAt->getTimestamp() - $startedAt->getTimestamp());
        $estimationErrorSeconds = $actualDurationSeconds - max(1, $estimatedDurationSeconds);
        $denominator = max(1, $estimatedDurationSeconds);
        $estimationAccuracyPercent = max(
            0.0,
            min(100.0, 100.0 - (abs($estimationErrorSeconds) / $denominator) * 100.0),
        );

        return new self(
            EngineExecutionHistoryId::generate(),
            $pipelineJobId,
            $sourceId,
            $stage,
            $engineId,
            $engineVersion,
            $provider,
            $hardwareProfile,
            $model,
            $language,
            $mediaDurationSeconds,
            $inputSizeBytes,
            $outputSizeBytes,
            max(1, $estimatedDurationSeconds),
            $actualDurationSeconds,
            $estimationErrorSeconds,
            round($estimationAccuracyPercent, 1),
            $startedAt,
            $completedAt,
            $status,
            $benchmarkScore,
            $notes,
        );
    }

    public function executionId(): EngineExecutionHistoryId
    {
        return $this->executionId;
    }

    public function pipelineJobId(): PipelineJobId
    {
        return $this->pipelineJobId;
    }

    public function sourceId(): string
    {
        return $this->sourceId;
    }

    public function stage(): PipelineStageType
    {
        return $this->stage;
    }

    public function engineId(): string
    {
        return $this->engineId;
    }

    public function engineVersion(): ?string
    {
        return $this->engineVersion;
    }

    public function provider(): ?string
    {
        return $this->provider;
    }

    public function hardwareProfile(): string
    {
        return $this->hardwareProfile;
    }

    public function model(): ?string
    {
        return $this->model;
    }

    public function language(): ?string
    {
        return $this->language;
    }

    public function mediaDurationSeconds(): ?int
    {
        return $this->mediaDurationSeconds;
    }

    public function inputSizeBytes(): ?int
    {
        return $this->inputSizeBytes;
    }

    public function outputSizeBytes(): ?int
    {
        return $this->outputSizeBytes;
    }

    public function estimatedDurationSeconds(): int
    {
        return $this->estimatedDurationSeconds;
    }

    public function actualDurationSeconds(): int
    {
        return $this->actualDurationSeconds;
    }

    public function estimationErrorSeconds(): int
    {
        return $this->estimationErrorSeconds;
    }

    public function estimationAccuracyPercent(): float
    {
        return $this->estimationAccuracyPercent;
    }

    public function startedAt(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function completedAt(): DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function status(): EngineExecutionStatus
    {
        return $this->status;
    }

    public function benchmarkScore(): ?float
    {
        return $this->benchmarkScore;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }
}
