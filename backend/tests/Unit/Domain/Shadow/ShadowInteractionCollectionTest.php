<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\ShadowAnswer;
use App\Domain\Shadow\ShadowInteraction;
use App\Domain\Shadow\ShadowInteractionCollection;
use App\Domain\Shadow\ShadowInteractionKind;
use App\Domain\Shadow\ShadowQuestion;
use App\Domain\Shadow\ShadowTimestamp;
use PHPUnit\Framework\TestCase;

final class ShadowInteractionCollectionTest extends TestCase
{
    public function testAppendIsImmutable(): void
    {
        $collection = ShadowInteractionCollection::empty();
        $updated = $collection->append(
            ShadowInteraction::createQuestion(
                ShadowQuestion::fromString('Explain this.'),
                ShadowTimestamp::fromSeconds(5.0),
            ),
        );

        self::assertTrue($collection->isEmpty());
        self::assertSame(1, $updated->count());
    }

    public function testRecentReturnsTail(): void
    {
        $collection = ShadowInteractionCollection::empty();

        for ($index = 1; $index <= 5; ++$index) {
            $collection = $collection->append(
                ShadowInteraction::createPause(ShadowTimestamp::fromSeconds((float) $index)),
            );
        }

        $recent = $collection->recent(2);

        self::assertCount(2, $recent);
        self::assertSame(ShadowInteractionKind::Pause, $recent[0]->kind());
        self::assertSame(4.0, $recent[0]->videoTimestamp()->seconds());
        self::assertSame(5.0, $recent[1]->videoTimestamp()->seconds());
    }
}
