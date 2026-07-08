<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\EngineAnalytics;

use App\Domain\EngineAnalytics\EngineExecutionHistory;
use App\Domain\EngineAnalytics\EngineExecutionStatus;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class EngineExecutionHistoryTest extends TestCase
{
    public function testComputesAccuracyFromEstimateAndActualDuration(): void
    {
        $startedAt = new DateTimeImmutable('2026-07-08 09:00:00');
        $completedAt = $startedAt->modify('+420 seconds');

        $execution = EngineExecutionHistory::fromPipelineExecution(
            PipelineJobId::generate(),
            '550e8400-e29b-41d4-a716-446655440099',
            PipelineStageType::SpeechToText,
            'faster_whisper',
            'low_end_local',
            EngineExecutionStatus::Completed,
            $startedAt,
            $completedAt,
            600,
        );

        self::assertSame(420, $execution->actualDurationSeconds());
        self::assertSame(-180, $execution->estimationErrorSeconds());
        self::assertGreaterThan(60.0, $execution->estimationAccuracyPercent());
    }
}
