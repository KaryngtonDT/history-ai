<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\AI;

use App\Domain\AI\AIEngineCapability;
use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationProviderInterface;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\TextToSpeechProviderInterface;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncProviderInterface;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceCloneProviderInterface;
use App\Infrastructure\AI\AIEngineRegistryFactory;
use App\Infrastructure\AI\AIProviderResolver;
use App\Infrastructure\AI\Exception\InvalidAIEngineConfigurationException;
use App\Infrastructure\Speech\DeterministicSpeechToTextProvider;
use App\Infrastructure\Speech\FasterWhisperOutputParser;
use App\Infrastructure\Speech\FasterWhisperProcessRunnerInterface;
use App\Infrastructure\Speech\FasterWhisperProvider;
use App\Infrastructure\Speech\SpeechToTextProviderFactory;
use App\Infrastructure\Translation\MockTranslationProvider;
use App\Infrastructure\Translation\OllamaTranslationPromptBuilder;
use App\Infrastructure\Translation\OllamaTranslationProvider;
use App\Infrastructure\Translation\TranslationProviderFactory;
use App\Infrastructure\TTS\AudioMapper;
use App\Infrastructure\TTS\F5TextToSpeechProvider;
use App\Infrastructure\TTS\FixedF5ProcessRunner;
use App\Infrastructure\TTS\MockTextToSpeechProvider;
use App\Infrastructure\TTS\TextToSpeechProviderFactory;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderProviderInterface;
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
use App\Domain\Pipeline\PipelineConfigurationResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AIProviderResolverTest extends TestCase
{
    private AIProviderResolver $resolver;

    private PipelineConfigurationResolverInterface&MockObject $pipelineConfigurationResolver;

    protected function setUp(): void
    {
        $registryFactory = new AIEngineRegistryFactory();
        $this->pipelineConfigurationResolver = $this->createMock(PipelineConfigurationResolverInterface::class);
        $this->pipelineConfigurationResolver->method('resolve')->willReturn(null);
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
            new SpeechToTextProviderFactory(
                'faster_whisper',
                $fasterWhisper,
                new DeterministicSpeechToTextProvider(new FasterWhisperOutputParser()),
            ),
            new TranslationProviderFactory('ollama', $ollama, $mockTranslation),
            $this->createTextToSpeechProviderFactory(),
            $this->createVoiceCloneProviderFactory(),
            $this->createLipSyncProviderFactory(),
            $this->createVideoRenderProviderFactory(),
            $this->pipelineConfigurationResolver,
        );
    }

    private function createTextToSpeechProviderFactory(): TextToSpeechProviderFactory
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-resolver-tts';

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory);
        }

        return new TextToSpeechProviderFactory(
            'f5',
            new F5TextToSpeechProvider(
                new FixedF5ProcessRunner(),
                new AudioMapper(),
                'f5-tts',
                'F5-TTS',
                '/models/f5',
                $outputDirectory,
            ),
            new MockTextToSpeechProvider(),
        );
    }

    private function createVoiceCloneProviderFactory(): VoiceCloneProviderFactory
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-resolver-voice-clone';

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory);
        }

        return new VoiceCloneProviderFactory(
            'openvoice',
            new OpenVoiceProvider(
                new FixedOpenVoiceProcessRunner(),
                new VoiceCloneMapper(),
                new VoiceCloneProcessingContext(),
                'openvoice',
                'openvoice_v2',
                '/models/openvoice',
                $outputDirectory,
            ),
            new MockVoiceCloneProvider(),
        );
    }

    private function createLipSyncProviderFactory(): LipSyncProviderFactory
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-resolver-lipsync';

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory);
        }

        return new LipSyncProviderFactory(
            'latentsync',
            new LatentSyncProvider(
                new FixedLatentSyncProcessRunner(),
                new LipSyncMapper(),
                'latentsync',
                'latentsync',
                '/models/latentsync',
                $outputDirectory,
            ),
            new MockLipSyncProvider(),
        );
    }

    private function createVideoRenderProviderFactory(): VideoRenderProviderFactory
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-resolver-render';

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory);
        }

        return new VideoRenderProviderFactory(
            'ffmpeg',
            new FFmpegVideoRenderProvider(
                new FixedFFmpegProcessRunner(),
                new VideoRenderMapper(),
                'ffmpeg',
                $outputDirectory,
            ),
            new MockVideoRenderProvider(),
        );
    }

    public function testRegistryExposesRegisteredProviders(): void
    {
        self::assertGreaterThanOrEqual(8, count($this->resolver->registry()->allProviders()));
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

    public function testEnabledProvidersForTextToSpeech(): void
    {
        self::assertCount(1, $this->resolver->registry()->enabledProviders(AIEngineCapability::TextToSpeech));
        self::assertSame(
            'f5_tts',
            $this->resolver->registry()->enabledProviders(AIEngineCapability::TextToSpeech)[0]->providerId(),
        );
    }

    public function testResolveTextToSpeechReturnsProvider(): void
    {
        $provider = $this->resolver->resolveTextToSpeech();

        self::assertInstanceOf(TextToSpeechProviderInterface::class, $provider);
    }

    public function testResolveTextToSpeechWithExplicitProvider(): void
    {
        $provider = $this->resolver->resolveTextToSpeech(TextToSpeechProvider::F5TTS);

        self::assertInstanceOf(TextToSpeechProviderInterface::class, $provider);
    }

    public function testDisabledTextToSpeechProviderThrows(): void
    {
        $this->expectException(InvalidAIEngineConfigurationException::class);

        $this->resolver->resolveTextToSpeech(TextToSpeechProvider::Kokoro);
    }

    public function testResolveVoiceCloneReturnsProvider(): void
    {
        $provider = $this->resolver->resolveVoiceClone();

        self::assertInstanceOf(VoiceCloneProviderInterface::class, $provider);
    }

    public function testResolveVoiceCloneWithExplicitProvider(): void
    {
        $provider = $this->resolver->resolveVoiceClone(VoiceCloneProvider::OpenVoice);

        self::assertInstanceOf(VoiceCloneProviderInterface::class, $provider);
    }

    public function testDisabledVoiceCloneProviderThrows(): void
    {
        $this->expectException(InvalidAIEngineConfigurationException::class);

        $this->resolver->resolveVoiceClone(VoiceCloneProvider::SeedVC);
    }

    public function testResolveLipSyncReturnsProvider(): void
    {
        $provider = $this->resolver->resolveLipSync();

        self::assertInstanceOf(LipSyncProviderInterface::class, $provider);
    }

    public function testResolveLipSyncWithExplicitProvider(): void
    {
        $provider = $this->resolver->resolveLipSync(LipSyncProvider::LatentSync);

        self::assertInstanceOf(LipSyncProviderInterface::class, $provider);
    }

    public function testDisabledLipSyncProviderThrows(): void
    {
        $this->expectException(InvalidAIEngineConfigurationException::class);

        $this->resolver->resolveLipSync(LipSyncProvider::Wav2Lip);
    }

    public function testResolveVideoRenderReturnsProvider(): void
    {
        $provider = $this->resolver->resolveVideoRender();

        self::assertInstanceOf(VideoRenderProviderInterface::class, $provider);
    }

    public function testResolveVideoRenderWithExplicitProvider(): void
    {
        $provider = $this->resolver->resolveVideoRender(VideoRenderProvider::FFmpeg);

        self::assertInstanceOf(VideoRenderProviderInterface::class, $provider);
    }

    public function testUsesPipelineConfigurationWhenPresent(): void
    {
        $pipelineConfiguration = \App\Domain\Pipeline\PipelineConfiguration::create(
            new \App\Domain\Pipeline\PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
            [
                \App\Domain\Pipeline\PipelineStage::create(\App\Domain\Pipeline\PipelineStageType::SpeechToText, 'faster_whisper'),
                \App\Domain\Pipeline\PipelineStage::create(\App\Domain\Pipeline\PipelineStageType::Translation, 'ollama'),
                \App\Domain\Pipeline\PipelineStage::create(\App\Domain\Pipeline\PipelineStageType::TextToSpeech, 'f5_tts'),
                \App\Domain\Pipeline\PipelineStage::create(\App\Domain\Pipeline\PipelineStageType::VoiceClone, 'openvoice'),
                \App\Domain\Pipeline\PipelineStage::create(\App\Domain\Pipeline\PipelineStageType::LipSync, 'latentsync'),
                \App\Domain\Pipeline\PipelineStage::create(\App\Domain\Pipeline\PipelineStageType::VideoRender, 'ffmpeg'),
            ],
        );

        $this->pipelineConfigurationResolver
            ->method('resolve')
            ->willReturn($pipelineConfiguration);

        self::assertNotNull($this->resolver->resolveTranslation());
    }
}
