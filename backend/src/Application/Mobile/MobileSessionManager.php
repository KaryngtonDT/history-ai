<?php

declare(strict_types=1);

namespace App\Application\Mobile;

use App\Domain\Mobile\Exception\InvalidMobileException;
use App\Domain\Mobile\MobileCapabilities;
use App\Domain\Mobile\MobileRepositoryInterface;
use App\Domain\Mobile\MobileWorkspace;

final class MobileSessionManager
{
    public function __construct(
        private readonly MobileRepositoryInterface $repository,
    ) {
    }

    public function getWorkspace(string $scopeKey = 'default'): MobileWorkspace
    {
        return $this->repository->findByScope($scopeKey) ?? MobileWorkspace::create(scopeKey: $scopeKey);
    }

    /** @param array<string, mixed> $payload */
    public function registerDevice(string $scopeKey, array $payload): MobileWorkspace
    {
        $deviceId = is_string($payload['deviceId'] ?? null) ? trim($payload['deviceId']) : '';
        $platform = is_string($payload['platform'] ?? null) ? trim($payload['platform']) : '';
        $name = is_string($payload['name'] ?? null) ? trim($payload['name']) : $platform;

        if ('' === $deviceId || '' === $platform) {
            throw new InvalidMobileException('Mobile device registration requires deviceId and platform.');
        }

        $capabilities = is_array($payload['capabilities'] ?? null)
            ? MobileCapabilities::fromArray($payload['capabilities'])
            : null;

        $workspace = $this->getWorkspace($scopeKey)->registerDevice($deviceId, $platform, $name, $capabilities);
        $this->repository->save($workspace);

        return $workspace;
    }

    public function connectDevice(string $scopeKey, string $deviceId, ?string $shadowSessionId = null): MobileWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey)->connectDevice($deviceId, $shadowSessionId);
        $this->repository->save($workspace);

        return $workspace;
    }

    public function sync(string $scopeKey = 'default'): MobileWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey)->sync();
        $this->repository->save($workspace);

        return $workspace;
    }

    /** @param array<string, mixed> $data */
    public function updateConnection(string $scopeKey, array $data): MobileWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey)->updateConnection($data);
        $this->repository->save($workspace);

        return $workspace;
    }

    /** @param array<string, mixed> $data */
    public function updatePreferences(string $scopeKey, array $data): MobileWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey)->updatePreferences($data);
        $this->repository->save($workspace);

        return $workspace;
    }

    public function setPushToken(string $scopeKey, ?string $pushToken): MobileWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey)->setPushToken($pushToken);
        $this->repository->save($workspace);

        return $workspace;
    }
}
