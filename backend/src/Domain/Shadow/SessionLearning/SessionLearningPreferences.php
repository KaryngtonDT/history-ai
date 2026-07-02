<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

final readonly class SessionLearningPreferences
{
    public function __construct(
        private bool $adaptiveEnabled,
    ) {
    }

    public static function default(): self
    {
        return new self(true);
    }

    public function adaptiveEnabled(): bool
    {
        return $this->adaptiveEnabled;
    }

    public function withAdaptiveEnabled(bool $enabled): self
    {
        return new self($enabled);
    }
}
