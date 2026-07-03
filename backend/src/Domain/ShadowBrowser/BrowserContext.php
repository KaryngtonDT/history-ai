<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

final readonly class BrowserContext
{
    public function __construct(
        private string $scopeKey,
        private string $url,
        private string $title,
        private string $tabId,
        private BrowserPlatform $platform,
        private ?string $selection,
        private ?string $shadowSessionId,
        private ?string $conversationSessionId,
    ) {
    }

    public static function fromTab(
        string $scopeKey,
        BrowserTab $tab,
        ?string $shadowSessionId = null,
        ?string $conversationSessionId = null,
    ): self {
        return new self(
            $scopeKey,
            $tab->url(),
            $tab->title(),
            $tab->tabId(),
            $tab->platform(),
            $tab->selection(),
            $shadowSessionId,
            $conversationSessionId,
        );
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function tabId(): string
    {
        return $this->tabId;
    }

    public function platform(): BrowserPlatform
    {
        return $this->platform;
    }

    public function selection(): ?string
    {
        return $this->selection;
    }

    public function shadowSessionId(): ?string
    {
        return $this->shadowSessionId;
    }

    public function conversationSessionId(): ?string
    {
        return $this->conversationSessionId;
    }
}
