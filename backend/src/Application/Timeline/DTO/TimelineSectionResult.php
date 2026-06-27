<?php

declare(strict_types=1);

namespace App\Application\Timeline\DTO;

use App\Domain\Timeline\TimelineEvent;
use App\Domain\Timeline\TimelineSection;

final readonly class TimelineSectionResult
{
    /**
     * @param list<TimelineEventResult> $events
     */
    public function __construct(
        public string $title,
        public array $events,
    ) {
    }

    public static function fromDomain(TimelineSection $section): self
    {
        return new self(
            title: $section->title(),
            events: array_map(
                static fn (TimelineEvent $event): TimelineEventResult => new TimelineEventResult(
                    $event->text(),
                ),
                $section->events(),
            ),
        );
    }
}
