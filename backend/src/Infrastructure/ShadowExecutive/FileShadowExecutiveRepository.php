<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowExecutive;

use App\Domain\ShadowExecutive\ExecutivePlan;
use App\Domain\ShadowExecutive\ExecutivePlanId;
use App\Domain\ShadowExecutive\ShadowExecutiveRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowExecutiveRepository implements ShadowExecutiveRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly ShadowExecutivePersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?ExecutivePlan
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $plan = $this->read($filename);

            if (null !== $plan && $plan->scopeKey() === $scopeKey) {
                return $plan;
            }
        }

        return null;
    }

    public function findById(ExecutivePlanId $id): ?ExecutivePlan
    {
        return $this->read($id->value.'.json');
    }

    public function save(ExecutivePlan $plan): void
    {
        $this->store->write($plan->id()->value.'.json', $this->mapper->toArray($plan));
    }

    private function read(string $filename): ?ExecutivePlan
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
