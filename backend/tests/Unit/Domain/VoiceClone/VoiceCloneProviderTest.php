<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VoiceClone;

use App\Domain\VoiceClone\VoiceCloneProvider;
use PHPUnit\Framework\TestCase;

final class VoiceCloneProviderTest extends TestCase
{
    public function testContainsExpectedProviders(): void
    {
        self::assertSame('openvoice', VoiceCloneProvider::OpenVoice->value);
        self::assertSame('seedvc', VoiceCloneProvider::SeedVC->value);
        self::assertSame('mock', VoiceCloneProvider::Mock->value);
    }

    public function testCanBeResolvedFromValue(): void
    {
        self::assertSame(
            VoiceCloneProvider::OpenVoice,
            VoiceCloneProvider::from('openvoice'),
        );
    }
}
