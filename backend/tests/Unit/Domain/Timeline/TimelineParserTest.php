<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Timeline;

use App\Domain\Timeline\Timeline;
use App\Domain\Timeline\TimelineEvent;
use App\Domain\Timeline\TimelineParser;
use App\Domain\Timeline\TimelineSection;
use PHPUnit\Framework\TestCase;

final class TimelineParserTest extends TestCase
{
    public function testReturnsEmptyTimelineForEmptyMarkdown(): void
    {
        $timeline = TimelineParser::parse('');

        self::assertSame([], $timeline->sections());
    }

    public function testReturnsEmptyTimelineForWhitespaceOnlyMarkdown(): void
    {
        $timeline = TimelineParser::parse("   \n\n  ");

        self::assertSame([], $timeline->sections());
    }

    public function testParsesSingleSectionWithEvents(): void
    {
        $timeline = TimelineParser::parse(
            implode("\n", ['## Roman Republic', '', '- 509 BC', '- Expansion']),
        );

        self::assertCount(1, $timeline->sections());
        self::assertSame('Roman Republic', $timeline->sections()[0]->title());
        self::assertSame(
            ['509 BC', 'Expansion'],
            $this->eventTexts($timeline->sections()[0]),
        );
    }

    public function testParsesMultipleSectionsInOrder(): void
    {
        $timeline = TimelineParser::parse(
            implode("\n", [
                '## Roman Republic',
                '- 509 BC',
                '## Roman Empire',
                '- 27 BC',
                '- Pax Romana',
            ]),
        );

        self::assertCount(2, $timeline->sections());
        self::assertSame('Roman Republic', $timeline->sections()[0]->title());
        self::assertSame(['509 BC'], $this->eventTexts($timeline->sections()[0]));
        self::assertSame('Roman Empire', $timeline->sections()[1]->title());
        self::assertSame(
            ['27 BC', 'Pax Romana'],
            $this->eventTexts($timeline->sections()[1]),
        );
    }

    public function testParsesEmptySectionWithNoEvents(): void
    {
        $timeline = TimelineParser::parse(
            implode("\n", ['## Roman Republic', '', '## Roman Empire', '- 27 BC']),
        );

        self::assertCount(2, $timeline->sections());
        self::assertSame('Roman Republic', $timeline->sections()[0]->title());
        self::assertSame([], $this->eventTexts($timeline->sections()[0]));
        self::assertSame(['27 BC'], $this->eventTexts($timeline->sections()[1]));
    }

    public function testIgnoresTopLevelH1Heading(): void
    {
        $timeline = TimelineParser::parse(
            implode("\n", ['# Timeline', '', '## Ancient Rome', '- 753 BC']),
        );

        self::assertCount(1, $timeline->sections());
        self::assertSame('Ancient Rome', $timeline->sections()[0]->title());
        self::assertSame(['753 BC'], $this->eventTexts($timeline->sections()[0]));
    }

    public function testPreservesEventTextExactlyWithoutParsingDates(): void
    {
        $timeline = TimelineParser::parse(
            implode("\n", ['## Dates', '- 509 BC — founding', '- Expansion']),
        );

        self::assertSame(
            ['509 BC — founding', 'Expansion'],
            $this->eventTexts($timeline->sections()[0]),
        );
    }

    public function testPreservesSectionAndEventOrderingFromMarkdown(): void
    {
        $timeline = TimelineParser::parse(
            implode("\n", [
                '## First',
                '- Alpha',
                '- Beta',
                '## Second',
                '- Gamma',
            ]),
        );

        self::assertSame(
            ['First', 'Second'],
            array_map(static fn (TimelineSection $section): string => $section->title(), $timeline->sections()),
        );
        self::assertSame(['Alpha', 'Beta'], $this->eventTexts($timeline->sections()[0]));
        self::assertSame(['Gamma'], $this->eventTexts($timeline->sections()[1]));
    }

    public function testEmptyFactoryReturnsTimelineWithNoSections(): void
    {
        self::assertSame([], Timeline::empty()->sections());
    }

    /**
     * @return list<string>
     */
    private function eventTexts(TimelineSection $section): array
    {
        return array_map(
            static fn (TimelineEvent $event): string => $event->text(),
            $section->events(),
        );
    }
}
