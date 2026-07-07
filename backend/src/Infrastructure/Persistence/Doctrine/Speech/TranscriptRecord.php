<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Speech;

use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptMetadata;
use App\Domain\Video\VideoId;
use App\Application\Speech\TranscriptJsonMapper;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'video_transcript')]
class TranscriptRecord
{
    #[ORM\Id]
    #[ORM\Column(name: 'video_id', type: Types::GUID)]
    private string $videoId;

    #[ORM\Column(name: 'transcript_id', type: Types::GUID)]
    private string $transcriptId;

    #[ORM\Column(type: Types::TEXT)]
    private string $payload;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(
        VideoId $videoId,
        Transcript $transcript,
        TranscriptJsonMapper $mapper,
        ?TranscriptMetadata $metadata = null,
    ): self {
        $record = new self();
        $record->videoId = $videoId->value;
        $record->syncFromDomain($transcript, $mapper, $metadata);
        $record->createdAt = new DateTimeImmutable();

        return $record;
    }

    public function syncFromDomain(
        Transcript $transcript,
        TranscriptJsonMapper $mapper,
        ?TranscriptMetadata $metadata = null,
    ): void {
        $this->transcriptId = $transcript->transcriptId()->value;
        $this->payload = $mapper->toJson($transcript);

        if (null !== $metadata) {
            $this->metadata = $metadata->toArray();
        }
    }

    public function toDomain(TranscriptJsonMapper $mapper): Transcript
    {
        return $mapper->fromJson($this->payload);
    }

    public function metadata(): ?TranscriptMetadata
    {
        return null !== $this->metadata ? TranscriptMetadata::fromArray($this->metadata) : null;
    }
}
