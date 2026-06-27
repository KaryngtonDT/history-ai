<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Map;

use App\Domain\Map\Coordinates;
use App\Domain\Map\HistoricalPlace;
use App\Domain\Map\PlaceName;
use PHPUnit\Framework\TestCase;

final class HistoricalPlaceTest extends TestCase
{
    public function testCreatesPlaceWithNameAndCoordinates(): void
    {
        $place = new HistoricalPlace(
            new PlaceName('Rome'),
            new Coordinates(41.9028, 12.4964),
        );

        self::assertSame('Rome', $place->name()->value());
        self::assertSame(41.9028, $place->coordinates()->latitude());
        self::assertSame(12.4964, $place->coordinates()->longitude());
        self::assertNull($place->description());
    }

    public function testStoresOptionalDescription(): void
    {
        $place = new HistoricalPlace(
            new PlaceName('Rome'),
            new Coordinates(41.9028, 12.4964),
            'Capital of the Roman Empire',
        );

        self::assertSame('Capital of the Roman Empire', $place->description());
    }

    public function testNormalizesBlankDescriptionToNull(): void
    {
        $place = new HistoricalPlace(
            new PlaceName('Rome'),
            new Coordinates(41.9028, 12.4964),
            '   ',
        );

        self::assertNull($place->description());
    }

    public function testTrimsDescription(): void
    {
        $place = new HistoricalPlace(
            new PlaceName('Rome'),
            new Coordinates(41.9028, 12.4964),
            '  Eternal City  ',
        );

        self::assertSame('Eternal City', $place->description());
    }
}
