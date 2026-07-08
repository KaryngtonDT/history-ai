<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Kernel;

use App\Application\Runtime\PipelineStageCapabilityMapper;
use App\Application\Runtime\RuntimeResolverInterface;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Runtime\EngineExecutionPlan;
use App\Domain\Runtime\RuntimeResolveContext;

final class EngineExecutionAdapter
{
    public function __construct(private readonly RuntimeResolverInterface $runtimeResolver)
    {
    }

    public function planForStage(
        PipelineStageType $stage,
        RuntimeResolveContext $context = new RuntimeResolveContext(),
    ): EngineExecutionPlan {
        $capability = PipelineStageCapabilityMapper::fromPipelineStage($stage);

        return $this->runtimeResolver->resolveCapability($capability, $context);
    }

    public function legacyProviderIdForStage(
        PipelineStageType $stage,
        RuntimeResolveContext $context = new RuntimeResolveContext(),
    ): string {
        $plan = $this->planForStage($stage, $context);
        $resolved = $plan->resolvedEngine;

        if ($resolved->blocked && !$resolved->executable) {
            throw new \RuntimeException(sprintf(
                'Engine "%s" is blocked for stage "%s": %s',
                $resolved->engineId,
                $stage->value,
                $resolved->blockedReason ?? 'unknown reason',
            ));
        }

        return $resolved->adapterKey;
    }

    public function planForCapability(
        EngineCatalogCapability $capability,
        RuntimeResolveContext $context = new RuntimeResolveContext(),
    ): EngineExecutionPlan {
        return $this->runtimeResolver->resolveCapability($capability, $context);
    }
}
