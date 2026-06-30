<?php

declare(strict_types=1);

namespace App\Application\Review\DTO;

final readonly class ReviewResult
{
    /**
     * @param array<string, int> $scores
     */
    public function __construct(
        public string $id,
        public string $videoId,
        public int $executionVersionNumber,
        public array $scores,
        public string $comment,
        public string $createdAt,
    ) {
    }
}
