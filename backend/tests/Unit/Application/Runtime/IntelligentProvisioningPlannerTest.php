<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Runtime;

use App\Application\Runtime\BlockedReasonResolver;
use App\Application\Runtime\EngineCompatibilityEvaluator;
use App\Application\Runtime\IntelligentProvisioningPlanner;
use App\Application\Runtime\RecommendedAlternativeResolver;
use App\Application\Runtime\RequirementDiffBuilder;
use App\Domain\Engine\Engine;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineCatalogRole;
use App\Domain\Engine\EngineFamily;
use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareDetectionReport;
use App\Domain\Hardware\HardwareProfile;
use App\Domain\Hardware\HardwareProfileType;
use App\Domain\Runtime\EngineExecutionMode;
use App\Domain\Runtime\RuntimeStatus;
use App\Domain\Engine\EngineRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class IntelligentProvisioningPlannerTest extends TestCase
{
    public function testSkipsLatentSyncAndSchedulesWav2LipOnLowEndHardware(): void
    {
        $report = $this->lowEndReport();
        $engines = [
            $this->engine('latentsync', EngineCatalogCapability::LipSync, RuntimeStatus::Blocked),
            $this->engine('wav2lip', EngineCatalogCapability::LipSync, RuntimeStatus::Blocked),
            $this->engine('ffmpeg', EngineCatalogCapability::VideoRender, RuntimeStatus::Ready),
        ];

        $repository = $this->createMock(EngineRepositoryInterface::class);
        $repository->method('all')->willReturn($engines);
        $repository->method('findById')->willReturnCallback(
            static fn (string $id): ?Engine => array_values(array_filter(
                $engines,
                static fn (Engine $engine): bool => $engine->id === $id,
            ))[0] ?? null,
        );

        $planner = new IntelligentProvisioningPlanner(
            $repository,
            new EngineCompatibilityEvaluator(
                new RequirementDiffBuilder(),
                new BlockedReasonResolver(),
                new RecommendedAlternativeResolver(),
            ),
        );

        $plan = $planner->plan($report);

        $provisionIds = array_map(static fn ($entry) => $entry->engineId, $plan->toProvision);
        $skippedIds = array_map(static fn ($entry) => $entry->engineId, $plan->skipped);

        self::assertContains('latentsync', $skippedIds);
        self::assertNotContains('latentsync', $provisionIds);
        self::assertContains('wav2lip', $provisionIds);
    }

    private function lowEndReport(): HardwareDetectionReport
    {
        $capabilities = new HardwareCapability(
            gpuVendor: 'AMD',
            gpuName: 'AMD Radeon integrated graphics',
            cudaAvailable: false,
            ramTotalGb: 16.0,
            ramAvailableGb: 4.5,
        );

        return new HardwareDetectionReport(
            new HardwareProfile(
                HardwareProfileType::LowEndLocal,
                $capabilities,
                'Low-end local machine',
            ),
            $capabilities,
            new \DateTimeImmutable(),
        );
    }

    private function engine(
        string $id,
        EngineCatalogCapability $capability,
        RuntimeStatus $status,
    ): Engine {
        return new Engine(
            id: $id,
            displayName: $id,
            capability: $capability,
            family: EngineFamily::LipSync,
            role: EngineCatalogRole::Default,
            installed: RuntimeStatus::Ready === $status,
            compatible: true,
            executionMode: EngineExecutionMode::Real,
            runtimeStatus: $status,
            executableFound: RuntimeStatus::Ready === $status,
            modelFound: RuntimeStatus::Ready === $status,
            configured: true,
        );
    }
}
