<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Source;

use App\Domain\Source\Exception\InvalidSourceIdException;
use App\Domain\Source\SourceId;
use PHPUnit\Framework\TestCase;

final class SourceIdTest extends TestCase
{
    public function testGenerateCreatesValidUuid(): void
    {
        $id = SourceId::generate();

        self::assertTrue(SourceId::isValid($id->value));
    }

    public function testRejectsInvalidUuid(): void
    {
        $this->expectException(InvalidSourceIdException::class);

        new SourceId('not-a-uuid');
    }

    public function testEqualsComparesValue(): void
    {
        $left = new SourceId('550e8400-e29b-41d4-a716-446655440000');
        $right = new SourceId('550e8400-e29b-41d4-a716-446655440000');
        $other = new SourceId('6ba7b810-9dad-11d1-80b4-00c04fd430c8');

        self::assertTrue($left->equals($right));
        self::assertFalse($left->equals($other));
    }
}
