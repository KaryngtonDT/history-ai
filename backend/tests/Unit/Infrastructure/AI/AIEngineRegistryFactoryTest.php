<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\AI;

use App\Domain\AI\AIEngineCapability;
use App\Infrastructure\AI\AIEngineRegistryFactory;
use PHPUnit\Framework\TestCase;

final class AIEngineRegistryFactoryTest extends TestCase
{
    public function testCreatesRegistryWithEnabledAndDisabledProviders(): void
    {
        $factory = new AIEngineRegistryFactory();
        $registry = $factory->create();

        self::assertNotNull($registry->findByCapability(AIEngineCapability::SpeechToText));
        self::assertNotNull($registry->findByCapability(AIEngineCapability::Translation));
        self::assertNotNull($registry->findByCapability(AIEngineCapability::TextToSpeech));

        self::assertCount(1, $registry->enabledProviders(AIEngineCapability::SpeechToText));
        self::assertSame(
            'faster_whisper',
            $registry->enabledProviders(AIEngineCapability::SpeechToText)[0]->providerId(),
        );
        self::assertCount(1, $registry->enabledProviders(AIEngineCapability::Translation));
        self::assertCount(1, $registry->enabledProviders(AIEngineCapability::TextToSpeech));
        self::assertSame(
            'f5_tts',
            $registry->enabledProviders(AIEngineCapability::TextToSpeech)[0]->providerId(),
        );
        self::assertCount(1, $registry->enabledProviders(AIEngineCapability::VoiceClone));
        self::assertSame(
            'openvoice',
            $registry->enabledProviders(AIEngineCapability::VoiceClone)[0]->providerId(),
        );
        self::assertCount(0, $registry->enabledProviders(AIEngineCapability::LipSync));
    }

    public function testCreatesDefaultConfiguration(): void
    {
        $configuration = (new AIEngineRegistryFactory())->createConfiguration();

        self::assertSame('faster_whisper', $configuration->defaultProviderFor(AIEngineCapability::SpeechToText));
        self::assertSame('ollama', $configuration->defaultProviderFor(AIEngineCapability::Translation));
        self::assertSame('f5_tts', $configuration->defaultProviderFor(AIEngineCapability::TextToSpeech));
        self::assertSame('openvoice', $configuration->defaultProviderFor(AIEngineCapability::VoiceClone));
    }
}
