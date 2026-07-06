<?php

declare(strict_types=1);

namespace App\Infrastructure\RuntimeCompletion;

use App\Application\Runtime\RuntimePlatformInterface;
use App\Application\RuntimeCompletion\RuntimeCompletionInterface;
use App\Application\RuntimeCompletion\RuntimeCompletionPlanner;
use App\Application\RuntimeDashboard\RuntimeDashboardInterface;
use App\Infrastructure\Runtime\Benchmark\BenchmarkRunner;
use App\Infrastructure\Runtime\Provisioning\EngineProvisioner;

final class RuntimeCompletionService implements RuntimeCompletionInterface
{
    public function __construct(
        private readonly RuntimeCompletionPlanner $planner,
        private readonly RuntimeDashboardInterface $dashboard,
        private readonly RuntimePlatformInterface $platform,
        private readonly EngineProvisioner $engineProvisioner,
        private readonly BenchmarkRunner $benchmarkRunner,
        private readonly RuntimeTechnologyReviewGenerator $technologyReviewGenerator,
        private readonly string $projectDir,
    ) {
    }

    public function plan(): array
    {
        return $this->planner->plan();
    }

    public function execute(): array
    {
        $before = $this->dashboard->dashboard();
        $plan = $this->planner->plan();
        $completionPlan = is_array($plan['compatibleEngineCompletionPlan'] ?? null)
            ? $plan['compatibleEngineCompletionPlan']
            : [];

        $results = [];
        $provisioned = [];

        foreach ($completionPlan as $entry) {
            if (!is_array($entry) || !isset($entry['engineId'])) {
                continue;
            }

            $engineId = (string) $entry['engineId'];
            $provision = $this->engineProvisioner->provision($engineId);
            $test = $this->platform->testEngine($engineId);
            $benchmark = $this->benchmarkRunner->runEngine($engineId);

            $result = [
                'engineId' => $engineId,
                'capability' => $entry['capability'] ?? null,
                'provision' => $provision,
                'test' => $test,
                'benchmark' => $benchmark,
                'installAttempted' => true,
            ];
            $results[] = $result;

            if (($provision['ok'] ?? false) === true || ($test['ok'] ?? false) === true) {
                $provisioned[] = $engineId;
            }
        }

        $after = $this->dashboard->dashboard();
        $validation = $this->platform->validatePipeline();

        $summary = [
            'ok' => [] === $completionPlan || $this->allSucceeded($results),
            'hardwareRedetected' => false,
            'before' => [
                'runtimeScore' => $before['overallRuntimeScore']['score'] ?? 0,
                'platformScore' => $before['platformScore']['score'] ?? 0,
            ],
            'after' => [
                'runtimeScore' => $after['overallRuntimeScore']['score'] ?? 0,
                'platformScore' => $after['platformScore']['score'] ?? 0,
            ],
            'plan' => $plan,
            'provisionedEngineIds' => $provisioned,
            'attemptedCount' => count($completionPlan),
            'results' => $results,
            'validation' => $validation,
            'dashboard' => $after,
            'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $this->technologyReviewGenerator->generate($before, $after, $plan, $summary);
        $this->writeProvisioningFinal($summary);

        return $summary;
    }

    /**
     * @param list<array<string, mixed>> $results
     */
    private function allSucceeded(array $results): bool
    {
        foreach ($results as $result) {
            $testOk = ($result['test']['ok'] ?? false) === true;
            $provisionOk = ($result['provision']['ok'] ?? false) === true;

            if (!$testOk && !$provisionOk) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $summary
     */
    private function writeProvisioningFinal(array $summary): void
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
            '**Sprint 70.7** — completion from Runtime Dashboard (no hardware re-detection).',
            '',
            sprintf(
                'Runtime Score: **%.1f → %.1f** | Platform Score: **%.1f → %.1f**',
                (float) ($summary['before']['runtimeScore'] ?? 0),
                (float) ($summary['after']['runtimeScore'] ?? 0),
                (float) ($summary['before']['platformScore'] ?? 0),
                (float) ($summary['after']['platformScore'] ?? 0),
            ),
            '',
            '| Engine | Capability | Provisioned | Test | Benchmark | Status |',
            '| --- | --- | --- | --- | --- | --- |',
        ];

        foreach ($summary['results'] as $result) {
            if (!is_array($result)) {
                continue;
            }

            $lines[] = sprintf(
                '| %s | %s | %s | %s | %s | %s |',
                $result['engineId'] ?? '',
                $result['capability'] ?? '',
                ($result['provision']['ok'] ?? false) ? 'yes' : 'no',
                ($result['test']['ok'] ?? false) ? 'PASS' : 'FAIL',
                ($result['benchmark']['ok'] ?? false) ? 'PASS' : 'FAIL',
                $result['provision']['status'] ?? 'unknown',
            );
        }

        file_put_contents($path, implode("\n", $lines)."\n");
    }
}
