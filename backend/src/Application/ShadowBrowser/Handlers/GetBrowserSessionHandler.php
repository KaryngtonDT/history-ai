<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser\Handlers;

use App\Application\ShadowBrowser\BrowserCoordinator;
use App\Application\ShadowBrowser\BrowserJsonMapper;

final class GetBrowserSessionHandler
{
    public function __construct(
        private readonly BrowserCoordinator $coordinator,
        private readonly BrowserJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $workspace = $this->coordinator->getWorkspace($scopeKey);

        return [
            'scopeKey' => $scopeKey,
            ...$this->mapper->session($workspace->activeSession()),
            'context' => $this->mapper->context($workspace->currentContext()),
        ];
    }
}
