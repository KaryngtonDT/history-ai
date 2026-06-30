<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\AI;

use App\Domain\AI\AIEngine;
use App\Domain\AI\AIEngineCapability;
use App\Domain\AI\AIEngineId;
use App\Domain\AI\AIEngineProvider;
use App\Domain\AI\Exception\InvalidAIEngineException;
use PHPUnit\Framework\TestCase;

final class AIEngineTest extends TestCase
{
    public function testSupportsMatchingCapability(): void
    {
        $engine = AIEngine::create(
            new AIEngineId('speech'),
            AIEngineCapability::SpeechToText,
            [
                AIEngineProvider::create('faster_whisper', 'Faster Whisper', AIEngineCapability::SpeechToText),
            ],
        );

        self::assertTrue($engine->supports(AIEngineCapability::SpeechToText));
        self::assertFalse($engine->supports(AIEngineCapability::Translation));
        self::assertSame(1, $engine->providerCount());
    }

    public function testEnableAndDisableReturnNewInstances(): void
    {
        $engine = AIEngine::create(
            new AIEngineId('translation'),
            AIEngineCapability::Translation,
            [],
            false,
        );

        $enabled = $engine->enable();
        $disabled = $enabled->disable();

        self::assertFalse($engine->isEnabled());
        self::assertTrue($enabled->isEnabled());
        self::assertFalse($disabled->isEnabled());
    }

    public function testEnableProviderUpdatesMatchingProvider(): void
    {
        $engine = AIEngine::create(
            new AIEngineId('translation'),
            AIEngineCapability::Translation,
            [
                AIEngineProvider::create('ollama', 'Ollama', AIEngineCapability::Translation, false),
            ],
        );

        $updated = $engine->enableProvider('ollama');

        self::assertCount(0, $engine->enabledProviders());
        self::assertCount(1, $updated->enabledProviders());
        self::assertSame('ollama', $updated->enabledProviders()[0]->providerId());
    }

    public function testRejectsMismatchedProviderCapability(): void
    {
        $this->expectException(InvalidAIEngineException::class);

        AIEngine::create(
            new AIEngineId('speech'),
            AIEngineCapability::SpeechToText,
            [
                AIEngineProvider::create('ollama', 'Ollama', AIEngineCapability::Translation),
            ],
        );
    }
}
