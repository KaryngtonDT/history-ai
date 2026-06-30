<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\AI;

use App\Domain\AI\AIEngineCapability;
use PHPUnit\Framework\TestCase;

final class AIEngineCapabilityTest extends TestCase
{
    public function testContainsExpectedCapabilities(): void
    {
        self::assertSame('speech_to_text', AIEngineCapability::SpeechToText->value);
        self::assertSame('translation', AIEngineCapability::Translation->value);
        self::assertSame('text_to_speech', AIEngineCapability::TextToSpeech->value);
        self::assertSame('voice_clone', AIEngineCapability::VoiceClone->value);
        self::assertSame('lip_sync', AIEngineCapability::LipSync->value);
    }

    public function testCanBeResolvedFromValue(): void
    {
        self::assertSame(
            AIEngineCapability::Translation,
            AIEngineCapability::from('translation'),
        );
    }
}
