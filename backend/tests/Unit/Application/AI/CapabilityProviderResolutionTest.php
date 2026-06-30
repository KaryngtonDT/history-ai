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
use App\Infrastructure\VideoRender\FFmpegVideoRenderProvider;
use App\Infrastructure\VideoRender\FixedFFmpegProcessRunner;
use App\Infrastructure\VideoRender\MockVideoRenderProvider;
use App\Infrastructure\VideoRender\VideoRenderMapper;
use App\Infrastructure\VideoRender\VideoRenderProviderFactory;
use App\Infrastructure\LipSync\FixedLatentSyncProcessRunner;
use App\Infrastructure\LipSync\LatentSyncProvider;
use App\Infrastructure\LipSync\LipSyncMapper;
use App\Infrastructure\LipSync\LipSyncProviderFactory;
use App\Infrastructure\LipSync\MockLipSyncProvider;
use App\Infrastructure\VoiceClone\FixedOpenVoiceProcessRunner;
use App\Infrastructure\VoiceClone\MockVoiceCloneProvider;
use App\Infrastructure\VoiceClone\OpenVoiceProvider;
use App\Infrastructure\VoiceClone\VoiceCloneMapper;
use App\Infrastructure\VoiceClone\VoiceCloneProcessingContext;
use App\Infrastructure\VoiceClone\VoiceCloneProviderFactory;
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
            new VoiceCloneProviderFactory(
                'openvoice',
                new OpenVoiceProvider(
                    new FixedOpenVoiceProcessRunner(),
                    new VoiceCloneMapper(),
                    new VoiceCloneProcessingContext(),
                    'openvoice',
                    'openvoice_v2',
                    '/models/openvoice',
                    sys_get_temp_dir().'/history-ai-capability-voice-clone',
                ),
                new MockVoiceCloneProvider(),
            ),
            new LipSyncProviderFactory(
                'latentsync',
                new LatentSyncProvider(
                    new FixedLatentSyncProcessRunner(),
                    new LipSyncMapper(),
                    'latentsync',
                    'latentsync',
                    '/models/latentsync',
                    sys_get_temp_dir().'/history-ai-capability-lipsync',
                ),
                new MockLipSyncProvider(),
            ),
            new VideoRenderProviderFactory(
                'ffmpeg',
                new FFmpegVideoRenderProvider(
                    new FixedFFmpegProcessRunner(),
                    new VideoRenderMapper(),
                    'ffmpeg',
                    sys_get_temp_dir().'/history-ai-capability-render',
                ),
                new MockVideoRenderProvider(),
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

    public function testResolvesVoiceCloneByCapability(): void
    {
        $providers = $this->resolver->registry()->enabledProviders(AIEngineCapability::VoiceClone);

        self::assertCount(1, $providers);
        self::assertSame('openvoice', $providers[0]->providerId());
        self::assertNotNull($this->resolver->resolveVoiceClone());
    }

    public function testResolvesLipSyncByCapability(): void
    {
        $providers = $this->resolver->registry()->enabledProviders(AIEngineCapability::LipSync);

        self::assertCount(1, $providers);
        self::assertSame('latentsync', $providers[0]->providerId());
        self::assertNotNull($this->resolver->resolveLipSync());
    }

    public function testResolvesVideoRenderByCapability(): void
    {
        $providers = $this->resolver->registry()->enabledProviders(AIEngineCapability::VideoRender);

        self::assertCount(1, $providers);
        self::assertSame('ffmpeg', $providers[0]->providerId());
        self::assertNotNull($this->resolver->resolveVideoRender());
    }

    public function testDisabledFutureProviderCannotBeResolved(): void
    {
        $this->expectException(InvalidAIEngineConfigurationException::class);

        $this->resolver->resolveSpeechToText('f5_tts');
    }
}
