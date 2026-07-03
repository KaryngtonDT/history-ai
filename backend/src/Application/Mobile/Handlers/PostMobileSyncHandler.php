<?php

declare(strict_types=1);

namespace App\Application\Mobile\Handlers;

use App\Application\Mobile\MobileCoordinator;
use App\Application\Mobile\MobileJsonMapper;
use App\Domain\Mobile\Exception\InvalidMobileException;

final class PostMobileSyncHandler
{
    public function __construct(
        private readonly MobileCoordinator $coordinator,
        private readonly MobileJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $deviceId = is_string($payload['deviceId'] ?? null) ? trim($payload['deviceId']) : '';
        $shadowSessionId = is_string($payload['shadowSessionId'] ?? null)
            ? $payload['shadowSessionId']
            : null;

        if ('' !== $deviceId) {
            $workspace = $this->coordinator->getWorkspace($scopeKey);

            if (null === $workspace->devices()->find($deviceId)) {
                throw new InvalidMobileException('Mobile device not registered.');
            }

            if (null === $workspace->activeSession() || $workspace->activeDeviceId() !== $deviceId) {
                $workspace = $this->coordinator->connectDevice($scopeKey, $deviceId, $shadowSessionId);
            } else {
                $workspace = $this->coordinator->sync($scopeKey);
            }
        } else {
            $workspace = $this->coordinator->sync($scopeKey);
        }

        return $this->mapper->workspace($workspace);
    }
}
