<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Handlers;

use App\Application\Pipeline\DTO\PipelineConfigurationResult;
use App\Application\Pipeline\PipelineConfigurationFactory;
use App\Domain\Pipeline\PipelineConfigurationRepositoryInterface;

final class ResetPipelineConfigurationHandler
{
    public function __construct(
        private readonly PipelineConfigurationRepositoryInterface $pipelineConfigurationRepository,
        private readonly PipelineConfigurationFactory $pipelineConfigurationFactory,
    ) {
    }

    public function __invoke(): PipelineConfigurationResult
    {
        $this->pipelineConfigurationRepository->deleteAll();

        $configuration = $this->pipelineConfigurationFactory->createDefault();
        $now = new \DateTimeImmutable();
        $configuration = $configuration
            ->withVersion(1)
            ->withTimestamps($now, $now);

        $this->pipelineConfigurationRepository->save($configuration);

        return PipelineConfigurationResult::fromConfiguration($configuration);
    }
}
