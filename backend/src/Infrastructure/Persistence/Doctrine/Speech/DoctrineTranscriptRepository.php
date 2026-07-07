<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Speech;

use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptMetadata;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Application\Speech\TranscriptJsonMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTranscriptRepository implements TranscriptRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranscriptJsonMapper $transcriptJsonMapper,
    ) {
    }

    public function save(VideoId $videoId, Transcript $transcript, ?TranscriptMetadata $metadata = null): void
    {
        $repository = $this->entityManager->getRepository(TranscriptRecord::class);
        $record = $repository->find($videoId->value);

        if (null === $record) {
            $this->entityManager->persist(TranscriptRecord::fromDomain(
                $videoId,
                $transcript,
                $this->transcriptJsonMapper,
                $metadata,
            ));
        } else {
            $record->syncFromDomain($transcript, $this->transcriptJsonMapper, $metadata);
        }

        $this->entityManager->flush();
    }

    public function findByVideoId(VideoId $videoId): ?Transcript
    {
        $record = $this->entityManager->find(TranscriptRecord::class, $videoId->value);

        return $record?->toDomain($this->transcriptJsonMapper);
    }

    public function findMetadataByVideoId(VideoId $videoId): ?TranscriptMetadata
    {
        $record = $this->entityManager->find(TranscriptRecord::class, $videoId->value);

        return $record?->metadata();
    }
}
