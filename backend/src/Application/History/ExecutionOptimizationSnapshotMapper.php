<?php

declare(strict_types=1);

namespace App\Application\History;

use App\Domain\Optimization\ExecutionOptimization;

final class ExecutionOptimizationSnapshotMapper
{
    /**
     * @return array{
     *     id: string,
     *     profile: string,
     *     summary: string,
     *     estimatedImpact: int,
     *     stages: list<array{
     *         stage: string,
     *         parameters: list<array{key: string, value: string}>
     *     }>
     * }
     */
    public function toArray(ExecutionOptimization $optimization): array
    {
        $stages = [];

        foreach ($optimization->stages()->all() as $stageConfiguration) {
            $parameters = [];

            foreach ($stageConfiguration->parameters()->all() as $parameter) {
                $parameters[] = [
                    'key' => $parameter->key(),
                    'value' => $parameter->value(),
                ];
            }

            $stages[] = [
                'stage' => $stageConfiguration->stage()->value,
                'parameters' => $parameters,
            ];
        }

        return [
            'id' => $optimization->id()->value,
            'profile' => $optimization->profile()->value,
            'summary' => $optimization->summary(),
            'estimatedImpact' => $optimization->estimatedImpact(),
            'stages' => $stages,
        ];
    }
}
