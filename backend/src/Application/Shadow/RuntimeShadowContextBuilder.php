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

        return [
            'principle' => 'Runtime decides. Worker executes. UI observes.',
            'selection' => $this->runtimePlatform->selection(),
            'recommendationProfiles' => $profiles,
            'doctorSummary' => [
                'readyCount' => $doctor['readyCount'] ?? 0,
                'totalCount' => $doctor['totalCount'] ?? 0,
                'blockedCount' => count($doctor['blocked'] ?? []),
                'missingCount' => count($doctor['missing'] ?? []),
            ],
            'capabilities' => array_map(
                static fn (array $cap): array => [
                    'capability' => $cap['capability'] ?? '',
                    'label' => $cap['label'] ?? '',
                    'selectionMode' => $cap['selectionMode'] ?? 'auto',
                    'currentEngineId' => $cap['currentEngineId'] ?? null,
                    'recommendedEngineId' => $cap['recommendedEngineId'] ?? null,
                    'engineCount' => count($cap['engines'] ?? []),
                ],
                $management['capabilities'] ?? [],
            ),
            'promptHints' => [
                'Which engine should I use?' => 'Use recommendationProfiles and capability current/recommended engines.',
                'Why did Runtime choose this engine?' => 'Inspect selection.resolved[].reason and intelligence.explanation from POST /api/runtime/resolve.',
                'Can I install an engine?' => 'Check capability engines[].autoProvisionSupported and POST /api/runtime/engines/{id}/install.',
                'Why is an engine blocked?' => 'Read engines[].blockedReason or GET /api/runtime/engines/{id}/blocked-reason.',
                'Which engine performs best?' => 'Compare analytics engines and recommendationProfiles.profiles.fastest.',
            ],
        ];
    }
}
