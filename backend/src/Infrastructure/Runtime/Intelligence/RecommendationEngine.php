<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Intelligence;

use App\Domain\Engine\EngineProfileName;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Domain\Runtime\RuntimeCapability;
use App\Domain\Runtime\RuntimeCapabilityClassification;
use App\Domain\Runtime\RuntimeConfiguration;
use App\Infrastructure\Runtime\Catalog\RuntimeCapabilityClassificationRegistry;

final class RecommendationEngine
{
    public function __construct(
        private readonly EngineRepositoryInterface $engineRepository,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recommend(RuntimeConfiguration $configuration): array
    {
        $recommendations = [];

        foreach (RuntimeCapability::cases() as $capability) {
            $meta = RuntimeCapabilityClassificationRegistry::for($capability);
            $candidates = $this->engineRepository->findByCapability(
                \App\Domain\Engine\EngineCatalogCapability::from($capability->value),
            );
            $ready = array_values(array_filter($candidates, static fn ($e) => $e->isReady()));
            $selected = $this->selectForProfile($ready, $configuration->profile);
            $requested = $configuration->manualSelections[$capability->value] ?? null;

            $recommendations[] = [
                'capability' => $capability->value,
                'label' => $capability->label(),
                'classification' => $meta->classification->value,
                'classificationLabel' => $meta->classification->label(),
                'recommendedEngineId' => $selected?->id,
                'recommendedDisplayName' => $selected?->displayName,
                'requestedEngineId' => $requested,
                'selectionMode' => $configuration->selectionMode->value,
                'profile' => $configuration->profile->value,
                'reason' => $this->reasonFor($meta->classification, $selected),
                'suggestionType' => $this->suggestionTypeFor($meta->classification, $selected, $ready),
                'confidence' => null === $selected ? 0 : 100,
            ];
        }

        return $recommendations;
    }

    /**
     * @param list<\App\Domain\Engine\Engine> $ready
     */
    private function suggestionTypeFor(
        RuntimeCapabilityClassification $classification,
        ?\App\Domain\Engine\Engine $selected,
        array $ready,
    ): string {
        if (null !== $selected) {
            return 'operational';
        }

        return match ($classification) {
            RuntimeCapabilityClassification::Optional => 'optional',
            RuntimeCapabilityClassification::Premium => 'future_upgrade',
            RuntimeCapabilityClassification::Experimental => 'disabled',
            default => [] === $ready ? 'missing' : 'blocked',
        };
    }

    private function reasonFor(
        RuntimeCapabilityClassification $classification,
        ?\App\Domain\Engine\Engine $selected,
    ): string {
        if (null === $selected) {
            return match ($classification) {
                RuntimeCapabilityClassification::Optional => 'Optional capability — install when needed.',
                RuntimeCapabilityClassification::Premium => 'Premium capability may require additional hardware.',
                RuntimeCapabilityClassification::Experimental => 'Experimental capability is disabled by default.',
                default => 'No ready engine found for this capability.',
            };
        }

        return sprintf('Selected for capability classification (%s).', $classification->label());
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
