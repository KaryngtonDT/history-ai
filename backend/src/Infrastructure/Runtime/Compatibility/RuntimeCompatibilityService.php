<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Compatibility;

use App\Application\Runtime\EngineCompatibilityEvaluator;
use App\Domain\Engine\Engine;
use App\Domain\Hardware\HardwareCompatibilityResult;
use App\Domain\Hardware\HardwareDetectionReport;
use App\Domain\Hardware\HardwareProvider;
use App\Domain\Hardware\HardwareRepositoryInterface;
use App\Infrastructure\Hardware\HardwareReportStore;
use App\Infrastructure\Runtime\Discovery\EngineDiscovery;

final class RuntimeCompatibilityService
{
    public function __construct(
        private readonly HardwareRepositoryInterface $hardwareRepository,
        private readonly HardwareReportStore $hardwareReportStore,
        private readonly EngineDiscovery $engineDiscovery,
        private readonly EngineCompatibilityEvaluator $compatibilityEvaluator,
    ) {
    }

    /**
     * @return list<HardwareCompatibilityResult>
     */
    public function evaluateAll(HardwareProvider $provider = HardwareProvider::Docker): array
    {
        $report = $this->hardwareRepository->detect();
        $results = [];

        foreach ($this->engineDiscovery->discover() as $engine) {
            $results[] = $this->compatibilityEvaluator->evaluate(
                $engine,
                $report->profile->type,
                $report->capabilities,
                $provider,
            );
        }

        return $results;
    }

    public function evaluateEngine(string $engineId, HardwareProvider $provider = HardwareProvider::Docker): ?HardwareCompatibilityResult
    {
        $engine = $this->engineDiscovery->findEngine($engineId);
        if (null === $engine) {
            return null;
        }

        $report = $this->hardwareRepository->detect();

        return $this->compatibilityEvaluator->evaluate(
            $engine,
            $report->profile->type,
            $report->capabilities,
            $provider,
        );
    }

    /**
     * @return list<HardwareCompatibilityResult>
     */
    public function evaluateFromReport(HardwareDetectionReport $report, HardwareProvider $provider = HardwareProvider::Docker): array
    {
        $results = [];

        foreach ($this->engineDiscovery->discover() as $engine) {
            $results[] = $this->compatibilityEvaluator->evaluate(
                $engine,
                $report->profile->type,
                $report->capabilities,
                $provider,
            );
        }

        return $results;
    }

    /**
     * @return array<string, mixed>
     */
    public function compatibilitySummary(): array
    {
        $report = $this->hardwareReportStore->has()
            ? $this->hardwareReportStore->require()
            : $this->hardwareRepository->detect();

        $results = $this->evaluateFromReport($report);
        $blockedByHardware = [];
        $blockedByInstall = [];
        $readyNow = [];

        foreach ($results as $result) {
            if ('ready' === $result->status) {
                $readyNow[] = $result->engineId;
                continue;
            }

            if (!$result->hardwareCompatible) {
                $blockedByHardware[] = $result->engineId;
                continue;
            }

            if ('missing' === $result->status || 'blocked' === $result->status) {
                $blockedByInstall[] = $result->engineId;
            }
        }

        return [
            'hardwareProfile' => $report->profile->toArray(),
            'engines' => array_map(static fn (HardwareCompatibilityResult $r): array => $r->toArray(), $results),
            'blockedByHardware' => $blockedByHardware,
            'blockedByInstall' => $blockedByInstall,
            'readyNow' => $readyNow,
        ];
    }
}
