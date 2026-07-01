<?php

declare(strict_types=1);

namespace App\Domain\Telemetry;

use App\Domain\Telemetry\Exception\InvalidPipelineTelemetryException;
use App\Domain\Workspace\ProjectId;
use DateTimeImmutable;

final readonly class PipelineTelemetry
{
    public function __construct(
        private PipelineTelemetryId $id,
        private string $workspaceId,
        private string $videoId,
        private bool $success,
        private ExecutionMetricCollection $metrics,
        private ProviderUsageCollection $providerUsages,
        private DateTimeImmutable $recordedAt,
        private ?string $batchJobId = null,
        private ?int $qualityScore = null,
        private ?string $errorMessage = null,
    ) {
        if (!ProjectId::isValid($workspaceId)) {
            throw new InvalidPipelineTelemetryException('Workspace id must be a valid UUID.');
        }

        if (!ProjectId::isValid($videoId)) {
            throw new InvalidPipelineTelemetryException('Video id must be a valid UUID.');
        }

        if (null !== $batchJobId && !ProjectId::isValid($batchJobId)) {
            throw new InvalidPipelineTelemetryException('Batch job id must be a valid UUID.');
        }

        if (null !== $qualityScore && ($qualityScore < 0 || $qualityScore > 100)) {
            throw new InvalidPipelineTelemetryException('Quality score must be between 0 and 100.');
        }
    }

    /**
     * @param list<ExecutionMetric> $metrics
     * @param list<ProviderUsage> $providerUsages
     */
    public static function create(
        PipelineTelemetryId $id,
        string $workspaceId,
        string $videoId,
        bool $success,
        array $metrics,
        array $providerUsages,
        ?string $batchJobId = null,
        ?int $qualityScore = null,
        ?string $errorMessage = null,
        ?DateTimeImmutable $recordedAt = null,
    ): self {
        $metricCollection = ExecutionMetricCollection::empty();

        foreach ($metrics as $metric) {
            $metricCollection = $metricCollection->append($metric);
        }

        $usageCollection = ProviderUsageCollection::empty();

        foreach ($providerUsages as $usage) {
            $usageCollection = $usageCollection->append($usage);
        }

        return new self(
            $id,
            $workspaceId,
            $videoId,
            $success,
            $metricCollection,
            $usageCollection,
            $recordedAt ?? new DateTimeImmutable(),
            $batchJobId,
            $qualityScore,
            null !== $errorMessage ? trim($errorMessage) : null,
        );
    }

    public static function reconstitute(
        PipelineTelemetryId $id,
        string $workspaceId,
        string $videoId,
        bool $success,
        ExecutionMetricCollection $metrics,
        ProviderUsageCollection $providerUsages,
        DateTimeImmutable $recordedAt,
        ?string $batchJobId,
        ?int $qualityScore,
        ?string $errorMessage,
    ): self {
        return new self(
            $id,
            $workspaceId,
            $videoId,
            $success,
            $metrics,
            $providerUsages,
            $recordedAt,
            $batchJobId,
            $qualityScore,
            $errorMessage,
        );
    }

    public function id(): PipelineTelemetryId
    {
        return $this->id;
    }

    public function workspaceId(): string
    {
        return $this->workspaceId;
    }

    public function videoId(): string
    {
        return $this->videoId;
    }

    public function batchJobId(): ?string
    {
        return $this->batchJobId;
    }

    public function success(): bool
    {
        return $this->success;
    }

    public function qualityScore(): ?int
    {
        return $this->qualityScore;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function metrics(): ExecutionMetricCollection
    {
        return $this->metrics;
    }

    public function providerUsages(): ProviderUsageCollection
    {
        return $this->providerUsages;
    }

    public function recordedAt(): DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function processingTimeSeconds(): ?float
    {
        return $this->metrics->findByType(ExecutionMetricType::ProcessingTime)?->value();
    }

    public function retryCount(): int
    {
        return (int) ($this->metrics->findByType(ExecutionMetricType::RetryCount)?->value() ?? 0);
    }
}
