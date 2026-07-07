<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\Exception\InvalidPipelineJobException;
use DateTimeImmutable;

final class PipelineJob
{
    /**
     * @param list<string> $invalidatesStages
     * @param list<string> $staleArtifactIds
     * @param list<string> $userChoiceOptions
     */
    private function __construct(
        private readonly PipelineJobId $jobId,
        private readonly string $sourceId,
        private readonly ?string $videoId,
        private readonly ?string $audioId,
        private readonly ?string $contentId,
        private readonly PipelineSourceType $sourceType,
        private readonly PipelineStageType $stage,
        private PipelineJobStatus $status,
        private int $progressPercent,
        private ?string $currentStep,
        private ?string $currentEngine,
        private ?string $provider,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private ?DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $completedAt,
        private ?int $estimatedDurationSeconds,
        private ?int $estimatedRemainingSeconds,
        private ?int $elapsedSeconds,
        private ?string $cancellationReason,
        private ?string $failureReason,
        private ?string $resultArtifactId,
        private ?PipelineStageType $dependsOnStage,
        private array $invalidatesStages,
        private array $staleArtifactIds,
        private ?TranscriptSource $transcriptSource,
        private bool $userChoiceRequired,
        private array $userChoiceOptions,
    ) {
    }

    public static function createQueued(
        PipelineJobId $jobId,
        string $sourceId,
        PipelineSourceType $sourceType,
        PipelineStageType $stage,
        ?string $videoId = null,
        ?string $audioId = null,
        ?string $contentId = null,
        ?string $provider = null,
        ?string $currentEngine = null,
        ?int $estimatedDurationSeconds = null,
        ?PipelineStageType $dependsOnStage = null,
        ?array $invalidatesStages = null,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            $jobId,
            $sourceId,
            $videoId,
            $audioId,
            $contentId ?? $videoId ?? $audioId,
            $sourceType,
            $stage,
            PipelineJobStatus::Queued,
            0,
            null,
            $currentEngine,
            $provider,
            $now,
            $now,
            null,
            null,
            $estimatedDurationSeconds,
            $estimatedDurationSeconds,
            0,
            null,
            null,
            null,
            $dependsOnStage,
            $invalidatesStages ?? [],
            [],
            null,
            false,
            [],
        );
    }

    /**
     * @param list<string> $invalidatesStages
     * @param list<string> $staleArtifactIds
     * @param list<string> $userChoiceOptions
     */
    public static function reconstitute(
        PipelineJobId $jobId,
        string $sourceId,
        ?string $videoId,
        ?string $audioId,
        ?string $contentId,
        PipelineSourceType $sourceType,
        PipelineStageType $stage,
        PipelineJobStatus $status,
        int $progressPercent,
        ?string $currentStep,
        ?string $currentEngine,
        ?string $provider,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        ?DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt,
        ?int $estimatedDurationSeconds,
        ?int $estimatedRemainingSeconds,
        ?int $elapsedSeconds,
        ?string $cancellationReason,
        ?string $failureReason,
        ?string $resultArtifactId,
        ?PipelineStageType $dependsOnStage,
        array $invalidatesStages,
        array $staleArtifactIds,
        ?TranscriptSource $transcriptSource,
        bool $userChoiceRequired,
        array $userChoiceOptions,
    ): self {
        return new self(
            $jobId,
            $sourceId,
            $videoId,
            $audioId,
            $contentId,
            $sourceType,
            $stage,
            $status,
            max(0, min(100, $progressPercent)),
            $currentStep,
            $currentEngine,
            $provider,
            $createdAt,
            $updatedAt,
            $startedAt,
            $completedAt,
            $estimatedDurationSeconds,
            $estimatedRemainingSeconds,
            $elapsedSeconds,
            $cancellationReason,
            $failureReason,
            $resultArtifactId,
            $dependsOnStage,
            $invalidatesStages,
            $staleArtifactIds,
            $transcriptSource,
            $userChoiceRequired,
            $userChoiceOptions,
        );
    }

    public function start(?string $currentStep = null): self
    {
        $this->assertStatus(PipelineJobStatus::Queued, 'start');

        return $this->with(
            status: PipelineJobStatus::Running,
            startedAt: new DateTimeImmutable(),
            currentStep: $currentStep ?? 'running',
            elapsedSeconds: 0,
        );
    }

    public function updateProgress(
        int $progressPercent,
        ?string $currentStep = null,
        ?int $estimatedRemainingSeconds = null,
    ): self {
        if (!in_array($this->status, [PipelineJobStatus::Running, PipelineJobStatus::Queued], true)) {
            throw new InvalidPipelineJobException(sprintf(
                'Cannot update progress for job in status "%s".',
                $this->status->value,
            ));
        }

        $elapsed = $this->computeElapsedSeconds();

        return $this->with(
            progressPercent: max(0, min(99, $progressPercent)),
            currentStep: $currentStep ?? $this->currentStep,
            estimatedRemainingSeconds: $estimatedRemainingSeconds ?? $this->estimatedRemainingSeconds,
            elapsedSeconds: $elapsed,
        );
    }

    public function complete(?string $resultArtifactId = null, ?TranscriptSource $transcriptSource = null): self
    {
        if (!in_array($this->status, [PipelineJobStatus::Running, PipelineJobStatus::Queued], true)) {
            throw new InvalidPipelineJobException(sprintf(
                'Cannot complete job in status "%s".',
                $this->status->value,
            ));
        }

        return $this->with(
            status: PipelineJobStatus::WaitingUserConfirmation,
            progressPercent: 100,
            completedAt: new DateTimeImmutable(),
            resultArtifactId: $resultArtifactId,
            transcriptSource: $transcriptSource ?? $this->transcriptSource,
            currentStep: 'completed',
            estimatedRemainingSeconds: 0,
            elapsedSeconds: $this->computeElapsedSeconds(),
        );
    }

    public function markCompletedWithoutConfirmation(?string $resultArtifactId = null): self
    {
        return $this->with(
            status: PipelineJobStatus::Completed,
            progressPercent: 100,
            completedAt: new DateTimeImmutable(),
            resultArtifactId: $resultArtifactId,
            currentStep: 'completed',
            estimatedRemainingSeconds: 0,
            elapsedSeconds: $this->computeElapsedSeconds(),
        );
    }

    public function confirmContinue(): self
    {
        $this->assertStatus(PipelineJobStatus::WaitingUserConfirmation, 'confirm');

        return $this->with(status: PipelineJobStatus::Completed);
    }

    public function requireUserChoice(array $options, ?string $message = null): self
    {
        return $this->with(
            status: PipelineJobStatus::WaitingUserChoice,
            userChoiceRequired: true,
            userChoiceOptions: $options,
            currentStep: $message ?? 'waiting_user_choice',
        );
    }

    public function applyUserChoice(TranscriptSource $source): self
    {
        $this->assertStatus(PipelineJobStatus::WaitingUserChoice, 'apply choice');

        return $this->with(
            status: PipelineJobStatus::WaitingUserConfirmation,
            transcriptSource: $source,
            userChoiceRequired: false,
            userChoiceOptions: [],
            progressPercent: 100,
            completedAt: new DateTimeImmutable(),
            currentStep: 'transcript_ready',
        );
    }

    public function fail(string $reason): self
    {
        if (PipelineJobStatus::Failed === $this->status) {
            return $this;
        }

        return $this->with(
            status: PipelineJobStatus::Failed,
            failureReason: $reason,
            currentStep: 'failed',
            elapsedSeconds: $this->computeElapsedSeconds(),
        );
    }

    public function cancel(string $reason): self
    {
        if (!$this->status->isActive() && !$this->status->isWaitingForUser()) {
            throw new InvalidPipelineJobException(sprintf(
                'Cannot cancel job in status "%s".',
                $this->status->value,
            ));
        }

        return $this->with(
            status: PipelineJobStatus::Cancelled,
            cancellationReason: $reason,
            currentStep: 'cancelled',
            elapsedSeconds: $this->computeElapsedSeconds(),
        );
    }

    public function markStaleArtifacts(array $artifactIds): self
    {
        return $this->with(
            staleArtifactIds: array_values(array_unique([...$this->staleArtifactIds, ...$artifactIds])),
        );
    }

    public function jobId(): PipelineJobId
    {
        return $this->jobId;
    }

    public function sourceId(): string
    {
        return $this->sourceId;
    }

    public function videoId(): ?string
    {
        return $this->videoId;
    }

    public function audioId(): ?string
    {
        return $this->audioId;
    }

    public function contentId(): ?string
    {
        return $this->contentId;
    }

    public function sourceType(): PipelineSourceType
    {
        return $this->sourceType;
    }

    public function stage(): PipelineStageType
    {
        return $this->stage;
    }

    public function status(): PipelineJobStatus
    {
        return $this->status;
    }

    public function progressPercent(): int
    {
        return $this->progressPercent;
    }

    public function currentStep(): ?string
    {
        return $this->currentStep;
    }

    public function currentEngine(): ?string
    {
        return $this->currentEngine;
    }

    public function provider(): ?string
    {
        return $this->provider;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function startedAt(): ?DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function completedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function estimatedDurationSeconds(): ?int
    {
        return $this->estimatedDurationSeconds;
    }

    public function estimatedRemainingSeconds(): ?int
    {
        return $this->estimatedRemainingSeconds;
    }

    public function elapsedSeconds(): ?int
    {
        return $this->elapsedSeconds;
    }

    public function cancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function failureReason(): ?string
    {
        return $this->failureReason;
    }

    public function resultArtifactId(): ?string
    {
        return $this->resultArtifactId;
    }

    public function dependsOnStage(): ?PipelineStageType
    {
        return $this->dependsOnStage;
    }

    /** @return list<string> */
    public function invalidatesStages(): array
    {
        return $this->invalidatesStages;
    }

    /** @return list<string> */
    public function staleArtifactIds(): array
    {
        return $this->staleArtifactIds;
    }

    public function transcriptSource(): ?TranscriptSource
    {
        return $this->transcriptSource;
    }

    public function userChoiceRequired(): bool
    {
        return $this->userChoiceRequired;
    }

    /** @return list<string> */
    public function userChoiceOptions(): array
    {
        return $this->userChoiceOptions;
    }

    private function assertStatus(PipelineJobStatus $expected, string $action): void
    {
        if ($this->status !== $expected) {
            throw new InvalidPipelineJobException(sprintf(
                'Cannot %s pipeline job in status "%s".',
                $action,
                $this->status->value,
            ));
        }
    }

    private function computeElapsedSeconds(): int
    {
        if (null === $this->startedAt) {
            return 0;
        }

        return max(0, (new DateTimeImmutable())->getTimestamp() - $this->startedAt->getTimestamp());
    }

    private function with(
        ?PipelineJobStatus $status = null,
        ?int $progressPercent = null,
        ?string $currentStep = null,
        ?string $currentEngine = null,
        ?string $provider = null,
        ?DateTimeImmutable $startedAt = null,
        ?DateTimeImmutable $completedAt = null,
        ?int $estimatedDurationSeconds = null,
        ?int $estimatedRemainingSeconds = null,
        ?int $elapsedSeconds = null,
        ?string $cancellationReason = null,
        ?string $failureReason = null,
        ?string $resultArtifactId = null,
        ?TranscriptSource $transcriptSource = null,
        ?bool $userChoiceRequired = null,
        ?array $userChoiceOptions = null,
        ?array $staleArtifactIds = null,
    ): self {
        return new self(
            $this->jobId,
            $this->sourceId,
            $this->videoId,
            $this->audioId,
            $this->contentId,
            $this->sourceType,
            $this->stage,
            $status ?? $this->status,
            $progressPercent ?? $this->progressPercent,
            $currentStep ?? $this->currentStep,
            $currentEngine ?? $this->currentEngine,
            $provider ?? $this->provider,
            $this->createdAt,
            new DateTimeImmutable(),
            $startedAt ?? $this->startedAt,
            $completedAt ?? $this->completedAt,
            $estimatedDurationSeconds ?? $this->estimatedDurationSeconds,
            $estimatedRemainingSeconds ?? $this->estimatedRemainingSeconds,
            $elapsedSeconds ?? $this->elapsedSeconds,
            $cancellationReason ?? $this->cancellationReason,
            $failureReason ?? $this->failureReason,
            $resultArtifactId ?? $this->resultArtifactId,
            $this->dependsOnStage,
            $this->invalidatesStages,
            $staleArtifactIds ?? $this->staleArtifactIds,
            $transcriptSource ?? $this->transcriptSource,
            $userChoiceRequired ?? $this->userChoiceRequired,
            $userChoiceOptions ?? $this->userChoiceOptions,
        );
    }
}
