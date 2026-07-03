<?php

declare(strict_types=1);

namespace App\Application\Mobile\Handlers;

use App\Application\Mobile\MobileCoordinator;
use App\Domain\Mobile\Exception\InvalidMobileException;

final class PostMobilePushTokenHandler
{
    public function __construct(
        private readonly MobileCoordinator $coordinator,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $token = is_string($payload['token'] ?? null) ? trim($payload['token']) : '';

        if ('' === $token) {
            throw new InvalidMobileException('Push token is required.');
        }

        $workspace = $this->coordinator->setPushToken($scopeKey, $token);

        return [
            'scopeKey' => $workspace->scopeKey(),
            'registered' => true,
        ];
    }
}
