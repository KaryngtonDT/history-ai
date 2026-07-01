<?php

declare(strict_types=1);

namespace App\Application\Review\Queries;

use App\Application\Collaboration\CollaboratorContext;

final readonly class GetReviewsQuery
{
    public function __construct(
        public string $videoId,
        public string $actorUserId = CollaboratorContext::DEFAULT_USER_ID,
    ) {
    }
}
