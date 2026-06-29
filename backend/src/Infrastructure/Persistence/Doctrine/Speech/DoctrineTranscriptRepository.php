<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Speech;

use App\Domain\Speech\Transcript;
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

    public function save(VideoId $videoId, Transcript $transcript): void
    {
        $repository = $this->entityManager->getRepository(TranscriptRecord::class);
        $record = $repository->find($videoId->value);

        if (null === $record) {
            $this->entityManager->persist(TranscriptRecord::fromDomain(
                $videoId,
                $transcript,
                $this->transcriptJsonMapper,
            ));
        } else {
            $record->syncFromDomain($transcript, $this->transcriptJsonMapper);
        }

        $this->entityManager->flush();
    }

    public function findByVideoId(VideoId $videoId): ?Transcript
    {
        $record = $this->entityManager->find(TranscriptRecord::class, $videoId->value);

        return $record?->toDomain($this->transcriptJsonMapper);
    }
}
