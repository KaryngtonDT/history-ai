<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser\Handlers;

use App\Application\ShadowBrowser\BrowserCoordinator;
use App\Application\ShadowBrowser\BrowserJsonMapper;

final class PutBrowserPermissionsHandler
{
    public function __construct(
        private readonly BrowserCoordinator $coordinator,
        private readonly BrowserJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $sitePolicies = is_array($payload['sitePolicies'] ?? null) ? $payload['sitePolicies'] : [];
        $updates = array_values(array_filter($sitePolicies, static fn ($item): bool => is_array($item)));

        $workspace = $this->coordinator->updatePermissions($scopeKey, $updates);

        return $this->mapper->permissions($workspace);
    }
}
