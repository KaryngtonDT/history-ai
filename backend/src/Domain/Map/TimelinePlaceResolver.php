<?php

declare(strict_types=1);

namespace App\Domain\Map;

use App\Domain\Timeline\Timeline;
use App\Domain\Timeline\TimelineEvent;

final class TimelinePlaceResolver
{
    /**
     * @var array<string, array{0: float, 1: float}>
     */
    private const PLACE_COORDINATES = [
        'Rome' => [41.9028, 12.4964],
        'Carthage' => [36.8529, 10.3233],
        'Athens' => [37.9838, 23.7275],
        'Alexandria' => [31.2001, 29.9187],
    ];

    public static function resolve(Timeline $timeline): HistoricalPlaceCollection
    {
        /** @var list<HistoricalPlace> $places */
        $places = [];
        /** @var array<string, true> $seen */
        $seen = [];

        foreach ($timeline->sections() as $section) {
            foreach ($section->events() as $event) {
                foreach (self::matchPlacesInEvent($event) as $match) {
                    if (isset($seen[$match['name']])) {
                        continue;
                    }

                    $seen[$match['name']] = true;
                    $places[] = new HistoricalPlace(
                        new PlaceName($match['name']),
                        new Coordinates($match['latitude'], $match['longitude']),
                        $event->text(),
                    );
                }
            }
        }

        return new HistoricalPlaceCollection($places);
    }

    /**
     * @return list<array{name: string, latitude: float, longitude: float, position: int}>
     */
    private static function matchPlacesInEvent(TimelineEvent $event): array
    {
        $matches = [];

        foreach (self::PLACE_COORDINATES as $name => [$latitude, $longitude]) {
            $position = self::findPlacePosition($event->text(), $name);

            if (null === $position) {
                continue;
            }

            $matches[] = [
                'name' => $name,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'position' => $position,
            ];
        }

        usort(
            $matches,
            static fn (array $left, array $right): int => $left['position'] <=> $right['position'],
        );

        return $matches;
    }

    private static function findPlacePosition(string $text, string $placeName): ?int
    {
        $pattern = sprintf('/\b%s\b/iu', preg_quote($placeName, '/'));

        if (1 !== preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        return $matches[0][1];
    }
}
