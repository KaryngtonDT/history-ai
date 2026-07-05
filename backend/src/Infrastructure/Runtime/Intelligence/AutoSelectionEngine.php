<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Intelligence;

use App\Domain\Engine\SelectionMode;
use App\Domain\Runtime\RuntimeConfiguration;

final class AutoSelectionEngine
{
    public function __construct(private readonly RecommendationEngine $recommendationEngine)
    {
    }

    /**
     * @return array<string, string>
     */
    public function resolveSelections(RuntimeConfiguration $configuration): array
    {
        $selections = [];

        foreach ($this->recommendationEngine->recommend($configuration) as $item) {
            if (!is_string($item['capability'] ?? null)) {
                continue;
            }

            if (SelectionMode::Manual === $configuration->selectionMode) {
                $selections[$item['capability']] = $configuration->manualSelections[$item['capability']]
                    ?? (string) ($item['recommendedEngineId'] ?? '');
                continue;
            }

            $selections[$item['capability']] = (string) ($item['recommendedEngineId'] ?? '');
        }

        return array_filter($selections, static fn (string $id): bool => '' !== $id);
    }
}
