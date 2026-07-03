<?php

declare(strict_types=1);

namespace App\Application\Mobile\Handlers;

use App\Application\Mobile\MobileCoordinator;
use App\Application\Mobile\MobileJsonMapper;
use App\Application\Mobile\MobileTodayBuilder;

final class GetMobileRevisionsHandler
{
    public function __construct(
        private readonly MobileCoordinator $coordinator,
        private readonly MobileTodayBuilder $todayBuilder,
        private readonly MobileJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey): array
    {
        $workspace = $this->coordinator->getWorkspace($scopeKey);

        return $this->mapper->revisions($workspace, $this->todayBuilder->revisions($scopeKey));
    }
}
