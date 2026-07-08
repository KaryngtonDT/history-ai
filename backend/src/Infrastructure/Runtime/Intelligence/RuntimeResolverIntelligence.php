<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Intelligence;

use App\Application\Hardware\HardwareReportBuilder;
use App\Application\Runtime\PipelineStageCapabilityMapper;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Domain\EngineAnalytics\EngineExecutionHistoryRepositoryInterface;
use App\Domain\EngineAnalytics\EngineExecutionStatus;
use App\Domain\Hardware\HardwareRepositoryInterface;
use App\Domain\Runtime\ResolvedEngineIntelligence;
use App\Domain\Runtime\RuntimeResolveContext;
use App\Domain\Runtime\RuntimeResolveReason;

final class RuntimeResolverIntelligence
{
    private const int SAMPLE_LIMIT = 50;

    public function __construct(
        private readonly EngineExecutionHistoryRepositoryInterface $historyRepository,
        private readonly HardwareRepositoryInterface $hardwareRepository,
        private readonly EngineRepositoryInterface $engineRepository,
    ) {
    }

    public function enrich(
        EngineCatalogCapability $capability,
        string $engineId,
        RuntimeResolveContext $context,
        RuntimeResolveReason $reason,
    ): ResolvedEngineIntelligence {
        $analytics = $this->findAnalytics($capability, $engineId);
        $alternative = $this->findAlternative($capability, $engineId);
        $hardware = $this->hardwareRepository->detect()->profile->type->value;

        return new ResolvedEngineIntelligence(
            alternativeEngineId: $alternative['id'] ?? null,
            alternativeDisplayName: $alternative['displayName'] ?? null,
            estimatedDurationSeconds: $analytics['medianDurationSeconds'] ?? $this->estimateFromContext($context),
            expectedAccuracy: $analytics['successRate'] ?? null,
            expectedMemoryMb: null,
            expectedCpuPercent: null,
            explanation: $this->buildExplanation(
                $capability,
                $engineId,
                $reason,
                $context,
                $analytics,
                $hardware,
            ),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findAnalytics(EngineCatalogCapability $capability, string $engineId): ?array
    {
        $stage = PipelineStageCapabilityMapper::toPipelineStage($capability);
        if (null === $stage) {
            return null;
        }

        $items = $this->historyRepository->findRecent(
            stage: $stage,
            engineId: $engineId,
            limit: self::SAMPLE_LIMIT,
        );

        if ([] === $items) {
            return null;
        }

        $completed = array_values(array_filter(
            $items,
            static fn ($item): bool => EngineExecutionStatus::Completed === $item->status(),
        ));
        $durations = array_map(
            static fn ($item): int => $item->actualDurationSeconds(),
            $completed,
        );
        sort($durations);
        $median = [] === $durations
            ? null
            : $durations[(int) floor((count($durations) - 1) / 2)];

        return [
            'medianDurationSeconds' => $median,
            'successRate' => round(count($completed) / count($items) * 100, 1),
            'executionCount' => count($items),
        ];
    }

    /**
     * @return array{id?: string, displayName?: string}
     */
    private function findAlternative(EngineCatalogCapability $capability, string $engineId): array
    {
        $candidates = $this->engineRepository->findByCapability($capability);
        foreach ($candidates as $candidate) {
            if ($candidate->id !== $engineId && $candidate->isReady()) {
                return ['id' => $candidate->id, 'displayName' => $candidate->displayName];
            }
        }

        return [];
    }

    /**
     * @param array<string, mixed>|null $analytics
     */
    private function buildExplanation(
        EngineCatalogCapability $capability,
        string $engineId,
        RuntimeResolveReason $reason,
        RuntimeResolveContext $context,
        ?array $analytics,
        string $hardware,
    ): string {
        $engine = $this->engineRepository->findById($engineId);
        $name = $engine?->displayName ?? $engineId;

        $parts = [
            sprintf('%s selected for %s.', $name, $capability->label()),
            sprintf('Reason: %s.', $reason->label()),
            sprintf('Hardware profile: %s.', $hardware),
        ];

        if (null !== $context->durationSeconds) {
            $parts[] = sprintf('Media duration: %d minutes.', (int) ceil($context->durationSeconds / 60));
        }

        if (null !== $context->language && '' !== $context->language) {
            $parts[] = sprintf('Language: %s.', $context->language);
        }

        if (null !== $analytics) {
            $parts[] = sprintf(
                'Historical median duration: %ds (success rate %.0f%%).',
                (int) ($analytics['medianDurationSeconds'] ?? 0),
                (float) ($analytics['successRate'] ?? 0),
            );
        }

        return implode(' ', $parts);
    }

    private function estimateFromContext(RuntimeResolveContext $context): ?int
    {
        if (null === $context->durationSeconds) {
            return null;
        }

        return max(30, (int) round($context->durationSeconds * 0.05));
    }
}
