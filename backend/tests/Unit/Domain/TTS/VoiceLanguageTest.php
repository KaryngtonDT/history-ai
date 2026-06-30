<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\TTS;

use App\Domain\TTS\VoiceLanguage;
use PHPUnit\Framework\TestCase;

final class VoiceLanguageTest extends TestCase
{
    public function testContainsExpectedValues(): void
    {
        self::assertSame('english', VoiceLanguage::English->value);
        self::assertSame('french', VoiceLanguage::French->value);
        self::assertSame('german', VoiceLanguage::German->value);
        self::assertSame('spanish', VoiceLanguage::Spanish->value);
        self::assertSame('italian', VoiceLanguage::Italian->value);
    }

    public function testCanBeResolvedFromValue(): void
    {
        self::assertSame(VoiceLanguage::French, VoiceLanguage::from('french'));
    }
}
