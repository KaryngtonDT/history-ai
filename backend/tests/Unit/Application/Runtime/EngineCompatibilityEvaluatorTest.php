<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Runtime;

use App\Application\Runtime\BlockedReasonResolver;
use App\Application\Runtime\EngineCompatibilityEvaluator;
use App\Application\Runtime\RecommendedAlternativeResolver;
use App\Application\Runtime\RequirementDiffBuilder;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineCatalogRole;
use App\Domain\Engine\EngineFamily;
use App\Domain\Engine\Engine;
use App\Domain\Hardware\BlockedReasonCode;
use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareProfileType;
use App\Domain\Runtime\EngineExecutionMode;
use App\Domain\Runtime\RuntimeStatus;
use PHPUnit\Framework\TestCase;

final class EngineCompatibilityEvaluatorTest extends TestCase
{
    private EngineCompatibilityEvaluator $evaluator;

    protected function setUp(): void
    {
        $this->evaluator = new EngineCompatibilityEvaluator(
            new RequirementDiffBuilder(),
            new BlockedReasonResolver(),
            new RecommendedAlternativeResolver(),
        );
    }

    public function testLatentSyncBlockedOnAmdIntegratedGpu(): void
    {
        $engine = $this->engine('latentsync', 'LatentSync', RuntimeStatus::Blocked);
        $capabilities = $this->lowEndAmdCapabilities();

        $result = $this->evaluator->evaluate(
            $engine,
            HardwareProfileType::LowEndLocal,
            $capabilities,
        );

        self::assertSame('blocked', $result->status);
        self::assertSame(BlockedReasonCode::NvidiaCudaRequired, $result->blockedReasonCode);
        self::assertContains('NVIDIA GPU', $result->missingRequirements);
        self::assertContains('CUDA', $result->missingRequirements);
        self::assertSame('wav2lip', $result->recommendedAlternative);
        self::assertTrue($result->canBeFixedByHardware);
        self::assertFalse($result->canBeFixedByInstall);
    }

    public function testWav2LipRecommendedAsLocalFallbackForEchoMimic(): void
    {
        $engine = $this->engine('echomimic_v2', 'EchoMimic V2', RuntimeStatus::Blocked);
        $capabilities = $this->lowEndAmdCapabilities();

        $result = $this->evaluator->evaluate(
            $engine,
            HardwareProfileType::LowEndLocal,
            $capabilities,
        );

        self::assertSame('blocked', $result->status);
        self::assertSame('wav2lip', $result->recommendedAlternative);
    }

    public function testFfmpegReadyWithoutNvidia(): void
    {
        $engine = new Engine(
            id: 'ffmpeg',
            displayName: 'FFmpeg',
            capability: EngineCatalogCapability::VideoRender,
            family: EngineFamily::Ffmpeg,
            role: EngineCatalogRole::Default,
            installed: true,
            compatible: true,
            executionMode: EngineExecutionMode::Real,
            runtimeStatus: RuntimeStatus::Ready,
            executableFound: true,
            modelFound: true,
            configured: true,
        );

        $result = $this->evaluator->evaluate(
            $engine,
            HardwareProfileType::LowEndLocal,
            $this->lowEndAmdCapabilities(),
        );

        self::assertSame('ready', $result->status);
        self::assertTrue($result->hardwareCompatible);
    }

    public function testNvencBlockedWithoutNvidia(): void
    {
        $engine = $this->engine('ffmpeg_nvenc', 'FFmpeg NVENC', RuntimeStatus::Blocked, executableFound: true);
        $capabilities = $this->lowEndAmdCapabilities();

        $result = $this->evaluator->evaluate(
            $engine,
            HardwareProfileType::LowEndLocal,
            $capabilities,
        );

        self::assertSame('blocked', $result->status);
        self::assertSame(BlockedReasonCode::NvencRequiresNvidia, $result->blockedReasonCode);
        self::assertSame('ffmpeg_av1', $result->recommendedAlternative);
    }

    public function testOptionalLanguagePackDoesNotBlockOpenVoiceCore(): void
    {
        $engine = new Engine(
            id: 'openvoice_v2',
            displayName: 'OpenVoice V2',
            capability: EngineCatalogCapability::VoiceClone,
            family: EngineFamily::VoiceClone,
            role: EngineCatalogRole::Default,
            installed: true,
            compatible: true,
            executionMode: EngineExecutionMode::Real,
            runtimeStatus: RuntimeStatus::Ready,
            executableFound: true,
            modelFound: true,
            configured: true,
        );

        $result = $this->evaluator->evaluate(
            $engine,
            HardwareProfileType::LowEndLocal,
            $this->lowEndAmdCapabilities(),
        );

        self::assertSame('ready', $result->status);
        self::assertNotContains('optional language pack', strtolower($result->humanReason));
    }

    private function engine(
        string $id,
        string $displayName,
        RuntimeStatus $status,
        bool $executableFound = false,
        bool $modelFound = false,
    ): Engine {
        return new Engine(
            id: $id,
            displayName: $displayName,
            capability: EngineCatalogCapability::LipSync,
            family: EngineFamily::LipSync,
            role: EngineCatalogRole::Default,
            installed: false,
            compatible: false,
            executionMode: EngineExecutionMode::Real,
            runtimeStatus: $status,
            executableFound: $executableFound,
            modelFound: $modelFound,
            configured: true,
        );
    }

    private function lowEndAmdCapabilities(): HardwareCapability
    {
        return new HardwareCapability(
            cpuModel: 'AMD Ryzen',
            ramTotalGb: 16.0,
            ramAvailableGb: 4.5,
            gpuVendor: 'AMD',
            gpuName: 'AMD Radeon integrated graphics',
            vramGb: null,
            cudaAvailable: false,
            dockerGpuAccess: false,
            wsl2: true,
        );
    }
}
