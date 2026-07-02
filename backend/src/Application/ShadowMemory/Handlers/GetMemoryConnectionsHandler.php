<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory\Handlers;

use App\Application\ShadowMemory\MemoryBuilder;

final class GetMemoryConnectionsHandler
{
    public function __construct(private readonly MemoryBuilder $builder)
    {
    }

    public function __invoke(string $scopeKey = 'default'): array
    {
        $timeline = $this->builder->ingestRelationship($scopeKey);

        return [
            'scopeKey' => $scopeKey,
            'connections' => array_map(
                static fn ($c) => [
                    'fromKey' => $c->fromKey(),
                    'toKey' => $c->toKey(),
                    'label' => $c->label(),
                    'reason' => $c->reason(),
                ],
                $timeline->connections()->all(),
            ),
        ];
    }
}
