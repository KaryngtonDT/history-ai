<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Catalog;

use App\Domain\Engine\Engine;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Infrastructure\Runtime\Discovery\EngineReadinessAssessor;

final class StaticEngineRepository implements EngineRepositoryInterface
{
    public function __construct(private readonly EngineReadinessAssessor $assessor)
    {
    }

    public function all(): array
    {
        return array_map(
            fn (EngineDefinition $definition): Engine => $this->assessor->assess($definition),
            EngineCatalogDefinitions::all(),
        );
    }

    public function findById(string $id): ?Engine
    {
        $definition = EngineCatalogDefinitions::findById($id);

        if (null === $definition) {
            return null;
        }

        return $this->assessor->assess($definition);
    }

    public function findByCapability(EngineCatalogCapability $capability): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (Engine $engine): bool => $engine->capability === $capability,
        ));
    }
}
