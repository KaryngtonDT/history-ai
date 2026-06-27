<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Map;

use App\Domain\Map\Coordinates;
use App\Domain\Map\HistoricalPlace;
use App\Domain\Map\HistoricalPlaceCollection;
use App\Domain\Map\PlaceName;
use PHPUnit\Framework\TestCase;

final class HistoricalPlaceCollectionTest extends TestCase
{
    public function testAllowsEmptyCollection(): void
    {
        $collection = HistoricalPlaceCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->places());
    }

    public function testPreservesInsertionOrder(): void
    {
        $rome = $this->createPlace('Rome', 41.9028, 12.4964);
        $athens = $this->createPlace('Athens', 37.9838, 23.7275);
        $alexandria = $this->createPlace('Alexandria', 31.2001, 29.9187);

        $collection = new HistoricalPlaceCollection([$rome, $athens, $alexandria]);

        self::assertSame(3, $collection->count());
        self::assertSame(
            ['Rome', 'Athens', 'Alexandria'],
            array_map(
                static fn (HistoricalPlace $place): string => $place->name()->value(),
                $collection->places(),
            ),
        );
    }

    public function testReturnedPlacesDoNotMutateCollection(): void
    {
        $collection = new HistoricalPlaceCollection([
            $this->createPlace('Rome', 41.9028, 12.4964),
        ]);

        $places = $collection->places();
        $places[] = $this->createPlace('Athens', 37.9838, 23.7275);

        self::assertSame(1, $collection->count());
        self::assertSame(['Rome'], array_map(
            static fn (HistoricalPlace $place): string => $place->name()->value(),
            $collection->places(),
        ));
    }

    public function testReindexesPlacesToPreserveListSemantics(): void
    {
        $collection = new HistoricalPlaceCollection([
            2 => $this->createPlace('Rome', 41.9028, 12.4964),
            5 => $this->createPlace('Athens', 37.9838, 23.7275),
        ]);

        self::assertSame([0, 1], array_keys($collection->places()));
    }

    private function createPlace(string $name, float $latitude, float $longitude): HistoricalPlace
    {
        return new HistoricalPlace(
            new PlaceName($name),
            new Coordinates($latitude, $longitude),
        );
    }
}
