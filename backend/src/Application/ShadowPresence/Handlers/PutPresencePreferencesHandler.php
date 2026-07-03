<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence\Handlers;

use App\Application\ShadowPresence\PresenceCoordinator;
use App\Application\ShadowPresence\PresenceJsonMapper;

final class PutPresencePreferencesHandler
{
    public function __construct(
        private readonly PresenceCoordinator $coordinator,
        private readonly PresenceJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $workspace = $this->coordinator->updatePreferences($scopeKey, $payload);

        return [
            'scopeKey' => $scopeKey,
            'preferences' => $this->mapper->preferences($workspace->preferences()),
        ];
    }
}
