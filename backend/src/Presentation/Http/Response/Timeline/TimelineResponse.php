<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Timeline;

use App\Application\Timeline\DTO\TimelineEventResult;
use App\Application\Timeline\DTO\TimelineResult;
use App\Application\Timeline\DTO\TimelineSectionResult;

final class TimelineResponse
{
    /**
     * @return array{
     *     sections: list<array{
     *         title: string,
     *         events: list<array{text: string}>
     *     }>
     * }
     */
    public static function fromResult(TimelineResult $result): array
    {
        return [
            'sections' => array_map(
                static fn (TimelineSectionResult $section): array => [
                    'title' => $section->title,
                    'events' => array_map(
                        static fn (TimelineEventResult $event): array => [
                            'text' => $event->text,
                        ],
                        $section->events,
                    ),
                ],
                $result->sections,
            ),
        ];
    }
}
