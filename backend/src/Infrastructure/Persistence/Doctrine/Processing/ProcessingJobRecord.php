<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Processing;

use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJob;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobProgress;
use App\Domain\Processing\ProcessingJobStatus;
use App\Domain\Processing\ProcessingJobType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'processing_jobs')]
class ProcessingJobRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(type: Types::GUID)]
    private string $contentId;

    #[ORM\Column(length: 32, enumType: ProcessingJobType::class)]
    private ProcessingJobType $type;

    #[ORM\Column(length: 32, enumType: ProcessingJobStatus::class)]
    private ProcessingJobStatus $status;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $progress;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $startedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $completedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $failedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    private function __construct()
    {
    }

    public static function fromDomain(ProcessingJob $job): self
    {
        $record = new self();
        $record->id = $job->id()->value;
        $record->syncFromDomain($job);
        $record->contentId = $job->contentId()->value;
        $record->type = $job->type();
        $record->createdAt = $job->createdAt();

        return $record;
    }

    public function syncFromDomain(ProcessingJob $job): void
    {
        $this->status = $job->status();
        $this->progress = $job->progress()->percentage();
        $this->startedAt = $job->startedAt();
        $this->completedAt = $job->completedAt();
        $this->failedAt = $job->failedAt();
        $this->updatedAt = $job->updatedAt();
    }

    public function toDomain(): ProcessingJob
    {
        return ProcessingJob::reconstitute(
            new ProcessingJobId($this->id),
            new ContentId($this->contentId),
            $this->type,
            $this->status,
            ProcessingJobProgress::fromPercentage($this->progress),
            $this->createdAt,
            $this->updatedAt,
            $this->startedAt,
            $this->completedAt,
            $this->failedAt,
        );
    }

    public function id(): string
    {
        return $this->id;
    }
}
