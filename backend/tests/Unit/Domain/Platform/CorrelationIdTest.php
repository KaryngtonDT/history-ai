<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Platform;

use App\Domain\Platform\CorrelationId;
use App\Domain\Platform\Exception\InvalidCorrelationIdException;
use PHPUnit\Framework\TestCase;

final class CorrelationIdTest extends TestCase
{
    public function testAcceptsValidUuidV4(): void
    {
        $id = new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d');

        self::assertSame('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d', $id->value);
    }

    public function testRejectsInvalidUuid(): void
    {
        $this->expectException(InvalidCorrelationIdException::class);

        new CorrelationId('not-a-uuid');
    }

    public function testRejectsNonVersionFourUuid(): void
    {
        $this->expectException(InvalidCorrelationIdException::class);

        new CorrelationId('550e8400-e29b-11d4-a716-446655440000');
    }

    public function testGenerateCreatesValidUuidV4(): void
    {
        $id = CorrelationId::generate();

        self::assertTrue(CorrelationId::isValid($id->value));
    }

    public function testEqualsComparesValue(): void
    {
        $left = new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d');
        $right = new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d');
        $other = CorrelationId::generate();

        self::assertTrue($left->equals($right));
        self::assertFalse($left->equals($other));
    }
}
