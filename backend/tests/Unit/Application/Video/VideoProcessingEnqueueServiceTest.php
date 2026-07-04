<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Video;

use App\Application\Video\Messages\ProcessVideoMessage;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Application\Video\VideoProcessingEnqueueService;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Video\VideoStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VideoProcessingEnqueueServiceTest extends TestCase
{
    private VideoRepositoryInterface&MockObject $videoRepository;
    private VideoProcessingQueueInterface&MockObject $videoProcessingQueue;
    private VideoProcessingEnqueueService $service;

    protected function setUp(): void
    {
        $this->videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $this->videoProcessingQueue = $this->createMock(VideoProcessingQueueInterface::class);
        $this->service = new VideoProcessingEnqueueService(
            $this->videoRepository,
            $this->videoProcessingQueue,
        );
    }

    public function testRequeuesFailedVideoBeforeDispatching(): void
    {
        $videoId = VideoId::generate();
        $failed = VideoJob::createUploaded($videoId, 'lecture.mp4', VideoLanguage::Unknown)
            ->withStoragePath('/tmp/lecture.mp4')
            ->queue()
            ->startProcessing()
            ->fail();

        $this->videoRepository
            ->expects(self::once())
            ->method('findById')
            ->with($videoId)
            ->willReturn($failed);

        $this->videoRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static fn (VideoJob $job): bool => VideoStatus::Queued === $job->status()));

        $this->videoProcessingQueue
            ->expects(self::once())
            ->method('enqueue')
            ->with($videoId, ProcessingMode::Manual);

        self::assertTrue($this->service->enqueueIfNeeded($videoId));
    }

    public function testSkipsProcessingVideo(): void
    {
        $videoId = VideoId::generate();
        $processing = VideoJob::createUploaded($videoId, 'lecture.mp4', VideoLanguage::Unknown)
            ->withStoragePath('/tmp/lecture.mp4')
            ->queue()
            ->startProcessing();

        $this->videoRepository
            ->expects(self::once())
            ->method('findById')
            ->willReturn($processing);

        $this->videoRepository->expects(self::never())->method('save');
        $this->videoProcessingQueue->expects(self::never())->method('enqueue');

        self::assertFalse($this->service->enqueueIfNeeded($videoId));
    }
}
