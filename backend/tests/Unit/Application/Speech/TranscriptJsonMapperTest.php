<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Speech;

use App\Application\Speech\TranscriptJsonMapper;
use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use PHPUnit\Framework\TestCase;

final class TranscriptJsonMapperTest extends TestCase
{
    private TranscriptJsonMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new TranscriptJsonMapper();
    }

    public function testRoundTripsTranscript(): void
    {
        $original = Transcript::create(
            new TranscriptId('550e8400-e29b-41d4-a716-446655440010'),
            TranscriptLanguage::English,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 1.5, 'Hello'),
                TranscriptSegment::create(1, 1.5, 3.0, 'world'),
            ]),
        );

        $restored = $this->mapper->fromJson($this->mapper->toJson($original));

        self::assertTrue($restored->transcriptId()->equals($original->transcriptId()));
        self::assertSame($original->language(), $restored->language());
        self::assertSame($original->text(), $restored->text());
        self::assertSame($original->duration(), $restored->duration());
    }
}
