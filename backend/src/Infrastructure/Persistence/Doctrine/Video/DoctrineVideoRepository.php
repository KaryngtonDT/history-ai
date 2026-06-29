<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Video;

use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineVideoRepository implements VideoRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(VideoJob $job): void
    {
        $repository = $this->entityManager->getRepository(VideoJobRecord::class);
        $record = $repository->find($job->id()->value);

        if (null === $record) {
            $this->entityManager->persist(VideoJobRecord::fromDomain($job));
        } else {
            $record->syncFromDomain($job);
        }

        $this->entityManager->flush();
    }

    public function findById(VideoId $id): ?VideoJob
    {
        $record = $this->entityManager->find(VideoJobRecord::class, $id->value);

        return $record?->toDomain();
    }
}
