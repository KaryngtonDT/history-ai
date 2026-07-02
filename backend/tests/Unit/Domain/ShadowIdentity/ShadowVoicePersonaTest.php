<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\ShadowConversationStyle;
use App\Domain\ShadowIdentity\ShadowNarrationStyle;
use App\Domain\ShadowIdentity\ShadowTeachingStyle;
use App\Domain\ShadowIdentity\ShadowVoicePersona;
use PHPUnit\Framework\TestCase;

final class ShadowVoicePersonaTest extends TestCase
{
    public function testAllPersonasDefineTraits(): void
    {
        foreach (ShadowVoicePersona::cases() as $persona) {
            $traits = $persona->traits();

            self::assertGreaterThanOrEqual(0, $traits->tone);
            self::assertLessThanOrEqual(10, $traits->tone);
            self::assertInstanceOf(ShadowConversationStyle::class, $persona->defaultConversationStyle());
            self::assertInstanceOf(ShadowNarrationStyle::class, $persona->defaultNarrationStyle());
            self::assertInstanceOf(ShadowTeachingStyle::class, $persona->defaultTeachingStyle());
        }
    }

    public function testStorytellerHasHighStorytellingTrait(): void
    {
        $traits = ShadowVoicePersona::Storyteller->traits();

        self::assertSame(10, $traits->storytelling);
    }

    public function testSocraticMentorUsesSocraticThinking(): void
    {
        self::assertSame(
            ShadowConversationStyle::Socratic,
            ShadowVoicePersona::SocraticMentor->defaultConversationStyle(),
        );
    }
}
