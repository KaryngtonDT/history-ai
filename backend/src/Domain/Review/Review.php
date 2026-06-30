<?php

declare(strict_types=1);

namespace App\Domain\Review;

use App\Domain\Review\Exception\InvalidReviewException;
use App\Domain\Video\VideoId;
use DateTimeImmutable;

final readonly class Review
{
    /**
     * @param array<string, ReviewScore> $scores keyed by ReviewCategory value
     */
    public function __construct(
        private ReviewId $id,
        private VideoId $videoId,
        private int $executionVersionNumber,
        private array $scores,
        private ReviewComment $comment,
        private DateTimeImmutable $createdAt,
    ) {
        foreach (ReviewCategory::cases() as $category) {
            if (!isset($this->scores[$category->value])) {
                throw new InvalidReviewException(sprintf(
                    'Review is missing score for category "%s".',
                    $category->value,
                ));
            }
        }

        if ($executionVersionNumber < 1) {
            throw new InvalidReviewException('Execution version number must be at least 1.');
        }
    }

    /**
     * @param array<string, ReviewScore> $scores keyed by ReviewCategory value
     */
    public static function create(
        ReviewId $id,
        VideoId $videoId,
        int $executionVersionNumber,
        array $scores,
        ReviewComment $comment,
        ?DateTimeImmutable $createdAt = null,
    ): self {
        return new self(
            $id,
            $videoId,
            $executionVersionNumber,
            $scores,
            $comment,
            $createdAt ?? new DateTimeImmutable(),
        );
    }

    public function id(): ReviewId
    {
        return $this->id;
    }

    public function videoId(): VideoId
    {
        return $this->videoId;
    }

    public function executionVersionNumber(): int
    {
        return $this->executionVersionNumber;
    }

    /**
     * @return array<string, ReviewScore>
     */
    public function scores(): array
    {
        return $this->scores;
    }

    public function scoreFor(ReviewCategory $category): ReviewScore
    {
        return $this->scores[$category->value];
    }

    public function comment(): ReviewComment
    {
        return $this->comment;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
