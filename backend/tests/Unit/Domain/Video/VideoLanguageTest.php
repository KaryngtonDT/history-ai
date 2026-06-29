<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Video;

use App\Domain\Video\VideoLanguage;
use PHPUnit\Framework\TestCase;

final class VideoLanguageTest extends TestCase
{
    public function testExposesSupportedLanguages(): void
    {
        self::assertSame('english', VideoLanguage::English->value);
        self::assertSame('french', VideoLanguage::French->value);
        self::assertSame('german', VideoLanguage::German->value);
        self::assertSame('unknown', VideoLanguage::Unknown->value);
    }
}
