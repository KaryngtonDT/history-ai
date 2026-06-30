<?php

declare(strict_types=1);

namespace App\Infrastructure\Pipeline;

use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationRepositoryInterface;
use App\Domain\Pipeline\PipelineConfigurationResolverInterface;

final class ResolvingPipelineConfiguration implements PipelineConfigurationResolverInterface
{
    public function __construct(
        private readonly RuntimePipelineConfigurationContext $runtimeContext,
        private readonly PipelineConfigurationRepositoryInterface $repository,
    ) {
    }

    public function resolve(): ?PipelineConfiguration
    {
        $runtimeConfiguration = $this->runtimeContext->get();

        if (null !== $runtimeConfiguration) {
            return $runtimeConfiguration;
        }

        return $this->repository->findLatest();
    }
}
