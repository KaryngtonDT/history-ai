<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowTeaching;

use App\Domain\ShadowTeaching\ShadowTeachingRepositoryInterface;
use App\Domain\ShadowTeaching\TeachingPlan;
use App\Domain\ShadowTeaching\TeachingPlanId;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowTeachingRepository implements ShadowTeachingRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly ShadowTeachingPersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?TeachingPlan
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $plan = $this->read($filename);

            if (null !== $plan && $plan->scopeKey() === $scopeKey) {
                return $plan;
            }
        }

        return null;
    }

    public function findById(TeachingPlanId $id): ?TeachingPlan
    {
        return $this->read($id->value.'.json');
    }

    public function save(TeachingPlan $plan): void
    {
        $this->store->write(
            $plan->id()->value.'.json',
            $this->mapper->toArray($plan),
        );
    }

    private function read(string $filename): ?TeachingPlan
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
