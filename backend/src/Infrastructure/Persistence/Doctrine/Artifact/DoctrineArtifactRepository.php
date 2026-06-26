<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Artifact;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineArtifactRepository implements ArtifactRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Artifact $artifact): void
    {
        $repository = $this->entityManager->getRepository(ArtifactRecord::class);
        $record = $repository->find($artifact->id()->value);

        if (null === $record) {
            $this->entityManager->persist(ArtifactRecord::fromDomain($artifact));
        }

        $this->entityManager->flush();
    }

    public function findById(ArtifactId $id): ?Artifact
    {
        $record = $this->entityManager->find(ArtifactRecord::class, $id->value);

        return $record?->toDomain();
    }
}
