<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Handlers;

use App\Application\Pipeline\Commands\SavePipelineConfigurationCommand;
use App\Application\Pipeline\DTO\PipelineConfigurationResult;
use App\Application\Pipeline\PipelineConfigurationValidator;
use App\Domain\Pipeline\Exception\InvalidPipelineConfigurationException;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineConfigurationRepositoryInterface;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use App\Application\Runtime\RuntimeSelectionSynchronizerInterface;

final class SavePipelineConfigurationHandler
{
    public function __construct(
        private readonly PipelineConfigurationRepositoryInterface $pipelineConfigurationRepository,
        private readonly PipelineConfigurationValidator $pipelineConfigurationValidator,
        private readonly RuntimeSelectionSynchronizerInterface $runtimeSelectionSynchronizer,
    ) {
    }

    public function __invoke(SavePipelineConfigurationCommand $command): PipelineConfigurationResult
    {
        $stages = [];

        foreach ($command->stages as $rawStage) {
            $stageValue = $rawStage['stage'] ?? null;
            $providerId = $rawStage['providerId'] ?? null;

            if (!is_string($stageValue) || !is_string($providerId)) {
                throw new InvalidPipelineConfigurationException('Each pipeline stage must include stage and providerId.');
            }

            $stageType = PipelineStageType::tryFrom($stageValue);

            if (null === $stageType) {
                throw new InvalidPipelineConfigurationException(sprintf('Invalid pipeline stage "%s".', $stageValue));
            }

            $this->pipelineConfigurationValidator->assertProviderEnabled($stageType, $providerId);
            $stages[] = PipelineStage::create($stageType, $providerId);
        }

        $latest = $this->pipelineConfigurationRepository->findLatest();
        $version = (null !== $latest ? $latest->version() : 0) + 1;
        $now = new \DateTimeImmutable();

        $configuration = PipelineConfiguration::create(
            PipelineConfigurationId::generate(),
            $stages,
            $version,
            $now,
            $now,
        );

        $this->pipelineConfigurationRepository->save($configuration);
        $this->runtimeSelectionSynchronizer->syncFromPipelineConfiguration($configuration);

        return PipelineConfigurationResult::fromConfiguration($configuration);
    }
}
