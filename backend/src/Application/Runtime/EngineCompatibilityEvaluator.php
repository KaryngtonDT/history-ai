<?php

declare(strict_types=1);

namespace App\Application\Runtime;

use App\Domain\Engine\Engine;
use App\Domain\Hardware\BlockedReasonCode;
use App\Domain\Hardware\CompatibilitySeverity;
use App\Domain\Hardware\FixType;
use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareCompatibilityResult;
use App\Domain\Hardware\HardwareProfileType;
use App\Domain\Hardware\HardwareProvider;
use App\Domain\Hardware\HardwareRequirement;
use App\Domain\Runtime\EngineExecutionMode;
use App\Domain\Runtime\RuntimeStatus;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;
use App\Infrastructure\Runtime\Catalog\EngineRequirementMatrix;

final class EngineCompatibilityEvaluator
{
    public function __construct(
        private readonly RequirementDiffBuilder $requirementDiffBuilder,
        private readonly BlockedReasonResolver $blockedReasonResolver,
        private readonly RecommendedAlternativeResolver $recommendedAlternativeResolver,
    ) {
    }

    public function evaluate(
        Engine $engine,
        HardwareProfileType $profileType,
        HardwareCapability $capabilities,
        HardwareProvider $provider = HardwareProvider::Docker,
    ): HardwareCompatibilityResult {
        $requirement = EngineRequirementMatrix::findByEngineId($engine->id)
            ?? new HardwareRequirement($engine->id);

        $missingHardware = $this->requirementDiffBuilder->build($requirement, $capabilities);
        $hardwareCompatible = [] === $missingHardware;
        $runtimeReady = $engine->isReady() && $hardwareCompatible;

        $status = $this->resolveStatus($engine, $hardwareCompatible, $runtimeReady);
        $alternative = $this->recommendedAlternativeResolver->resolve($engine->id, $capabilities, !$hardwareCompatible);

        if ($runtimeReady) {
            return new HardwareCompatibilityResult(
                engineId: $engine->id,
                status: 'ready',
                hardwareProfile: $profileType,
                blockedReasonCode: BlockedReasonCode::None,
                humanReason: sprintf('%s is ready on this hardware profile.', $engine->displayName),
                missingRequirements: [],
                recommendedAlternative: null,
                canBeFixedByInstall: false,
                canBeFixedByHardware: false,
                canBeFixedByRemoteProvider: false,
                severity: CompatibilitySeverity::Info,
                provider: $provider,
                fixTypes: [],
                documentationLink: $requirement->documentationLink,
                hardwareCompatible: true,
            );
        }

        $reason = $this->blockedReasonResolver->resolve(
            $engine->id,
            $requirement,
            $capabilities,
            $engine->runtimeStatus,
            $engine->executableFound,
            $engine->modelFound,
            $missingHardware,
        );

        $fixTypes = $this->resolveFixTypes($requirement, $missingHardware, $engine, $alternative);
        $severity = 'warning' === $reason['severity']
            ? CompatibilitySeverity::Warning
            : CompatibilitySeverity::Blocking;

        return new HardwareCompatibilityResult(
            engineId: $engine->id,
            status: $status,
            hardwareProfile: $profileType,
            blockedReasonCode: $reason['code'],
            humanReason: $reason['humanReason'],
            missingRequirements: $this->mergeMissing($missingHardware, $engine),
            recommendedAlternative: $alternative,
            canBeFixedByInstall: $this->canBeFixedByInstall($engine, $missingHardware),
            canBeFixedByHardware: [] !== $missingHardware,
            canBeFixedByRemoteProvider: $requirement->cudaRequired || $requirement->nvencRequired,
            severity: $severity,
            provider: $provider,
            fixTypes: $fixTypes,
            documentationLink: $requirement->documentationLink ?? $engine->documentationPath,
            hardwareCompatible: $hardwareCompatible,
        );
    }

    private function resolveStatus(Engine $engine, bool $hardwareCompatible, bool $runtimeReady): string
    {
        if ($runtimeReady) {
            return 'ready';
        }

        if (EngineExecutionMode::Mock === $engine->executionMode || RuntimeStatus::Mock === $engine->runtimeStatus) {
            return 'mock';
        }

        if (!$hardwareCompatible) {
            return 'blocked';
        }

        if (RuntimeStatus::Misconfigured === $engine->runtimeStatus) {
            return 'misconfigured';
        }

        if (!$engine->executableFound || !$engine->modelFound) {
            return 'missing';
        }

        return strtolower($engine->runtimeStatus->value);
    }

    /**
     * @param list<string> $missingHardware
     * @return list<FixType>
     */
    private function resolveFixTypes(
        HardwareRequirement $requirement,
        array $missingHardware,
        Engine $engine,
        ?string $alternative,
    ): array {
        $fixes = [];

        if (null !== $alternative) {
            $fixes[] = FixType::UseCompatibleAlternative;
        }

        if ([] !== $missingHardware) {
            $fixes[] = FixType::UpgradeHardware;
            if ($requirement->cudaRequired || $requirement->nvencRequired) {
                $fixes[] = FixType::UseRemoteGpuProvider;
            }
        }

        if (!$engine->modelFound && $requirement->engineId !== 'ffmpeg') {
            $fixes[] = FixType::InstallModel;
        }

        if (!$engine->executableFound) {
            $fixes[] = FixType::InstallDependency;
        }

        return array_values(array_unique($fixes, SORT_REGULAR));
    }

  private function canBeFixedByInstall(Engine $engine, array $missingHardware): bool
    {
        return [] === $missingHardware && (!$engine->executableFound || !$engine->modelFound);
    }

    /**
     * @param list<string> $missingHardware
     * @return list<string>
     */
    private function mergeMissing(array $missingHardware, Engine $engine): array
    {
        $missing = $missingHardware;

        if (!$engine->executableFound) {
            $missing[] = 'Engine binary';
        }

        if (!$engine->modelFound && EngineCatalogDefinitions::findById($engine->id)?->requiresModelFiles) {
            $missing[] = 'Model files';
        }

        return array_values(array_unique($missing));
    }
}
