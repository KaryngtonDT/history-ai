<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Speech;

use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use PHPUnit\Framework\TestCase;

final class TranscriptSegmentCollectionTest extends TestCase
{
    public function testEmptyCollectionIsAllowed(): void
    {
        $collection = TranscriptSegmentCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->all());
    }

    public function testAppendPreservesInsertionOrder(): void
    {
        $first = TranscriptSegment::create(0, 0.0, 1.0, 'First');
        $second = TranscriptSegment::create(1, 1.0, 2.0, 'Second');
        $third = TranscriptSegment::create(2, 2.0, 3.0, 'Third');

        $collection = TranscriptSegmentCollection::empty()
            ->append($first)
            ->append($second)
            ->append($third);

        self::assertSame(3, $collection->count());
        self::assertSame([0, 1, 2], array_map(
            static fn (TranscriptSegment $segment): int => $segment->index(),
            $collection->all(),
        ));
    }

    public function testReturnedSegmentsDoNotMutateCollection(): void
    {
        $collection = TranscriptSegmentCollection::empty()->append(
            TranscriptSegment::create(0, 0.0, 1.0, 'Only'),
        );

        $segments = $collection->all();
        $segments[] = TranscriptSegment::create(1, 1.0, 2.0, 'Extra');

        self::assertSame(1, $collection->count());
    }
}
