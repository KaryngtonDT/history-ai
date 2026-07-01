<?php

declare(strict_types=1);

namespace App\Infrastructure\Learning;

use App\Domain\Learning\LearningProfile;
use App\Domain\Learning\LearningProfileId;
use App\Domain\Learning\LearningProfileRepositoryInterface;

final class InMemoryLearningProfileRepository implements LearningProfileRepositoryInterface
{
    /** @var array<string, LearningProfile> */
    private array $profilesById = [];

    /** @var array<string, string> */
    private array $scopeIndex = [];

    public function findByScope(string $scopeKey): ?LearningProfile
    {
        $profileId = $this->scopeIndex[$scopeKey] ?? null;

        if (null === $profileId) {
            return null;
        }

        return $this->profilesById[$profileId] ?? null;
    }

    public function findById(LearningProfileId $id): ?LearningProfile
    {
        return $this->profilesById[$id->value] ?? null;
    }

    public function save(LearningProfile $profile): void
    {
        $this->profilesById[$profile->id()->value] = $profile;
        $this->scopeIndex[$profile->scopeKey()] = $profile->id()->value;
    }

    public function clear(): void
    {
        $this->profilesById = [];
        $this->scopeIndex = [];
    }
}
