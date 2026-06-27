<?php

declare(strict_types=1);

namespace App\Domain\Timeline;

final readonly class TimelineSection
{
    /**
     * @param list<TimelineEvent> $events
     */
    public function __construct(
        private string $title,
        private array $events,
    ) {
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return list<TimelineEvent>
     */
    public function events(): array
    {
        return $this->events;
    }
}
