<?php

declare(strict_types=1);

namespace App\Application\RuntimeDashboard;

use App\Domain\RuntimeDashboard\RuntimeScore;
use App\Domain\RuntimeDashboard\RuntimeScoreBreakdown;

final class RuntimeScoreCalculator
{
    /**
     * @param array{
     *   runtimeHealth: float,
     *   compatibleInstalled: float,
     *   engineTests: float,
     *   benchmarks: float,
     *   documentation: float,
     *   hardwareCompatibility: float,
     *   provisioning: float
     * } $inputs
     */
    public function calculate(array $inputs): RuntimeScore
    {
        $weights = [
            'runtime_health' => ['label' => 'Runtime Health', 'weight' => 0.20],
            'compatible_installed' => ['label' => 'Compatible Engines Installed', 'weight' => 0.20],
            'engine_tests' => ['label' => 'Engine Tests', 'weight' => 0.15],
            'benchmarks' => ['label' => 'Benchmarks', 'weight' => 0.15],
            'documentation' => ['label' => 'Documentation', 'weight' => 0.05],
            'hardware_compatibility' => ['label' => 'Hardware Compatibility', 'weight' => 0.10],
            'provisioning' => ['label' => 'Provisioning', 'weight' => 0.15],
        ];

        $keyMap = [
            'runtime_health' => 'runtimeHealth',
            'compatible_installed' => 'compatibleInstalled',
            'engine_tests' => 'engineTests',
            'benchmarks' => 'benchmarks',
            'documentation' => 'documentation',
            'hardware_compatibility' => 'hardwareCompatibility',
            'provisioning' => 'provisioning',
        ];

        $breakdown = [];
        $total = 0.0;

        foreach ($weights as $key => $meta) {
            $score = max(0.0, min(100.0, $inputs[$keyMap[$key]] ?? 0.0));
            $contribution = round($score * $meta['weight'], 2);
            $total += $contribution;
            $breakdown[] = new RuntimeScoreBreakdown(
                key: $key,
                label: $meta['label'],
                score: $score,
                weight: $meta['weight'],
                weightedContribution: $contribution,
                explanation: $this->explanationFor($key, $score),
                improvement: $this->improvementFor($key, $score),
            );
        }

        $final = round($total, 1);

        return new RuntimeScore(
            score: $final,
            grade: $this->gradeFor($final),
            summary: $this->summaryFor($final),
            breakdown: $breakdown,
        );
    }

    private function gradeFor(float $score): string
    {
        return match (true) {
            $score >= 90.0 => 'Excellent',
            $score >= 75.0 => 'Good',
            $score >= 60.0 => 'Fair',
            default => 'Needs attention',
        };
    }

    private function summaryFor(float $score): string
    {
        if ($score >= 90.0) {
            return 'Everything compatible with your hardware is operational. Remaining blocked engines require different hardware or manual install.';
        }

        if ($score >= 75.0) {
            return 'Runtime is healthy with minor gaps. Review provisioning and benchmarks for compatible engines.';
        }

        return 'Several compatible engines need attention. Use recommendations and provisioning actions below.';
    }

    private function explanationFor(string $key, float $score): string
    {
        return match ($key) {
            'runtime_health' => sprintf('Engine readiness across the catalog (%.0f%%).', $score),
            'compatible_installed' => sprintf('Hardware-compatible engines that reached READY (%.0f%%).', $score),
            'engine_tests' => sprintf('Recent engine probe success rate (%.0f%%).', $score),
            'benchmarks' => sprintf('Benchmark pass rate on tested engines (%.0f%%).', $score),
            'documentation' => sprintf('Runtime documentation coverage (%.0f%%).', $score),
            'hardware_compatibility' => sprintf('Share of engines compatible with detected hardware (%.0f%%).', $score),
            'provisioning' => sprintf('Auto-provision progress for compatible engines (%.0f%%).', $score),
            default => sprintf('Score %.0f%%.', $score),
        };
    }

    private function improvementFor(string $key, float $score): ?string
    {
        if ($score >= 95.0) {
            return null;
        }

        return match ($key) {
            'compatible_installed', 'provisioning' => 'Run Provision All Compatible Engines.',
            'benchmarks', 'engine_tests' => 'Run Benchmark All from the engine console.',
            'documentation' => 'Review docs/operations and docs/architecture runtime guides.',
            'hardware_compatibility' => 'Premium engines may require NVIDIA CUDA hardware.',
            default => null,
        };
    }
}
