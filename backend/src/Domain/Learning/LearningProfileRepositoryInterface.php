<?php

declare(strict_types=1);

namespace App\Domain\Learning;

interface LearningProfileRepositoryInterface
{
    public function findByScope(string $scopeKey): ?LearningProfile;

    public function findById(LearningProfileId $id): ?LearningProfile;

    public function save(LearningProfile $profile): void;
}
