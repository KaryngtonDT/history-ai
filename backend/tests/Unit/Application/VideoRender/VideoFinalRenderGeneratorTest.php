<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\VideoRender;

use App\Application\VideoRender\VideoFinalRenderGenerator;
use App\Application\VideoRender\VideoRenderJsonMapper;
use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncRepositoryInterface;
use App\Domain\LipSync\LipSyncVideo;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\TTS\AudioId;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VideoRender\FinalVideoArtifact;
use App\Domain\VideoRender\FinalVideoId;
use App\Domain\VideoRender\FinalVideoRepositoryInterface;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderProviderInterface;
use App\Domain\VideoRender\VideoRenderQuality;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VideoFinalRenderGeneratorTest extends TestCase
{
    private VideoRepositoryInterface&MockObject $videoRepository;

    private LipSyncRepositoryInterface&MockObject $lipSyncRepository;

    private FinalVideoRepositoryInterface&MockObject $finalVideoRepository;

    private ArtifactRepositoryInterface&MockObject $artifactRepository;

    private AIProviderResolverInterface&MockObject $aiProviderResolver;

    private VideoRenderProviderInterface&MockObject $renderProvider;

    private VideoFinalRenderGenerator $generator;

    protected function setUp(): void
    {
        $this->videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $this->lipSyncRepository = $this->createMock(LipSyncRepositoryInterface::class);
        $this->finalVideoRepository = $this->createMock(FinalVideoRepositoryInterface::class);
        $this->artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $this->aiProviderResolver = $this->createMock(AIProviderResolverInterface::class);
        $this->renderProvider = $this->createMock(VideoRenderProviderInterface::class);

        $this->generator = new VideoFinalRenderGenerator(
            $this->videoRepository,
            $this->lipSyncRepository,
            $this->finalVideoRepository,
            $this->artifactRepository,
            $this->aiProviderResolver,
            new VideoRenderJsonMapper(),
            '/tmp/final-videos',
            VideoRenderFormat::MP4,
            VideoRenderQuality::Standard,
        );
    }

    public function testGeneratesFinalVideoAndArtifact(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $video = VideoJob::createUploaded($videoId, 'lecture.mp4', VideoLanguage::English)
            ->withStoragePath('/var/video-storage/lecture.mp4');

        $lipSync = LipSyncArtifact::create(
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            $videoId,
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            LipSyncProvider::LatentSync,
            LipSyncVideo::create(
                new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
                '/tmp/synced.mp4',
                3.5,
            ),
        );

        $rendered = FinalVideoArtifact::create(
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440090'),
            $videoId,
            $lipSync->artifactId(),
            VideoRenderProvider::FFmpeg,
            VideoRenderFormat::MP4,
            VideoRenderQuality::Standard,
            3.5,
            4096,
        );

        $this->videoRepository
            ->expects(self::once())
            ->method('findById')
            ->with($videoId)
            ->willReturn($video);

        $this->lipSyncRepository
            ->expects(self::once())
            ->method('findAllDetailedByVideoId')
            ->with($videoId)
            ->willReturn([
                [
                    'language' => TranslationLanguage::French,
                    'artifact' => $lipSync,
                    'storagePath' => '/tmp/synced.mp4',
                ],
            ]);

        $this->aiProviderResolver
            ->expects(self::once())
            ->method('resolveVideoRender')
            ->willReturn($this->renderProvider);

        $this->renderProvider
            ->expects(self::once())
            ->method('render')
            ->with($lipSync, VideoRenderFormat::MP4, VideoRenderQuality::Standard)
            ->willReturn($rendered);

        $this->finalVideoRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                $videoId,
                TranslationLanguage::French,
                $rendered,
                '/tmp/final-videos/550e8400-e29b-41d4-a716-446655440090.mp4',
            );

        $this->artifactRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(
                static fn ($artifact): bool => $artifact->type() === ArtifactType::FinalVideo
                    && $artifact->contentId()->equals(new ContentId($videoId->value)),
            ));

        $this->generator->generate($videoId);
    }
}
