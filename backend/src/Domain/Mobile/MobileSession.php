<?php

declare(strict_types=1);

namespace App\Domain\Mobile;

use App\Domain\Mobile\Exception\InvalidMobileException;

final readonly class MobileSession
{
    public function __construct(
        private string $id,
        private string $scopeKey,
        private string $deviceId,
        private MobileState $state,
        private ?string $shadowSessionId,
        private \DateTimeImmutable $connectedAt,
        private \DateTimeImmutable $lastActiveAt,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidMobileException('Mobile session scope cannot be empty.');
        }

        if ('' === trim($deviceId)) {
            throw new InvalidMobileException('Mobile session device id cannot be empty.');
        }

        if (null !== $shadowSessionId && '' === trim($shadowSessionId)) {
            throw new InvalidMobileException('Shadow session id cannot be empty when provided.');
        }
    }

    public static function connect(
        string $scopeKey,
        string $deviceId,
        ?string $shadowSessionId = null,
    ): self {
        $now = new \DateTimeImmutable();

        return new self(
            bin2hex(random_bytes(16)),
            trim($scopeKey),
            trim($deviceId),
            MobileState::Connected,
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

    public function deviceId(): string
    {
        return $this->deviceId;
    }

    public function state(): MobileState
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
            $this->deviceId,
            $this->state,
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
            $this->deviceId,
            MobileState::Disconnected,
            $this->shadowSessionId,
            $this->connectedAt,
            new \DateTimeImmutable(),
        );
    }
}
