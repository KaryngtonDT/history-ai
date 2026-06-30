<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\History;

use App\Domain\History\Exception\InvalidExecutionHistoryException;
use App\Domain\History\ExecutionHistory;
use App\Domain\History\ExecutionHistoryId;
use App\Domain\History\ExecutionSnapshot;
use App\Domain\History\ExecutionVersion;
use App\Domain\History\ExecutionVersionCollection;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Quality\QualityReportId;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\FinalVideoId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ExecutionHistoryTest extends TestCase
{
    public function testCreateEmptyHistory(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $history = ExecutionHistory::create(ExecutionHistoryId::generate(), $videoId);

        self::assertTrue($history->isEmpty());
        self::assertSame(0, $history->count());
        self::assertNull($history->latest());
        self::assertTrue($videoId->equals($history->videoId()));
    }

    public function testAppendSnapshotCreatesSequentialVersions(): void
    {
        $history = ExecutionHistory::create(
            ExecutionHistoryId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
        );

        $first = $history->appendSnapshot($this->sampleSnapshot(1));
        $second = $first->appendSnapshot($this->sampleSnapshot(2));

        self::assertSame(2, $second->count());
        self::assertSame(1, $second->version(1)->versionNumber());
        self::assertSame(2, $second->version(2)->versionNumber());
        self::assertSame(2, $second->latest()?->versionNumber());
    }

    public function testAppendVersionPreservesImmutability(): void
    {
        $history = ExecutionHistory::create(
            ExecutionHistoryId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
        );
        $updated = $history->appendVersion($this->sampleVersion(1));

        self::assertTrue($history->isEmpty());
        self::assertSame(1, $updated->count());
    }

    public function testVersionLookupThrowsForMissingVersion(): void
    {
        $history = ExecutionHistory::create(
            ExecutionHistoryId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
        );

        $this->expectException(InvalidExecutionHistoryException::class);

        $history->version(1);
    }

    private function sampleSnapshot(int $index): ExecutionSnapshot
    {
        $suffix = str_pad((string) $index, 4, '0', STR_PAD_LEFT);

        return ExecutionSnapshot::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-44665544'.$suffix),
            new ExecutionOptimizationId('550e8400-e29b-41d4-a716-44665545'.$suffix),
            new QualityReportId('550e8400-e29b-41d4-a716-44665546'.$suffix),
            new FinalVideoId('550e8400-e29b-41d4-a716-44665547'.$suffix),
        );
    }

    private function sampleVersion(int $number): ExecutionVersion
    {
        $suffix = str_pad((string) $number, 4, '0', STR_PAD_LEFT);

        return ExecutionVersion::create(
            $number,
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-44665544'.$suffix),
            new ExecutionOptimizationId('550e8400-e29b-41d4-a716-44665545'.$suffix),
            new QualityReportId('550e8400-e29b-41d4-a716-44665546'.$suffix),
            new FinalVideoId('550e8400-e29b-41d4-a716-44665547'.$suffix),
            new DateTimeImmutable('2026-06-26T10:00:00+00:00'),
        );
    }
}
