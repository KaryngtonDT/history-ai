<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Domain\ShadowPresence\PresenceSurface;

final class PresenceContextResolver
{
    public function resolveSurface(?string $surfaceValue, PresenceSurface $fallback = PresenceSurface::Web): PresenceSurface
    {
        if (null === $surfaceValue || '' === trim($surfaceValue)) {
            return $fallback;
        }

        return PresenceSurface::tryFrom($surfaceValue) ?? $fallback;
    }

    public function resolveScopeKey(?string $scopeKey): string
    {
        if (null === $scopeKey || '' === trim($scopeKey)) {
            return 'default';
        }

        return trim($scopeKey);
    }
}
