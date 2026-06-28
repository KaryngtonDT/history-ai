<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Semantic\EmbeddingVector;
use App\Domain\Semantic\Exception\InvalidEmbeddingVectorException;
use PHPUnit\Framework\TestCase;

final class EmbeddingVectorTest extends TestCase
{
    public function testAcceptsValidVector(): void
    {
        $vector = new EmbeddingVector([0.1, 0.2, 0.3]);

        self::assertSame([0.1, 0.2, 0.3], $vector->values());
        self::assertSame(3, $vector->dimension());
    }

    public function testNormalizesIntegerValuesToFloats(): void
    {
        $vector = new EmbeddingVector([1, 0, -1]);

        self::assertSame([1.0, 0.0, -1.0], $vector->values());
    }

    public function testRejectsEmptyVector(): void
    {
        $this->expectException(InvalidEmbeddingVectorException::class);

        new EmbeddingVector([]);
    }

    public function testRejectsNonNumericValues(): void
    {
        $this->expectException(InvalidEmbeddingVectorException::class);

        /** @phpstan-ignore argument.type */
        new EmbeddingVector(['not-a-number']);
    }

    public function testEqualsComparesValues(): void
    {
        self::assertTrue((new EmbeddingVector([0.5, 0.25]))->equals(new EmbeddingVector([0.5, 0.25])));
        self::assertFalse((new EmbeddingVector([0.5, 0.25]))->equals(new EmbeddingVector([0.5, 0.3])));
    }
}
