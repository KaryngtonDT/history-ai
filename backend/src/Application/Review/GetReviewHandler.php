<?php

declare(strict_types=1);

namespace App\Application\Review;

use App\Application\Review\DTO\ReviewResult;
use App\Application\Review\Queries\GetReviewsQuery;
use App\Domain\Review\ReviewRepositoryInterface;
use App\Domain\Review\ReviewScore;
use App\Domain\Video\VideoId;

final class GetReviewHandler
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepository,
    ) {
    }

    /**
     * @return list<ReviewResult>
     */
    public function __invoke(GetReviewsQuery $query): array
    {
        $reviews = $this->reviewRepository->findByVideoId(new VideoId($query->videoId));

        return array_map(
            static fn ($review): ReviewResult => new ReviewResult(
                $review->id()->value,
                $review->videoId()->value,
                $review->executionVersionNumber(),
                array_map(
                    static fn (ReviewScore $score): int => $score->value(),
                    $review->scores(),
                ),
                $review->comment()->value(),
                $review->createdAt()->format(\DateTimeInterface::ATOM),
            ),
            $reviews,
        );
    }
}
