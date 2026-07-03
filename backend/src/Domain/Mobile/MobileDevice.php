<?php

declare(strict_types=1);

namespace App\Domain\Mobile;

use App\Domain\Mobile\Exception\InvalidMobileException;

final readonly class MobileDevice
{
    public function __construct(
        private string $deviceId,
        private string $platform,
        private string $name,
        private MobileCapabilities $capabilities,
        private \DateTimeImmutable $registeredAt,
        private \DateTimeImmutable $lastSeenAt,
    ) {
        if ('' === trim($deviceId)) {
            throw new InvalidMobileException('Mobile device id cannot be empty.');
        }

        if ('' === trim($platform)) {
            throw new InvalidMobileException('Mobile device platform cannot be empty.');
        }
    }

    public static function register(
        string $deviceId,
        string $platform,
        string $name,
        ?MobileCapabilities $capabilities = null,
    ): self {
        $now = new \DateTimeImmutable();

        return new self(
            trim($deviceId),
            trim($platform),
            trim($name) !== '' ? trim($name) : trim($platform),
            $capabilities ?? MobileCapabilities::createDefault(),
            $now,
            $now,
        );
    }

    public function deviceId(): string
    {
        return $this->deviceId;
    }

    public function platform(): string
    {
        return $this->platform;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function capabilities(): MobileCapabilities
    {
        return $this->capabilities;
    }

    public function registeredAt(): \DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function lastSeenAt(): \DateTimeImmutable
    {
        return $this->lastSeenAt;
    }

    public function touch(): self
    {
        return new self(
            $this->deviceId,
            $this->platform,
            $this->name,
            $this->capabilities,
            $this->registeredAt,
            new \DateTimeImmutable(),
        );
    }

    public function withName(string $name): self
    {
        return new self(
            $this->deviceId,
            $this->platform,
            trim($name) !== '' ? trim($name) : $this->name,
            $this->capabilities,
            $this->registeredAt,
            new \DateTimeImmutable(),
        );
    }

    public function withCapabilities(MobileCapabilities $capabilities): self
    {
        return new self(
            $this->deviceId,
            $this->platform,
            $this->name,
            $capabilities,
            $this->registeredAt,
            new \DateTimeImmutable(),
        );
    }
}
