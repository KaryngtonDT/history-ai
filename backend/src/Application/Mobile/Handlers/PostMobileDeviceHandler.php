<?php

declare(strict_types=1);

namespace App\Application\Mobile\Handlers;

use App\Application\Mobile\MobileCoordinator;
use App\Application\Mobile\MobileJsonMapper;
use App\Domain\Mobile\Exception\InvalidMobileException;

final class PostMobileDeviceHandler
{
    public function __construct(
        private readonly MobileCoordinator $coordinator,
        private readonly MobileJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $workspace = $this->coordinator->registerDevice($scopeKey, $payload);
        $deviceId = is_string($payload['deviceId'] ?? null) ? trim($payload['deviceId']) : '';
        $device = $workspace->devices()->find($deviceId);

        if (null === $device) {
            throw new InvalidMobileException('Mobile device registration failed.');
        }

        return [
            'scopeKey' => $workspace->scopeKey(),
            'device' => $this->mapper->device($device),
            'workspace' => $this->mapper->workspace($workspace),
        ];
    }
}
