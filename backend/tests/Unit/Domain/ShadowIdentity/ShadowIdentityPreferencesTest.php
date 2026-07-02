<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\ShadowConversationStyle;
use App\Domain\ShadowIdentity\ShadowIdentityPreferences;
use App\Domain\ShadowIdentity\ShadowVoicePersona;
use PHPUnit\Framework\TestCase;

final class ShadowIdentityPreferencesTest extends TestCase
{
    public function testDefaultUsesTeacherPersona(): void
    {
        $preferences = ShadowIdentityPreferences::default();

        self::assertSame(ShadowVoicePersona::Teacher, $preferences->persona());
        self::assertSame(ShadowConversationStyle::Conversational, $preferences->conversationStyle());
    }

    public function testPersonaSwitchUpdatesDerivedStyles(): void
    {
        $updated = ShadowIdentityPreferences::default()->withPersona(ShadowVoicePersona::Storyteller);

        self::assertSame(ShadowVoicePersona::Storyteller, $updated->persona());
        self::assertGreaterThan(
            ShadowIdentityPreferences::default()->personaTraits()->storytelling,
            $updated->personaTraits()->storytelling,
        );
    }

    public function testExamplesLevelCanBeIncreased(): void
    {
        $updated = ShadowIdentityPreferences::default()->withExamplesLevel(9);

        self::assertSame(9, $updated->examplesLevel());
        self::assertSame(9, $updated->personaTraits()->examples);
    }
}
