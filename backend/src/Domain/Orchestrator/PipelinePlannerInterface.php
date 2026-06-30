<?php

declare(strict_types=1);

namespace App\Domain\Orchestrator;

use App\Domain\VideoIntelligence\VideoIntelligence;

interface PipelinePlannerInterface
{
    public function recommend(VideoIntelligence $intelligence): PipelineRecommendation;

    public function recommendWithStrategy(
        VideoIntelligence $intelligence,
        ProcessingStrategy $strategy,
    ): PipelineRecommendation;
}
