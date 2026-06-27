<?php

declare(strict_types=1);

namespace App\Domain\Collection;

interface CollectionRepositoryInterface
{
    public function save(Collection $collection): void;

    public function findById(CollectionId $id): ?Collection;

    /**
     * @return list<Collection>
     */
    public function findAll(): array;
}
