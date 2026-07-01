<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\YouTube;

use App\Domain\YouTube\YouTubeVideo;
use App\Domain\YouTube\YouTubeVideoId;
use App\Domain\YouTube\YouTubeVideoRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineYouTubeVideoRepository implements YouTubeVideoRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(YouTubeVideo $video): void
    {
        $repository = $this->entityManager->getRepository(YouTubeImportRecord::class);
        $record = $repository->find($video->id()->value);

        if (null === $record) {
            $this->entityManager->persist(YouTubeImportRecord::fromDomain($video));
        } else {
            $record->syncFromDomain($video);
        }

        $this->entityManager->flush();
    }

    public function findById(YouTubeVideoId $id): ?YouTubeVideo
    {
        $record = $this->entityManager->find(YouTubeImportRecord::class, $id->value);

        return $record?->toDomain();
    }

    public function findRecent(int $limit = 20): array
    {
        /** @var list<YouTubeImportRecord> $records */
        $records = $this->entityManager->createQueryBuilder()
            ->select('youtube')
            ->from(YouTubeImportRecord::class, 'youtube')
            ->orderBy('youtube.importedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(static fn (YouTubeImportRecord $record): YouTubeVideo => $record->toDomain(), $records);
    }
}
