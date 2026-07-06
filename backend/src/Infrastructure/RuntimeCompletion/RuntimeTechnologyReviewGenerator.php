<?php

declare(strict_types=1);

namespace App\Infrastructure\RuntimeCompletion;

use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;
use App\Infrastructure\Runtime\Catalog\EngineRequirementMatrix;

final class RuntimeTechnologyReviewGenerator
{
    public function __construct(
        private readonly string $projectDir,
        private readonly string $backendStorageDir,
    ) {
    }

    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     * @param array<string, mixed> $plan
     * @param array<string, mixed> $execution
     */
    public function generate(array $before, array $after, array $plan, array $execution): void
    {
        $content = $this->buildMarkdown($before, $after, $plan, $execution);
        $this->persist('Runtime-Technology-Review-After70_6.md', $content);
    }

    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     * @param array<string, mixed> $plan
     * @param array<string, mixed> $execution
     */
    private function buildMarkdown(array $before, array $after, array $plan, array $execution): string
    {
        $beforeScore = (float) ($before['overallRuntimeScore']['score'] ?? 0);
        $afterScore = (float) ($after['overallRuntimeScore']['score'] ?? 0);
        $beforePlatform = (float) ($before['platformScore']['score'] ?? 0);
        $afterPlatform = (float) ($after['platformScore']['score'] ?? 0);
        $profile = (string) ($after['summary']['hardwareProfileLabel'] ?? $plan['hardwareProfileLabel'] ?? 'Unknown');
        $profileType = (string) ($after['summary']['hardwareProfile'] ?? $plan['hardwareProfile'] ?? 'unknown');

        $lines = [
            '# Runtime Technology Review — After Sprint 70.6 Dashboard',
            '',
            '**Generated:** '.($execution['at'] ?? (new \DateTimeImmutable())->format(DATE_ATOM)),
            '**Source:** Runtime Dashboard (single source of truth) — no hardware re-detection during completion.',
            '',
            '## Executive Summary',
            '',
            sprintf(
                'Runtime Score moved from **%.1f** to **%.1f** (Δ %+.1f). Platform Score from **%.1f** to **%.1f**.',
                $beforeScore,
                $afterScore,
                $afterScore - $beforeScore,
                $beforePlatform,
                $afterPlatform,
            ),
            '',
            sprintf('Hardware profile: **%s** (`%s`).', $profile, $profileType),
            '',
            sprintf(
                'Completion run provisioned **%d** recommended compatible engine(s): %s.',
                count($execution['provisionedEngineIds'] ?? []),
                [] === ($execution['provisionedEngineIds'] ?? [])
                    ? 'none required'
                    : implode(', ', $execution['provisionedEngineIds']),
            ),
            '',
            '## Score Evolution',
            '',
            '| Metric | Before | After |',
            '| --- | ---: | ---: |',
            sprintf('| Runtime Score | %.1f | %.1f |', $beforeScore, $afterScore),
            sprintf('| Platform Score | %.1f | %.1f |', $beforePlatform, $afterPlatform),
            '',
            '## Capability Scores',
            '',
            '| Capability | Score | Status |',
            '| --- | ---: | --- |',
        ];

        foreach ($after['capabilityScores'] ?? [] as $cap) {
            if (!is_array($cap)) {
                continue;
            }
            $status = '';
            foreach ($after['capabilityStatuses'] ?? [] as $row) {
                if (is_array($row) && ($row['capability'] ?? '') === ($cap['capability'] ?? '')) {
                    $status = (string) ($row['statusLabel'] ?? $row['status'] ?? '');
                    break;
                }
            }
            $lines[] = sprintf(
                '| %s | %s%% | %s |',
                $cap['label'] ?? $cap['capability'] ?? '',
                $cap['score'] ?? 0,
                $status,
            );
        }

        $pipeline = is_array($after['hardware']['recommendedPipeline'] ?? null)
            ? $after['hardware']['recommendedPipeline']
            : [];

        $lines[] = '';
        $lines[] = '## Current Pipeline (recommended for this hardware)';
        $lines[] = '';
        $lines[] = '| Stage | Engine ID |';
        $lines[] = '| --- | --- |';
        foreach ($pipeline as $stage => $engineId) {
            $lines[] = sprintf('| %s | `%s` |', $stage, $engineId);
        }

        $lines[] = '';
        $lines[] = '## Future NVIDIA Pipeline';
        $lines[] = '';
        $lines[] = '| Stage | Engine ID |';
        $lines[] = '| --- | --- |';
        $lines[] = '| speech | `parakeet` or `canary` |';
        $lines[] = '| lipSync | `latentsync` |';
        $lines[] = '| render | `ffmpeg_nvenc` |';
        $lines[] = '';
        $lines[] = '## Enterprise Pipeline';
        $lines[] = '';
        $lines[] = 'High-end NVIDIA (RTX 4090+, 24 GB VRAM): LatentSync, EchoMimic, Parakeet, FFmpeg NVENC.';
        $lines[] = '';
        $lines[] = '## Per-Capability Detail';
        $lines[] = '';

        foreach ($plan['capabilities'] ?? [] as $cap) {
            if (!is_array($cap)) {
                continue;
            }

            $lines[] = '### '.($cap['label'] ?? $cap['capability'] ?? 'Capability');
            $lines[] = '';
            $lines[] = sprintf('- **Reference:** %s', $cap['referenceDisplayName'] ?? $cap['referenceEngineId'] ?? '—');
            $lines[] = sprintf('- **Recommended:** `%s`', $cap['recommendedEngineId'] ?? '—');
            $lines[] = sprintf('- **Current:** `%s`', $cap['currentEngineId'] ?? '—');
            $lines[] = '';

            $this->appendEngineList($lines, 'Installed', $cap['installedEngines'] ?? []);
            $this->appendEngineList($lines, 'Missing compatible', $cap['missingCompatibleEngines'] ?? []);
            $this->appendEngineList($lines, 'Blocked', $cap['blockedEngines'] ?? []);
            $this->appendEngineList($lines, 'Future premium', $cap['futurePremiumEngines'] ?? []);
            $lines[] = '';
        }

        $lines[] = '## Future Hardware — Premium Engines (retained in registry)';
        $lines[] = '';

        foreach ($after['premiumFeatures'] ?? [] as $feature) {
            if (!is_array($feature)) {
                continue;
            }

            $engineId = (string) ($feature['engineId'] ?? '');
            $req = EngineRequirementMatrix::findByEngineId($engineId);
            $definition = EngineCatalogDefinitions::findById($engineId);
            $gain = $this->estimateScoreGain($engineId);

            $lines[] = '### '.($feature['displayName'] ?? $engineId);
            $lines[] = '';
            $lines[] = sprintf('- **Status:** BLOCKED');
            $lines[] = sprintf('- **Reason:** %s', $feature['humanReason'] ?? 'Hardware requirement not met');
            $lines[] = sprintf('- **Needs:** %s', implode(', ', $feature['needs'] ?? []));
            $lines[] = sprintf('- **Alternative:** %s', $feature['recommendedAlternative'] ?? '—');
            if (null !== $req?->minimumVramGb) {
                $lines[] = sprintf('- **Recommended upgrade:** NVIDIA GPU with %.0f GB VRAM', $req->minimumVramGb);
            }
            $lines[] = sprintf('- **Estimated Runtime gain:** +%.0f', $gain);
            $lines[] = sprintf('- **Remote provider:** %s', $definition ? 'possible via REMOTE GPU' : 'see ENGINE_COMPATIBILITY.md');
            $lines[] = '';
        }

        $lines[] = '## Validation';
        $lines[] = '';
        $validation = is_array($execution['validation'] ?? null) ? $execution['validation'] : [];
        $lines[] = sprintf('- Pipeline validation: **%s**', strtoupper((string) ($validation['status'] ?? 'unknown')));
        $lines[] = sprintf('- Provisioned engines: %s', implode(', ', $execution['provisionedEngineIds'] ?? []) ?: 'none');

        return implode("\n", $lines);
    }

