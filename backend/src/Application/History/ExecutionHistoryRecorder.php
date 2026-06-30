<?php

declare(strict_types=1);

namespace App\Application\History;

use App\Application\History\Commands\RecordExecutionHistoryCommand;
use App\Domain\Optimization\RuntimeExecutionOptimizationContextInterface;
use App\Domain\Pipeline\PipelineConfigurationResolverInterface;
use App\Domain\Pipeline\RuntimePipelineConfigurationContextInterface;
use App\Domain\Quality\QualityReport;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\FinalVideoRepositoryInterface;

final class ExecutionHistoryRecorder
{
    public function __construct(
        private readonly RecordExecutionHistoryHandler $recordExecutionHistoryHandler,
        private readonly RuntimePipelineConfigurationContextInterface $runtimePipelineContext,
        private readonly RuntimeExecutionOptimizationContextInterface $runtimeOptimizationContext,
        private readonly PipelineConfigurationResolverInterface $pipelineConfigurationResolver,
        private readonly FinalVideoRepositoryInterface $finalVideoRepository,
    ) {
    }

    public function recordCompletedExecution(VideoId $videoId, ?QualityReport $qualityReport): void
    {
        if (null === $qualityReport) {
            return;
        }

        $pipelineConfiguration = $this->runtimePipelineContext->get()
            ?? $this->pipelineConfigurationResolver->resolve();
        $optimization = $this->runtimeOptimizationContext->get();
        $finalVideos = $this->finalVideoRepository->findAllDetailedByVideoId($videoId);

        if (null === $pipelineConfiguration || null === $optimization || [] === $finalVideos) {
            return;
        }

        ($this->recordExecutionHistoryHandler)(new RecordExecutionHistoryCommand(
            $videoId,
            $pipelineConfiguration,
            $optimization,
            $qualityReport,
            $finalVideos[0]['artifact']->finalVideoId(),
        ));
    }
}
