<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Domain\ShadowPresence\PresenceCapability;

final class PresenceDispatcher
{
    public function __construct(
        private readonly PresencePermissionEvaluator $permissionEvaluator,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function dispatch(
        PresenceCapability $capability,
        string $scopeKey,
        array $payload,
        \App\Domain\ShadowPresence\PresencePreferences $preferences,
    ): array {
        $this->permissionEvaluator->assertGranted($preferences, $capability);

        return [
            'status' => 'stub',
            'capability' => $capability->value,
            'scopeKey' => $scopeKey,
            'payload' => $payload,
        ];
    }
}
