<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Telemetry;

use App\Application\Telemetry\CollectPipelineMetricsHandler;
use App\Application\Telemetry\PipelineTelemetryRecorder;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Quality\PublicationRecommendation;
use App\Domain\Quality\QualityCategory;
use App\Domain\Quality\QualityMetric;
use App\Domain\Quality\QualityMetricCollection;
use App\Domain\Quality\QualityReport;
use App\Domain\Quality\QualityReportId;
use App\Domain\Quality\QualityScore;
use App\Domain\Scheduler\ExecutionResource;
use App\Domain\Scheduler\ExecutionSchedule;
use App\Domain\Scheduler\ExecutionScheduleId;
use App\Domain\Scheduler\ResourceRequirement;
use App\Domain\Scheduler\ResourceRequirementCollection;
use App\Domain\Scheduler\ResourceType;
use App\Domain\Scheduler\ScheduledStage;
use App\Domain\Scheduler\ScheduledStageCollection;
use App\Domain\Scheduler\SchedulingStrategy;
use App\Domain\Telemetry\ExecutionMetricType;
use App\Domain\Telemetry\PipelineTelemetryRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;
use App\Application\Video\Messages\ProcessVideoMessage;
use App\Infrastructure\Pipeline\RuntimePipelineConfigurationContext;
use App\Infrastructure\Scheduler\RuntimeExecutionScheduleContext;
use PHPUnit\Framework\TestCase;

final class PipelineTelemetryRecorderTest extends TestCase
{
    public function testRecordCreatesMetricsForSuccessfulExecution(): void
    {
        $repository = new InMemoryPipelineTelemetryRepository();
        $recorder = $this->createRecorder($repository, true);

        $recorder->record(
            new VideoId('550e8400-e29b-41d4-a716-446655490010'),
            new ProcessVideoMessage('550e8400-e29b-41d4-a716-446655490010'),
            true,
            125.5,
            [
                PipelineStageType::Translation->value => 45.0,
                PipelineStageType::TextToSpeech->value => 30.0,
            ],
            $this->sampleQualityReport(),
            null,
            1,
            12.0,
        );

        $records = $repository->findByWorkspaceId('550e8400-e29b-41d4-a716-446655490001');
        self::assertCount(1, $records);
        self::assertTrue($records[0]->success());
        self::assertSame(94, $records[0]->qualityScore());
        self::assertSame(125.5, $records[0]->processingTimeSeconds());
        self::assertSame(1, $records[0]->retryCount());
        self::assertNotNull($records[0]->metrics()->findByType(ExecutionMetricType::QueueTime));
        self::assertCount(2, $records[0]->providerUsages()->all());
    }

    public function testRecordIgnoresFailuresWithoutBlocking(): void
    {
        $repository = new class implements PipelineTelemetryRepositoryInterface {
            public function append(\App\Domain\Telemetry\PipelineTelemetry $telemetry): void
            {
                throw new \RuntimeException('storage unavailable');
            }

            public function findByWorkspaceId(string $workspaceId): array
            {
                return [];
            }
        };

        $recorder = $this->createRecorder($repository, true);

        $recorder->record(
            new VideoId('550e8400-e29b-41d4-a716-446655490010'),
            new ProcessVideoMessage('550e8400-e29b-41d4-a716-446655490010'),
            false,
            10.0,
            [],
            null,
            'LipSync timeout',
            0,
            0.0,
        );

        self::assertTrue(true);
    }

    public function testResolveInitialQueueTimeUsesPendingStages(): void
    {
        $repository = new InMemoryPipelineTelemetryRepository();
        $recorder = $this->createRecorder($repository, false);

        self::assertSame(180.0, $recorder->resolveInitialQueueTimeSeconds());
    }

    private function createRecorder(
        PipelineTelemetryRepositoryInterface $repository,
        bool $withProject,
    ): PipelineTelemetryRecorder {
        $projectRepository = $this->createMock(ProjectRepositoryInterface::class);
        $projectRepository->method('findProjectIdByVideoId')->willReturn(
            $withProject ? new ProjectId('550e8400-e29b-41d4-a716-446655490001') : null,
        );

        $pipelineContext = new RuntimePipelineConfigurationContext();
        $pipelineContext->set(PipelineConfiguration::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655490099'),
            [
                PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
                PipelineStage::create(PipelineStageType::Translation, 'ollama'),
                PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
                PipelineStage::create(PipelineStageType::VoiceClone, 'openvoice'),
                PipelineStage::create(PipelineStageType::LipSync, 'latentsync'),
                PipelineStage::create(PipelineStageType::VideoRender, 'ffmpeg'),
            ],
        ));

        $scheduleContext = new RuntimeExecutionScheduleContext();
        $scheduleContext->set(ExecutionSchedule::create(
            ExecutionScheduleId::generate(),
            SchedulingStrategy::Balanced,
            new ScheduledStageCollection([
                ScheduledStage::create(
                    PipelineStageType::SpeechToText,
                    1,
                    new ResourceRequirementCollection([ResourceRequirement::create(ResourceType::Gpu)]),
                    60,
                    1,
                ),
                ScheduledStage::create(
                    PipelineStageType::Translation,
                    2,
                    new ResourceRequirementCollection([ResourceRequirement::create(ResourceType::Gpu)]),
                    120,
                    1,
                ),
            ]),
            [
                ExecutionResource::create(ResourceType::Gpu, 1, 2, 2),
            ],
            180,
        ));

        return new PipelineTelemetryRecorder(
            new CollectPipelineMetricsHandler($repository),
            $projectRepository,
            $pipelineContext,
            $scheduleContext,
        );
    }

    private function sampleQualityReport(): QualityReport
    {
        $metrics = [];

        foreach (QualityCategory::scored() as $category) {
            $metrics[] = QualityMetric::create($category, QualityScore::create(94));
        }

        return QualityReport::create(
            QualityReportId::generate(),
            new QualityMetricCollection($metrics),
            QualityScore::create(94),
            PublicationRecommendation::Ready,
        );
    }
}
