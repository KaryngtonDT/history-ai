<?php

declare(strict_types=1);

namespace App\Application\Review\Queries;

final readonly class GetReviewsQuery
{
    public function __construct(public string $videoId)
    {
    }
}
