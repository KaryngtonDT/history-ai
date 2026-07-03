<?php

declare(strict_types=1);

namespace App\Application\Mobile;

use App\Application\ShadowPresence\PresenceCoordinator;
use App\Domain\Mobile\MobileCapabilities;
use App\Domain\Mobile\MobileWorkspace;
use App\Domain\ShadowPresence\PresenceSurface;

final class MobileCoordinator
{
    public function __construct(
        private readonly MobileSessionManager $sessionManager,
        private readonly PresenceCoordinator $presenceCoordinator,
    ) {
    }

    public function getWorkspace(string $scopeKey = 'default'): MobileWorkspace
    {
        return $this->sessionManager->getWorkspace($scopeKey);
    }

    /** @param array<string, mixed> $payload */
    public function registerDevice(string $scopeKey, array $payload): MobileWorkspace
    {
        $this->presenceCoordinator->updatePreferences($scopeKey, [
            'surfaceEnabled' => [PresenceSurface::Mobile->value => true],
        ]);

        return $this->sessionManager->registerDevice($scopeKey, $payload);
    }

    public function connectDevice(string $scopeKey, string $deviceId, ?string $shadowSessionId = null): MobileWorkspace
    {
        $this->presenceCoordinator->updatePreferences($scopeKey, [
            'surfaceEnabled' => [PresenceSurface::Mobile->value => true],
        ]);
        $this->presenceCoordinator->connect($scopeKey, PresenceSurface::Mobile, $shadowSessionId);

        return $this->sessionManager->connectDevice($scopeKey, $deviceId, $shadowSessionId);
    }

    public function sync(string $scopeKey = 'default'): MobileWorkspace
    {
        return $this->sessionManager->sync($scopeKey);
    }

    /** @param array<string, mixed> $data */
    public function updateConnection(string $scopeKey, array $data): MobileWorkspace
    {
        return $this->sessionManager->updateConnection($scopeKey, $data);
    }

    /** @param array<string, mixed> $data */
    public function updatePreferences(string $scopeKey, array $data): MobileWorkspace
    {
        return $this->sessionManager->updatePreferences($scopeKey, $data);
    }

    public function setPushToken(string $scopeKey, ?string $pushToken): MobileWorkspace
    {
        return $this->sessionManager->setPushToken($scopeKey, $pushToken);
    }
}
