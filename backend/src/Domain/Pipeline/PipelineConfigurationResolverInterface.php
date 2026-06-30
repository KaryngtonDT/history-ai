<?php

declare(strict_types=1);

namespace App\Domain\Pipeline;

interface PipelineConfigurationResolverInterface
{
    public function resolve(): ?PipelineConfiguration;
}
