<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser\Handlers;

use App\Application\ShadowBrowser\BrowserCoordinator;
use App\Application\ShadowBrowser\BrowserExplainabilityService;

final class GetBrowserExplainHandler
{
    public function __construct(
        private readonly BrowserCoordinator $coordinator,
        private readonly BrowserExplainabilityService $explainabilityService,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $workspace = $this->coordinator->getWorkspace($scopeKey);
        $lastActivity = $workspace->activities()->last();

        return $this->explainabilityService->explain($lastActivity);
    }
}
