<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Speech;

use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use PHPUnit\Framework\TestCase;

final class TranscriptTest extends TestCase
{
    private const string TRANSCRIPT_ID = '550e8400-e29b-41d4-a716-446655440010';

    public function testCreateExposesFields(): void
    {
        $segments = new TranscriptSegmentCollection([
            TranscriptSegment::create(0, 0.0, 2.0, 'Hello'),
            TranscriptSegment::create(1, 2.0, 4.5, 'world'),
        ]);
        $transcript = Transcript::create(
            new TranscriptId(self::TRANSCRIPT_ID),
            TranscriptLanguage::English,
            $segments,
        );

        self::assertTrue($transcript->transcriptId()->equals(new TranscriptId(self::TRANSCRIPT_ID)));
        self::assertSame(TranscriptLanguage::English, $transcript->language());
        self::assertSame(2, $transcript->segmentCount());
    }

    public function testTextJoinsSegmentTexts(): void
    {
        $transcript = Transcript::create(
            new TranscriptId(self::TRANSCRIPT_ID),
            TranscriptLanguage::French,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 1.0, 'Bonjour'),
                TranscriptSegment::create(1, 1.0, 2.0, 'le monde'),
            ]),
        );

        self::assertSame('Bonjour le monde', $transcript->text());
    }

    public function testDurationReturnsLatestEndTime(): void
    {
        $transcript = Transcript::create(
            new TranscriptId(self::TRANSCRIPT_ID),
            TranscriptLanguage::German,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 12.5, 'Erster Satz'),
                TranscriptSegment::create(1, 12.5, 45.75, 'Zweiter Satz'),
            ]),
        );

        self::assertSame(45.75, $transcript->duration());
    }

    public function testEmptyTranscriptReturnsZeroDurationAndEmptyText(): void
    {
        $transcript = Transcript::create(
            new TranscriptId(self::TRANSCRIPT_ID),
            TranscriptLanguage::Unknown,
            TranscriptSegmentCollection::empty(),
        );

        self::assertSame('', $transcript->text());
        self::assertSame(0.0, $transcript->duration());
        self::assertSame(0, $transcript->segmentCount());
    }
}
