<?php

declare(strict_types=1);

namespace App\Domain\Orchestrator;

use App\Domain\Video\VideoJob;

interface VideoAnalysisFactoryInterface
{
    public function fromVideoJob(VideoJob $job): VideoAnalysis;
}
