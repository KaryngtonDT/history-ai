<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Provisioning;

use App\Application\Runtime\IntelligentProvisioningPlanner;
use App\Application\Runtime\ProvisioningPlan;
use App\Domain\Runtime\RuntimeStatus;
use App\Infrastructure\Hardware\HardwareReportStore;
use App\Infrastructure\Runtime\Benchmark\BenchmarkRunner;
use App\Infrastructure\Runtime\Intelligence\AutoSelectionEngine;
use App\Infrastructure\Runtime\Readiness\ReadinessEngine;
use App\Domain\Runtime\RuntimeRepositoryInterface;

final class IntelligentEngineProvisioner
{
    public function __construct(
        private readonly HardwareReportStore $hardwareReportStore,
        private readonly IntelligentProvisioningPlanner $planner,
        private readonly EngineProvisioner $engineProvisioner,
        private readonly ReadinessEngine $readinessEngine,
        private readonly BenchmarkRunner $benchmarkRunner,
        private readonly AutoSelectionEngine $autoSelectionEngine,
        private readonly RuntimeRepositoryInterface $runtimeRepository,
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function provisionCompatibleAll(): array
    {
        $report = $this->hardwareReportStore->require();
        $plan = $this->planner->plan($report);
        $results = [];
        $provisioned = [];

        foreach ($plan->skipped as $skipped) {
            $results[] = $skipped->toArray();
        }

        foreach ($plan->toProvision as $entry) {
            $result = $this->engineProvisioner->provision($entry->engineId);
            $result['plan'] = $entry->toArray();
            $result['installAttempted'] = true;
            $results[] = $result;
            $provisioned[] = $entry->engineId;
        }

        $readiness = $this->readinessEngine->evaluate()->toArray();
        $validation = $this->validatePipeline();
        $benchmark = $this->benchmarkRunner->runFull();

        $summary = [
            'ok' => $this->allProvisionedReady($results),
            'hardwareProfile' => $plan->hardwareProfile,
            'sourceReportAt' => $plan->sourceReportAt,
            'hardwareRedetected' => false,
            'plan' => $plan->toArray(),
            'provisionedEngineIds' => $provisioned,
            'readyCount' => $readiness['readyCount'] ?? 0,
            'totalCount' => $readiness['totalCount'] ?? 0,
            'results' => $results,
            'readiness' => $readiness,
            'validation' => $validation,
            'benchmark' => $benchmark,
            'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $this->writeReport($summary);

        return $summary;
    }

    public function buildPlan(): ProvisioningPlan
    {
        return $this->planner->plan($this->hardwareReportStore->require());
    }

    /**
     * @param list<array<string, mixed>> $results
     */
    private function allProvisionedReady(array $results): bool
    {
        foreach ($results as $result) {
            if (!($result['installAttempted'] ?? false)) {
                continue;
            }

            if (($result['status'] ?? '') !== RuntimeStatus::Ready->value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $summary
     */
    private function writeReport(array $summary): void
    {
        $path = $this->projectDir.'/docs/reports/Engine-Provisioning-Final.md';
        if (!is_dir(dirname($path))) {
            return;
        }

        $lines = [
            '# Engine Provisioning — Final Report',
            '',
            'Generated: '.($summary['at'] ?? 'unknown'),
            '',
            'Hardware profile: **'.($summary['hardwareProfile'] ?? 'unknown').'**',
            'Source report: '.($summary['sourceReportAt'] ?? 'unknown'),
            'Hardware re-detected during provisioning: **no**',
            '',
            '| Engine | Capability | Installed | Provider | Status | Reason | Alternative | Next Action |',
            '| --- | --- | --- | --- | --- | --- | --- | --- |',
        ];

        foreach ($summary['results'] as $result) {
            if (!is_array($result)) {
                continue;
            }

            $engineId = (string) ($result['engineId'] ?? '');
            $attempted = (bool) ($result['installAttempted'] ?? false);
            $status = (string) ($result['status'] ?? 'blocked');
            $reason = (string) ($result['humanReason'] ?? $result['blockedReason'] ?? '');
            $alternative = (string) ($result['recommendedAlternative'] ?? '—');
            $capability = (string) ($result['capability'] ?? ($result['plan']['capability'] ?? ''));
            $next = $attempted
                ? (($result['ok'] ?? false) ? 'Use in pipeline' : 'Review install logs')
                : 'Do not install on this hardware';

            $lines[] = sprintf(
                '| %s | %s | %s | host | %s | %s | %s | %s |',
                $engineId,
                $capability,
                $attempted ? 'yes' : 'no',
                $status,
                str_replace('|', '/', $reason),
                $alternative,
                $next,
            );
        }

        file_put_contents($path, implode("\n", $lines)."\n");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePipeline(): array
    {
        $pipelineId = bin2hex(random_bytes(16));
        $pipelineId = sprintf(
            '%s-%s-%s-%s-%s',
            substr($pipelineId, 0, 8),
            substr($pipelineId, 8, 4),
            substr($pipelineId, 12, 4),
            substr($pipelineId, 16, 4),
            substr($pipelineId, 20, 12),
        );
        $readiness = $this->readinessEngine->evaluate();
        $config = $this->runtimeRepository->getConfiguration();
        $selections = $this->autoSelectionEngine->resolveSelections($config);
        $steps = [];
        $passed = true;

        foreach ($readiness->engines as $engine) {
            if (!$engine->configured) {
                continue;
            }

            $stepOk = $engine->isReady();
            $passed = $passed && $stepOk;
            $steps[] = [
                'capability' => $engine->capability->value,
                'requestedEngineId' => $selections[$engine->capability->value] ?? $engine->id,
                'executedEngineId' => $engine->id,
                'status' => $engine->status->value,
                'mode' => $engine->mode->value,
                'provider' => 'host',
                'executableFound' => $engine->executableFound,
                'modelFound' => $engine->modelFound,
                'fallbackUsed' => false,
                'reason' => $stepOk ? null : ($engine->errorReason ?? 'Engine not ready'),
                'confidence' => $stepOk ? 100 : 0,
            ];
        }

        $report = [
            'pipelineId' => $pipelineId,
            'status' => $passed ? 'pass' : 'fail',
            'steps' => $steps,
            'validatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $this->runtimeRepository->saveValidationReport($pipelineId, $report);

        return $report;
    }
}
