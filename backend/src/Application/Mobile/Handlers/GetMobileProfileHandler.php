<?php

declare(strict_types=1);

namespace App\Application\Mobile\Handlers;

use App\Application\Mobile\MobileCoordinator;
use App\Application\Mobile\MobileJsonMapper;

final class GetMobileProfileHandler
{
    public function __construct(
        private readonly MobileCoordinator $coordinator,
        private readonly MobileJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey): array
    {
        return $this->mapper->profile($this->coordinator->getWorkspace($scopeKey));
    }
}
