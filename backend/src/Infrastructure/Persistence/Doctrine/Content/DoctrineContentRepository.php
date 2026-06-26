<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Content;

use App\Domain\Content\Content;
use App\Domain\Content\ContentId;
use App\Domain\Content\ContentRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineContentRepository implements ContentRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Content $content): void
    {
        $repository = $this->entityManager->getRepository(ContentRecord::class);
        $record = $repository->find($content->id()->value);

        if (null === $record) {
            $record = ContentRecord::fromDomain($content);
            $this->entityManager->persist($record);
        } else {
            $record->syncFromDomain($content);
        }

        $this->entityManager->flush();
    }

    public function findById(ContentId $id): ?Content
    {
        $record = $this->entityManager->find(ContentRecord::class, $id->value);

        return $record?->toDomain();
    }
}
