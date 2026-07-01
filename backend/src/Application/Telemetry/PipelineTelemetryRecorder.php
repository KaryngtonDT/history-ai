<?php

declare(strict_types=1);

namespace App\Application\Telemetry;

use App\Application\Telemetry\Commands\CollectPipelineMetricsCommand;
use App\Application\Video\Messages\ProcessVideoMessage;
use App\Domain\Pipeline\Exception\InvalidPipelineConfigurationException;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Quality\QualityReport;
use App\Domain\Pipeline\RuntimePipelineConfigurationContextInterface;
use App\Domain\Scheduler\ResourceType;
use App\Domain\Scheduler\RuntimeExecutionScheduleContextInterface;
use App\Domain\Scheduler\ScheduledStageStatus;
use App\Domain\Telemetry\ExecutionMetric;
use App\Domain\Telemetry\ExecutionMetricType;
use App\Domain\Telemetry\PipelineTelemetry;
use App\Domain\Telemetry\PipelineTelemetryId;
use App\Domain\Telemetry\ProviderUsage;
use App\Domain\Video\VideoId;
use App\Domain\Workspace\ProjectRepositoryInterface;
use Throwable;

final class PipelineTelemetryRecorder
{
    public function __construct(
        private readonly CollectPipelineMetricsHandler $collectPipelineMetricsHandler,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly RuntimePipelineConfigurationContextInterface $runtimePipelineContext,
        private readonly RuntimeExecutionScheduleContextInterface $runtimeScheduleContext,
    ) {
    }

    /**
     * @param array<string, float> $stageDurations
     */
    public function record(
        VideoId $videoId,
        ProcessVideoMessage $message,
        bool $success,
        float $processingDurationSeconds,
        array $stageDurations,
        ?QualityReport $qualityReport,
        ?string $errorMessage,
        int $retryCount,
        float $initialQueueTimeSeconds,
    ): void {
        try {
            $projectId = $this->projectRepository->findProjectIdByVideoId($videoId);

            if (null === $projectId) {
                return;
            }

            $metrics = [
                ExecutionMetric::of(ExecutionMetricType::ProcessingTime, max(0.0, $processingDurationSeconds)),
                ExecutionMetric::of(ExecutionMetricType::QueueTime, max(0.0, $initialQueueTimeSeconds)),
                ExecutionMetric::of(ExecutionMetricType::RetryCount, (float) max(0, $retryCount)),
            ];

            $gpuUsage = $this->resolveGpuUsagePercent();

            if ($gpuUsage > 0.0) {
                $metrics[] = ExecutionMetric::of(ExecutionMetricType::GpuUsage, $gpuUsage);
            }

            $metrics[] = ExecutionMetric::of(
                ExecutionMetricType::SuccessRate,
                $success ? 100.0 : 0.0,
            );

            $providerUsages = $this->buildProviderUsages($stageDurations);

            $telemetry = PipelineTelemetry::create(
                PipelineTelemetryId::generate(),
                $projectId->value,
                $videoId->value,
                $success,
                $metrics,
                $providerUsages,
                $message->batchJobId,
                $qualityReport?->overallScore()->value(),
                $errorMessage,
            );

            ($this->collectPipelineMetricsHandler)(new CollectPipelineMetricsCommand($telemetry));
        } catch (Throwable) {
        }
    }

    /**
     * @param array<string, float> $stageDurations
     *
     * @return list<ProviderUsage>
     */
    private function buildProviderUsages(array $stageDurations): array
    {
        $configuration = $this->runtimePipelineContext->get();

        if (null === $configuration) {
            return [];
        }

        $usages = [];

        foreach ($stageDurations as $stageValue => $durationSeconds) {
            try {
                $stage = PipelineStageType::from($stageValue);
                $providerId = $configuration->providerFor($stage);
                $usages[] = ProviderUsage::create($stageValue, $providerId, 1, max(0.0, $durationSeconds));
            } catch (InvalidPipelineConfigurationException) {
            }
        }

        return $usages;
    }

    private function resolveGpuUsagePercent(): float
    {
        $schedule = $this->runtimeScheduleContext->get();

        if (null === $schedule) {
            return 0.0;
        }

        $gpuResource = $schedule->resourceFor(ResourceType::Gpu);

        if (null === $gpuResource || $gpuResource->maxConcurrency() < 1) {
            return 0.0;
        }

        return round(($gpuResource->running() / $gpuResource->maxConcurrency()) * 100, 1);
    }

    public function resolveInitialQueueTimeSeconds(): float
    {
        $schedule = $this->runtimeScheduleContext->get();

        if (null === $schedule) {
            return 0.0;
        }

        $queueSeconds = 0.0;

        foreach ($schedule->stages()->all() as $stage) {
            if (ScheduledStageStatus::Pending === $stage->status()) {
                $queueSeconds += $stage->estimatedDurationSeconds();
            }
        }

        return $queueSeconds;
    }
}
