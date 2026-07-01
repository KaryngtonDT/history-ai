<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shadow;

use App\Application\Shadow\CurrentSegmentResolver;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use PHPUnit\Framework\TestCase;

final class CurrentSegmentResolverTest extends TestCase
{
    private CurrentSegmentResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new CurrentSegmentResolver();
    }

    public function testResolvesExactSegment(): void
    {
        $segments = new TranscriptSegmentCollection([
            TranscriptSegment::create(0, 0.0, 5.0, 'Hello world.'),
            TranscriptSegment::create(1, 5.0, 10.0, 'Second segment.'),
        ]);

        $segment = $this->resolver->resolveExact($segments, 7.5);

        self::assertNotNull($segment);
        self::assertSame(1, $segment->index());
    }

    public function testResolvesNearestSegmentWhenNoExactMatch(): void
    {
        $segments = new TranscriptSegmentCollection([
            TranscriptSegment::create(0, 0.0, 5.0, 'Hello world.'),
            TranscriptSegment::create(1, 10.0, 15.0, 'Later segment.'),
        ]);

        $segment = $this->resolver->resolveNearest($segments, 6.0);

        self::assertNotNull($segment);
        self::assertSame(0, $segment->index());
    }

    public function testReturnsNullForEmptySegments(): void
    {
        self::assertNull($this->resolver->resolveNearest(TranscriptSegmentCollection::empty(), 1.0));
    }
}
