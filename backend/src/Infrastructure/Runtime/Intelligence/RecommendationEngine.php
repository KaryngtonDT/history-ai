<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Intelligence;

use App\Domain\Engine\EngineProfileName;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Domain\Runtime\RuntimeCapability;
use App\Domain\Runtime\RuntimeConfiguration;
use App\Infrastructure\Runtime\Readiness\ReadinessEngine;

final class RecommendationEngine
{
    public function __construct(
        private readonly ReadinessEngine $readinessEngine,
        private readonly EngineRepositoryInterface $engineRepository,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recommend(RuntimeConfiguration $configuration): array
    {
        $report = $this->readinessEngine->evaluate();
        $recommendations = [];

        foreach (RuntimeCapability::cases() as $capability) {
            $candidates = $this->engineRepository->findByCapability(
                \App\Domain\Engine\EngineCatalogCapability::from($capability->value),
            );
            $ready = array_values(array_filter($candidates, static fn ($e) => $e->installed));
            $selected = $this->selectForProfile($ready, $configuration->profile);
            $requested = $configuration->manualSelections[$capability->value] ?? null;

            $recommendations[] = [
                'capability' => $capability->value,
                'label' => $capability->label(),
                'recommendedEngineId' => $selected?->id,
                'recommendedDisplayName' => $selected?->displayName,
                'requestedEngineId' => $requested,
                'selectionMode' => $configuration->selectionMode->value,
                'profile' => $configuration->profile->value,
                'reason' => null === $selected
                    ? 'No ready engine found for this capability.'
                    : sprintf('Selected for %s profile.', $configuration->profile->label()),
                'confidence' => null === $selected ? 0 : 100,
            ];
        }

        return $recommendations;
    }

    /**
     * @param list<\App\Domain\Engine\Engine> $engines
     */
    private function selectForProfile(array $engines, EngineProfileName $profile): ?\App\Domain\Engine\Engine
    {
        if ([] === $engines) {
            return null;
        }

        return match ($profile) {
            EngineProfileName::Fast => $engines[0],
            default => $engines[0],
        };
    }
}
