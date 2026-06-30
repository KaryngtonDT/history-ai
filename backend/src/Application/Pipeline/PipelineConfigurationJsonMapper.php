<?php

declare(strict_types=1);

namespace App\Application\Pipeline;

use App\Domain\Pipeline\Exception\InvalidPipelineConfigurationException;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use JsonException;

final class PipelineConfigurationJsonMapper
{
    /**
     * @return array{
     *     id: string,
     *     version: int,
     *     stages: list<array{stage: string, providerId: string}>,
     *     createdAt: ?string,
     *     updatedAt: ?string
     * }
     */
    public function toArray(PipelineConfiguration $configuration): array
    {
        $stages = [];

        foreach ($configuration->stages()->all() as $stage) {
            $stages[] = [
                'stage' => $stage->stage()->value,
                'providerId' => $stage->providerId(),
            ];
        }

        return [
            'id' => $configuration->id()->value,
            'version' => $configuration->version(),
            'stages' => $stages,
            'createdAt' => $configuration->createdAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $configuration->updatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    public function toJson(PipelineConfiguration $configuration): string
    {
        return json_encode($this->toArray($configuration), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): PipelineConfiguration
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidPipelineConfigurationException('Stored pipeline configuration is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidPipelineConfigurationException('Stored pipeline configuration must be a JSON object.');
        }

        $id = is_string($decoded['id'] ?? null) ? $decoded['id'] : null;
        $version = is_numeric($decoded['version'] ?? null) ? (int) $decoded['version'] : null;
        $rawStages = $decoded['stages'] ?? null;

        if (null === $id || null === $version || !is_array($rawStages)) {
            throw new InvalidPipelineConfigurationException('Stored pipeline configuration is missing required fields.');
        }

        $stages = [];

        foreach ($rawStages as $rawStage) {
            if (!is_array($rawStage)) {
                throw new InvalidPipelineConfigurationException('Stored pipeline stage must be an object.');
            }

            $stageValue = is_string($rawStage['stage'] ?? null) ? $rawStage['stage'] : null;
            $providerId = is_string($rawStage['providerId'] ?? null) ? $rawStage['providerId'] : null;
            $stageType = null !== $stageValue ? PipelineStageType::tryFrom($stageValue) : null;

            if (null === $stageType || null === $providerId) {
                throw new InvalidPipelineConfigurationException('Stored pipeline stage is missing required fields.');
            }

            $stages[] = PipelineStage::create($stageType, $providerId);
        }

        $createdAt = $this->parseDateTime($decoded['createdAt'] ?? null);
        $updatedAt = $this->parseDateTime($decoded['updatedAt'] ?? null);

        return PipelineConfiguration::create(
            new PipelineConfigurationId($id),
            $stages,
            $version,
            $createdAt,
            $updatedAt,
        );
    }

    private function parseDateTime(mixed $value): ?\DateTimeImmutable
    {
        if (!is_string($value) || '' === trim($value)) {
            return null;
        }

        return new \DateTimeImmutable($value);
    }
}
