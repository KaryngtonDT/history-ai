<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser\Handlers;

use App\Application\ShadowBrowser\BrowserCoordinator;
use App\Application\ShadowBrowser\BrowserHistoryBuilder;

final class GetBrowserHistoryHandler
{
    public function __construct(
        private readonly BrowserCoordinator $coordinator,
        private readonly BrowserHistoryBuilder $historyBuilder,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default', ?int $limit = null): array
    {
        $workspace = $this->coordinator->getWorkspace($scopeKey);

        return [
            'scopeKey' => $scopeKey,
            'activities' => $this->historyBuilder->build($workspace, $limit),
        ];
    }
}
