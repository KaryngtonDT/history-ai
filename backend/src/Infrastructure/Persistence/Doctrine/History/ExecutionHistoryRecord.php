<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\History;

use App\Domain\History\ExecutionHistory;
use App\Domain\History\ExecutionHistoryId;
use App\Domain\History\ExecutionVersionCollection;
use App\Domain\Video\VideoId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'video_execution_histories')]
#[ORM\UniqueConstraint(name: 'uniq_video_execution_histories_video', columns: ['video_id'])]
class ExecutionHistoryRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(name: 'video_id', type: Types::GUID)]
    private string $videoId;

    /** @var list<array<string, mixed>> */
    #[ORM\Column(type: Types::JSON)]
    private array $versions = [];

    private function __construct()
    {
    }

    public static function fromDomain(ExecutionHistory $history, array $snapshots): self
    {
        $record = new self();
        $record->id = $history->id()->value;
        $record->videoId = $history->videoId()->value;
        $record->versions = (new ExecutionVersionJsonMapper())->snapshotsToArray($snapshots);

        return $record;
    }

    public function updateFromDomain(ExecutionHistory $history, array $snapshots): void
    {
        $this->videoId = $history->videoId()->value;
        $this->versions = (new ExecutionVersionJsonMapper())->snapshotsToArray($snapshots);
    }

    public function toDomain(): ExecutionHistory
    {
        $mapper = new ExecutionVersionJsonMapper();
        $versions = $mapper->versionsFromJson(json_encode($this->versions, JSON_THROW_ON_ERROR));

        return ExecutionHistory::reconstitute(
            new ExecutionHistoryId($this->id),
            new VideoId($this->videoId),
            new ExecutionVersionCollection($versions),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function versionsPayload(): array
    {
        return $this->versions;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function videoId(): string
    {
        return $this->videoId;
    }
}
