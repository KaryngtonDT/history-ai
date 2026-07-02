<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory\Handlers;

use App\Application\ShadowMemory\MemoryBuilder;
use App\Domain\ShadowMemory\MemoryCategory;

final class GetMemoryMilestonesHandler
{
    public function __construct(private readonly MemoryBuilder $builder)
    {
    }

    public function __invoke(string $scopeKey = 'default'): array
    {
        $timeline = $this->builder->ingestRelationship($scopeKey);

        return [
            'scopeKey' => $scopeKey,
            'milestones' => array_map(
                static fn ($entry) => [
                    'id' => $entry->id(),
                    'label' => $entry->label(),
                    'detail' => $entry->detail(),
                    'recordedAt' => $entry->recordedAt()->format(DATE_ATOM),
                ],
                $timeline->entries()->byCategory(MemoryCategory::Milestone),
            ),
        ];
    }
}
