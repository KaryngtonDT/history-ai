<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\History;

use App\Domain\History\Exception\InvalidExecutionHistoryException;
use App\Domain\History\ExecutionVersion;
use App\Domain\History\ExecutionVersionCollection;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Quality\QualityReportId;
use App\Domain\VideoRender\FinalVideoId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ExecutionVersionCollectionTest extends TestCase
{
    public function testAppendMaintainsOrderAndLatest(): void
    {
        $collection = ExecutionVersionCollection::empty()
            ->append($this->sampleVersion(1))
            ->append($this->sampleVersion(2));

        self::assertSame(2, $collection->count());
        self::assertSame(1, $collection->all()[0]->versionNumber());
        self::assertSame(2, $collection->all()[1]->versionNumber());
        self::assertSame(2, $collection->latest()?->versionNumber());
        self::assertSame(3, $collection->nextVersionNumber());
    }

    public function testDuplicateVersionNumberThrows(): void
    {
        $collection = ExecutionVersionCollection::empty()->append($this->sampleVersion(1));

        $this->expectException(InvalidExecutionHistoryException::class);

        $collection->append($this->sampleVersion(1));
    }

    public function testVersionLookupThrowsWhenMissing(): void
    {
        $collection = ExecutionVersionCollection::empty();

        $this->expectException(InvalidExecutionHistoryException::class);

        $collection->version(1);
    }

    public function testConstructRejectsDuplicateVersionNumbers(): void
    {
        $this->expectException(InvalidExecutionHistoryException::class);

        new ExecutionVersionCollection([
            $this->sampleVersion(1),
            $this->sampleVersion(1),
        ]);
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
