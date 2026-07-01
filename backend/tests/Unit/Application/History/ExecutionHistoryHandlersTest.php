<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\History;

use App\Application\History\Commands\RecordExecutionHistoryCommand;
use App\Application\History\CompareExecutionHandler;
use App\Application\History\ExecutionOptimizationSnapshotMapper;
use App\Application\History\ExecutionVersionSnapshot;
use App\Application\History\GetExecutionHistoryHandler;
use App\Application\History\Ports\ExecutionHistorySnapshotStoreInterface;
use App\Application\History\Queries\CompareExecutionQuery;
use App\Application\History\Queries\GetExecutionHistoryQuery;
use App\Application\History\RecordExecutionHistoryHandler;
use App\Application\Pipeline\PipelineConfigurationJsonMapper;
use App\Application\Quality\QualityReportJsonMapper;
use App\Tests\Support\AllowAllAuthorizationGuardTrait;
use App\Domain\History\Exception\InvalidExecutionHistoryException;
use App\Domain\History\ExecutionHistory;
use App\Domain\History\ExecutionHistoryId;
use App\Domain\History\ExecutionHistoryRepositoryInterface;
use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Optimization\OptimizationParameter;
use App\Domain\Optimization\OptimizationParameterCollection;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Optimization\OptimizationStageConfiguration;
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
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\FinalVideoId;
use PHPUnit\Framework\TestCase;

final class ExecutionHistoryHandlersTest extends TestCase
{
    use AllowAllAuthorizationGuardTrait;
    public function testRecordsFirstVersion(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $store = new InMemoryExecutionHistoryStore();
        $handler = $this->createHandler($store);

        $result = $handler(new RecordExecutionHistoryCommand(
            $videoId,
            $this->pipelineConfiguration('10', 'ollama'),
            $this->optimization('11', OptimizationProfile::Balanced),
            $this->qualityReport('12', 91),
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440013'),
        ));

        self::assertSame(1, $result->versionNumber);
        self::assertSame('balanced', $result->optimizationProfile);
        self::assertSame(91, $result->qualityScore);
        self::assertCount(1, $store->findAllByVideoId($videoId));
    }

    public function testAppendsSecondVersion(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $store = new InMemoryExecutionHistoryStore();
        $handler = $this->createHandler($store);

        $handler(new RecordExecutionHistoryCommand(
            $videoId,
            $this->pipelineConfiguration('10', 'ollama'),
            $this->optimization('11', OptimizationProfile::Balanced),
            $this->qualityReport('12', 91),
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440013'),
        ));

        $second = $handler(new RecordExecutionHistoryCommand(
            $videoId,
            $this->pipelineConfiguration('20', 'mock'),
            $this->optimization('21', OptimizationProfile::Quality),
            $this->qualityReport('22', 96),
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440023'),
        ));

        self::assertSame(2, $second->versionNumber);
        self::assertCount(2, $store->findAllByVideoId($videoId));
    }

    public function testGetExecutionHistoryReturnsRecordedVersions(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $store = new InMemoryExecutionHistoryStore();
        $recordHandler = new RecordExecutionHistoryHandler(
            new InMemoryExecutionHistoryRepository($store),
            $store,
            new PipelineConfigurationJsonMapper(),
            new ExecutionOptimizationSnapshotMapper(),
            new QualityReportJsonMapper(),
        );
        $recordHandler(new RecordExecutionHistoryCommand(
            $videoId,
            PipelineConfiguration::create(
                new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
                [
                    PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
                    PipelineStage::create(PipelineStageType::Translation, 'ollama'),
                    PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
                    PipelineStage::create(PipelineStageType::VoiceClone, 'openvoice'),
                    PipelineStage::create(PipelineStageType::LipSync, 'latentsync'),
                    PipelineStage::create(PipelineStageType::VideoRender, 'ffmpeg'),
                ],
            ),
            ExecutionOptimization::create(
                new ExecutionOptimizationId('550e8400-e29b-41d4-a716-446655440011'),
                OptimizationProfile::Balanced,
                new OptimizationStageCollection([]),
                'Balanced optimization.',
                4,
            ),
            QualityReport::create(
                new QualityReportId('550e8400-e29b-41d4-a716-446655440012'),
                new QualityMetricCollection(array_map(
                    static fn (QualityCategory $category): QualityMetric => QualityMetric::create(
                        $category,
                        QualityScore::create(91),
                        'ok',
                    ),
                    QualityCategory::scored(),
                )),
                QualityScore::create(91),
                PublicationRecommendation::Ready,
            ),
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440013'),
        ));

        $handler = new GetExecutionHistoryHandler(
            new InMemoryExecutionHistoryRepository($store),
            $store,
        );

        $result = $handler(new GetExecutionHistoryQuery($videoId->value));

        self::assertSame($videoId->value, $result->videoId);
        self::assertCount(1, $result->versions);
        self::assertSame(91, $result->versions[0]->qualityScore);
    }

