<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\BatchJob;
use App\Domain\Workspace\BatchJobId;
use App\Domain\Workspace\BatchJobProgress;
use App\Domain\Workspace\BatchJobStatus;
use App\Domain\Workspace\ProjectId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'workspace_batch_jobs')]
#[ORM\Index(name: 'idx_workspace_batch_jobs_project', columns: ['project_id'])]
class BatchJobRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(type: Types::GUID)]
    private string $projectId;

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $videoIds = [];

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $targetLanguages = [];

    #[ORM\Column(length: 32)]
    private string $status;

    #[ORM\Column(type: Types::INTEGER)]
    private int $progress;

    /** @var array<string, string> */
    #[ORM\Column(type: Types::JSON)]
    private array $outcomes = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(BatchJob $batchJob): self
    {
        $record = new self();
        $record->applyDomain($batchJob);
        $record->createdAt = new DateTimeImmutable();

        return $record;
    }

    public function applyDomain(BatchJob $batchJob): void
    {
        $this->id = $batchJob->id()->value;
        $this->projectId = $batchJob->projectId()->value;
        $this->videoIds = array_map(
            static fn (VideoId $videoId): string => $videoId->value,
            $batchJob->videoIds(),
        );
        $this->targetLanguages = $batchJob->targetLanguages();
        $this->status = $batchJob->status()->value;
        $this->progress = $batchJob->progress()->percentage();
    }

    public function toDomain(): BatchJob
    {
        $videoIds = array_map(
            static fn (string $videoId): VideoId => new VideoId($videoId),
            $this->videoIds,
        );

        return BatchJob::create(
            new BatchJobId($this->id),
            new ProjectId($this->projectId),
            $videoIds,
            $this->targetLanguages,
        )->withStatus(
            BatchJobStatus::from($this->status),
            BatchJobProgress::fromPercentage($this->progress),
        );
    }

    /**
     * @return array<string, string>
     */
    public function outcomes(): array
    {
        return $this->outcomes;
    }

    public function setOutcomes(array $outcomes): void
    {
        $this->outcomes = $outcomes;
    }

    public function updateStatus(BatchJobStatus $status, BatchJobProgress $progress): void
    {
        $this->status = $status->value;
        $this->progress = $progress->percentage();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function projectId(): string
    {
        return $this->projectId;
    }

    /** @return list<string> */
    public function videoIds(): array
    {
        return $this->videoIds;
    }
}
