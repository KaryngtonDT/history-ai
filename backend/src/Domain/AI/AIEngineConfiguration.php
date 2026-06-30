<?php

declare(strict_types=1);

namespace App\Domain\AI;

use App\Domain\AI\Exception\InvalidAIEngineException;

final readonly class AIEngineConfiguration
{
    /**
     * @param array<string, string> $defaultProviderByCapability capability value => provider id
     */
    public function __construct(private array $defaultProviderByCapability = [])
    {
        foreach ($this->defaultProviderByCapability as $capability => $providerId) {
            if ('' === trim($providerId)) {
                throw new InvalidAIEngineException('Default provider id must not be empty.');
            }

            if (null === AIEngineCapability::tryFrom($capability)) {
                throw new InvalidAIEngineException(sprintf('Unknown capability "%s".', $capability));
            }
        }
    }

    public static function empty(): self
    {
        return new self();
    }

    public function defaultProviderFor(AIEngineCapability $capability): ?string
    {
        return $this->defaultProviderByCapability[$capability->value] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function allDefaults(): array
    {
        return $this->defaultProviderByCapability;
    }

    public function withDefaultProvider(AIEngineCapability $capability, string $providerId): self
    {
        if ('' === trim($providerId)) {
            throw new InvalidAIEngineException('Default provider id must not be empty.');
        }

        return new self([
            ...$this->defaultProviderByCapability,
            $capability->value => $providerId,
        ]);
    }
}
