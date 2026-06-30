<?php

declare(strict_types=1);

namespace App\Domain\Pipeline;

interface PipelineConfigurationRepositoryInterface
{
    public function save(PipelineConfiguration $configuration): void;

    public function findLatest(): ?PipelineConfiguration;

    public function deleteAll(): void;
}
