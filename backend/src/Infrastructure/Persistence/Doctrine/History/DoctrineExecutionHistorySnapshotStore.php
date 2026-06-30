<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\History;

use App\Application\History\ExecutionVersionSnapshot;
use App\Application\History\Ports\ExecutionHistorySnapshotStoreInterface;
use App\Domain\History\ExecutionHistory;
use App\Domain\Video\VideoId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineExecutionHistorySnapshotStore implements ExecutionHistorySnapshotStoreInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ExecutionVersionJsonMapper $jsonMapper,
    ) {
    }

    public function findAllByVideoId(VideoId $videoId): array
    {
        $record = $this->entityManager->getRepository(ExecutionHistoryRecord::class)->findOneBy([
            'videoId' => $videoId->value,
        ]);

        if (null === $record) {
            return [];
        }

        return $this->jsonMapper->snapshotsFromArray($record->versionsPayload());
    }

    public function findByVideoIdAndVersion(VideoId $videoId, int $versionNumber): ?ExecutionVersionSnapshot
    {
        foreach ($this->findAllByVideoId($videoId) as $snapshot) {
            if ($snapshot->version->versionNumber() === $versionNumber) {
                return $snapshot;
            }
        }

        return null;
    }

    public function append(ExecutionHistory $history, ExecutionVersionSnapshot $snapshot): void
    {
        $record = $this->entityManager->getRepository(ExecutionHistoryRecord::class)->findOneBy([
            'videoId' => $history->videoId()->value,
        ]);

        $existingSnapshots = null !== $record
            ? $this->jsonMapper->snapshotsFromArray($record->versionsPayload())
            : [];
        $existingSnapshots[] = $snapshot;

        if (null === $record) {
            $this->entityManager->persist(ExecutionHistoryRecord::fromDomain($history, $existingSnapshots));
        } else {
            $record->updateFromDomain($history, $existingSnapshots);
        }

        $this->entityManager->flush();
    }
}
