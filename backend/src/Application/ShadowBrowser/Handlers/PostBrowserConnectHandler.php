<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser\Handlers;

use App\Application\ShadowBrowser\BrowserCoordinator;
use App\Application\ShadowBrowser\BrowserJsonMapper;

final class PostBrowserConnectHandler
{
    public function __construct(
        private readonly BrowserCoordinator $coordinator,
        private readonly BrowserJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $shadowSessionId = is_string($payload['shadowSessionId'] ?? null)
            ? $payload['shadowSessionId']
            : null;

        $workspace = $this->coordinator->connect($scopeKey, $shadowSessionId);

        return $this->mapper->workspace($workspace);
    }
}
