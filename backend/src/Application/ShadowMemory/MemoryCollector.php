<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory;

final class MemoryCollector
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array{kind: string, payload: array<string, mixed>}
     */
    public function collect(array $payload): array
    {
        return [
            'kind' => is_string($payload['kind'] ?? null) ? $payload['kind'] : 'generic',
            'payload' => is_array($payload['data'] ?? null) ? $payload['data'] : $payload,
        ];
    }
}
