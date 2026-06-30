<?php

declare(strict_types=1);

namespace App\Application\Orchestrator\DTO;

use App\Domain\Orchestrator\PipelineRecommendation;

final readonly class PipelineRecommendationResult
{
    /**
     * @param list<array{stage: string, providerId: string}> $stages
     * @param list<string> $reasons
     */
    public function __construct(
        public string $id,
        public string $strategy,
        public string $explanation,
        public int $estimatedDurationSeconds,
        public int $estimatedQuality,
        public float $estimatedVramGb,
        public array $stages,
        public array $reasons = [],
    ) {
    }

    public static function fromRecommendation(PipelineRecommendation $recommendation): self
    {
        $stages = [];

        foreach ($recommendation->pipelineConfiguration()->stages()->all() as $stage) {
            $stages[] = [
                'stage' => $stage->stage()->value,
                'providerId' => $stage->providerId(),
            ];
        }

        return new self(
            $recommendation->id()->value,
            $recommendation->strategy()->value,
            $recommendation->explanation(),
            $recommendation->estimatedDurationSeconds(),
            $recommendation->estimatedQuality(),
            $recommendation->estimatedVramGb(),
            $stages,
            $recommendation->reasons(),
        );
    }
}
