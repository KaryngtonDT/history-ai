<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Handlers;

use App\Application\Pipeline\DTO\PipelineConfigurationResult;
use App\Application\Pipeline\PipelineConfigurationFactory;
use App\Domain\Pipeline\PipelineConfigurationRepositoryInterface;

final class LoadPipelineConfigurationHandler
{
    public function __construct(
        private readonly PipelineConfigurationRepositoryInterface $pipelineConfigurationRepository,
        private readonly PipelineConfigurationFactory $pipelineConfigurationFactory,
    ) {
    }

    public function __invoke(): PipelineConfigurationResult
    {
        $configuration = $this->pipelineConfigurationRepository->findLatest()
            ?? $this->pipelineConfigurationFactory->createDefault();

        return PipelineConfigurationResult::fromConfiguration($configuration);
    }
}
