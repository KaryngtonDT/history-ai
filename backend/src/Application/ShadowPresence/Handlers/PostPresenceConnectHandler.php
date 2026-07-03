<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence\Handlers;

use App\Application\ShadowPresence\PresenceContextResolver;
use App\Application\ShadowPresence\PresenceCoordinator;
use App\Application\ShadowPresence\PresenceJsonMapper;
use App\Domain\ShadowPresence\Exception\InvalidShadowPresenceException;

final class PostPresenceConnectHandler
{
    public function __construct(
        private readonly PresenceCoordinator $coordinator,
        private readonly PresenceContextResolver $resolver,
        private readonly PresenceJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $surfaceValue = is_string($payload['surface'] ?? null) ? $payload['surface'] : null;
        $surface = $this->resolver->resolveSurface($surfaceValue);

        if (null === $surfaceValue || null === $this->mapper->parseSurface($surfaceValue)) {
            throw new InvalidShadowPresenceException('Presence surface is required.');
        }

        $shadowSessionId = is_string($payload['shadowSessionId'] ?? null)
            ? $payload['shadowSessionId']
            : null;

        $workspace = $this->coordinator->connect($scopeKey, $surface, $shadowSessionId);

        return $this->mapper->workspace($workspace);
    }
}
