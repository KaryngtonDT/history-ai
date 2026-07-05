<?php

declare(strict_types=1);

namespace App\Domain\Engine;

interface EngineRepositoryInterface
{
    /**
     * @return list<Engine>
     */
    public function all(): array;

    public function findById(string $id): ?Engine;

    /**
     * @return list<Engine>
     */
    public function findByCapability(EngineCatalogCapability $capability): array;
}
