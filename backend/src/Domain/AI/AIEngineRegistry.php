<?php

declare(strict_types=1);

namespace App\Domain\AI;

use App\Domain\AI\Exception\InvalidAIEngineException;

final readonly class AIEngineRegistry
{
    /**
     * @param list<AIEngine> $engines
     */
    public function __construct(private array $engines)
    {
        $seenIds = [];
        $seenCapabilities = [];

        foreach ($this->engines as $engine) {
            $id = $engine->id()->value;

            if (isset($seenIds[$id])) {
                throw new InvalidAIEngineException(sprintf('Duplicate AI engine id "%s".', $id));
            }

            $seenIds[$id] = true;

            $capability = $engine->capability()->value;

            if (isset($seenCapabilities[$capability])) {
                throw new InvalidAIEngineException(sprintf(
                    'Duplicate AI engine capability "%s".',
                    $capability,
                ));
            }

            $seenCapabilities[$capability] = true;
        }
    }

    /**
     * @param list<AIEngine> $engines
     */
    public static function fromEngines(array $engines): self
    {
        return new self(array_values($engines));
    }

    public function findByCapability(AIEngineCapability $capability): ?AIEngine
    {
        foreach ($this->engines as $engine) {
            if ($engine->supports($capability)) {
                return $engine;
            }
        }

        return null;
    }

    public function findById(AIEngineId $id): ?AIEngine
    {
        foreach ($this->engines as $engine) {
            if ($engine->id()->equals($id)) {
                return $engine;
            }
        }

        return null;
    }

    /**
     * @return list<AIEngineProvider>
     */
    public function enabledProviders(AIEngineCapability $capability): array
    {
        $engine = $this->findByCapability($capability);

        if (null === $engine || !$engine->isEnabled()) {
            return [];
        }

        return $engine->enabledProviders();
    }

    /**
     * @return list<AIEngineProvider>
     */
    public function allProviders(): array
    {
        $providers = [];

        foreach ($this->engines as $engine) {
            foreach ($engine->providers() as $provider) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    /**
     * @return list<AIEngine>
     */
    public function all(): array
    {
        return $this->engines;
    }
}
