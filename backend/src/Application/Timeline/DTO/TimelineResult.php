<?php

declare(strict_types=1);

namespace App\Application\Timeline\DTO;

use App\Domain\Timeline\Timeline;
use App\Domain\Timeline\TimelineSection;

final readonly class TimelineResult
{
    /**
     * @param list<TimelineSectionResult> $sections
     */
    public function __construct(
        public array $sections,
    ) {
    }

    public static function fromDomain(Timeline $timeline): self
    {
        return new self(
            sections: array_map(
                static fn (TimelineSection $section): TimelineSectionResult => TimelineSectionResult::fromDomain(
                    $section,
                ),
                $timeline->sections(),
            ),
        );
    }
}
