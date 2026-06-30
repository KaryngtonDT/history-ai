<?php

declare(strict_types=1);

namespace App\Application\Orchestrator\Handlers;

use App\Application\Orchestrator\DTO\PipelineRecommendationResult;
use App\Application\Orchestrator\Queries\RecommendPipelineConfigurationQuery;
use App\Domain\Orchestrator\PipelinePlannerInterface;

final class RecommendPipelineConfigurationHandler
{
    public function __construct(
        private readonly PipelinePlannerInterface $planner,
    ) {
    }

    public function __invoke(RecommendPipelineConfigurationQuery $query): PipelineRecommendationResult
    {
        $recommendation = null !== $query->strategy
            ? $this->planner->recommendWithStrategy($query->analysis, $query->strategy)
            : $this->planner->recommend($query->analysis);

        return PipelineRecommendationResult::fromRecommendation($recommendation);
    }
}
