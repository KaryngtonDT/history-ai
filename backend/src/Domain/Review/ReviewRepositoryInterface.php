<?php

declare(strict_types=1);

namespace App\Domain\Review;

use App\Domain\Video\VideoId;

interface ReviewRepositoryInterface
{
    public function append(Review $review): void;

    /**
     * @return list<Review>
     */
    public function findByVideoId(VideoId $videoId): array;

    /**
     * @return list<Review>
     */
    public function findAll(): array;
}
