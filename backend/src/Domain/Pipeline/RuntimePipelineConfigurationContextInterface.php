<?php

declare(strict_types=1);

namespace App\Domain\Pipeline;

interface RuntimePipelineConfigurationContextInterface
{
    public function set(?PipelineConfiguration $configuration): void;

    public function clear(): void;
}
