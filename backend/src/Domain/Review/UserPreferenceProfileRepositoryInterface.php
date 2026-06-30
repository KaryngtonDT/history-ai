<?php

declare(strict_types=1);

namespace App\Domain\Review;

interface UserPreferenceProfileRepositoryInterface
{
    public function findCurrent(): ?UserPreferenceProfile;

    public function save(UserPreferenceProfile $profile): void;
}
