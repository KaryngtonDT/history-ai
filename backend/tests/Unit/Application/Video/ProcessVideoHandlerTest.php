<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Video;

use App\Application\Video\Handlers\ProcessVideoHandler;
use App\Application\Video\Messages\ProcessVideoMessage;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Video\VideoStatus;
use App\Application\Speech\TranscriptJsonMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ProcessVideoHandlerTest extends TestCase
{
    private VideoRepositoryInterface&MockObject $videoRepository;

    private SpeechToTextProviderInterface&MockObject $speechToTextProvider;

    private TranscriptRepositoryInterface&MockObject $transcriptRepository;

    private ArtifactRepositoryInterface&MockObject $artifactRepository;

    private ProcessVideoHandler $handler;

    protected function setUp(): void
    {
        $this->videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $this->speechToTextProvider = $this->createMock(SpeechToTextProviderInterface::class);
        $this->transcriptRepository = $this->createMock(TranscriptRepositoryInterface::class);
        $this->artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);

        $this->handler = new ProcessVideoHandler(
            $this->videoRepository,
            $this->speechToTextProvider,
            $this->transcriptRepository,
            $this->artifactRepository,
            new TranscriptJsonMapper(),
        );
    }

    public function testProcessesQueuedVideoIntoTranscriptArtifact(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $queued = VideoJob::createUploaded($videoId, 'lecture.mp4', VideoLanguage::English)
            ->withStoragePath('/var/video-storage/lecture.mp4')
            ->queue();

        $transcript = Transcript::create(
            new TranscriptId('550e8400-e29b-41d4-a716-446655440010'),
            TranscriptLanguage::English,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 2.0, 'Hello world'),
            ]),
        );

        $this->videoRepository
            ->expects(self::exactly(2))
            ->method('save')
            ->willReturnCallback(function (VideoJob $job) use ($videoId): void {
                static $call = 0;
                ++$call;

                if (1 === $call) {
                    self::assertSame(VideoStatus::Processing, $job->status());
                }

                if (2 === $call) {
                    self::assertSame(VideoStatus::Completed, $job->status());
                    self::assertTrue($job->id()->equals($videoId));
                }
            });

        $this->videoRepository
            ->expects(self::once())
            ->method('findById')
            ->with($videoId)
            ->willReturn($queued);

        $this->speechToTextProvider
            ->expects(self::once())
            ->method('transcribe')
            ->willReturn($transcript);

        $this->transcriptRepository
            ->expects(self::once())
            ->method('save')
            ->with($videoId, $transcript);

        $this->artifactRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(function ($artifact) use ($videoId): bool {
                return $artifact->type() === ArtifactType::Transcript
                    && $artifact->contentId()->equals(new ContentId($videoId->value));
            }));

        ($this->handler)(new ProcessVideoMessage($videoId->value));
    }

    public function testMarksVideoFailedWhenTranscriptionFails(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $queued = VideoJob::createUploaded($videoId, 'lecture.mp4', VideoLanguage::English)
            ->withStoragePath('/var/video-storage/lecture.mp4')
            ->queue();

        $this->videoRepository
            ->method('findById')
            ->willReturn($queued);

        $this->speechToTextProvider
            ->method('transcribe')
            ->willThrowException(new \RuntimeException('transcription failed'));

        $this->videoRepository
            ->expects(self::exactly(2))
            ->method('save')
            ->willReturnCallback(function (VideoJob $job): void {
                static $call = 0;
                ++$call;

                if (2 === $call) {
                    self::assertSame(VideoStatus::Failed, $job->status());
                }
            });

        $this->transcriptRepository->expects(self::never())->method('save');
        $this->artifactRepository->expects(self::never())->method('save');

        ($this->handler)(new ProcessVideoMessage($videoId->value));
    }
}
