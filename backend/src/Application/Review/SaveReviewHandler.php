<?php

declare(strict_types=1);

namespace App\Application\Review;

use App\Application\Collaboration\WorkspaceAuthorizationGuard;
use App\Application\Review\Commands\SaveReviewCommand;
use App\Application\Review\DTO\ReviewResult;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Review\Review;
use App\Domain\Review\ReviewCategory;
use App\Domain\Review\ReviewComment;
use App\Domain\Review\ReviewId;
use App\Domain\Review\ReviewRepositoryInterface;
use App\Domain\Review\ReviewScore;

final class SaveReviewHandler
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepository,
        private readonly BuildPreferenceProfileHandler $profileHandler,
        private readonly WorkspaceAuthorizationGuard $authorizationGuard,
    ) {
    }

    public function __invoke(SaveReviewCommand $command): ReviewResult
    {
        $this->authorizationGuard->assertVideoAction(
            $command->videoId->value,
            $command->actorUserId,
            WorkspaceAction::Review,
        );

        $scores = [];

        foreach (ReviewCategory::cases() as $category) {
            $scores[$category->value] = ReviewScore::fromInt($command->scores[$category->value]);
        }

        $review = Review::create(
            ReviewId::generate(),
            $command->videoId,
            $command->executionVersionNumber,
            $scores,
            ReviewComment::fromString($command->comment),
        );

        $this->reviewRepository->append($review);
        $this->profileHandler->rebuild();

        return new ReviewResult(
            $review->id()->value,
            $review->videoId()->value,
            $review->executionVersionNumber(),
            array_map(
                static fn (ReviewScore $score): int => $score->value(),
                $review->scores(),
            ),
            $review->comment()->value(),
            $review->createdAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
