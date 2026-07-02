<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\Exception\InvalidShadowIdentityException;

final readonly class ShadowIdentitySnapshot
{
    public function __construct(
        private ShadowIdentityId $id,
        private \DateTimeImmutable $recordedAt,
        private string $label,
        private ShadowIdentityPreferences $preferences,
        private string $source,
    ) {
        if ('' === trim($label)) {
            throw new InvalidShadowIdentityException('Snapshot label cannot be empty.');
        }

        if ('' === trim($source)) {
            throw new InvalidShadowIdentityException('Snapshot source cannot be empty.');
        }
    }

    public static function capture(
        ShadowIdentityPreferences $preferences,
        string $label,
        string $source = 'user',
    ): self {
        return new self(
            ShadowIdentityId::generate(),
            new \DateTimeImmutable(),
            trim($label),
            $preferences,
            trim($source),
        );
    }

    public function id(): ShadowIdentityId
    {
        return $this->id;
    }

    public function recordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function preferences(): ShadowIdentityPreferences
    {
        return $this->preferences;
    }

    public function source(): string
    {
        return $this->source;
    }
}
