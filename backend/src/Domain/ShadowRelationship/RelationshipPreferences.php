<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

final readonly class RelationshipPreferences
{
    public function __construct(
        private bool $adaptiveEnabled,
        private bool $rememberRelationship,
        private bool $requireApprovalForInferences,
    ) {
    }

    public static function default(): self
    {
        return new self(true, true, true);
    }

    public function adaptiveEnabled(): bool
    {
        return $this->adaptiveEnabled;
    }

    public function rememberRelationship(): bool
    {
        return $this->rememberRelationship;
    }

    public function requireApprovalForInferences(): bool
    {
        return $this->requireApprovalForInferences;
    }

    public function withAdaptiveEnabled(bool $enabled): self
    {
        return new self($enabled, $this->rememberRelationship, $this->requireApprovalForInferences);
    }

    public function withRememberRelationship(bool $remember): self
    {
        return new self($this->adaptiveEnabled, $remember, $this->requireApprovalForInferences);
    }
}
