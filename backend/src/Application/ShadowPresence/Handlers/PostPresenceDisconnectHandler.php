<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence\Handlers;

use App\Application\ShadowPresence\PresenceCoordinator;
use App\Application\ShadowPresence\PresenceJsonMapper;

final class PostPresenceDisconnectHandler
{
    public function __construct(
        private readonly PresenceCoordinator $coordinator,
        private readonly PresenceJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $workspace = $this->coordinator->disconnect($scopeKey);

        return $this->mapper->workspace($workspace);
    }
}
