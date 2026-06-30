<?php

declare(strict_types=1);

namespace App\Domain\AI;

use App\Domain\AI\Exception\InvalidAIEngineException;

final readonly class AIEngineProvider
{
    public function __construct(
        private string $providerId,
        private string $displayName,
        private AIEngineCapability $capability,
        private bool $enabled,
    ) {
        if ('' === trim($this->providerId)) {
            throw new InvalidAIEngineException('Provider id must not be empty.');
        }

        if ('' === trim($this->displayName)) {
            throw new InvalidAIEngineException('Provider display name must not be empty.');
        }
    }

    public static function create(
        string $providerId,
        string $displayName,
        AIEngineCapability $capability,
        bool $enabled = true,
    ): self {
        return new self($providerId, $displayName, $capability, $enabled);
    }

    public function providerId(): string
    {
        return $this->providerId;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function capability(): AIEngineCapability
    {
        return $this->capability;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function withEnabled(bool $enabled): self
    {
        return new self($this->providerId, $this->displayName, $this->capability, $enabled);
    }

    public function enable(): self
    {
        return $this->withEnabled(true);
    }

    public function disable(): self
    {
        return $this->withEnabled(false);
    }
}
