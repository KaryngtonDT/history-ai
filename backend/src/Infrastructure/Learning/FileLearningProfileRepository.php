<?php

declare(strict_types=1);

namespace App\Infrastructure\Learning;

use App\Application\Learning\LearningProfileJsonMapper;
use App\Domain\Learning\LearningProfile;
use App\Domain\Learning\LearningProfileId;
use App\Domain\Learning\LearningProfileRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileLearningProfileRepository implements LearningProfileRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly LearningProfileJsonMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?LearningProfile
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $profile = $this->readProfile($filename);

            if (null !== $profile && $profile->scopeKey() === $scopeKey) {
                return $profile;
            }
        }

        return null;
    }

    public function findById(LearningProfileId $id): ?LearningProfile
    {
        return $this->readProfile($this->filenameForId($id->value));
    }

    public function save(LearningProfile $profile): void
    {
        $this->store->write(
            $this->filenameForId($profile->id()->value),
            json_decode($this->mapper->toJson($profile), true, 512, JSON_THROW_ON_ERROR),
        );
    }

    public function clear(): void
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $this->store->delete($filename);
        }
    }

    private function readProfile(string $filename): ?LearningProfile
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }

    private function filenameForId(string $id): string
    {
        return $id . '.json';
    }
}
