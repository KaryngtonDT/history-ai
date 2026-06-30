<?php

declare(strict_types=1);

namespace App\Domain\Quality;

use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\VideoIntelligence\VideoIntelligence;
use App\Domain\VideoRender\FinalVideoArtifact;

interface QualityEvaluatorInterface
{
    public function evaluate(
        VideoIntelligence $intelligence,
        ExecutionOptimization $optimization,
        ?FinalVideoArtifact $finalVideo,
    ): QualityReport;
}
