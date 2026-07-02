<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory\Handlers;

use App\Application\ShadowMemory\MemoryBuilder;
use App\Domain\ShadowMemory\MemoryCategory;

final class GetMemoryConceptsHandler
{
    public function __construct(private readonly MemoryBuilder $builder)
    {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $timeline = $this->builder->ingestRelationship($scopeKey);

        return [
            'scopeKey' => $scopeKey,
            'concepts' => array_map(
                static fn ($item) => [
                    'key' => $item->key(),
                    'label' => $item->label(),
                    'progress' => $item->progress()->value,
                    'progressPercent' => $item->progressPercent(),
                ],
                $timeline->knowledge()->byCategory(MemoryCategory::Concept),
            ),
        ];
    }
}
