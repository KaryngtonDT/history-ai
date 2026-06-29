<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Speech;

use App\Domain\Speech\TranscriptLanguage;
use PHPUnit\Framework\TestCase;

final class TranscriptLanguageTest extends TestCase
{
    public function testEnumValues(): void
    {
        self::assertSame('english', TranscriptLanguage::English->value);
        self::assertSame('french', TranscriptLanguage::French->value);
        self::assertSame('german', TranscriptLanguage::German->value);
        self::assertSame('unknown', TranscriptLanguage::Unknown->value);
    }
}
