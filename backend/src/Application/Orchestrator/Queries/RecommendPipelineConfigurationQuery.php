<?php

declare(strict_types=1);

namespace App\Application\Orchestrator\Queries;

use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Orchestrator\VideoAnalysis;

final readonly class RecommendPipelineConfigurationQuery
{
    public function __construct(
        public VideoAnalysis $analysis,
        public ?ProcessingStrategy $strategy = null,
    ) {
    }
}
