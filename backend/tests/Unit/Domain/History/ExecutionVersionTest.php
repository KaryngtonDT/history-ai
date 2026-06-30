<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\History;

use App\Domain\History\Exception\InvalidExecutionHistoryException;
use App\Domain\History\ExecutionVersion;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Quality\QualityReportId;
use App\Domain\VideoRender\FinalVideoId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ExecutionVersionTest extends TestCase
{
    public function testCreateExecutionVersion(): void
    {
        $createdAt = new DateTimeImmutable('2026-06-26T12:00:00+00:00');
        $version = ExecutionVersion::create(
            1,
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
            new ExecutionOptimizationId('550e8400-e29b-41d4-a716-446655440011'),
            new QualityReportId('550e8400-e29b-41d4-a716-446655440012'),
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440013'),
            $createdAt,
        );

        self::assertSame(1, $version->versionNumber());
        self::assertSame('550e8400-e29b-41d4-a716-446655440010', $version->pipelineConfigurationId()->value);
        self::assertSame('550e8400-e29b-41d4-a716-446655440011', $version->optimizationId()->value);
        self::assertSame('550e8400-e29b-41d4-a716-446655440012', $version->qualityReportId()->value);
        self::assertSame('550e8400-e29b-41d4-a716-446655440013', $version->renderedVideoId()->value);
        self::assertSame($createdAt, $version->createdAt());
    }

    public function testInvalidVersionNumberThrows(): void
    {
        $this->expectException(InvalidExecutionHistoryException::class);

        new ExecutionVersion(
            0,
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
            new ExecutionOptimizationId('550e8400-e29b-41d4-a716-446655440011'),
            new QualityReportId('550e8400-e29b-41d4-a716-446655440012'),
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440013'),
            new DateTimeImmutable(),
        );
    }
}
