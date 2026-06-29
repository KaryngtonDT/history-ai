<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Speech;

use App\Domain\Speech\Exception\InvalidTranscriptException;
use App\Domain\Speech\TranscriptSegment;
use PHPUnit\Framework\TestCase;

final class TranscriptSegmentTest extends TestCase
{
    public function testCreateNormalizesText(): void
    {
        $segment = TranscriptSegment::create(0, 0.0, 2.5, '  Hello world  ');

        self::assertSame(0, $segment->index());
        self::assertSame(0.0, $segment->startTime());
        self::assertSame(2.5, $segment->endTime());
        self::assertSame('Hello world', $segment->text());
    }

    public function testAllowsEqualStartAndEndTime(): void
    {
        $segment = TranscriptSegment::create(1, 3.0, 3.0, 'Marker');

        self::assertSame(3.0, $segment->startTime());
        self::assertSame(3.0, $segment->endTime());
    }

    public function testRejectsEndBeforeStart(): void
    {
        $this->expectException(InvalidTranscriptException::class);

        TranscriptSegment::create(0, 5.0, 4.0, 'Invalid timing');
    }

    public function testRejectsEmptyText(): void
    {
        $this->expectException(InvalidTranscriptException::class);

        TranscriptSegment::create(0, 0.0, 1.0, '   ');
    }

    public function testRejectsNegativeIndex(): void
    {
        $this->expectException(InvalidTranscriptException::class);

        new TranscriptSegment(-1, 0.0, 1.0, 'Invalid index');
    }
}
