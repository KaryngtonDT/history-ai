<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\History;

use App\Domain\History\ExecutionSnapshot;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Quality\QualityReportId;
use App\Domain\VideoRender\FinalVideoId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ExecutionSnapshotTest extends TestCase
{
    public function testSnapshotCreatesVersionWithExpectedReferences(): void
    {
        $snapshot = ExecutionSnapshot::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440020'),
            new ExecutionOptimizationId('550e8400-e29b-41d4-a716-446655440021'),
            new QualityReportId('550e8400-e29b-41d4-a716-446655440022'),
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440023'),
        );

        $createdAt = new DateTimeImmutable('2026-06-26T15:30:00+00:00');
        $version = $snapshot->toVersion(3, $createdAt);

        self::assertSame(3, $version->versionNumber());
        self::assertSame('550e8400-e29b-41d4-a716-446655440020', $version->pipelineConfigurationId()->value);
        self::assertSame('550e8400-e29b-41d4-a716-446655440021', $version->optimizationId()->value);
        self::assertSame('550e8400-e29b-41d4-a716-446655440022', $version->qualityReportId()->value);
        self::assertSame('550e8400-e29b-41d4-a716-446655440023', $version->renderedVideoId()->value);
        self::assertSame($createdAt, $version->createdAt());
    }
}
