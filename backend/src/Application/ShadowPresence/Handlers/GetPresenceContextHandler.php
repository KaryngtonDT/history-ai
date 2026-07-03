<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence\Handlers;

use App\Application\ShadowPresence\PresenceContextResolver;
use App\Application\ShadowPresence\PresenceCoordinator;
use App\Application\ShadowPresence\PresenceJsonMapper;

final class GetPresenceContextHandler
{
    public function __construct(
        private readonly PresenceCoordinator $coordinator,
        private readonly PresenceContextResolver $resolver,
        private readonly PresenceJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey, ?string $surfaceValue = null): array
    {
        $surface = $this->resolver->resolveSurface($surfaceValue);
        $context = $this->coordinator->context($scopeKey, $surface);

        return $this->mapper->context($context);
    }
}
