<?php

declare(strict_types=1);

namespace App\Application\RuntimeCompletion;

use App\Application\Runtime\RuntimePlatformInterface;
use App\Application\RuntimeDashboard\RuntimeDashboardInterface;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineCatalogTier;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;
use App\Infrastructure\Runtime\Provisioning\EngineProvisioningCatalog;

final class RuntimeCompletionPlanner
{
    /**
     * @var list<string>
     */
    private const HARDWARE_BLOCKED_CODES = [
        'nvidia_cuda_required',
        'nvenc_required',
        'insufficient_vram',
        'insufficient_ram',
        'gpu_vendor_mismatch',
        'rocm_required',
    ];

    public function __construct(
        private readonly RuntimeDashboardInterface $dashboard,
        private readonly RuntimePlatformInterface $platform,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function plan(): array
    {
        $dashboard = $this->dashboard->dashboard();
        $readiness = $this->platform->readiness();
        $engines = is_array($readiness['engines'] ?? null) ? $readiness['engines'] : [];

        $recommendedPipeline = is_array($dashboard['hardware']['recommendedPipeline'] ?? null)
            ? $dashboard['hardware']['recommendedPipeline']
            : [];

        $recByCapability = [];
        foreach ($dashboard['engineRecommendations'] ?? [] as $rec) {
            if (is_array($rec) && isset($rec['capability'])) {
                $recByCapability[$rec['capability']] = $rec;
            }
        }

        $byCapability = [];
        foreach ($engines as $engine) {
            if (!is_array($engine) || !isset($engine['capability'], $engine['id'])) {
                continue;
            }
            $byCapability[$engine['capability']][] = $engine;
        }

        $capabilities = [];
        $toProvision = [];
        $plannedIds = [];

        foreach (EngineCatalogCapability::cases() as $capability) {
            $capKey = $capability->value;
            $capEngines = $byCapability[$capKey] ?? [];
            $default = EngineCatalogDefinitions::defaultForCapability($capability);
            $pipelineKey = $this->pipelineKeyFor($capability);
            $recommendedId = $recommendedPipeline[$pipelineKey]
                ?? ($recByCapability[$capKey]['recommendedEngineId'] ?? $default?->id);

            $installed = [];
            $blocked = [];
            $missingCompatible = [];
            $futurePremium = [];

            foreach ($capEngines as $engine) {
                $engineId = (string) $engine['id'];
                $compat = is_array($engine['compatibility'] ?? null) ? $engine['compatibility'] : [];
                $tier = EngineCatalogTier::tryFrom((string) ($engine['tier'] ?? '')) ?? EngineCatalogTier::Default;
                $status = (string) ($engine['status'] ?? '');

                if ('ready' === $status) {
                    $installed[] = $this->engineSummary($engine, $compat);

                    continue;
                }

                if ($this->isFuturePremium($tier, $compat)) {
                    $futurePremium[] = $this->engineSummary($engine, $compat, blocked: true);

                    continue;
                }

                if ($this->isHardwareBlocked($compat)) {
                    $blocked[] = $this->engineSummary($engine, $compat, blocked: true);

                    continue;
                }

                if ($this->isMissingCompatible($engine, $compat, $tier, $recommendedId, $recommendedPipeline, $recByCapability, $capKey)) {
                    $missingCompatible[] = $this->engineSummary($engine, $compat);
                }
            }

            foreach ($missingCompatible as $entry) {
                $engineId = (string) ($entry['engineId'] ?? '');
                if ('' === $engineId || isset($plannedIds[$engineId])) {
                    continue;
                }

                if ($engineId !== $recommendedId) {
                    continue;
                }

                $spec = EngineProvisioningCatalog::find($engineId);
                if (null === $spec || !$spec->autoProvisionSupported) {
                    continue;
                }

                $toProvision[] = [
                    'engineId' => $engineId,
                    'capability' => $capKey,
                    'displayName' => (string) ($entry['displayName'] ?? $engineId),
                    'reason' => sprintf(
                        'Recommended for %s — compatible, not READY.',
                        $dashboard['summary']['hardwareProfile'] ?? 'profile',
                    ),
                    'recommended' => true,
                ];
                $plannedIds[$engineId] = true;
            }

            $configured = array_values(array_filter($capEngines, static fn (array $e): bool => (bool) ($e['configured'] ?? false)));
            $currentId = $configured[0]['id'] ?? $recommendedId;
            $readyCount = count(array_filter($capEngines, static fn (array $e): bool => 'ready' === ($e['status'] ?? '')));

            $capabilities[] = [
                'capability' => $capKey,
                'label' => $capability->label(),
                'referenceEngineId' => $default?->id,
                'referenceDisplayName' => $default?->displayName,
                'recommendedEngineId' => $recommendedId,
                'currentEngineId' => $currentId,
                'installedEngines' => $installed,
                'missingCompatibleEngines' => $missingCompatible,
                'blockedEngines' => $blocked,
                'futurePremiumEngines' => $futurePremium,
                'readyCount' => $readyCount,
                'engineCount' => count($capEngines),
            ];
        }

        return [
            'title' => 'Runtime Completion Plan',
            'generatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'source' => 'runtime_dashboard',
            'hardwareRedetected' => false,
            'hardwareProfile' => $dashboard['summary']['hardwareProfile'] ?? 'unknown',
            'hardwareProfileLabel' => $dashboard['summary']['hardwareProfileLabel'] ?? 'Unknown',
            'runtimeScore' => $dashboard['overallRuntimeScore']['score'] ?? 0,
            'platformScore' => $dashboard['platformScore']['score'] ?? 0,
            'capabilities' => $capabilities,
            'compatibleEngineCompletionPlan' => $toProvision,
            'completionCount' => count($toProvision),
        ];
    }

    /**
     * @param array<string, mixed> $compat
     */
    private function isHardwareBlocked(array $compat): bool
    {
        if (!($compat['hardwareCompatible'] ?? false)) {
            return true;
        }

        $code = (string) ($compat['blockedReasonCode'] ?? '');

        return in_array($code, self::HARDWARE_BLOCKED_CODES, true)
            && !($compat['canBeFixedByInstall'] ?? false);
    }

    /**
     * @param array<string, mixed> $compat
     */
    private function isFuturePremium(EngineCatalogTier $tier, array $compat): bool
    {
        if (EngineCatalogTier::PremiumNvidia !== $tier) {
            return false;
        }

        return !($compat['hardwareCompatible'] ?? false) || $this->isHardwareBlocked($compat);
    }

    /**
     * @param array<string, mixed> $engine
     * @param array<string, mixed> $compat
     * @param array<string, string> $recommendedPipeline
     * @param array<string, array<string, mixed>> $recByCapability
     */
    private function isMissingCompatible(
        array $engine,
        array $compat,
        EngineCatalogTier $tier,
        ?string $recommendedId,
        array $recommendedPipeline,
        array $recByCapability,
        string $capKey,
    ): bool {
        if (!($compat['hardwareCompatible'] ?? false)) {
            return false;
        }

        if ('ready' === ($engine['status'] ?? '')) {
            return false;
        }

        if (EngineCatalogTier::Legacy === $tier) {
            return false;
        }

        return ($compat['hardwareCompatible'] ?? false) && !$this->isHardwareBlocked($compat);
    }

    /**
     * @param array<string, mixed> $engine
     * @param array<string, mixed> $compat
     * @param array<string, string> $recommendedPipeline
     * @param array<string, array<string, mixed>> $recByCapability
     */
    private function shouldProvision(
        array $engine,
        array $compat,
        EngineCatalogTier $tier,
        ?string $recommendedId,
        array $recommendedPipeline,
        array $recByCapability,
        string $capKey,
    ): bool {
        $engineId = (string) $engine['id'];

        if (!$this->isRecommended($engineId, $capKey, $recommendedId, $recommendedPipeline, $recByCapability)) {
            return false;
        }

        if (EngineCatalogTier::Legacy === $tier || EngineCatalogTier::Experimental === $tier) {
            return false;
        }

        if ($this->isHardwareBlocked($compat)) {
            return false;
        }

        $spec = EngineProvisioningCatalog::find($engineId);

        return null !== $spec && $spec->autoProvisionSupported;
    }

    /**
     * @param array<string, string> $recommendedPipeline
     * @param array<string, array<string, mixed>> $recByCapability
     */
    private function isRecommended(
        string $engineId,
        string $capKey,
        ?string $recommendedId,
        array $recommendedPipeline,
        array $recByCapability,
    ): bool {
        if ($recommendedId === $engineId) {
            return true;
        }

        $rec = $recByCapability[$capKey] ?? null;

        return is_array($rec) && ($rec['recommendedEngineId'] ?? null) === $engineId;
    }

    /**
     * @param array<string, mixed> $engine
     * @param array<string, mixed> $compat
     *
     * @return array<string, mixed>
     */
    private function engineSummary(array $engine, array $compat, bool $blocked = false): array
    {
        return [
            'engineId' => $engine['id'],
            'displayName' => $engine['displayName'] ?? $engine['id'],
            'status' => $engine['status'] ?? 'unknown',
            'tier' => $engine['tier'] ?? null,
            'hardwareCompatible' => $compat['hardwareCompatible'] ?? false,
            'blockedReason' => $compat['humanReason'] ?? null,
            'blockedReasonCode' => $compat['blockedReasonCode'] ?? null,
            'recommendedAlternative' => $compat['recommendedAlternative'] ?? null,
            'provider' => $compat['provider'] ?? null,
            'providerLabel' => $compat['providerLabel'] ?? null,
            'canBeFixedByInstall' => $compat['canBeFixedByInstall'] ?? false,
            'blocked' => $blocked,
        ];
    }

    private function pipelineKeyFor(EngineCatalogCapability $capability): string
    {
        return match ($capability) {
            EngineCatalogCapability::SpeechToText => 'speech',
            EngineCatalogCapability::Translation => 'translation',
            EngineCatalogCapability::TextToSpeech => 'tts',
            EngineCatalogCapability::VoiceClone => 'voiceClone',
            EngineCatalogCapability::LipSync => 'lipSync',
            EngineCatalogCapability::VideoRender => 'render',
            default => $capability->value,
        };
    }
}
