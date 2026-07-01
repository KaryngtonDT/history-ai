<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowTimestamp;
use PHPUnit\Framework\TestCase;

final class ShadowTimestampTest extends TestCase
{
    public function testAcceptsZeroAndPositiveValues(): void
    {
        self::assertSame(0.0, ShadowTimestamp::zero()->seconds());
        self::assertSame(123.4, ShadowTimestamp::fromSeconds(123.4)->seconds());
    }

    public function testRejectsNegativeTimestamp(): void
    {
        $this->expectException(InvalidShadowSessionException::class);
        ShadowTimestamp::fromSeconds(-0.1);
    }
}
