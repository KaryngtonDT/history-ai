<?php

declare(strict_types=1);

namespace App\Application\Mobile\Handlers;

use App\Application\Mobile\MobileCoordinator;
use App\Application\Mobile\MobileJsonMapper;

final class PutMobileConnectionHandler
{
    public function __construct(
        private readonly MobileCoordinator $coordinator,
        private readonly MobileJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $workspace = $this->coordinator->updateConnection($scopeKey, $payload);

        return [
            'scopeKey' => $workspace->scopeKey(),
            'connection' => $this->mapper->connection($workspace->connection()),
        ];
    }
}
