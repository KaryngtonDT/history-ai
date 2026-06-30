<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Review;

use App\Application\Review\DTO\ReviewResult;

final class ReviewResponseFactory
{
    /**
     * @return array<string, mixed>
     */
    public static function fromResult(ReviewResult $result): array
    {
        return [
            'id' => $result->id,
            'videoId' => $result->videoId,
            'executionVersionNumber' => $result->executionVersionNumber,
            'scores' => $result->scores,
            'comment' => $result->comment,
            'createdAt' => $result->createdAt,
        ];
    }
}
