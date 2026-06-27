<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Map;

use App\Domain\Map\Coordinates;
use App\Domain\Map\Exception\InvalidCoordinatesException;
use PHPUnit\Framework\TestCase;

final class CoordinatesTest extends TestCase
{
    public function testAcceptsValidCoordinates(): void
    {
        $coordinates = new Coordinates(41.9028, 12.4964);

        self::assertSame(41.9028, $coordinates->latitude());
        self::assertSame(12.4964, $coordinates->longitude());
    }

    public function testAcceptsBoundaryValues(): void
    {
        $northPole = new Coordinates(90.0, 0.0);
        $southPole = new Coordinates(-90.0, 0.0);
        $dateLine = new Coordinates(0.0, 180.0);
        $antiMeridian = new Coordinates(0.0, -180.0);

        self::assertSame(90.0, $northPole->latitude());
        self::assertSame(-90.0, $southPole->latitude());
        self::assertSame(180.0, $dateLine->longitude());
        self::assertSame(-180.0, $antiMeridian->longitude());
    }

    public function testRejectsLatitudeAboveRange(): void
    {
        $this->expectException(InvalidCoordinatesException::class);

        new Coordinates(90.1, 0.0);
    }

    public function testRejectsLatitudeBelowRange(): void
    {
        $this->expectException(InvalidCoordinatesException::class);

        new Coordinates(-90.1, 0.0);
    }

    public function testRejectsLongitudeAboveRange(): void
    {
        $this->expectException(InvalidCoordinatesException::class);

        new Coordinates(0.0, 180.1);
    }

    public function testRejectsLongitudeBelowRange(): void
    {
        $this->expectException(InvalidCoordinatesException::class);

        new Coordinates(0.0, -180.1);
    }

    public function testEqualsComparesLatitudeAndLongitude(): void
    {
        $left = new Coordinates(41.9028, 12.4964);
        $right = new Coordinates(41.9028, 12.4964);
        $different = new Coordinates(48.8566, 2.3522);

        self::assertTrue($left->equals($right));
        self::assertFalse($left->equals($different));
    }
}
