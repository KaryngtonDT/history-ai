<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowMentor;

use App\Domain\ShadowMentor\MentorPlan;
use App\Domain\ShadowMentor\MentorPlanId;
use App\Domain\ShadowMentor\ShadowMentorRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowMentorRepository implements ShadowMentorRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly ShadowMentorPersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?MentorPlan
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $plan = $this->read($filename);

            if (null !== $plan && $plan->scopeKey() === $scopeKey) {
                return $plan;
            }
        }

        return null;
    }

    public function findById(MentorPlanId $id): ?MentorPlan
    {
        return $this->read($id->value.'.json');
    }

    public function save(MentorPlan $plan): void
    {
        $this->store->write($plan->id()->value.'.json', $this->mapper->toArray($plan));
    }

    private function read(string $filename): ?MentorPlan
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
