<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

use App\Domain\ShadowBrowser\Exception\InvalidShadowBrowserException;

final readonly class BrowserTab
{
    public function __construct(
        private string $tabId,
        private string $url,
        private string $title,
        private BrowserPlatform $platform,
        private ?string $selection,
    ) {
        if ('' === trim($tabId)) {
            throw new InvalidShadowBrowserException('Browser tab id cannot be empty.');
        }

        if ('' === trim($url)) {
            throw new InvalidShadowBrowserException('Browser tab url cannot be empty.');
        }
    }

    public static function create(
        string $tabId,
        string $url,
        string $title = '',
        BrowserPlatform $platform = BrowserPlatform::Unknown,
        ?string $selection = null,
    ): self {
        return new self(
            trim($tabId),
            trim($url),
            trim($title),
            $platform,
            null !== $selection && '' !== trim($selection) ? trim($selection) : null,
        );
    }

    public function tabId(): string
    {
        return $this->tabId;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function platform(): BrowserPlatform
    {
        return $this->platform;
    }

    public function selection(): ?string
    {
        return $this->selection;
    }

    public function withUpdates(
        ?string $url = null,
        ?string $title = null,
        ?BrowserPlatform $platform = null,
        ?string $selection = null,
        bool $clearSelection = false,
    ): self {
        return new self(
            $this->tabId,
            $url ?? $this->url,
            $title ?? $this->title,
            $platform ?? $this->platform,
            $clearSelection ? null : ($selection ?? $this->selection),
        );
    }
}
