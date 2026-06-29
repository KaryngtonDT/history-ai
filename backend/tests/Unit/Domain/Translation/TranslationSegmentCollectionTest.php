<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Translation;

use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use PHPUnit\Framework\TestCase;

final class TranslationSegmentCollectionTest extends TestCase
{
    public function testEmptyCollection(): void
    {
        $collection = TranslationSegmentCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->all());
    }

    public function testAppendReturnsNewImmutableCollection(): void
    {
        $first = TranslationSegment::create(0, 'Hello', 'Bonjour');
        $second = TranslationSegment::create(1, 'world', 'le monde');

        $collection = TranslationSegmentCollection::empty()
            ->append($first)
            ->append($second);

        self::assertSame(2, $collection->count());
        self::assertSame(
            [0, 1],
            array_map(
                static fn (TranslationSegment $segment): int => $segment->index(),
                $collection->all(),
            ),
        );
    }
}
