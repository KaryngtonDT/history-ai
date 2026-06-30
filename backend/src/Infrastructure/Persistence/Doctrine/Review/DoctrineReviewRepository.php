<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Review;

use App\Domain\Review\Review;
use App\Domain\Review\ReviewRepositoryInterface;
use App\Domain\Video\VideoId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineReviewRepository implements ReviewRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReviewJsonMapper $jsonMapper,
    ) {
    }

    public function append(Review $review): void
    {
        $this->entityManager->persist(
            VideoReviewRecord::fromPayload($this->jsonMapper->toArray($review)),
        );
        $this->entityManager->flush();
    }

    public function findByVideoId(VideoId $videoId): array
    {
        /** @var list<VideoReviewRecord> $records */
        $records = $this->entityManager->getRepository(VideoReviewRecord::class)->findBy(
            ['videoId' => $videoId->value],
            ['createdAt' => 'ASC'],
        );

        return array_map(
            fn (VideoReviewRecord $record): Review => $this->jsonMapper->fromArray($record->toPayload()),
            $records,
        );
    }

    public function findAll(): array
    {
        /** @var list<VideoReviewRecord> $records */
        $records = $this->entityManager->getRepository(VideoReviewRecord::class)->findBy(
            [],
            ['createdAt' => 'ASC'],
        );

        return array_map(
            fn (VideoReviewRecord $record): Review => $this->jsonMapper->fromArray($record->toPayload()),
            $records,
        );
    }
}
