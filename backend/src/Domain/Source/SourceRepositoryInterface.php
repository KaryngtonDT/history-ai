<?php

declare(strict_types=1);

namespace App\Domain\Source;

interface SourceRepositoryInterface
{
    public function save(Source $source): void;

    public function findById(SourceId $id): ?Source;

    public function delete(SourceId $id): void;

    /**
     * @return list<Source>
     */
    public function findByType(SourceType $type, int $limit = 20): array;
}
