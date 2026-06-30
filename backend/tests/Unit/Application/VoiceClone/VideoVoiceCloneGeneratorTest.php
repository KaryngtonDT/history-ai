<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\VoiceClone;

use App\Application\VoiceClone\VideoVoiceCloneGenerator;
use App\Application\VoiceClone\VoiceCloneJsonMapper;
use App\Domain\AI\AIProviderResolverInterface;
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
use App\Domain\TTS\AudioArtifact;
use App\Domain\TTS\AudioId;
use App\Domain\TTS\AudioRepositoryInterface;
use App\Domain\TTS\FileFormat;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceGender;
use App\Domain\TTS\VoiceLanguage;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VoiceClone\VoiceCloneArtifact;
use App\Domain\VoiceClone\VoiceCloneArtifactId;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceCloneProviderInterface;
use App\Domain\VoiceClone\VoiceCloneReferenceContextInterface;
use App\Domain\VoiceClone\VoiceCloneRepositoryInterface;
use App\Domain\VoiceClone\VoiceProfile;
use App\Domain\VoiceClone\VoiceProfileId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VideoVoiceCloneGeneratorTest extends TestCase
{
    private VideoRepositoryInterface&MockObject $videoRepository;

    private TranslationRepositoryInterface&MockObject $translationRepository;

    private AudioRepositoryInterface&MockObject $audioRepository;

    private VoiceCloneRepositoryInterface&MockObject $voiceCloneRepository;

    private ArtifactRepositoryInterface&MockObject $artifactRepository;

    private AIProviderResolverInterface&MockObject $aiProviderResolver;

    private VoiceCloneProviderInterface&MockObject $voiceCloneProvider;

    private VoiceCloneReferenceContextInterface&MockObject $processingContext;

    private VideoVoiceCloneGenerator $generator;

    protected function setUp(): void
    {
        $this->videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $this->translationRepository = $this->createMock(TranslationRepositoryInterface::class);
        $this->audioRepository = $this->createMock(AudioRepositoryInterface::class);
        $this->voiceCloneRepository = $this->createMock(VoiceCloneRepositoryInterface::class);
        $this->artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $this->aiProviderResolver = $this->createMock(AIProviderResolverInterface::class);
        $this->voiceCloneProvider = $this->createMock(VoiceCloneProviderInterface::class);
        $this->processingContext = $this->createMock(VoiceCloneReferenceContextInterface::class);

        $this->generator = new VideoVoiceCloneGenerator(
            $this->videoRepository,
            $this->translationRepository,
            $this->audioRepository,
            $this->voiceCloneRepository,
            $this->artifactRepository,
            $this->aiProviderResolver,
            $this->processingContext,
            new VoiceCloneJsonMapper(),
        );
    }

    public function testGeneratesVoiceCloneAndArtifact(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $video = VideoJob::createUploaded($videoId, 'lecture.mp4', VideoLanguage::English)
            ->withStoragePath('/var/video-storage/lecture.mp4');

        $translation = Translation::create(
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TranslationLanguage::English,
            TranslationLanguage::French,
            TranslationProvider::Qwen,
            new TranslationSegmentCollection([
                TranslationSegment::create(0, 'Hello', 'Bonjour'),
            ]),
        );

        $sourceAudio = AudioArtifact::create(
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            $translation->translationId(),
            TextToSpeechProvider::F5TTS,
            Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female),
            3.5,
            FileFormat::Wav,
            '/tmp/generic.wav',
            TranslationLanguage::French,
        );

        $cloned = VoiceCloneArtifact::create(
            new VoiceCloneArtifactId('550e8400-e29b-41d4-a716-446655440050'),
            VoiceProfile::create(
                new VoiceProfileId('550e8400-e29b-41d4-a716-446655440040'),
                TranslationLanguage::English,
                3.5,
                44100,
            ),
            VoiceCloneProvider::OpenVoice,
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            $sourceAudio->audioId(),
            '/tmp/cloned.wav',
            TranslationLanguage::French,
        );

        $this->videoRepository
            ->expects(self::once())
            ->method('findById')
            ->with($videoId)
            ->willReturn($video);

        $this->translationRepository
            ->expects(self::once())
            ->method('findAllByVideoId')
            ->with($videoId)
            ->willReturn([$translation]);

        $this->audioRepository
            ->expects(self::once())
            ->method('findByVideoIdAndLanguage')
            ->with($videoId, TranslationLanguage::French)
            ->willReturn($sourceAudio);

        $this->aiProviderResolver
            ->expects(self::once())
            ->method('resolveVoiceClone')
            ->willReturn($this->voiceCloneProvider);

        $this->processingContext
            ->expects(self::once())
            ->method('withReference')
            ->willReturnCallback(static fn (string $path, callable $callback) => $callback());

        $this->voiceCloneProvider
            ->expects(self::once())
            ->method('cloneVoice')
            ->with($sourceAudio, $translation)
            ->willReturn($cloned);

        $this->voiceCloneRepository->expects(self::once())->method('save');
        $this->artifactRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(
                static fn ($artifact): bool => $artifact->type() === ArtifactType::VoiceClone
                    && $artifact->contentId()->equals(new ContentId($videoId->value)),
            ));

        $this->generator->generate($videoId);
    }
}
