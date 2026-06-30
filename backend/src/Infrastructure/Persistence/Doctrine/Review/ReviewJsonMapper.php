<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Review;

use App\Domain\Review\Review;
use App\Domain\Review\ReviewCategory;
use App\Domain\Review\ReviewComment;
use App\Domain\Review\ReviewId;
use App\Domain\Review\ReviewScore;
use App\Domain\Video\VideoId;
use DateTimeImmutable;

final class ReviewJsonMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Review $review): array
    {
        $scores = [];

        foreach ($review->scores() as $category => $score) {
            $scores[$category] = $score->value();
        }

        return [
            'id' => $review->id()->value,
            'videoId' => $review->videoId()->value,
            'executionVersionNumber' => $review->executionVersionNumber(),
            'scores' => $scores,
            'comment' => $review->comment()->value(),
            'createdAt' => $review->createdAt()->format(DateTimeImmutable::ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function fromArray(array $payload): Review
    {
        $scores = [];

        foreach (ReviewCategory::cases() as $category) {
            $scores[$category->value] = ReviewScore::fromInt((int) $payload['scores'][$category->value]);
        }

        return Review::create(
            new ReviewId((string) $payload['id']),
            new VideoId((string) $payload['videoId']),
            (int) $payload['executionVersionNumber'],
            $scores,
            ReviewComment::fromString((string) ($payload['comment'] ?? '')),
            new DateTimeImmutable((string) $payload['createdAt']),
        );
    }
}
