<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Telemetry;

use App\Domain\Telemetry\ExecutionMetric;
use App\Domain\Telemetry\ExecutionMetricCollection;
use App\Domain\Telemetry\ExecutionMetricType;
use App\Domain\Telemetry\PipelineTelemetry;
use App\Domain\Telemetry\PipelineTelemetryId;
use App\Domain\Telemetry\ProviderUsage;
use App\Domain\Telemetry\ProviderUsageCollection;
use DateTimeImmutable;

final class TelemetryJsonMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toPayload(PipelineTelemetry $telemetry): array
    {
        return [
            'id' => $telemetry->id()->value(),
            'workspaceId' => $telemetry->workspaceId(),
            'videoId' => $telemetry->videoId(),
            'success' => $telemetry->success(),
            'metrics' => array_map(
                static fn (ExecutionMetric $metric): array => [
                    'type' => $metric->type()->value,
                    'value' => $metric->value(),
                    'unit' => $metric->unit(),
                ],
                $telemetry->metrics()->all(),
            ),
            'providerUsages' => array_map(
                static fn (ProviderUsage $usage): array => [
                    'stage' => $usage->stage(),
                    'providerId' => $usage->providerId(),
                    'invocationCount' => $usage->invocationCount(),
                    'totalDurationSeconds' => $usage->totalDurationSeconds(),
                ],
                $telemetry->providerUsages()->all(),
            ),
            'recordedAt' => $telemetry->recordedAt()->format(\DateTimeInterface::ATOM),
            'batchJobId' => $telemetry->batchJobId(),
            'qualityScore' => $telemetry->qualityScore(),
            'errorMessage' => $telemetry->errorMessage(),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function fromPayload(array $payload): PipelineTelemetry
    {
        $metrics = [];

        foreach ($payload['metrics'] ?? [] as $metricPayload) {
            if (!is_array($metricPayload)) {
                continue;
            }

            $metrics[] = ExecutionMetric::of(
                ExecutionMetricType::from((string) $metricPayload['type']),
                (float) $metricPayload['value'],
                isset($metricPayload['unit']) ? (string) $metricPayload['unit'] : null,
            );
        }

        $providerUsages = [];

        foreach ($payload['providerUsages'] ?? [] as $usagePayload) {
            if (!is_array($usagePayload)) {
                continue;
            }

            $providerUsages[] = ProviderUsage::create(
                (string) $usagePayload['stage'],
                (string) $usagePayload['providerId'],
                (int) ($usagePayload['invocationCount'] ?? 1),
                (float) ($usagePayload['totalDurationSeconds'] ?? 0.0),
            );
        }

        $metricCollection = ExecutionMetricCollection::empty();

        foreach ($metrics as $metric) {
            $metricCollection = $metricCollection->append($metric);
        }

        $usageCollection = ProviderUsageCollection::empty();

        foreach ($providerUsages as $usage) {
            $usageCollection = $usageCollection->append($usage);
        }

        return PipelineTelemetry::reconstitute(
            new PipelineTelemetryId((string) $payload['id']),
            (string) $payload['workspaceId'],
            (string) $payload['videoId'],
            (bool) ($payload['success'] ?? false),
            $metricCollection,
            $usageCollection,
            new DateTimeImmutable((string) ($payload['recordedAt'] ?? 'now')),
            isset($payload['batchJobId']) ? (string) $payload['batchJobId'] : null,
            isset($payload['qualityScore']) ? (int) $payload['qualityScore'] : null,
            isset($payload['errorMessage']) ? (string) $payload['errorMessage'] : null,
        );
    }
}
