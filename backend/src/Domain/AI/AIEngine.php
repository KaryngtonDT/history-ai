<?php

declare(strict_types=1);

namespace App\Domain\AI;

use App\Domain\AI\Exception\InvalidAIEngineException;

final readonly class AIEngine
{
    /**
     * @param list<AIEngineProvider> $providers
     */
    public function __construct(
        private AIEngineId $id,
        private AIEngineCapability $capability,
        private bool $enabled,
        private array $providers,
    ) {
        foreach ($this->providers as $provider) {
            if ($provider->capability() !== $this->capability) {
                throw new InvalidAIEngineException(sprintf(
                    'Provider "%s" capability "%s" does not match engine capability "%s".',
                    $provider->providerId(),
                    $provider->capability()->value,
                    $this->capability->value,
                ));
            }
        }
    }

    /**
     * @param list<AIEngineProvider> $providers
     */
    public static function create(
        AIEngineId $id,
        AIEngineCapability $capability,
        array $providers,
        bool $enabled = true,
    ): self {
        return new self($id, $capability, $enabled, array_values($providers));
    }

    public function id(): AIEngineId
    {
        return $this->id;
    }

    public function capability(): AIEngineCapability
    {
        return $this->capability;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function supports(AIEngineCapability $capability): bool
    {
        return $this->capability === $capability;
    }

    public function enable(): self
    {
        return new self($this->id, $this->capability, true, $this->providers);
    }

    public function disable(): self
    {
        return new self($this->id, $this->capability, false, $this->providers);
    }

    /**
     * @return list<AIEngineProvider>
     */
    public function providers(): array
    {
        return $this->providers;
    }

    public function providerCount(): int
    {
        return count($this->providers);
    }

    /**
     * @return list<AIEngineProvider>
     */
    public function enabledProviders(): array
    {
        return array_values(array_filter(
            $this->providers,
            static fn (AIEngineProvider $provider): bool => $provider->isEnabled(),
        ));
    }

    public function withProvider(AIEngineProvider $provider): self
    {
        if ($provider->capability() !== $this->capability) {
            throw new InvalidAIEngineException('Provider capability must match engine capability.');
        }

        $providers = [];

        foreach ($this->providers as $existing) {
            if ($existing->providerId() === $provider->providerId()) {
                continue;
            }

            $providers[] = $existing;
        }

        $providers[] = $provider;

        return new self($this->id, $this->capability, $this->enabled, $providers);
    }

    public function enableProvider(string $providerId): self
    {
        return $this->updateProviderEnabledState($providerId, true);
    }

    public function disableProvider(string $providerId): self
    {
        return $this->updateProviderEnabledState($providerId, false);
    }

    private function updateProviderEnabledState(string $providerId, bool $enabled): self
    {
        $updated = false;
        $providers = [];

        foreach ($this->providers as $provider) {
            if ($provider->providerId() !== $providerId) {
                $providers[] = $provider;
                continue;
            }

            $providers[] = $provider->withEnabled($enabled);
            $updated = true;
        }

        if (!$updated) {
            throw new InvalidAIEngineException(sprintf('Unknown provider id "%s".', $providerId));
        }

        return new self($this->id, $this->capability, $this->enabled, $providers);
    }
}
