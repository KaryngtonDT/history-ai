<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\AI;

use App\Domain\AI\AIEngineCapability;
use App\Domain\AI\AIEngineProvider;
use App\Domain\AI\Exception\InvalidAIEngineException;
use PHPUnit\Framework\TestCase;

final class AIEngineProviderTest extends TestCase
{
    public function testCreateExposesFields(): void
    {
        $provider = AIEngineProvider::create(
            'faster_whisper',
            'Faster Whisper',
            AIEngineCapability::SpeechToText,
            true,
        );

        self::assertSame('faster_whisper', $provider->providerId());
        self::assertSame('Faster Whisper', $provider->displayName());
        self::assertSame(AIEngineCapability::SpeechToText, $provider->capability());
        self::assertTrue($provider->isEnabled());
    }

    public function testEnableAndDisableReturnNewInstances(): void
    {
        $provider = AIEngineProvider::create(
            'ollama',
            'Ollama',
            AIEngineCapability::Translation,
            false,
        );

        $enabled = $provider->enable();
        $disabled = $enabled->disable();

        self::assertFalse($provider->isEnabled());
        self::assertTrue($enabled->isEnabled());
        self::assertFalse($disabled->isEnabled());
    }

    public function testRejectsEmptyProviderId(): void
    {
        $this->expectException(InvalidAIEngineException::class);

        new AIEngineProvider('', 'Name', AIEngineCapability::Translation, true);
    }
}
