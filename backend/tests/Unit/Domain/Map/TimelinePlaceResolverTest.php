<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Map;

use App\Domain\Map\HistoricalPlace;
use App\Domain\Map\HistoricalPlaceCollection;
use App\Domain\Map\TimelinePlaceResolver;
use App\Domain\Timeline\Timeline;
use App\Domain\Timeline\TimelineEvent;
use App\Domain\Timeline\TimelineSection;
use PHPUnit\Framework\TestCase;

final class TimelinePlaceResolverTest extends TestCase
{
    public function testResolvesRomeFromEventText(): void
    {
        $timeline = $this->createTimeline([
            '753 BC — Foundation of Rome',
        ]);

        $places = TimelinePlaceResolver::resolve($timeline);

        self::assertSame(1, $places->count());
        self::assertSame('Rome', $places->places()[0]->name()->value());
        self::assertSame(41.9028, $places->places()[0]->coordinates()->latitude());
        self::assertSame(12.4964, $places->places()[0]->coordinates()->longitude());
        self::assertSame('753 BC — Foundation of Rome', $places->places()[0]->description());
    }

    public function testResolvesMultiplePlaces(): void
    {
        $timeline = $this->createTimeline([
            '753 BC — Foundation of Rome',
            '146 BC — Destruction of Carthage',
            'Battle near Athens',
        ]);

        $places = TimelinePlaceResolver::resolve($timeline);

        self::assertSame(3, $places->count());
        self::assertSame(
            ['Rome', 'Carthage', 'Athens'],
            $this->placeNames($places),
        );
    }

    public function testMatchesPlaceNamesCaseInsensitively(): void
    {
        $timeline = $this->createTimeline([
            'Library of alexandria founded',
        ]);

        $places = TimelinePlaceResolver::resolve($timeline);

        self::assertSame(['Alexandria'], $this->placeNames($places));
    }

    public function testRemovesDuplicatePlaces(): void
    {
        $timeline = $this->createTimeline([
            '753 BC — Foundation of Rome',
            '27 BC — Augustus rules Rome',
        ]);

        $places = TimelinePlaceResolver::resolve($timeline);

        self::assertSame(1, $places->count());
        self::assertSame('Rome', $places->places()[0]->name()->value());
        self::assertSame('753 BC — Foundation of Rome', $places->places()[0]->description());
    }

    public function testPreservesOrderOfFirstAppearance(): void
    {
        $timeline = $this->createTimeline([
            'Trade between Athens and Alexandria',
            'Later conflict in Rome',
        ]);

        $places = TimelinePlaceResolver::resolve($timeline);

        self::assertSame(['Athens', 'Alexandria', 'Rome'], $this->placeNames($places));
    }

    public function testPreservesTextOrderWithinSingleEvent(): void
    {
        $timeline = $this->createTimeline([
            'Journey from Rome to Athens',
        ]);

        $places = TimelinePlaceResolver::resolve($timeline);

        self::assertSame(['Rome', 'Athens'], $this->placeNames($places));
    }

    public function testIgnoresUnknownPlaces(): void
    {
        $timeline = $this->createTimeline([
            'Battle of Teutoburg Forest',
            '753 BC — Foundation of Rome',
        ]);

        $places = TimelinePlaceResolver::resolve($timeline);

        self::assertSame(['Rome'], $this->placeNames($places));
    }

    public function testEmptyTimelineReturnsEmptyCollection(): void
    {
        $places = TimelinePlaceResolver::resolve(Timeline::empty());

        self::assertTrue($places->isEmpty());
        self::assertSame([], $places->places());
    }

    /**
     * @param list<string> $eventTexts
     */
    private function createTimeline(array $eventTexts): Timeline
    {
        $events = array_map(
            static fn (string $text): TimelineEvent => new TimelineEvent($text),
            $eventTexts,
        );

        return new Timeline([
            new TimelineSection('Events', $events),
        ]);
    }

    /**
     * @return list<string>
     */
    private function placeNames(HistoricalPlaceCollection $collection): array
    {
        return array_map(
            static fn (HistoricalPlace $place): string => $place->name()->value(),
            $collection->places(),
        );
    }
}
