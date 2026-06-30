<?php

declare(strict_types=1);

namespace App\Application\Review;

use App\Application\Review\DTO\PreferenceProfileResult;
use App\Domain\Review\ReviewCollection;
use App\Domain\Review\ReviewRepositoryInterface;
use App\Domain\Review\UserPreferenceProfile;
use App\Domain\Review\UserPreferenceProfileRepositoryInterface;

final class BuildPreferenceProfileHandler
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepository,
        private readonly UserPreferenceProfileRepositoryInterface $profileRepository,
    ) {
    }

    public function rebuild(): ?PreferenceProfileResult
    {
        $reviews = $this->reviewRepository->findAll();

        if ([] === $reviews) {
            return null;
        }

        $collection = ReviewCollection::empty();

        foreach ($reviews as $review) {
            $collection = $collection->append($review);
        }

        $profile = UserPreferenceProfile::deriveFromReviews($collection);
        $this->profileRepository->save($profile);

        return $this->toResult($profile, $collection);
    }

    public function getCurrent(): ?PreferenceProfileResult
    {
        $profile = $this->profileRepository->findCurrent();

        if (null === $profile) {
            return $this->rebuild();
        }

        $reviews = $this->reviewRepository->findAll();
        $collection = ReviewCollection::empty();

        foreach ($reviews as $review) {
            $collection = $collection->append($review);
        }

        if ($collection->isEmpty()) {
            return null;
        }

        return $this->toResult($profile, $collection);
    }

    private function toResult(UserPreferenceProfile $profile, ReviewCollection $collection): PreferenceProfileResult
    {
        return new PreferenceProfileResult(
            $profile->translationStyle()->value,
            $profile->voiceStability()->value,
            $profile->renderingPreset()->value,
            $profile->lipSyncStrength()->value,
            $collection->latestComment()->value(),
            $collection->count(),
            $profile->explanationLines(),
        );
    }
}
