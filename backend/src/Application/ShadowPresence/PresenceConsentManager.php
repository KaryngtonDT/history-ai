<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Domain\ShadowPresence\PresenceCapability;
use App\Domain\ShadowPresence\PresencePermission;
use App\Domain\ShadowPresence\PresencePreferences;

final class PresenceConsentManager
{
    /** @return list<PresencePermission> */
    public function defaultConnectGrants(): array
    {
        return [
            new PresencePermission(PresenceCapability::AskQuestion, true),
            new PresencePermission(PresenceCapability::SearchBrain, true),
            new PresencePermission(PresenceCapability::ResumeConversation, true),
            new PresencePermission(PresenceCapability::ReadSelection, false),
            new PresencePermission(PresenceCapability::ReadPageContext, false),
            new PresencePermission(PresenceCapability::ReadWorkspace, false),
            new PresencePermission(PresenceCapability::ProactiveHint, false),
        ];
    }

    public function applyConnectConsent(PresencePreferences $preferences): PresencePreferences
    {
        $permissions = $preferences->permissions();

        foreach ($this->defaultConnectGrants() as $grant) {
            if ($grant->granted()) {
                $permissions = $permissions->upsert($grant);
            }
        }

        return $preferences->withUpdates(permissions: $permissions);
    }

    public function revokeTemporaryScopes(PresencePreferences $preferences): PresencePreferences
    {
        $permissions = $preferences->permissions();

        foreach ([
            PresenceCapability::ReadSelection,
            PresenceCapability::ReadPageContext,
            PresenceCapability::ReadWorkspace,
        ] as $capability) {
            $permissions = $permissions->upsert(new PresencePermission($capability, false));
        }

        return $preferences->withUpdates(permissions: $permissions);
    }
}
