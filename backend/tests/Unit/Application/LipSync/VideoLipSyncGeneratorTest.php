<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\LipSync;

use App\Application\LipSync\LipSyncJsonMapper;
use App\Application\LipSync\VideoLipSyncGenerator;
use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncProviderInterface;
use App\Domain\LipSync\LipSyncRepositoryInterface;
use App\Domain\LipSync\LipSyncVideo;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\TTS\AudioId;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VoiceClone\VoiceCloneArtifact;
use App\Domain\VoiceClone\VoiceCloneArtifactId;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceCloneRepositoryInterface;
use App\Domain\VoiceClone\VoiceProfile;
use App\Domain\VoiceClone\VoiceProfileId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VideoLipSyncGeneratorTest extends TestCase
{
    private VideoRepositoryInterface&MockObject $videoRepository;

    private VoiceCloneRepositoryInterface&MockObject $voiceCloneRepository;

    private LipSyncRepositoryInterface&MockObject $lipSyncRepository;

    private ArtifactRepositoryInterface&MockObject $artifactRepository;

    private AIProviderResolverInterface&MockObject $aiProviderResolver;

    private LipSyncProviderInterface&MockObject $lipSyncProvider;

    private VideoLipSyncGenerator $generator;

    protected function setUp(): void
    {
        $this->videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $this->voiceCloneRepository = $this->createMock(VoiceCloneRepositoryInterface::class);
        $this->lipSyncRepository = $this->createMock(LipSyncRepositoryInterface::class);
        $this->artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $this->aiProviderResolver = $this->createMock(AIProviderResolverInterface::class);
        $this->lipSyncProvider = $this->createMock(LipSyncProviderInterface::class);

        $this->generator = new VideoLipSyncGenerator(
            $this->videoRepository,
            $this->voiceCloneRepository,
            $this->lipSyncRepository,
            $this->artifactRepository,
            $this->aiProviderResolver,
            new LipSyncJsonMapper(),
        );
    }

    public function testGeneratesLipSyncAndArtifact(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $video = VideoJob::createUploaded($videoId, 'lecture.mp4', VideoLanguage::English)
            ->withStoragePath('/var/video-storage/lecture.mp4');

        $voiceClone = VoiceCloneArtifact::create(
            new VoiceCloneArtifactId('550e8400-e29b-41d4-a716-446655440050'),
            VoiceProfile::create(
                new VoiceProfileId('550e8400-e29b-41d4-a716-446655440040'),
                TranslationLanguage::English,
                3.5,
                44100,
            ),
            VoiceCloneProvider::OpenVoice,
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            '/tmp/cloned.wav',
            TranslationLanguage::French,
        );

        $synced = LipSyncArtifact::create(
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            $videoId,
            $voiceClone->clonedAudioId(),
            LipSyncProvider::LatentSync,
            LipSyncVideo::create(
                new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
                '/tmp/synced.mp4',
                3.5,
            ),
        );

        $this->videoRepository
            ->expects(self::once())
            ->method('findById')
            ->with($videoId)
            ->willReturn($video);

        $this->voiceCloneRepository
            ->expects(self::once())
            ->method('findAllByVideoId')
            ->with($videoId)
            ->willReturn([$voiceClone]);

        $this->aiProviderResolver
            ->expects(self::once())
            ->method('resolveLipSync')
            ->willReturn($this->lipSyncProvider);

        $this->lipSyncProvider
            ->expects(self::once())
            ->method('synchronize')
            ->with($video, $voiceClone)
            ->willReturn($synced);

        $this->lipSyncRepository->expects(self::once())->method('save');
        $this->artifactRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(
                static fn ($artifact): bool => $artifact->type() === ArtifactType::LipSync
                    && $artifact->contentId()->equals(new ContentId($videoId->value)),
            ));

        $this->generator->generate($videoId);
    }
}
