<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Processing;

use App\Domain\Processing\ProcessingJob;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineProcessingJobRepository implements ProcessingJobRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(ProcessingJob $job): void
    {
        $repository = $this->entityManager->getRepository(ProcessingJobRecord::class);
        $record = $repository->find($job->id()->value);

        if (null === $record) {
            $record = ProcessingJobRecord::fromDomain($job);
            $this->entityManager->persist($record);
        } else {
            $record->syncFromDomain($job);
        }

        $this->entityManager->flush();
    }

    public function findById(ProcessingJobId $id): ?ProcessingJob
    {
        $record = $this->entityManager->find(ProcessingJobRecord::class, $id->value);

        return $record?->toDomain();
    }
}
