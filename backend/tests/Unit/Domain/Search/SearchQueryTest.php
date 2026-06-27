<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Search;

use App\Domain\Search\Exception\InvalidSearchQueryException;
use App\Domain\Search\SearchQuery;
use PHPUnit\Framework\TestCase;

final class SearchQueryTest extends TestCase
{
    public function testAcceptsValidQuery(): void
    {
        $query = new SearchQuery('Roman Empire');

        self::assertSame('Roman Empire', $query->value());
    }

    public function testTrimsWhitespace(): void
    {
        $query = new SearchQuery('  Roman Empire  ');

        self::assertSame('Roman Empire', $query->value());
    }

    public function testRejectsEmptyQuery(): void
    {
        $this->expectException(InvalidSearchQueryException::class);
        $this->expectExceptionMessage('Search query cannot be empty.');

        new SearchQuery('');
    }

    public function testRejectsWhitespaceOnlyQuery(): void
    {
        $this->expectException(InvalidSearchQueryException::class);
        $this->expectExceptionMessage('Search query cannot be empty.');

        new SearchQuery('   ');
    }

    public function testRejectsQueryExceedingMaxLength(): void
    {
        $this->expectException(InvalidSearchQueryException::class);
        $this->expectExceptionMessage('Search query cannot exceed 255 characters.');

        new SearchQuery(str_repeat('a', 256));
    }

    public function testEqualsComparesNormalizedValue(): void
    {
        $left = new SearchQuery('  Rome  ');
        $right = new SearchQuery('Rome');

        self::assertTrue($left->equals($right));
    }
}
