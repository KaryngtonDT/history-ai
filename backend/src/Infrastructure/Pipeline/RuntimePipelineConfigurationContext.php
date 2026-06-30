<?php

declare(strict_types=1);

namespace App\Infrastructure\Pipeline;

use App\Domain\Pipeline\PipelineConfiguration;

final class RuntimePipelineConfigurationContext implements \App\Domain\Pipeline\RuntimePipelineConfigurationContextInterface
{
    private ?PipelineConfiguration $configuration = null;

    public function set(?PipelineConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function get(): ?PipelineConfiguration
    {
        return $this->configuration;
    }

    public function clear(): void
    {
        $this->configuration = null;
    }
}
