<?php

declare(strict_types=1);

namespace App\Domain\Timeline;

final readonly class Timeline
{
    /**
     * @param list<TimelineSection> $sections
     */
    public function __construct(private array $sections)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<TimelineSection>
     */
    public function sections(): array
    {
        return $this->sections;
    }
}
