<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

use App\Domain\ShadowPresence\Exception\InvalidShadowPresenceException;

final readonly class PresenceSession
{
    public function __construct(
        private string $id,
        private string $scopeKey,
        private PresenceSurface $surface,
        private PresenceState $state,
        private ?string $shadowSessionId,
        private \DateTimeImmutable $connectedAt,
        private \DateTimeImmutable $lastActiveAt,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowPresenceException('Presence session scope cannot be empty.');
        }

        if (null !== $shadowSessionId && '' === trim($shadowSessionId)) {
            throw new InvalidShadowPresenceException('Shadow session id cannot be empty when provided.');
        }
    }

    public static function connect(
        string $scopeKey,
        PresenceSurface $surface,
        ?string $shadowSessionId = null,
    ): self {
        $now = new \DateTimeImmutable();

        return new self(
            bin2hex(random_bytes(16)),
            trim($scopeKey),
            $surface,
            PresenceState::Connected,
            $shadowSessionId,
            $now,
            $now,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function surface(): PresenceSurface
    {
        return $this->surface;
    }

    public function state(): PresenceState
    {
        return $this->state;
    }

    public function shadowSessionId(): ?string
    {
        return $this->shadowSessionId;
    }

    public function connectedAt(): \DateTimeImmutable
    {
        return $this->connectedAt;
    }

    public function lastActiveAt(): \DateTimeImmutable
    {
        return $this->lastActiveAt;
    }

    public function touch(): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->surface,
            $this->state,
            $this->shadowSessionId,
            $this->connectedAt,
            new \DateTimeImmutable(),
        );
    }

    public function markIdle(): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->surface,
            PresenceState::Idle,
            $this->shadowSessionId,
            $this->connectedAt,
            new \DateTimeImmutable(),
        );
    }

    public function disconnect(): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->surface,
            PresenceState::Disconnected,
            $this->shadowSessionId,
            $this->connectedAt,
            new \DateTimeImmutable(),
        );
    }

    public function withShadowSessionId(?string $shadowSessionId): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->surface,
            $this->state,
            $shadowSessionId,
            $this->connectedAt,
            $this->lastActiveAt,
        );
    }
}
