<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser\Handlers;

use App\Application\ShadowBrowser\BrowserCoordinator;
use App\Application\ShadowBrowser\BrowserJsonMapper;
use App\Domain\ShadowBrowser\Exception\InvalidShadowBrowserException;

final class PostBrowserPlatformHandler
{
    public function __construct(
        private readonly BrowserCoordinator $coordinator,
        private readonly BrowserJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $url = is_string($payload['url'] ?? null) ? trim($payload['url']) : '';

        if ('' === $url) {
            throw new InvalidShadowBrowserException('Platform detection requires a url.');
        }

        $result = $this->coordinator->detectPlatform($scopeKey, $url);

        return $this->mapper->platform($url, $result['platform']->value, $result['host']);
    }
}
