<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\TTS;

use App\Domain\TTS\TextToSpeechProvider;
use PHPUnit\Framework\TestCase;

final class TextToSpeechProviderTest extends TestCase
{
    public function testContainsExpectedValues(): void
    {
        self::assertSame('f5_tts', TextToSpeechProvider::F5TTS->value);
        self::assertSame('kokoro', TextToSpeechProvider::Kokoro->value);
        self::assertSame('xtts', TextToSpeechProvider::XTTS->value);
        self::assertSame('mock', TextToSpeechProvider::Mock->value);
    }

    public function testCanBeResolvedFromValue(): void
    {
        self::assertSame(TextToSpeechProvider::F5TTS, TextToSpeechProvider::from('f5_tts'));
    }
}
