<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Application\Runtime\RuntimePlatformInterface;

final class RuntimeShadowContextBuilder
{
    public function __construct(private readonly RuntimePlatformInterface $runtimePlatform)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $management = $this->runtimePlatform->engineManagement();
        $profiles = $this->runtimePlatform->recommendationProfiles();
        $doctor = $this->runtimePlatform->doctorReport();
        $platformHealth = $this->runtimePlatform->platformHealth();

        return [
            'principle' => 'Runtime decides. Worker executes. UI observes.',
            'selection' => $this->runtimePlatform->selection(),
            'recommendationProfiles' => $profiles,
            'platformHealth' => $platformHealth,
            'doctorSummary' => [
                'coreStatus' => $doctor['coreStatus'] ?? 'unknown',
                'readyCount' => $doctor['readyCount'] ?? 0,
                'totalCount' => $doctor['totalCount'] ?? 0,
                'blockedCount' => count($doctor['blocked'] ?? []),
                'missingCount' => count($doctor['missing'] ?? []),
            ],
            'capabilities' => array_map(
                static fn (array $cap): array => [
                    'capability' => $cap['capability'] ?? '',
                    'label' => $cap['label'] ?? '',
                    'classification' => $cap['classification'] ?? null,
                    'classificationLabel' => $cap['classificationLabel'] ?? null,
                    'required' => $cap['required'] ?? false,
                    'selectionMode' => $cap['selectionMode'] ?? 'auto',
                    'currentEngineId' => $cap['currentEngineId'] ?? null,
                    'recommendedEngineId' => $cap['recommendedEngineId'] ?? null,
                    'engineCount' => count($cap['engines'] ?? []),
                ],
                $management['capabilities'] ?? [],
            ),
            'promptHints' => [
                'Why is Runtime healthy if LatentSync is blocked?' => 'LatentSync is a Premium capability. Core health only considers Core capabilities.',
                'Why is OCR not installed?' => 'OCR is Optional — not installed by default. Install from Provision Center when needed.',
                'Which capabilities are optional?' => 'Check classification=optional in platformHealth.capabilities (OCR, Vision, Embeddings, Reranking).',
                'What will improve if I buy an NVIDIA GPU?' => 'Review platformHealth.premiumAvailability and futureHardware hints on premium capabilities.',
                'Which premium capabilities become available?' => 'Compare premiumAvailability.capabilities where availability is unsupported_hardware.',
                'Which engine should I use?' => 'Use recommendationProfiles and capability current/recommended engines.',
                'Why did Runtime choose this engine?' => 'Inspect selection.resolved[].reason and intelligence.explanation from POST /api/runtime/resolve.',
            ],
        ];
    }
}
