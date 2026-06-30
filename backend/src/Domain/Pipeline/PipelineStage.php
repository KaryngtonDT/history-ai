<?php

declare(strict_types=1);

namespace App\Domain\Pipeline;

use App\Domain\Pipeline\Exception\InvalidPipelineConfigurationException;

final readonly class PipelineStage
{
    public function __construct(
        private PipelineStageType $stage,
        private string $providerId,
    ) {
        if ('' === trim($this->providerId)) {
            throw new InvalidPipelineConfigurationException('Pipeline stage provider id must not be empty.');
        }
    }

    public static function create(PipelineStageType $stage, string $providerId): self
    {
        return new self($stage, $providerId);
    }

    public function stage(): PipelineStageType
    {
        return $this->stage;
    }

    public function providerId(): string
    {
        return $this->providerId;
    }

    public function withProviderId(string $providerId): self
    {
        return new self($this->stage, $providerId);
    }
}
