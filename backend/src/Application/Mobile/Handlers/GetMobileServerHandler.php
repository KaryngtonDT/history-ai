<?php

declare(strict_types=1);

namespace App\Application\Mobile\Handlers;

use App\Application\Mobile\MobileCoordinator;
use App\Application\Mobile\MobileJsonMapper;
use App\Application\Mobile\MobileServerBuilder;

final class GetMobileServerHandler
{
    public function __construct(
        private readonly MobileCoordinator $coordinator,
        private readonly MobileServerBuilder $serverBuilder,
        private readonly MobileJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey): array
    {
        return $this->mapper->server(
            $this->coordinator->getWorkspace($scopeKey),
            $this->serverBuilder->build(),
        );
    }
}
