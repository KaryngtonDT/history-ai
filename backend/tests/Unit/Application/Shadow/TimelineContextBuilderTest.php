<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shadow;

use App\Application\Shadow\CurrentSegmentResolver;
use App\Application\Shadow\TimelineContextBuilder;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use PHPUnit\Framework\TestCase;

final class TimelineContextBuilderTest extends TestCase
{
    private TimelineContextBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new TimelineContextBuilder(new CurrentSegmentResolver());
    }

    public function testBuildsDeterministicContextWindow(): void
    {
        $segments = new TranscriptSegmentCollection([
            TranscriptSegment::create(0, 0.0, 2.0, 'one'),
            TranscriptSegment::create(1, 2.0, 4.0, 'two'),
            TranscriptSegment::create(2, 4.0, 6.0, 'three'),
            TranscriptSegment::create(3, 6.0, 8.0, 'four'),
            TranscriptSegment::create(4, 8.0, 10.0, 'five'),
        ]);

        $context = $this->builder->buildNearbyTranscriptContext($segments, 2);

        self::assertSame('one two three four five', $context);
    }

    public function testBuildsTranslationContextWindow(): void
    {
        $segments = new TranslationSegmentCollection([
            TranslationSegment::create(0, 'one', 'eins'),
            TranslationSegment::create(1, 'two', 'zwei'),
            TranslationSegment::create(2, 'three', 'drei'),
        ]);

        $context = $this->builder->buildNearbyTranslationContext($segments, 1);

        self::assertSame('eins zwei drei', $context);
    }
}
