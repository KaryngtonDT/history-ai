<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\History;

use App\Domain\History\ExecutionHistory;
use App\Domain\History\ExecutionHistoryId;
use App\Domain\History\ExecutionHistoryRepositoryInterface;
use App\Domain\Video\VideoId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineExecutionHistoryRepository implements ExecutionHistoryRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ExecutionVersionJsonMapper $jsonMapper,
    ) {
    }

    public function save(ExecutionHistory $history): void
    {
        $record = $this->entityManager->getRepository(ExecutionHistoryRecord::class)->findOneBy([
            'videoId' => $history->videoId()->value,
        ]);

        if (null === $record) {
            $this->entityManager->persist(ExecutionHistoryRecord::fromDomain($history, []));
        } else {
            $record->updateFromDomain(
                $history,
                $this->jsonMapper->snapshotsFromArray($record->versionsPayload()),
            );
        }

        $this->entityManager->flush();
    }

    public function findByVideoId(VideoId $videoId): ?ExecutionHistory
    {
        $record = $this->entityManager->getRepository(ExecutionHistoryRecord::class)->findOneBy([
            'videoId' => $videoId->value,
        ]);

        return $record?->toDomain();
    }

    public function findOrCreateForVideo(VideoId $videoId): ExecutionHistory
    {
        $existing = $this->findByVideoId($videoId);

        if (null !== $existing) {
            return $existing;
        }

        return ExecutionHistory::create(ExecutionHistoryId::generate(), $videoId);
    }
}
