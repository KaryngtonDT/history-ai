<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

final readonly class PresencePreferences
{
    /** @param array<string, bool> $surfaceEnabled */
    public function __construct(
        private string $shortcuts,
        private bool $notifications,
        private bool $voiceEnabled,
        private bool $proactiveEnabled,
        private array $surfaceEnabled,
        private PresencePermissionCollection $permissions,
    ) {
    }

    public static function default(): self
    {
        return new self(
            'Ctrl+Shift+Space',
            true,
            false,
            false,
            [
                PresenceSurface::Web->value => true,
                PresenceSurface::Desktop->value => true,
                PresenceSurface::Browser->value => false,
                PresenceSurface::Ide->value => false,
                PresenceSurface::Mobile->value => false,
            ],
            self::defaultPermissions(),
        );
    }

    public static function defaultPermissions(): PresencePermissionCollection
    {
        $permissions = [];

        foreach (PresenceCapability::cases() as $capability) {
            $granted = match ($capability) {
                PresenceCapability::AskQuestion,
                PresenceCapability::SearchBrain,
                PresenceCapability::ResumeConversation => true,
                default => false,
            };

            $permissions[] = new PresencePermission($capability, $granted);
        }

        return new PresencePermissionCollection($permissions);
    }

    public function shortcuts(): string
    {
        return $this->shortcuts;
    }

    public function notifications(): bool
    {
        return $this->notifications;
    }

    public function voiceEnabled(): bool
    {
        return $this->voiceEnabled;
    }

    public function proactiveEnabled(): bool
    {
        return $this->proactiveEnabled;
    }

    /** @return array<string, bool> */
    public function surfaceEnabled(): array
    {
        return $this->surfaceEnabled;
    }

    public function permissions(): PresencePermissionCollection
    {
        return $this->permissions;
    }

    public function isSurfaceEnabled(PresenceSurface $surface): bool
    {
        return $this->surfaceEnabled[$surface->value] ?? false;
    }

    /** @param array<string, bool>|null $surfaceEnabled */
    public function withUpdates(
        ?string $shortcuts = null,
        ?bool $notifications = null,
        ?bool $voiceEnabled = null,
        ?bool $proactiveEnabled = null,
        ?array $surfaceEnabled = null,
        ?PresencePermissionCollection $permissions = null,
    ): self {
        $mergedSurfaces = $this->surfaceEnabled;

        if (null !== $surfaceEnabled) {
            foreach ($surfaceEnabled as $key => $enabled) {
                $mergedSurfaces[$key] = $enabled;
            }
        }

        $permissions = $permissions ?? $this->permissions;

        if (null !== $proactiveEnabled) {
            $permissions = $permissions->upsert(
                new PresencePermission(PresenceCapability::ProactiveHint, $proactiveEnabled),
            );
        }

        return new self(
            $shortcuts ?? $this->shortcuts,
            $notifications ?? $this->notifications,
            $voiceEnabled ?? $this->voiceEnabled,
            $proactiveEnabled ?? $this->proactiveEnabled,
            $mergedSurfaces,
            $permissions,
        );
    }
}
