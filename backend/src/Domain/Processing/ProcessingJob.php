<?php

declare(strict_types=1);

namespace App\Domain\Processing;

use App\Domain\Content\ContentId;
use App\Domain\Processing\Exception\InvalidProcessingJobException;
use DateTimeImmutable;

final class ProcessingJob
{
    private function __construct(
        private readonly ProcessingJobId $id,
        private readonly ContentId $contentId,
        private readonly ProcessingJobType $type,
        private ProcessingJobStatus $status,
        private ProcessingJobProgress $progress,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private ?DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $completedAt,
        private ?DateTimeImmutable $failedAt,
    ) {
    }

    public static function create(
        ProcessingJobId $id,
        ContentId $contentId,
        ProcessingJobType $type,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            $id,
            $contentId,
            $type,
            ProcessingJobStatus::Pending,
            ProcessingJobProgress::zero(),
            $now,
            $now,
            null,
            null,
            null,
        );
    }

    /**
     * Rebuilds a ProcessingJob aggregate from persistence. Used by infrastructure only.
     */
    public static function reconstitute(
        ProcessingJobId $id,
        ContentId $contentId,
        ProcessingJobType $type,
        ProcessingJobStatus $status,
        ProcessingJobProgress $progress,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        ?DateTimeImmutable $startedAt = null,
        ?DateTimeImmutable $completedAt = null,
        ?DateTimeImmutable $failedAt = null,
    ): self {
        return new self(
            $id,
            $contentId,
            $type,
            $status,
            $progress,
            $createdAt,
            $updatedAt,
            $startedAt,
            $completedAt,
            $failedAt,
        );
    }

    public function start(): void
    {
        $this->assertStatus(ProcessingJobStatus::Pending, 'start');

        $this->status = ProcessingJobStatus::Running;
        $this->startedAt = new DateTimeImmutable();
        $this->touch();
    }

    public function updateProgress(ProcessingJobProgress $progress): void
    {
        $this->assertStatus(ProcessingJobStatus::Running, 'update progress');

        if ($progress->percentage() <= 0 || $progress->percentage() >= 100) {
            throw new InvalidProcessingJobException(
                'Running progress must be strictly between 0 and 100.',
            );
        }

        if (!$progress->isGreaterThan($this->progress)) {
            throw new InvalidProcessingJobException('Progress cannot decrease or stay the same.');
        }

        $this->progress = $progress;
        $this->touch();
    }

    public function complete(): void
    {
        $this->assertStatus(ProcessingJobStatus::Running, 'complete');

        $this->status = ProcessingJobStatus::Completed;
        $this->progress = ProcessingJobProgress::complete();
        $this->completedAt = new DateTimeImmutable();
        $this->touch();
    }

    public function fail(): void
    {
        $this->assertStatus(ProcessingJobStatus::Running, 'fail');

        $this->status = ProcessingJobStatus::Failed;
        $this->failedAt = new DateTimeImmutable();
        $this->touch();
    }

    public function cancel(): void
    {
        $this->assertStatus(ProcessingJobStatus::Pending, 'cancel');

        $this->status = ProcessingJobStatus::Cancelled;
        $this->touch();
    }

    public function id(): ProcessingJobId
    {
        return $this->id;
    }

    public function contentId(): ContentId
    {
        return $this->contentId;
    }

    public function type(): ProcessingJobType
    {
        return $this->type;
    }

    public function status(): ProcessingJobStatus
    {
        return $this->status;
    }

    public function progress(): ProcessingJobProgress
    {
        return $this->progress;
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

    public function failedAt(): ?DateTimeImmutable
    {
        return $this->failedAt;
    }

    private function assertStatus(ProcessingJobStatus $expected, string $action): void
    {
        if ($this->status !== $expected) {
            throw new InvalidProcessingJobException(sprintf(
                'Cannot %s a processing job in status "%s".',
                $action,
                $this->status->value,
            ));
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
