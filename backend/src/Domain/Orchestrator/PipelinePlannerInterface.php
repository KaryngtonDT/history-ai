<?php

declare(strict_types=1);

namespace App\Domain\Orchestrator;

interface PipelinePlannerInterface
{
    public function recommend(VideoAnalysis $analysis): PipelineRecommendation;

    public function recommendWithStrategy(
        VideoAnalysis $analysis,
        ProcessingStrategy $strategy,
    ): PipelineRecommendation;
}
