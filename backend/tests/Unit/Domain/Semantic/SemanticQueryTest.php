<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Semantic\Exception\InvalidSemanticQueryException;
use App\Domain\Semantic\SemanticQuery;
use PHPUnit\Framework\TestCase;

final class SemanticQueryTest extends TestCase
{
    public function testTrimsWhitespace(): void
    {
        $query = new SemanticQuery('  Roman Republic  ');

        self::assertSame('Roman Republic', $query->value());
    }

    public function testRejectsEmptyQuery(): void
    {
        $this->expectException(InvalidSemanticQueryException::class);

        new SemanticQuery('');
    }

    public function testRejectsWhitespaceOnlyQuery(): void
    {
        $this->expectException(InvalidSemanticQueryException::class);

        new SemanticQuery('   ');
    }

    public function testRejectsQueryAboveMaxLength(): void
    {
        $this->expectException(InvalidSemanticQueryException::class);

        new SemanticQuery(str_repeat('a', 501));
    }

    public function testAcceptsQueryAtMaxLength(): void
    {
        $value = str_repeat('a', 500);

        $query = new SemanticQuery($value);

        self::assertSame($value, $query->value());
    }

    public function testEqualsComparesValue(): void
    {
        self::assertTrue((new SemanticQuery('Roman'))->equals(new SemanticQuery('Roman')));
        self::assertFalse((new SemanticQuery('Roman'))->equals(new SemanticQuery('Greek')));
    }
}
