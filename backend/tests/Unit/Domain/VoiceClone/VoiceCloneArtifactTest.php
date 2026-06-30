<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VoiceClone;

use App\Domain\Translation\TranslationLanguage;
use App\Domain\TTS\AudioId;
use App\Domain\VoiceClone\VoiceCloneArtifact;
use App\Domain\VoiceClone\VoiceCloneArtifactId;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceProfile;
use App\Domain\VoiceClone\VoiceProfileId;
use PHPUnit\Framework\TestCase;

final class VoiceCloneArtifactTest extends TestCase
{
    public function testCreateExposesFields(): void
    {
        $profile = VoiceProfile::create(
            new VoiceProfileId('550e8400-e29b-41d4-a716-446655440040'),
            TranslationLanguage::English,
            8.0,
            44100,
        );

        $artifact = VoiceCloneArtifact::create(
            new VoiceCloneArtifactId('550e8400-e29b-41d4-a716-446655440050'),
            $profile,
            VoiceCloneProvider::OpenVoice,
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
        );

        self::assertTrue($artifact->artifactId()->equals(
            new VoiceCloneArtifactId('550e8400-e29b-41d4-a716-446655440050'),
        ));
        self::assertSame($profile, $artifact->profile());
        self::assertSame(VoiceCloneProvider::OpenVoice, $artifact->provider());
        self::assertTrue($artifact->clonedAudioId()->equals(
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
        ));
    }
}
