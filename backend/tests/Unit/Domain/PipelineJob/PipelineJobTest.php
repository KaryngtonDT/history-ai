<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\PipelineJob;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobStatus;
use App\Domain\PipelineJob\PipelineSourceType;
use App\Domain\PipelineJob\TranscriptSource;
use PHPUnit\Framework\TestCase;

final class PipelineJobTest extends TestCase
{
    public function testActiveJobLifecycle(): void
    {
        $job = PipelineJob::createQueued(
            PipelineJobId::generate(),
            'video-1',
            PipelineSourceType::Video,
            PipelineStageType::SpeechToText,
            videoId: 'video-1',
            estimatedDurationSeconds: 600,
        );

        self::assertSame(PipelineJobStatus::Queued, $job->status());

        $running = $job->start('local_stt');
        self::assertSame(PipelineJobStatus::Running, $running->status());

        $waiting = $running->complete('artifact-1');
        self::assertSame(PipelineJobStatus::WaitingUserConfirmation, $waiting->status());
        self::assertSame(100, $waiting->progressPercent());

        $done = $waiting->confirmContinue();
        self::assertSame(PipelineJobStatus::Completed, $done->status());
    }

    public function testApplyUserChoiceAcceptsQueuedJobsWaitingForUserChoice(): void
    {
        $job = PipelineJob::reconstitute(
            PipelineJobId::generate(),
            'video-1',
            'video-1',
            null,
            'video-1',
            PipelineSourceType::Youtube,
            PipelineStageType::SpeechToText,
            PipelineJobStatus::Queued,
            0,
            null,
            null,
            null,
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            null,
            null,
            null,
            null,
            0,
            null,
            null,
            null,
            null,
            [],
            [],
            null,
            true,
            ['youtube_transcript', 'local_engine'],
        );

        $updated = $job->applyUserChoice(TranscriptSource::YoutubeOriginalCaptions);

        self::assertSame(PipelineJobStatus::WaitingUserConfirmation, $updated->status());
    }

    public function testAcceptLocalSttChoiceMovesWaitingJobBackToQueued(): void
    {
        $job = PipelineJob::createQueued(
            PipelineJobId::generate(),
            'video-1',
            PipelineSourceType::Youtube,
            PipelineStageType::SpeechToText,
            videoId: 'video-1',
        )->requireUserChoice(['youtube_transcript', 'local_engine']);

        $queued = $job->acceptLocalSttChoice();

        self::assertSame(PipelineJobStatus::Queued, $queued->status());
        self::assertFalse($queued->userChoiceRequired());
    }
}
