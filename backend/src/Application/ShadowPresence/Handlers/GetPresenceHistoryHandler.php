<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence\Handlers;

use App\Application\ShadowPresence\PresenceCoordinator;
use App\Application\ShadowPresence\PresenceHistoryBuilder;

final class GetPresenceHistoryHandler
{
    public function __construct(
        private readonly PresenceCoordinator $coordinator,
        private readonly PresenceHistoryBuilder $historyBuilder,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default', ?int $limit = null): array
    {
        $workspace = $this->coordinator->getWorkspace($scopeKey);

        return [
            'scopeKey' => $scopeKey,
            'events' => $this->historyBuilder->build($workspace, $limit),
        ];
    }
}
