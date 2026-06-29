<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Speech;

use App\Domain\Speech\Transcript;
use App\Domain\Video\VideoId;
use App\Infrastructure\Speech\TranscriptJsonMapper;
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

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(
        VideoId $videoId,
        Transcript $transcript,
        TranscriptJsonMapper $mapper,
    ): self {
        $record = new self();
        $record->videoId = $videoId->value;
        $record->syncFromDomain($transcript, $mapper);
        $record->createdAt = new DateTimeImmutable();

        return $record;
    }

    public function syncFromDomain(Transcript $transcript, TranscriptJsonMapper $mapper): void
    {
        $this->transcriptId = $transcript->transcriptId()->value;
        $this->payload = $mapper->toJson($transcript);
    }

    public function toDomain(TranscriptJsonMapper $mapper): Transcript
    {
        return $mapper->fromJson($this->payload);
    }
}
