<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\PipelineJob;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobStatus;
use App\Domain\PipelineJob\PipelineSourceType;
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
}
