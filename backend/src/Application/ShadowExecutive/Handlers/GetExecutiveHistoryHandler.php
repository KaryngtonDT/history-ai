<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive\Handlers;

use App\Application\ShadowExecutive\ExecutiveCoordinator;
use App\Application\ShadowExecutive\ExecutiveJsonMapper;

final class GetExecutiveHistoryHandler
{
    public function __construct(
        private readonly ExecutiveCoordinator $coordinator,
        private readonly ExecutiveJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return $this->mapper->history($this->coordinator->getPlan($scopeKey));
    }
}
