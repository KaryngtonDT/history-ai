<?php

declare(strict_types=1);

namespace App\Application\Pipeline\DTO;

use App\Domain\Pipeline\PipelineConfiguration;

final readonly class PipelineConfigurationResult
{
    public function __construct(
        public string $id,
        public int $version,
        public string $createdAt,
        public string $updatedAt,
        /** @var list<array{stage: string, providerId: string}> */
        public array $stages,
    ) {
    }

    public static function fromConfiguration(PipelineConfiguration $configuration): self
    {
        $stages = [];

        foreach ($configuration->stages()->all() as $stage) {
            $stages[] = [
                'stage' => $stage->stage()->value,
                'providerId' => $stage->providerId(),
            ];
        }

        return new self(
            id: $configuration->id()->value,
            version: $configuration->version(),
            createdAt: $configuration->createdAt()?->format(\DateTimeInterface::ATOM) ?? '',
            updatedAt: $configuration->updatedAt()?->format(\DateTimeInterface::ATOM) ?? '',
            stages: $stages,
        );
    }
}
