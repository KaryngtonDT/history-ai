<?php

declare(strict_types=1);

namespace App\Domain\Orchestrator;

use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Review\UserPreferenceProfile;
use App\Domain\VideoIntelligence\VideoIntelligence;

interface PipelinePlannerInterface
{
    public function recommend(
        VideoIntelligence $intelligence,
        ?UserPreferenceProfile $preferences = null,
    ): PipelineRecommendation;

    public function recommendWithStrategy(
        VideoIntelligence $intelligence,
        ProcessingStrategy $strategy,
        ?UserPreferenceProfile $preferences = null,
    ): PipelineRecommendation;
}