    public function testGetExecutionHistoryEmptyHistoryThrows(): void
    {
        $handler = new GetExecutionHistoryHandler(
            new InMemoryExecutionHistoryRepository(new InMemoryExecutionHistoryStore()),
            new InMemoryExecutionHistoryStore(),
        );

        $this->expectException(InvalidExecutionHistoryException::class);

        $handler(new GetExecutionHistoryQuery('550e8400-e29b-41d4-a716-446655440099'));
    }

    public function testCompareVersionsReportsDifferences(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $store = new InMemoryExecutionHistoryStore();
        $recordHandler = new RecordExecutionHistoryHandler(
            new InMemoryExecutionHistoryRepository($store),
            $store,
            new PipelineConfigurationJsonMapper(),
            new ExecutionOptimizationSnapshotMapper(),
            new QualityReportJsonMapper(),
        );

        $recordHandler(new RecordExecutionHistoryCommand(
            $videoId,
            PipelineConfiguration::create(
                new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
                [
                    PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
                    PipelineStage::create(PipelineStageType::Translation, 'ollama'),
                    PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
                    PipelineStage::create(PipelineStageType::VoiceClone, 'openvoice'),
                    PipelineStage::create(PipelineStageType::LipSync, 'latentsync'),
                    PipelineStage::create(PipelineStageType::VideoRender, 'ffmpeg'),
                ],
            ),
            ExecutionOptimization::create(
                new ExecutionOptimizationId('550e8400-e29b-41d4-a716-446655440011'),
                OptimizationProfile::Balanced,
                new OptimizationStageCollection([
                    new OptimizationStageConfiguration(
                        OptimizationStage::SpeechToText,
                        new OptimizationParameterCollection([
                            new OptimizationParameter('beamSize', '5'),
                        ]),
                    ),
                ]),
                'Balanced optimization.',
                4,
            ),
            QualityReport::create(
                new QualityReportId('550e8400-e29b-41d4-a716-446655440012'),
                new QualityMetricCollection(array_map(
                    static fn (QualityCategory $category): QualityMetric => QualityMetric::create(
                        $category,
                        QualityScore::create(91),
                        'ok',
                    ),
                    QualityCategory::scored(),
                )),
                QualityScore::create(91),
                PublicationRecommendation::Ready,
            ),
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440013'),
        ));

        $recordHandler(new RecordExecutionHistoryCommand(
            $videoId,
            PipelineConfiguration::create(
                new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440020'),
                [
                    PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
                    PipelineStage::create(PipelineStageType::Translation, 'mock'),
                    PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
                    PipelineStage::create(PipelineStageType::VoiceClone, 'openvoice'),
                    PipelineStage::create(PipelineStageType::LipSync, 'latentsync'),
                    PipelineStage::create(PipelineStageType::VideoRender, 'ffmpeg'),
                ],
            ),
            ExecutionOptimization::create(
                new ExecutionOptimizationId('550e8400-e29b-41d4-a716-446655440021'),
                OptimizationProfile::Quality,
                new OptimizationStageCollection([
                    new OptimizationStageConfiguration(
                        OptimizationStage::SpeechToText,
                        new OptimizationParameterCollection([
                            new OptimizationParameter('beamSize', '7'),
                        ]),
                    ),
                ]),
                'Quality optimization.',
                5,
            ),
            QualityReport::create(
                new QualityReportId('550e8400-e29b-41d4-a716-446655440022'),
                new QualityMetricCollection(array_map(
                    static fn (QualityCategory $category): QualityMetric => QualityMetric::create(
                        $category,
                        QualityScore::create(96),
                        'ok',
                    ),
                    QualityCategory::scored(),
                )),
                QualityScore::create(96),
                PublicationRecommendation::Ready,
            ),
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440023'),
        ));

        $handler = new CompareExecutionHandler($store, $this->allowAllAuthorizationGuard());
        $result = $handler(new CompareExecutionQuery($videoId->value, 1, 2));

        self::assertSame(1, $result->leftVersion);
        self::assertSame(2, $result->rightVersion);
        self::assertNotEmpty($result->providerDifferences);
        self::assertNotNull($result->optimizationDifference);
        self::assertNotNull($result->qualityScoreDifference);
        self::assertSame(5, $result->qualityScoreDifference->delta);
    }

