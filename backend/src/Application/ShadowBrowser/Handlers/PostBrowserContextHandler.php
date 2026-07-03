<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser\Handlers;

use App\Application\ShadowBrowser\BrowserCoordinator;
use App\Application\ShadowBrowser\BrowserJsonMapper;

final class PostBrowserContextHandler
{
    public function __construct(
        private readonly BrowserCoordinator $coordinator,
        private readonly BrowserJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $workspace = $this->coordinator->updateContext($scopeKey, $payload);

        return [
            'scopeKey' => $scopeKey,
            'session' => $this->mapper->session($workspace->activeSession()),
            'context' => $this->mapper->context($workspace->currentContext()),
        ];
    }
}