    private function persist(string $filename, string $content): void
    {
        foreach ([
            $this->projectDir.'/docs/reports',
            $this->backendStorageDir.'/runtime/reports',
        ] as $dir) {
            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                continue;
            }
            file_put_contents($dir.'/'.$filename, $content);
        }
    }

    /**
     * @param list<string> $lines
     * @param list<array<string, mixed>> $engines
     */
    private function appendEngineList(array &$lines, string $title, array $engines): void
    {
        if ([] === $engines) {
            $lines[] = sprintf('- **%s:** none', $title);

            return;
        }

        $lines[] = sprintf('- **%s:**', $title);
        foreach ($engines as $engine) {
            if (!is_array($engine)) {
                continue;
            }
            $lines[] = sprintf(
                '  - `%s` — %s%s',
                $engine['engineId'] ?? '',
                $engine['status'] ?? '',
                isset($engine['blockedReason']) ? ' — '.$engine['blockedReason'] : '',
            );
        }
    }

    private function estimateScoreGain(string $engineId): float
    {
        return match (true) {
            str_contains($engineId, 'latent') => 8.0,
            str_contains($engineId, 'echo') => 6.0,
            str_contains($engineId, 'parakeet'), str_contains($engineId, 'canary') => 4.0,
            str_contains($engineId, 'nvenc') => 3.0,
            default => 2.0,
        };
    }
}
