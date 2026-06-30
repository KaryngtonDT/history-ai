<?php

declare(strict_types=1);

namespace App\Domain\Pipeline;

use App\Domain\Pipeline\Exception\InvalidPipelineConfigurationException;

final readonly class PipelineConfiguration
{
    public function __construct(
        private PipelineConfigurationId $id,
        private PipelineStageCollection $stages,
        private int $version = 1,
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        if ($this->version < 1) {
            throw new InvalidPipelineConfigurationException('Pipeline configuration version must be at least 1.');
        }

        $this->validate();
    }

    /**
     * @param list<PipelineStage> $stages
     */
    public static function create(
        PipelineConfigurationId $id,
        array $stages,
        int $version = 1,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ): self {
        return new self($id, PipelineStageCollection::fromStages($stages), $version, $createdAt, $updatedAt);
    }

    public function id(): PipelineConfigurationId
    {
        return $this->id;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function stages(): PipelineStageCollection
    {
        return $this->stages;
    }

    public function stageCount(): int
    {
        return $this->stages->count();
    }

    public function providerFor(PipelineStageType $stage): string
    {
        $match = $this->stages->findByType($stage);

        if (null === $match) {
            throw new InvalidPipelineConfigurationException(sprintf(
                'No provider configured for stage "%s".',
                $stage->value,
            ));
        }

        return $match->providerId();
    }

    public function replace(PipelineStageType $stage, string $providerId): self
    {
        return new self(
            $this->id,
            $this->stages->replace(PipelineStage::create($stage, $providerId)),
            $this->version,
        );
    }

    public function withVersion(int $version): self
    {
        return new self($this->id, $this->stages, $version, $this->createdAt, $this->updatedAt);
    }

    public function withTimestamps(\DateTimeImmutable $createdAt, \DateTimeImmutable $updatedAt): self
    {
        return new self($this->id, $this->stages, $this->version, $createdAt, $updatedAt);
    }

    public function validate(): void
    {
        $requiredStages = PipelineStageType::all();

        if ($this->stages->count() !== count($requiredStages)) {
            throw new InvalidPipelineConfigurationException(sprintf(
                'Pipeline configuration must define exactly %d stages.',
                count($requiredStages),
            ));
        }

        foreach ($requiredStages as $requiredStage) {
            if (null === $this->stages->findByType($requiredStage)) {
                throw new InvalidPipelineConfigurationException(sprintf(
                    'Pipeline configuration is missing stage "%s".',
                    $requiredStage->value,
                ));
            }
        }
    }
}
