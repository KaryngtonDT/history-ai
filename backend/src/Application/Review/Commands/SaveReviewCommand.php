<?php

declare(strict_types=1);

namespace App\Application\Review\Commands;

use App\Application\Collaboration\CollaboratorContext;
use App\Domain\Review\ReviewCategory;
use App\Domain\Video\VideoId;

final readonly class SaveReviewCommand
{
    /**
     * @param array<string, int> $scores keyed by ReviewCategory value
     */
    public function __construct(
        public VideoId $videoId,
        public int $executionVersionNumber,
        public array $scores,
        public string $comment,
        public string $actorUserId = CollaboratorContext::DEFAULT_USER_ID,
    ) {
        foreach (ReviewCategory::cases() as $category) {
            if (!isset($this->scores[$category->value])) {
                throw new \InvalidArgumentException(sprintf(
                    'Missing score for category "%s".',
                    $category->value,
                ));
            }
        }
    }
}
