<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

use App\Domain\ShadowBrowser\Exception\InvalidShadowBrowserException;

final readonly class BrowserSitePolicy
{
    /** @param array<string, bool> $permissions */
    public function __construct(
        private string $host,
        private bool $allowed,
        private array $permissions,
    ) {
        if ('' === trim($host)) {
            throw new InvalidShadowBrowserException('Browser site policy host cannot be empty.');
        }
    }

    public static function create(string $host, bool $allowed = true): self
    {
        $permissions = [];

        foreach (BrowserPermission::cases() as $permission) {
            $granted = match ($permission) {
                BrowserPermission::AskQuestion,
                BrowserPermission::SearchBrain,
                BrowserPermission::ResumeConversation,
                BrowserPermission::DetectPlatform => true,
                default => false,
            };

            $permissions[$permission->value] = $granted;
        }

        return new self(strtolower(trim($host)), $allowed, $permissions);
    }

    public function host(): string
    {
        return $this->host;
    }

    public function allowed(): bool
    {
        return $this->allowed;
    }

    /** @return array<string, bool> */
    public function permissions(): array
    {
        return $this->permissions;
    }

    public function isGranted(BrowserPermission $permission): bool
    {
        if (!$this->allowed) {
            return false;
        }

        return $this->permissions[$permission->value] ?? false;
    }

    /** @param array<string, bool>|null $permissions */
    public function withUpdates(?bool $allowed = null, ?array $permissions = null): self
    {
        $merged = $this->permissions;

        if (null !== $permissions) {
            foreach ($permissions as $key => $granted) {
                if (is_string($key)) {
                    $merged[$key] = (bool) $granted;
                }
            }
        }

        return new self(
            $this->host,
            $allowed ?? $this->allowed,
            $merged,
        );
    }
}
