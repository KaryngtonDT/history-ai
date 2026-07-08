<?php

declare(strict_types=1);

namespace App\Application\Runtime;

use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Runtime\EngineExecutionPlan;
use App\Domain\Runtime\ResolvedEngine;
use App\Domain\Runtime\RuntimeResolveContext;
use App\Domain\Runtime\RuntimeResolveRequest;

interface RuntimeResolverInterface
{
    public function resolve(RuntimeResolveRequest $request): EngineExecutionPlan;

    public function resolveCapability(
        EngineCatalogCapability $capability,
        RuntimeResolveContext $context = new RuntimeResolveContext(),
    ): EngineExecutionPlan;

    public function resolveEngineMetadata(
        EngineCatalogCapability $capability,
        RuntimeResolveContext $context = new RuntimeResolveContext(),
    ): ResolvedEngine;

    /**
     * @return array<string, mixed>
     */
    public function capabilitySelectionView(EngineCatalogCapability $capability): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function capabilities(): array;

    /**
     * @return array<string, mixed>
     */
    public function selection(): array;
}
