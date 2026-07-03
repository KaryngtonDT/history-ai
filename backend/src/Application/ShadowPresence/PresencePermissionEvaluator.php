<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Domain\ShadowPresence\PresenceCapability;
use App\Domain\ShadowPresence\PresencePermission;
use App\Domain\ShadowPresence\PresencePreferences;

final class PresencePermissionEvaluator
{
    public function isGranted(PresencePreferences $preferences, PresenceCapability $capability): bool
    {
        if (PresenceCapability::ProactiveHint === $capability && !$preferences->proactiveEnabled()) {
            return false;
        }

        return $preferences->permissions()->isGranted($capability);
    }

    /** @return list<string> */
    public function grantedCapabilities(PresencePreferences $preferences): array
    {
        $values = [];

        foreach (PresenceCapability::cases() as $capability) {
            if ($this->isGranted($preferences, $capability)) {
                $values[] = $capability->value;
            }
        }

        return $values;
    }

    public function assertGranted(PresencePreferences $preferences, PresenceCapability $capability): void
    {
        if (!$this->isGranted($preferences, $capability)) {
            throw new \DomainException(
                sprintf('Presence capability "%s" is not granted.', $capability->value),
            );
        }
    }

    /** @param list<array{capability: string, granted: bool}> $updates */
    public function applyPermissionUpdates(
        PresencePreferences $preferences,
        array $updates,
    ): PresencePreferences {
        $permissions = $preferences->permissions();

        foreach ($updates as $update) {
            $capability = PresenceCapability::tryFrom($update['capability'] ?? '');

            if (null === $capability) {
                continue;
            }

            $permissions = $permissions->upsert(
                new PresencePermission($capability, (bool) ($update['granted'] ?? false)),
            );
        }

        return $preferences->withUpdates(permissions: $permissions);
    }
}
