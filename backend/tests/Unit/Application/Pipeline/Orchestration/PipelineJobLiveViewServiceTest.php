<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Pipeline\Orchestration;

use App\Application\Pipeline\Orchestration\PipelineJobLiveViewService;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobStatus;
use App\Domain\PipelineJob\PipelineSourceType;
use PHPUnit\Framework\TestCase;

final class PipelineJobLiveViewServiceTest extends TestCase
{
    public function testRunningJobComputesLiveElapsedAndRemaining(): void
    {
        $startedAt = (new \DateTimeImmutable())->modify('-120 seconds');
        $job = PipelineJob::reconstitute(
            PipelineJobId::generate(),
            'source-1',
            'video-1',
            null,
            'video-1',
            PipelineSourceType::Video,
            PipelineStageType::SpeechToText,
            PipelineJobStatus::Running,
            20,
            'transcribing',
            'faster_whisper_large_v3',
            'docker',
            $startedAt,
            new \DateTimeImmutable(),
            $startedAt,
            null,
            600,
            480,
            120,
            null,
            null,
            null,
            null,
            [],
            [],
            null,
            false,
            [],
            ['checkpoint' => 'transcribing'],
        );

        $service = new PipelineJobLiveViewService();
        $view = $service->enrich($job, ['hardwareProfile' => 'CPU_ONLY']);

        self::assertTrue($view['isLive']);
        self::assertGreaterThanOrEqual(119, $view['elapsedSeconds']);
        self::assertSame('active', $view['workerStatus']);
        self::assertFalse($view['workerStale']);
        self::assertGreaterThan(20, $view['progressPercent']);
        self::assertNotNull($view['estimatedCompletionAt']);
    }

    public function testCompletedJobFreezesLiveValues(): void
    {
        $job = PipelineJob::reconstitute(
            PipelineJobId::generate(),
            'source-1',
            'video-1',
            null,
            'video-1',
            PipelineSourceType::Video,
            PipelineStageType::SpeechToText,
            PipelineJobStatus::Completed,
            100,
            'completed',
            'faster_whisper_large_v3',
            'docker',
            new \DateTimeImmutable('-600 seconds'),
            new \DateTimeImmutable(),
            new \DateTimeImmutable('-600 seconds'),
            new \DateTimeImmutable('-60 seconds'),
            600,
            0,
            540,
            null,
            null,
            null,
            null,
            [],
            [],
            null,
            false,
            [],
        );

        $service = new PipelineJobLiveViewService();
        $view = $service->enrich($job, []);

        self::assertTrue($view['liveFrozen']);
        self::assertSame('completed', $view['workerStatus']);
        self::assertSame(100, $view['progressPercent']);
    }

    public function testStaleWorkerMarkedAsWaitingForUpdate(): void
    {
        $startedAt = (new \DateTimeImmutable())->modify('-300 seconds');
        $updatedAt = (new \DateTimeImmutable())->modify('-30 seconds');
        $job = PipelineJob::reconstitute(
            PipelineJobId::generate(),
            'source-1',
            'video-1',
            null,
            'video-1',
            PipelineSourceType::Video,
            PipelineStageType::SpeechToText,
            PipelineJobStatus::Running,
            40,
            'transcribing',
            null,
            null,
            new \DateTimeImmutable('-400 seconds'),
            $updatedAt,
            $startedAt,
            null,
            600,
            300,
            300,
            null,
            null,
            null,
            null,
            [],
            [],
            null,
            false,
            [],
        );

        $service = new PipelineJobLiveViewService();
        $view = $service->enrich($job, []);

        self::assertTrue($view['workerStale']);
        self::assertSame('waiting_for_update', $view['workerStatus']);
    }
}
