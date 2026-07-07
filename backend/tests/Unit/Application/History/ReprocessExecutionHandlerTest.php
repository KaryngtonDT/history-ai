<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\History;

use App\Application\Collaboration\WorkspaceAuthorizationGuard;
use App\Application\History\Commands\ReprocessExecutionCommand;
use App\Application\History\ReprocessExecutionHandler;
use App\Application\Pipeline\PipelineConfigurationJsonMapper;
use App\Tests\Support\AllowAllAuthorizationGuardTrait;
use App\Domain\History\Exception\InvalidExecutionHistoryException;
use App\Domain\History\ExecutionReplayContextInterface;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Video\VideoId;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ReprocessExecutionHandlerTest extends TestCase
{
    use AllowAllAuthorizationGuardTrait;

    private InMemoryExecutionHistoryStore $store;

    private ExecutionReplayContextInterface&MockObject $replayContext;

    private VideoProcessingQueueInterface&MockObject $videoProcessingQueue;

    protected function setUp(): void
    {
        $this->store = new InMemoryExecutionHistoryStore();
        $this->replayContext = $this->createMock(ExecutionReplayContextInterface::class);
        $this->videoProcessingQueue = $this->createMock(VideoProcessingQueueInterface::class);
    }

    public function testReplayArmsContextAndEnqueuesVideo(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $this->seedSnapshot($videoId);

        $this->replayContext
            ->expects(self::once())
            ->method('arm')
            ->with(
                $videoId,
                self::callback(fn (PipelineConfiguration $configuration): bool => 'mock' === $configuration->providerFor(PipelineStageType::Translation)),
            );

        $this->videoProcessingQueue
            ->expects(self::once())
            ->method('enqueue')
            ->with($videoId, ProcessingMode::Manual, null, null);

        $this->handler()->__invoke(new ReprocessExecutionCommand($videoId->value, 1));
    }

    public function testReplayWithProviderOverrides(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $this->seedSnapshot($videoId);

        $this->replayContext
            ->expects(self::once())
            ->method('arm')
            ->with(
                $videoId,
                self::callback(fn (PipelineConfiguration $configuration): bool => 'openvoice_v3' === $configuration->providerFor(PipelineStageType::VoiceClone)),
            );

        $this->videoProcessingQueue
            ->expects(self::once())
            ->method('enqueue')
            ->with($videoId, ProcessingMode::Manual, null, null);

        $this->handler()->__invoke(new ReprocessExecutionCommand(
            $videoId->value,
            1,
            ['voice_clone' => 'openvoice_v3'],
        ));
    }

    public function testMissingVersionThrows(): void
    {
        $this->replayContext->expects(self::never())->method('arm');
        $this->videoProcessingQueue->expects(self::never())->method('enqueue');

        $this->expectException(InvalidExecutionHistoryException::class);

        $this->handler()->__invoke(new ReprocessExecutionCommand(
            '550e8400-e29b-41d4-a716-446655440099',
            1,
        ));
    }

    private function handler(): ReprocessExecutionHandler
    {
        return new ReprocessExecutionHandler(
            $this->store,
            $this->replayContext,
            new PipelineConfigurationJsonMapper(),
            $this->videoProcessingQueue,
            $this->allowAllAuthorizationGuard(),
        );
    }

    private function seedSnapshot(VideoId $videoId): void
    {
        $recordHandler = new \App\Application\History\RecordExecutionHistoryHandler(
            new InMemoryExecutionHistoryRepository($this->store),
            $this->store,
            new PipelineConfigurationJsonMapper(),
            new \App\Application\History\ExecutionOptimizationSnapshotMapper(),
            new \App\Application\Quality\QualityReportJsonMapper(),
        );

        $recordHandler(new \App\Application\History\Commands\RecordExecutionHistoryCommand(
            $videoId,
            PipelineConfiguration::create(
                new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
                [
                    PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
                    PipelineStage::create(PipelineStageType::Translation, 'mock'),
                    PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
                    PipelineStage::create(PipelineStageType::VoiceClone, 'openvoice'),
                    PipelineStage::create(PipelineStageType::LipSync, 'latentsync'),
                    PipelineStage::create(PipelineStageType::VideoRender, 'ffmpeg'),
                ],
            ),
            \App\Domain\Optimization\ExecutionOptimization::create(
                new \App\Domain\Optimization\ExecutionOptimizationId('550e8400-e29b-41d4-a716-446655440011'),
                \App\Domain\Optimization\OptimizationProfile::Balanced,
                new \App\Domain\Optimization\OptimizationStageCollection([]),
                'Balanced optimization.',
                4,
            ),
            \App\Domain\Quality\QualityReport::create(
                new \App\Domain\Quality\QualityReportId('550e8400-e29b-41d4-a716-446655440012'),
                new \App\Domain\Quality\QualityMetricCollection(array_map(
                    static fn (\App\Domain\Quality\QualityCategory $category): \App\Domain\Quality\QualityMetric => \App\Domain\Quality\QualityMetric::create(
                        $category,
                        \App\Domain\Quality\QualityScore::create(91),
                        'ok',
                    ),
                    \App\Domain\Quality\QualityCategory::scored(),
                )),
                \App\Domain\Quality\QualityScore::create(91),
                \App\Domain\Quality\PublicationRecommendation::Ready,
            ),
            new \App\Domain\VideoRender\FinalVideoId('550e8400-e29b-41d4-a716-446655440013'),
        ));
    }
}
