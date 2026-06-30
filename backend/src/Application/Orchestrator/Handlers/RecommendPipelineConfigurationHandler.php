<?php

declare(strict_types=1);

namespace App\Application\Orchestrator\Handlers;

use App\Application\Orchestrator\DTO\PipelineRecommendationResult;
use App\Application\Orchestrator\Queries\RecommendPipelineConfigurationQuery;
use App\Domain\Orchestrator\PipelinePlannerInterface;
use App\Domain\Review\UserPreferenceProfileRepositoryInterface;

final class RecommendPipelineConfigurationHandler
{
    public function __construct(
        private readonly PipelinePlannerInterface $planner,
        private readonly UserPreferenceProfileRepositoryInterface $profileRepository,
    ) {
    }

    public function __invoke(RecommendPipelineConfigurationQuery $query): PipelineRecommendationResult
    {
        if (null !== $query->strategy) {
            $recommendation = $this->planner->recommendWithStrategy(
                $query->intelligence,
                $query->strategy,
            );
        } else {
            $recommendation = $this->planner->recommend(
                $query->intelligence,
                $this->profileRepository->findCurrent(),
            );
        }

        return PipelineRecommendationResult::fromRecommendation($recommendation);
    }
}
