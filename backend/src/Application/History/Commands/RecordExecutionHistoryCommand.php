<?php

declare(strict_types=1);

namespace App\Application\History\Commands;

use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Quality\QualityReport;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\FinalVideoId;

final readonly class RecordExecutionHistoryCommand
{
    public function __construct(
        public VideoId $videoId,
        public PipelineConfiguration $pipelineConfiguration,
        public ExecutionOptimization $optimization,
        public QualityReport $qualityReport,
        public FinalVideoId $renderedVideoId,
    ) {
    }
}
