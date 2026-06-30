<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\TTS;

use App\Domain\TTS\VoiceGender;
use PHPUnit\Framework\TestCase;

final class VoiceGenderTest extends TestCase
{
    public function testContainsExpectedValues(): void
    {
        self::assertSame('male', VoiceGender::Male->value);
        self::assertSame('female', VoiceGender::Female->value);
        self::assertSame('neutral', VoiceGender::Neutral->value);
    }

    public function testCanBeResolvedFromValue(): void
    {
        self::assertSame(VoiceGender::Female, VoiceGender::from('female'));
    }
}
