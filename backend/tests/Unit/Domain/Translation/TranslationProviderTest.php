<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Translation;

use App\Domain\Translation\TranslationProvider;
use PHPUnit\Framework\TestCase;

final class TranslationProviderTest extends TestCase
{
    public function testSupportedProviders(): void
    {
        self::assertSame('qwen', TranslationProvider::Qwen->value);
        self::assertSame('deepseek', TranslationProvider::DeepSeek->value);
        self::assertSame('gemini', TranslationProvider::Gemini->value);
        self::assertSame('gpt', TranslationProvider::Gpt->value);
        self::assertSame('mock', TranslationProvider::Mock->value);
    }
}
