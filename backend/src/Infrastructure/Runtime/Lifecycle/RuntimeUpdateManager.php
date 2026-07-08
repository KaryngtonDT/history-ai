<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Lifecycle;

final class RuntimeUpdateManager
{
    public function __construct(private readonly RuntimeProvisionManager $provisionManager)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function update(string $engineId): array
    {
        $result = $this->provisionManager->install($engineId);
        $result['action'] = 'update';

        return $result;
    }
}
