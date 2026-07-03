<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence\Handlers;

use App\Application\ShadowPresence\PresenceCoordinator;
use App\Application\ShadowPresence\PresenceJsonMapper;

final class GetPresenceSessionHandler
{
    public function __construct(
        private readonly PresenceCoordinator $coordinator,
        private readonly PresenceJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $workspace = $coordinator->getWorkspace($scopeKey);

        return [
            'scopeKey' => $scopeKey,
            ...$this->mapper->session($workspace->activeSession()),
            'preferences' => $this->mapper->preferences($workspace->preferences()),
        ];
    }
}