    public function testCompareMissingVersionThrows(): void
    {
        $handler = new CompareExecutionHandler(new InMemoryExecutionHistoryStore(), $this->allowAllAuthorizationGuard());

        $this->expectException(InvalidExecutionHistoryException::class);

        $handler(new CompareExecutionQuery('550e8400-e29b-41d4-a716-446655440099', 1, 2));
    }

    private function createHandler(InMemoryExecutionHistoryStore $store): RecordExecutionHistoryHandler
    {
        return new RecordExecutionHistoryHandler(
            new InMemoryExecutionHistoryRepository($store),
            $store,
            new PipelineConfigurationJsonMapper(),
            new ExecutionOptimizationSnapshotMapper(),
            new QualityReportJsonMapper(),
        );
    }

    private function pipelineConfiguration(string $suffix, string $translationProvider): PipelineConfiguration
    {
        $id = sprintf('550e8400-e29b-41d4-a716-44665544%04d', (int) $suffix);

        return PipelineConfiguration::create(
            new PipelineConfigurationId($id),
            [
                PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
                PipelineStage::create(PipelineStageType::Translation, $translationProvider),
                PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
                PipelineStage::create(PipelineStageType::VoiceClone, 'openvoice'),
                PipelineStage::create(PipelineStageType::LipSync, 'latentsync'),
                PipelineStage::create(PipelineStageType::VideoRender, 'ffmpeg'),
            ],
        );
    }

    private function optimization(string $suffix, OptimizationProfile $profile): ExecutionOptimization
    {
        $id = sprintf('550e8400-e29b-41d4-a716-44665545%04d', (int) $suffix);

        return ExecutionOptimization::create(
            new ExecutionOptimizationId($id),
            $profile,
            new OptimizationStageCollection([
                new OptimizationStageConfiguration(
                    OptimizationStage::SpeechToText,
                    new OptimizationParameterCollection([
                        new OptimizationParameter('beamSize', '5'),
                    ]),
                ),
            ]),
            'Optimization summary',
            4,
        );
    }

    private function qualityReport(string $suffix, int $score): QualityReport
    {
        $metrics = [];
        $id = sprintf('550e8400-e29b-41d4-a716-44665546%04d', (int) $suffix);

        foreach (QualityCategory::scored() as $category) {
            $metrics[] = QualityMetric::create($category, QualityScore::create($score), 'ok');
        }

        return QualityReport::create(
            new QualityReportId($id),
            new QualityMetricCollection($metrics),
            QualityScore::create($score),
            PublicationRecommendation::Ready,
        );
    }
}

final class InMemoryExecutionHistoryRepository implements ExecutionHistoryRepositoryInterface
{
    public function __construct(
        private readonly InMemoryExecutionHistoryStore $store,
    ) {
    }

    public function save(ExecutionHistory $history): void
    {
        $this->store->rememberHistory($history);
    }

    public function findByVideoId(VideoId $videoId): ?ExecutionHistory
    {
        return $this->store->findHistory($videoId);
    }

    public function findOrCreateForVideo(VideoId $videoId): ExecutionHistory
    {
        return $this->store->findHistory($videoId)
            ?? ExecutionHistory::create(ExecutionHistoryId::generate(), $videoId);
    }
}

final class InMemoryExecutionHistoryStore implements ExecutionHistorySnapshotStoreInterface
{
    /** @var array<string, list<ExecutionVersionSnapshot>> */
    private array $snapshots = [];

    /** @var array<string, ExecutionHistory> */
    private array $histories = [];

    public function findAllByVideoId(VideoId $videoId): array
    {
        return $this->snapshots[$videoId->value] ?? [];
    }

    public function findByVideoIdAndVersion(VideoId $videoId, int $versionNumber): ?ExecutionVersionSnapshot
    {
        foreach ($this->findAllByVideoId($videoId) as $snapshot) {
            if ($snapshot->version->versionNumber() === $versionNumber) {
                return $snapshot;
            }
        }

        return null;
    }

    public function append(ExecutionHistory $history, ExecutionVersionSnapshot $snapshot): void
    {
        $this->histories[$history->videoId()->value] = $history;
        $this->snapshots[$history->videoId()->value] ??= [];
        $this->snapshots[$history->videoId()->value][] = $snapshot;
    }

    public function rememberHistory(ExecutionHistory $history): void
    {
        $this->histories[$history->videoId()->value] = $history;
    }

    public function findHistory(VideoId $videoId): ?ExecutionHistory
    {
        return $this->histories[$videoId->value] ?? null;
    }
}
