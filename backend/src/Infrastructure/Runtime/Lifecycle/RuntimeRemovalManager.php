<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Lifecycle;

use App\Domain\Runtime\RuntimeConfiguration;
use App\Domain\Runtime\RuntimeRepositoryInterface;

final class RuntimeRemovalManager
{
    public function __construct(private readonly RuntimeRepositoryInterface $runtimeRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function remove(string $engineId): array
    {
        $config = $this->runtimeRepository->getConfiguration();
        $disabled = $config->disabledEngines;

        if (!in_array($engineId, $disabled, true)) {
            $disabled[] = $engineId;
        }

        $manual = $config->manualSelections;
        foreach ($manual as $capability => $selected) {
            if ($selected === $engineId) {
                unset($manual[$capability]);
            }
        }

        $locked = $config->lockedSelections;
        foreach ($locked as $capability => $selected) {
            if ($selected === $engineId) {
                unset($locked[$capability]);
            }
        }

        $updated = $config
            ->withDisabledEngines($disabled)
            ->withManualSelections($manual)
            ->withLockedSelections($locked);

        $this->runtimeRepository->saveConfiguration($updated);

        return [
            'action' => 'remove',
            'engineId' => $engineId,
            'disabled' => true,
            'message' => 'Engine marked disabled in Runtime configuration. Binary artifacts remain on disk.',
            'configuration' => $updated->toArray(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function enable(string $engineId): array
    {
        $config = $this->runtimeRepository->getConfiguration();
        $disabled = array_values(array_filter(
            $config->disabledEngines,
            static fn (string $id): bool => $id !== $engineId,
        ));

        $updated = $config->withDisabledEngines($disabled);
        $this->runtimeRepository->saveConfiguration($updated);

        return [
            'action' => 'enable',
            'engineId' => $engineId,
            'enabled' => true,
            'configuration' => $updated->toArray(),
        ];
    }
}
