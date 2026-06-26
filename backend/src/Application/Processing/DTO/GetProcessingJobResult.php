<?php

declare(strict_types=1);

namespace App\Application\Processing\DTO;

use App\Domain\Processing\ProcessingJob;
use DateTimeInterface;

final readonly class GetProcessingJobResult
{
    public function __construct(
        public string $id,
        public string $contentId,
        public string $type,
        public string $status,
        public int $progress,
        public ?string $startedAt,
        public ?string $completedAt,
        public ?string $failedAt,
    ) {
    }

    public static function fromDomain(ProcessingJob $job): self
    {
        return new self(
            id: $job->id()->value,
            contentId: $job->contentId()->value,
            type: $job->type()->value,
            status: $job->status()->value,
            progress: $job->progress()->percentage(),
            startedAt: $job->startedAt()?->format(DateTimeInterface::ATOM),
            completedAt: $job->completedAt()?->format(DateTimeInterface::ATOM),
            failedAt: $job->failedAt()?->format(DateTimeInterface::ATOM),
        );
    }
}
