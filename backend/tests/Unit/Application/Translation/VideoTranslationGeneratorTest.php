<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Translation;

use App\Application\Translation\TranslationJsonMapper;
use App\Application\Translation\VideoTranslationGenerator;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Domain\Pipeline\PipelineConfigurationResolverInterface;
use App\Infrastructure\AI\AIEngineRegistryFactory;
use App\Infrastructure\AI\AIProviderResolver;
use App\Infrastructure\Speech\DeterministicSpeechToTextProvider;
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VideoTranslationGeneratorTest extends TestCase
{
    private TranscriptRepositoryInterface&MockObject $transcriptRepository;

    private TranslationRepositoryInterface&MockObject $translationRepository;

    private ArtifactRepositoryInterface&MockObject $artifactRepository;

    private VideoTranslationGenerator $generator;

    protected function setUp(): void
    {
        $this->transcriptRepository = $this->createMock(TranscriptRepositoryInterface::class);
        $this->translationRepository = $this->createMock(TranslationRepositoryInterface::class);
        $this->artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);

        $registryFactory = new AIEngineRegistryFactory();
        $ollamaProvider = new OllamaTranslationProvider(
            new FixedOllamaClient(),
            new OllamaTranslationPromptBuilder(),
            'qwen3',
        );
        $pipelineConfigurationResolver = $this->createStub(PipelineConfigurationResolverInterface::class);
        $pipelineConfigurationResolver->method('resolve')->willReturn(null);
        $aiProviderResolver = new AIProviderResolver(
            $registryFactory->create(),
            $registryFactory->createConfiguration(),
            new SpeechToTextProviderFactory(
                'faster_whisper',
                new FasterWhisperProvider(
                    $this->createStub(FasterWhisperProcessRunnerInterface::class),
                    new FasterWhisperOutputParser(),
                    'faster-whisper',
                    'base',
                ),
                new DeterministicSpeechToTextProvider(new FasterWhisperOutputParser()),
            ),
            new TranslationProviderFactory('ollama', $ollamaProvider, new MockTranslationProvider()),
            new TextToSpeechProviderFactory(
                'f5',
                new F5TextToSpeechProvider(
                    new FixedF5ProcessRunner(),
                    new AudioMapper(),
                    'f5-tts',
                    'F5-TTS',
                    '/models/f5',
                    sys_get_temp_dir().'/history-ai-translation-tts',
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
                    sys_get_temp_dir().'/history-ai-translation-voice-clone',
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
                    sys_get_temp_dir().'/history-ai-translation-lipsync',
                ),
                new MockLipSyncProvider(),
            ),
            new VideoRenderProviderFactory(
                'ffmpeg',
                new FFmpegVideoRenderProvider(
                    new FixedFFmpegProcessRunner(),
                    new VideoRenderMapper(),
                    'ffmpeg',
                    sys_get_temp_dir().'/history-ai-translation-render',
                ),
                new MockVideoRenderProvider(),
            ),
            $pipelineConfigurationResolver,
        );

        $this->generator = new VideoTranslationGenerator(
            $this->transcriptRepository,
            $this->translationRepository,
            $this->artifactRepository,
            $aiProviderResolver,
            new TranslationJsonMapper(),
        );
    }

    public function testGeneratesTranslationAndArtifact(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $transcript = Transcript::create(
            new TranscriptId('550e8400-e29b-41d4-a716-446655440010'),
            TranscriptLanguage::English,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 2.0, 'Hello world'),
            ]),
        );

        $this->transcriptRepository
            ->expects(self::once())
            ->method('findByVideoId')
            ->with($videoId)
            ->willReturn($transcript);

        $this->translationRepository->expects(self::once())->method('save');
        $this->artifactRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(
                static fn ($artifact): bool => $artifact->type() === ArtifactType::Translation
                    && $artifact->contentId()->equals(new ContentId($videoId->value)),
            ));

        $this->generator->generate($videoId, [TranslationLanguage::French]);
    }
}
