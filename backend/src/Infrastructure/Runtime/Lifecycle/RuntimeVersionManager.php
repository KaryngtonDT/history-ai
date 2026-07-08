<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Lifecycle;

use App\Domain\Engine\EngineRepositoryInterface;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;

final class RuntimeVersionManager
{
    public function __construct(private readonly EngineRepositoryInterface $engineRepository)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function version(string $engineId): ?array
    {
        $engine = $this->engineRepository->findById($engineId);
        $definition = EngineCatalogDefinitions::findById($engineId);

        if (null === $engine && null === $definition) {
            return null;
        }

        return [
            'engineId' => $engineId,
            'displayName' => $engine?->displayName ?? $definition?->displayName,
            'version' => $engine?->version?->toArray(),
            'expectedModel' => $engine?->expectedModel ?? $definition?->expectedModel,
            'ollamaModelTag' => $engine?->ollamaModelTag ?? $definition?->ollamaModelTag,
            'family' => $engine?->family->value ?? $definition?->family->value,
            'tier' => $engine?->tier->value ?? $definition?->tier->value,
        ];
    }
}
