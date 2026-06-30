<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\AI;

use App\Domain\AI\AIEngineCapability;
use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationProviderInterface;
use App\Infrastructure\AI\AIEngineRegistryFactory;
use App\Infrastructure\AI\AIProviderResolver;
use App\Infrastructure\AI\Exception\InvalidAIEngineConfigurationException;
use App\Infrastructure\Speech\FasterWhisperOutputParser;
use App\Infrastructure\Speech\FasterWhisperProcessRunnerInterface;
use App\Infrastructure\Speech\FasterWhisperProvider;
use App\Infrastructure\Speech\SpeechToTextProviderFactory;
use App\Infrastructure\Translation\MockTranslationProvider;
use App\Infrastructure\Translation\OllamaTranslationPromptBuilder;
use App\Infrastructure\Translation\OllamaTranslationProvider;
use App\Infrastructure\Translation\TranslationProviderFactory;
use PHPUnit\Framework\TestCase;

final class AIProviderResolverTest extends TestCase
{
    private AIProviderResolver $resolver;

    protected function setUp(): void
    {
        $registryFactory = new AIEngineRegistryFactory();
        $fasterWhisper = new FasterWhisperProvider(
            $this->createMock(FasterWhisperProcessRunnerInterface::class),
            new FasterWhisperOutputParser(),
            'faster-whisper',
            'base',
        );
        $ollama = new OllamaTranslationProvider(
            new \App\Infrastructure\Translation\FixedOllamaClient(),
            new OllamaTranslationPromptBuilder(),
            'qwen3',
        );
        $mockTranslation = new MockTranslationProvider();

        $this->resolver = new AIProviderResolver(
            $registryFactory->create(),
            $registryFactory->createConfiguration(),
            new SpeechToTextProviderFactory('faster_whisper', $fasterWhisper),
            new TranslationProviderFactory('ollama', $ollama, $mockTranslation),
        );
    }

    public function testRegistryExposesRegisteredProviders(): void
    {
        self::assertGreaterThanOrEqual(7, count($this->resolver->registry()->allProviders()));
    }

    public function testResolveSpeechToTextReturnsProvider(): void
    {
        $provider = $this->resolver->resolveSpeechToText();

        self::assertInstanceOf(SpeechToTextProviderInterface::class, $provider);
    }

    public function testResolveTranslationReturnsProvider(): void
    {
        $provider = $this->resolver->resolveTranslation();

        self::assertInstanceOf(TranslationProviderInterface::class, $provider);
    }

    public function testResolveTranslationWithExplicitProvider(): void
    {
        $provider = $this->resolver->resolveTranslation(TranslationProvider::Qwen);

        self::assertInstanceOf(TranslationProviderInterface::class, $provider);
    }

    public function testDisabledProviderThrows(): void
    {
        $this->expectException(InvalidAIEngineConfigurationException::class);

        $this->resolver->resolveSpeechToText('f5_tts');
    }

    public function testUnknownProviderThrows(): void
    {
        $this->expectException(InvalidAIEngineConfigurationException::class);

        $this->resolver->resolveSpeechToText('unknown');
    }

    public function testEnabledProvidersForFutureCapabilitiesAreEmpty(): void
    {
        self::assertSame([], $this->resolver->registry()->enabledProviders(AIEngineCapability::TextToSpeech));
    }
}
