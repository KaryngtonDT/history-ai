<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Review;

use App\Domain\Review\UserPreferenceProfile;
use App\Domain\Review\UserPreferenceProfileRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserPreferenceProfileRepository implements UserPreferenceProfileRepositoryInterface
{
    private const string DEFAULT_ID = 'default';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function findCurrent(): ?UserPreferenceProfile
    {
        $record = $this->entityManager->find(UserPreferenceProfileRecord::class, self::DEFAULT_ID);

        return $record?->toDomain();
    }

    public function save(UserPreferenceProfile $profile): void
    {
        $record = $this->entityManager->find(UserPreferenceProfileRecord::class, self::DEFAULT_ID);

        if (null === $record) {
            $this->entityManager->persist(UserPreferenceProfileRecord::fromDomain($profile));
        } else {
            $record->updateFromDomain($profile);
        }

        $this->entityManager->flush();
    }
}
