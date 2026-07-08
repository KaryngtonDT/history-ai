<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Runtime\Kernel;

use App\Application\Runtime\BlockedReasonResolver;
use App\Application\Runtime\EngineCompatibilityEvaluator;
use App\Application\Runtime\RecommendedAlternativeResolver;
use App\Application\Runtime\RequirementDiffBuilder;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Domain\Engine\SelectionMode;
use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareDetectionReport;
use App\Domain\Hardware\HardwareProfile;
use App\Domain\Hardware\HardwareRepositoryInterface;
use App\Domain\Runtime\RuntimeConfiguration;
use App\Domain\Runtime\RuntimeRepositoryInterface;
use App\Domain\Runtime\RuntimeResolveContext;
use App\Infrastructure\Hardware\HardwareReportStore;
use App\Infrastructure\Runtime\Compatibility\RuntimeCompatibilityService;
use App\Infrastructure\Runtime\Discovery\BinaryScanner;
use App\Infrastructure\Runtime\Discovery\CudaScanner;
use App\Infrastructure\Runtime\Discovery\EngineDiscovery;
use App\Infrastructure\Runtime\Discovery\EnvironmentScanner;
use App\Infrastructure\Runtime\Discovery\PythonScanner;
use App\Infrastructure\Runtime\Intelligence\RecommendationEngine;
use App\Infrastructure\Storage\JsonFileStore;
use App\Application\Hardware\HardwareReportBuilder;
use App\Infrastructure\Runtime\Kernel\EngineAdapterRegistry;
use App\Infrastructure\Runtime\Intelligence\RuntimeResolverIntelligence;
use App\Infrastructure\Runtime\Kernel\RuntimeResolver;
use App\Infrastructure\Runtime\Readiness\EngineStatusFinalizer;
use App\Infrastructure\Runtime\Readiness\ReadinessEngine;
use PHPUnit\Framework\TestCase;

final class RuntimeResolverTest extends TestCase
{
    public function testResolvesManualSelection(): void
    {
        $runtimeRepository = $this->createStub(RuntimeRepositoryInterface::class);
        $runtimeRepository->method('getConfiguration')->willReturn(
            new RuntimeConfiguration(
                \App\Domain\Engine\EngineProfileName::Balanced,
                SelectionMode::Manual,
                ['speech_to_text' => 'faster_whisper_large_v3'],
            ),
        );

        $engine = $this->createEngine('faster_whisper_large_v3', EngineCatalogCapability::SpeechToText, true);
        $engineRepository = $this->createStub(EngineRepositoryInterface::class);
        $engineRepository->method('findByCapability')->willReturn([$engine]);
        $engineRepository->method('findById')->willReturn($engine);

        $discoveryRepository = $this->createStub(EngineRepositoryInterface::class);
        $discoveryRepository->method('all')->willReturn([]);

        $engineDiscovery = new EngineDiscovery(
            $discoveryRepository,
            new PythonScanner(new BinaryScanner()),
            new CudaScanner(new BinaryScanner()),
            new EnvironmentScanner(),
            'faster_whisper',
            'ollama',
            'f5',
            'openvoice',
            'latentsync',
            'ffmpeg',
        );

        $recommendationEngine = new RecommendationEngine(
            new ReadinessEngine(
                $engineDiscovery,
                new EngineStatusFinalizer(),
            ),
            $engineRepository,
        );

        $hardwareReport = (new HardwareReportBuilder(new \App\Application\Hardware\HardwareProfileClassifier()))
            ->build(new HardwareCapability());
        $hardwareRepository = new class($hardwareReport) implements HardwareRepositoryInterface {
            public function __construct(private readonly HardwareDetectionReport $report)
            {
            }

            public function detect(): HardwareDetectionReport
            {
                return $this->report;
            }

            public function profile(): HardwareProfile
            {
                return $this->report->profile;
            }

            /**
             * @return array<string, mixed>
             */
            public function overview(): array
            {
                return ['profile' => ['type' => 'cpu_only']];
            }
        };

        $compatibilityService = new RuntimeCompatibilityService(
            $hardwareRepository,
            new HardwareReportStore(new JsonFileStore(sys_get_temp_dir())),
            $engineDiscovery,
            new EngineCompatibilityEvaluator(
                new RequirementDiffBuilder(),
                new BlockedReasonResolver(sys_get_temp_dir()),
                new RecommendedAlternativeResolver(),
            ),
        );

        $resolver = new RuntimeResolver(
            $runtimeRepository,
            $engineRepository,
            $recommendationEngine,
            $hardwareRepository,
            new HardwareReportBuilder(new \App\Application\Hardware\HardwareProfileClassifier()),
            $compatibilityService,
            new EngineAdapterRegistry(),
            new RuntimeResolverIntelligence(
                $this->createStub(\App\Domain\EngineAnalytics\EngineExecutionHistoryRepositoryInterface::class),
                $hardwareRepository,
                $engineRepository,
            ),
        );

        $plan = $resolver->resolveCapability(
            EngineCatalogCapability::SpeechToText,
            new RuntimeResolveContext(),
        );

        self::assertSame('faster_whisper_large_v3', $plan->resolvedEngine->engineId);
        self::assertSame('faster_whisper', $plan->adapterKey);
        self::assertSame('user_selection', $plan->resolvedEngine->reason->value);
    }

    private function createEngine(
        string $id,
        EngineCatalogCapability $capability,
        bool $ready,
    ): \App\Domain\Engine\Engine {
        return new \App\Domain\Engine\Engine(
            id: $id,
            displayName: $id,
            capability: $capability,
            family: \App\Domain\Engine\EngineFamily::Whisper,
            role: \App\Domain\Engine\EngineCatalogRole::Default,
            installed: $ready,
            compatible: true,
            runtimeStatus: $ready ? \App\Domain\Runtime\RuntimeStatus::Ready : \App\Domain\Runtime\RuntimeStatus::Missing,
            configured: true,
        );
    }
}
