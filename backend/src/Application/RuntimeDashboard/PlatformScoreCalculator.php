<?php

declare(strict_types=1);

namespace App\Application\RuntimeDashboard;

final class PlatformScoreCalculator
{
    /**
     * @param array<string, float|null> $components keyed by component id (null = not applicable)
     */
    public function calculate(array $components): array
    {
        $items = [];
        $total = 0.0;
        $weightSum = 0.0;

        foreach ($components as $key => $score) {
            if (null === $score) {
                $items[] = [
                    'key' => $key,
                    'label' => $this->labelFor($key),
                    'score' => null,
                    'status' => 'not_applicable',
                ];
                continue;
            }

            $items[] = [
                'key' => $key,
                'label' => $this->labelFor($key),
                'score' => round($score, 1),
                'status' => $score >= 90.0 ? 'healthy' : ($score >= 70.0 ? 'degraded' : 'unhealthy'),
            ];
            $total += $score;
            $weightSum += 1.0;
        }

        $overall = $weightSum > 0 ? round($total / $weightSum, 1) : 0.0;

        return [
            'score' => $overall,
            'grade' => $overall >= 90.0 ? 'Excellent' : ($overall >= 75.0 ? 'Good' : 'Fair'),
            'components' => $items,
        ];
    }

    private function labelFor(string $key): string
    {
        return match ($key) {
            'runtime' => 'Runtime',
            'shadow' => 'Shadow',
            'storage' => 'Storage',
            'worker' => 'Worker',
            'api' => 'API',
            'docker' => 'Docker',
            'documentation' => 'Documentation',
            'postgres' => 'PostgreSQL',
            default => ucfirst(str_replace('_', ' ', $key)),
        };
    }
}
