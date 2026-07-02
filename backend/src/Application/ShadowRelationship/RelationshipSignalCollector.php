<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipSignal;

final class RelationshipSignalCollector
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return list<RelationshipSignal>
     */
    public function collect(array $payload): array
    {
        $source = is_string($payload['source'] ?? null) ? $payload['source'] : 'unknown';
        $kind = is_string($payload['kind'] ?? null) ? $payload['kind'] : 'generic';
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;

        return [RelationshipSignal::create($source, $kind, $data)];
    }
}
