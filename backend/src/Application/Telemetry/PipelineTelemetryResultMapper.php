<?php

declare(strict_types=1);

namespace App\Application\Telemetry;

use App\Application\Telemetry\DTO\PipelineTelemetryResult;
use App\Domain\Telemetry\PipelineTelemetry;

final class PipelineTelemetryResultMapper
{
    public static function fromDomain(PipelineTelemetry $telemetry): PipelineTelemetryResult
    {
        return new PipelineTelemetryResult(
            $telemetry->id()->value(),
            $telemetry->workspaceId(),
            $telemetry->videoId(),
            $telemetry->success(),
            array_map(
                static fn ($metric): array => [
                    'type' => $metric->type()->value,
                    'value' => $metric->value(),
                    'unit' => $metric->unit(),
                ],
                $telemetry->metrics()->all(),
            ),
            array_map(
                static fn ($usage): array => [
                    'stage' => $usage->stage(),
                    'providerId' => $usage->providerId(),
                    'invocationCount' => $usage->invocationCount(),
                    'totalDurationSeconds' => $usage->totalDurationSeconds(),
                ],
                $telemetry->providerUsages()->all(),
            ),
            $telemetry->recordedAt()->format(\DateTimeInterface::ATOM),
            $telemetry->batchJobId(),
            $telemetry->qualityScore(),
            $telemetry->errorMessage(),
        );
    }
}
