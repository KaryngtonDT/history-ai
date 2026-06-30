<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\AI;

use App\Domain\AI\AIEngine;
use App\Domain\AI\AIEngineCapability;
use App\Domain\AI\AIEngineId;
use App\Domain\AI\AIEngineProvider;
use App\Domain\AI\AIEngineRegistry;
use App\Domain\AI\Exception\InvalidAIEngineException;
use PHPUnit\Framework\TestCase;

final class AIEngineRegistryTest extends TestCase
{
    private AIEngineRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = AIEngineRegistry::fromEngines([
            AIEngine::create(
                new AIEngineId('speech'),
                AIEngineCapability::SpeechToText,
                [
                    AIEngineProvider::create('faster_whisper', 'Faster Whisper', AIEngineCapability::SpeechToText),
                ],
            ),
            AIEngine::create(
                new AIEngineId('translation'),
                AIEngineCapability::Translation,
                [
                    AIEngineProvider::create('ollama', 'Ollama', AIEngineCapability::Translation),
                ],
            ),
            AIEngine::create(
                new AIEngineId('text-to-speech'),
                AIEngineCapability::TextToSpeech,
                [
                    AIEngineProvider::create('f5_tts', 'F5-TTS', AIEngineCapability::TextToSpeech, false),
                ],
            ),
        ]);
    }

    public function testFindByCapabilityReturnsMatchingEngine(): void
    {
        $engine = $this->registry->findByCapability(AIEngineCapability::Translation);

        self::assertNotNull($engine);
        self::assertSame('translation', $engine->id()->value);
    }

    public function testFindByIdReturnsMatchingEngine(): void
    {
        $engine = $this->registry->findById(new AIEngineId('speech'));

        self::assertNotNull($engine);
        self::assertSame(AIEngineCapability::SpeechToText, $engine->capability());
    }

    public function testEnabledProvidersFiltersDisabledProviders(): void
    {
        $providers = $this->registry->enabledProviders(AIEngineCapability::Translation);

        self::assertCount(1, $providers);
        self::assertSame('ollama', $providers[0]->providerId());
    }

    public function testAllProvidersReturnsEveryRegisteredProvider(): void
    {
        self::assertCount(3, $this->registry->allProviders());
    }

    public function testRejectsDuplicateCapabilities(): void
    {
        $this->expectException(InvalidAIEngineException::class);

        AIEngineRegistry::fromEngines([
            AIEngine::create(new AIEngineId('speech-a'), AIEngineCapability::SpeechToText, []),
            AIEngine::create(new AIEngineId('speech-b'), AIEngineCapability::SpeechToText, []),
        ]);
    }
}
