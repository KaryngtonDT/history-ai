<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\AI;

use App\Domain\AI\AIEngineCapability;
use App\Infrastructure\AI\AIEngineRegistryFactory;
use App\Infrastructure\AI\AIProviderResolver;
use App\Infrastructure\AI\Exception\InvalidAIEngineConfigurationException;
use App\Infrastructure\Speech\FasterWhisperOutputParser;
use App\Infrastructure\Speech\FasterWhisperProcessRunnerInterface;
use App\Infrastructure\Speech\FasterWhisperProvider;
use App\Infrastructure\Speech\SpeechToTextProviderFactory;
use App\Infrastructure\Translation\FixedOllamaClient;
use App\Infrastructure\Translation\MockTranslationProvider;
use App\Infrastructure\Translation\OllamaTranslationPromptBuilder;
use App\Infrastructure\Translation\OllamaTranslationProvider;
use App\Infrastructure\Translation\TranslationProviderFactory;
use App\Infrastructure\TTS\AudioMapper;
use App\Infrastructure\TTS\F5TextToSpeechProvider;
use App\Infrastructure\TTS\FixedF5ProcessRunner;
use App\Infrastructure\TTS\MockTextToSpeechProvider;
use App\Infrastructure\TTS\TextToSpeechProviderFactory;
use PHPUnit\Framework\TestCase;

final class CapabilityProviderResolutionTest extends TestCase
{
    private AIProviderResolver $resolver;

    protected function setUp(): void
    {
        $registryFactory = new AIEngineRegistryFactory();
        $this->resolver = new AIProviderResolver(
            $registryFactory->create(),
            $registryFactory->createConfiguration(),
            new SpeechToTextProviderFactory(
                'faster_whisper',
                new FasterWhisperProvider(
                    $this->createMock(FasterWhisperProcessRunnerInterface::class),
                    new FasterWhisperOutputParser(),
                    'faster-whisper',
                    'base',
                ),
            ),
            new TranslationProviderFactory(
                'ollama',
                new OllamaTranslationProvider(
                    new FixedOllamaClient(),
                    new OllamaTranslationPromptBuilder(),
                    'qwen3',
                ),
                new MockTranslationProvider(),
            ),
            new TextToSpeechProviderFactory(
                'f5',
                new F5TextToSpeechProvider(
                    new FixedF5ProcessRunner(),
                    new AudioMapper(),
                    'f5-tts',
                    'F5-TTS',
                    '/models/f5',
                    sys_get_temp_dir().'/history-ai-capability-tts',
                ),
                new MockTextToSpeechProvider(),
            ),
        );
    }

    public function testFindsEnabledSpeechProviderByCapability(): void
    {
        $providers = $this->resolver->registry()->enabledProviders(AIEngineCapability::SpeechToText);

        self::assertCount(1, $providers);
        self::assertSame('faster_whisper', $providers[0]->providerId());
    }

    public function testFallsBackToConfiguredDefaultProvider(): void
    {
        self::assertNotNull($this->resolver->resolveSpeechToText());
        self::assertNotNull($this->resolver->resolveTranslation());
    }

    public function testResolvesTextToSpeechByCapability(): void
    {
        $providers = $this->resolver->registry()->enabledProviders(AIEngineCapability::TextToSpeech);

        self::assertCount(1, $providers);
        self::assertSame('f5_tts', $providers[0]->providerId());
        self::assertNotNull($this->resolver->resolveTextToSpeech());
    }

    public function testDisabledFutureProviderCannotBeResolved(): void
    {
        $this->expectException(InvalidAIEngineConfigurationException::class);

        $this->resolver->resolveSpeechToText('f5_tts');
    }
}
