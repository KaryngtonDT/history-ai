<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Map;

use App\Domain\Map\Exception\InvalidPlaceNameException;
use App\Domain\Map\PlaceName;
use PHPUnit\Framework\TestCase;

final class PlaceNameTest extends TestCase
{
    public function testTrimsWhitespace(): void
    {
        $name = new PlaceName('  Rome  ');

        self::assertSame('Rome', $name->value());
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(InvalidPlaceNameException::class);

        new PlaceName('');
    }

    public function testRejectsWhitespaceOnlyName(): void
    {
        $this->expectException(InvalidPlaceNameException::class);

        new PlaceName('   ');
    }

    public function testEqualsComparesNormalizedValue(): void
    {
        $left = new PlaceName('Rome');
        $right = new PlaceName('Rome');
        $different = new PlaceName('Athens');

        self::assertTrue($left->equals($right));
        self::assertFalse($left->equals($different));
    }
}
