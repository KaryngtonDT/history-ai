<?php

declare(strict_types=1);

namespace App\Application\Optimization\DTO;

use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\OptimizationStageConfiguration;

final readonly class ExecutionOptimizationResult
{
    /**
     * @param list<array{
     *     stage: string,
     *     parameters: list<array{key: string, value: string}>,
     *     explanations: list<string>
     * }> $stages
     * @param list<string> $explanations
     */
    public function __construct(
        public string $id,
        public string $videoId,
        public string $profile,
        public string $summary,
        public int $estimatedImpact,
        public array $stages,
        public array $explanations,
    ) {
    }

    public static function fromOptimization(string $videoId, ExecutionOptimization $optimization): self
    {
        $stages = [];

        foreach ($optimization->stages()->all() as $stageConfiguration) {
            $stages[] = self::mapStage($stageConfiguration);
        }

        return new self(
            $optimization->id()->value,
            $videoId,
            $optimization->profile()->value,
            $optimization->summary(),
            $optimization->estimatedImpact(),
            $stages,
            $optimization->explanations(),
        );
    }

    /**
     * @return array{
     *     stage: string,
     *     parameters: list<array{key: string, value: string}>,
     *     explanations: list<string>
     * }
     */
    private static function mapStage(OptimizationStageConfiguration $configuration): array
    {
        $parameters = [];

        foreach ($configuration->parameters()->all() as $parameter) {
            $parameters[] = [
                'key' => $parameter->key(),
                'value' => $parameter->value(),
            ];
        }

        return [
            'stage' => $configuration->stage()->value,
            'parameters' => $parameters,
            'explanations' => [],
        ];
    }
}
