<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

use App\Domain\ShadowBrowser\Exception\InvalidShadowBrowserException;

final readonly class BrowserSession
{
    public function __construct(
        private string $id,
        private string $scopeKey,
        private BrowserState $state,
        private ?string $shadowSessionId,
        private ?BrowserTab $activeTab,
        private \DateTimeImmutable $connectedAt,
        private \DateTimeImmutable $lastActiveAt,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowBrowserException('Browser session scope cannot be empty.');
        }

        if (null !== $shadowSessionId && '' === trim($shadowSessionId)) {
            throw new InvalidShadowBrowserException('Shadow session id cannot be empty when provided.');
        }
    }

    public static function connect(
        string $scopeKey,
        ?string $shadowSessionId = null,
    ): self {
        $now = new \DateTimeImmutable();

        return new self(
            bin2hex(random_bytes(16)),
            trim($scopeKey),
            BrowserState::Connected,
            $shadowSessionId,
            null,
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

    public function state(): BrowserState
    {
        return $this->state;
    }

    public function shadowSessionId(): ?string
    {
        return $this->shadowSessionId;
    }

    public function activeTab(): ?BrowserTab
    {
        return $this->activeTab;
    }

    public function connectedAt(): \DateTimeImmutable
    {
        return $this->connectedAt;
    }

    public function lastActiveAt(): \DateTimeImmutable
    {
        return $this->lastActiveAt;
    }

    public function touch(?BrowserTab $activeTab = null): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->state,
            $this->shadowSessionId,
            $activeTab ?? $this->activeTab,
            $this->connectedAt,
            new \DateTimeImmutable(),
        );
    }

    public function disconnect(): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            BrowserState::Disconnected,
            $this->shadowSessionId,
            null,
            $this->connectedAt,
            new \DateTimeImmutable(),
        );
    }

    public function withShadowSessionId(?string $shadowSessionId): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->state,
            $shadowSessionId,
            $this->activeTab,
            $this->connectedAt,
            $this->lastActiveAt,
        );
    }
}
