<?php

declare(strict_types=1);

namespace App\Domain\Review;

use App\Domain\Review\Exception\InvalidReviewException;

final readonly class ReviewCollection
{
    /** @var list<Review> */
    private array $reviews;

    /**
     * @param list<Review> $reviews
     */
    public function __construct(array $reviews = [])
    {
        $ordered = $reviews;

        usort(
            $ordered,
            static fn (Review $left, Review $right): int => $left->createdAt() <=> $right->createdAt(),
        );

        $this->reviews = $ordered;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<Review>
     */
    public function all(): array
    {
        return $this->reviews;
    }

    public function count(): int
    {
        return count($this->reviews);
    }

    public function isEmpty(): bool
    {
        return [] === $this->reviews;
    }

    public function append(Review $review): self
    {
        return new self([...$this->reviews, $review]);
    }

    public function latest(): ?Review
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->reviews[array_key_last($this->reviews)];
    }

    public function latestComment(): ReviewComment
    {
        $latest = $this->latest();

        return null !== $latest ? $latest->comment() : ReviewComment::empty();
    }

    /**
     * @return array<string, ReviewScore> keyed by ReviewCategory value
     */
    public function averageScores(): array
    {
        if ($this->isEmpty()) {
            throw new InvalidReviewException('Cannot average scores on an empty review collection.');
        }

        $totals = [];
        $counts = [];

        foreach (ReviewCategory::cases() as $category) {
            $totals[$category->value] = 0;
            $counts[$category->value] = 0;
        }

        foreach ($this->reviews as $review) {
            foreach ($review->scores() as $categoryValue => $score) {
                $totals[$categoryValue] += $score->value();
                ++$counts[$categoryValue];
            }
        }

        $averages = [];

        foreach (ReviewCategory::cases() as $category) {
            $count = $counts[$category->value];
            $averages[$category->value] = ReviewScore::fromInt((int) round($totals[$category->value] / $count));
        }

        return $averages;
    }
}
