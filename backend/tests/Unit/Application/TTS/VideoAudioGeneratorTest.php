<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\TTS;

use App\Application\TTS\AudioJsonMapper;
use App\Application\TTS\DefaultVoiceSelector;
use App\Application\TTS\VideoAudioGenerator;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use App\Domain\TTS\AudioRepositoryInterface;
use App\Domain\TTS\TextToSpeechProviderInterface;
use App\Domain\Video\VideoId;
use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\TTS\AudioArtifact;
use App\Domain\TTS\AudioId;
use App\Domain\TTS\FileFormat;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceGender;
use App\Domain\TTS\VoiceLanguage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VideoAudioGeneratorTest extends TestCase
{
    private TranslationRepositoryInterface&MockObject $translationRepository;

    private AudioRepositoryInterface&MockObject $audioRepository;

    private ArtifactRepositoryInterface&MockObject $artifactRepository;

    private AIProviderResolverInterface&MockObject $aiProviderResolver;

    private TextToSpeechProviderInterface&MockObject $textToSpeechProvider;

    private VideoAudioGenerator $generator;

    protected function setUp(): void
    {
        $this->translationRepository = $this->createMock(TranslationRepositoryInterface::class);
        $this->audioRepository = $this->createMock(AudioRepositoryInterface::class);
        $this->artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $this->aiProviderResolver = $this->createMock(AIProviderResolverInterface::class);
        $this->textToSpeechProvider = $this->createMock(TextToSpeechProviderInterface::class);

        $this->generator = new VideoAudioGenerator(
            $this->translationRepository,
            $this->audioRepository,
            $this->artifactRepository,
            $this->aiProviderResolver,
            new DefaultVoiceSelector(),
            new AudioJsonMapper(),
        );
    }

    public function testGeneratesAudioAndArtifact(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $translation = Translation::create(
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TranslationLanguage::English,
            TranslationLanguage::French,
            TranslationProvider::Qwen,
            new TranslationSegmentCollection([
                TranslationSegment::create(0, 'Hello', 'Bonjour'),
            ]),
        );

        $audio = AudioArtifact::create(
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            $translation->translationId(),
            TextToSpeechProvider::F5TTS,
            Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female),
            3.5,
            FileFormat::Wav,
            '/tmp/audio.wav',
            TranslationLanguage::French,
        );

        $this->translationRepository
            ->expects(self::once())
            ->method('findAllByVideoId')
            ->with($videoId)
            ->willReturn([$translation]);

        $this->aiProviderResolver
            ->expects(self::once())
            ->method('resolveTextToSpeech')
            ->willReturn($this->textToSpeechProvider);

        $this->textToSpeechProvider
            ->expects(self::once())
            ->method('synthesize')
            ->willReturn($audio);

        $this->audioRepository->expects(self::once())->method('save');
        $this->artifactRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(
                static fn ($artifact): bool => $artifact->type() === ArtifactType::Audio
                    && $artifact->contentId()->equals(new ContentId($videoId->value)),
            ));

        $this->generator->generate($videoId);
    }
}
