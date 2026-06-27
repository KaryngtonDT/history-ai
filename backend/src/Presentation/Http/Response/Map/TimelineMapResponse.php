<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Map;

use App\Application\Map\DTO\HistoricalPlaceResult;
use App\Application\Map\DTO\TimelineMapResult;

final class TimelineMapResponse
{
    /**
     * @return array{
     *     places: list<array{
     *         name: string,
     *         coordinates: array{latitude: float, longitude: float},
     *         description: string|null
     *     }>
     * }
     */
    public static function fromResult(TimelineMapResult $result): array
    {
        return [
            'places' => array_map(
                static fn (HistoricalPlaceResult $place): array => [
                    'name' => $place->name,
                    'coordinates' => [
                        'latitude' => $place->coordinates->latitude,
                        'longitude' => $place->coordinates->longitude,
                    ],
                    'description' => $place->description,
                ],
                $result->places,
            ),
        ];
    }
}
