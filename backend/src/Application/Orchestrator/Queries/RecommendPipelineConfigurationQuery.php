<?php

declare(strict_types=1);

namespace App\Application\Orchestrator\Queries;

use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\VideoIntelligence\VideoIntelligence;

final readonly class RecommendPipelineConfigurationQuery
{
    public function __construct(
        public VideoIntelligence $intelligence,
        public ?ProcessingStrategy $strategy = null,
    ) {
    }
}
