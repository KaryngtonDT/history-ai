<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Kernel;

use App\Application\Runtime\PipelineStageCapabilityMapper;
use App\Application\Runtime\RuntimeResolverInterface;
use App\Application\Runtime\RuntimeSelectionSynchronizerInterface;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationRepositoryInterface;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Runtime\RuntimeRepositoryInterface;
use App\Domain\Engine\SelectionMode;
use Psr\Log\LoggerInterface;

final class RuntimeSelectionSynchronizer implements RuntimeSelectionSynchronizerInterface
{
    public function __construct(
        private readonly PipelineConfigurationRepositoryInterface $pipelineConfigurationRepository,
        private readonly RuntimeRepositoryInterface $runtimeRepository,
        private readonly EngineAdapterRegistry $adapterRegistry,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function syncFromPipelineConfiguration(?PipelineConfiguration $configuration = null): void
    {
        $configuration ??= $this->pipelineConfigurationRepository->findLatest();

        if (null === $configuration) {
            return;
        }

        $manual = [];
        foreach ($configuration->stages()->all() as $stage) {
            $capability = PipelineStageCapabilityMapper::fromPipelineStage($stage->stage());
            $engineId = $this->adapterRegistry->engineIdForLegacyProviderAndCapability(
                $stage->providerId(),
                $capability->value,
            );
            $manual[$capability->value] = $engineId;
        }

        $runtimeConfig = $this->runtimeRepository->getConfiguration();
        $updated = $runtimeConfig
            ->withSelectionMode(SelectionMode::Manual)
            ->withManualSelections($manual);

        $this->runtimeRepository->saveConfiguration($updated);

        $this->logger->info('Runtime selection synchronized from pipeline configuration.', [
            'manualSelections' => $manual,
        ]);
    }

    /**
     * @return array<string, string> capability => legacy provider id
     */
    public function legacyProvidersFromRuntime(): array
    {
        $config = $this->runtimeRepository->getConfiguration();
        $providers = [];

        foreach ($config->manualSelections as $capability => $engineId) {
            $stage = $this->capabilityToStage($capability);

            if (null === $stage) {
                continue;
            }

            $providers[$stage->value] = $this->adapterRegistry->adapterKeyForEngine($engineId);
        }

        return $providers;
    }

    private function capabilityToStage(string $capability): ?PipelineStageType
    {
        $catalogCapability = \App\Domain\Engine\EngineCatalogCapability::tryFrom($capability);

        if (null === $catalogCapability) {
            return null;
        }

        return PipelineStageCapabilityMapper::toPipelineStage($catalogCapability);
    }
}
