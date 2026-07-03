<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence\Handlers;

use App\Application\ShadowPresence\PresenceCoordinator;
use App\Application\ShadowPresence\PresenceExplainabilityService;

final class GetPresenceExplainHandler
{
    public function __construct(
        private readonly PresenceCoordinator $coordinator,
        private readonly PresenceExplainabilityService $explainabilityService,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $workspace = $this->coordinator->getWorkspace($scopeKey);
        $lastEvent = $workspace->events()->last();

        return $this->explainabilityService->explain($lastEvent);
    }
}
