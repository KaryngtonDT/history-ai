<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory\Handlers;

use App\Application\ShadowMemory\MemoryBuilder;
use App\Domain\ShadowMemory\MemoryCategory;

final class GetMemoryVocabularyHandler
{
    public function __construct(private readonly MemoryBuilder $builder)
    {
    }

    public function __invoke(string $scopeKey = 'default'): array
    {
        $timeline = $this->builder->ingestRelationship($scopeKey);

        return [
            'scopeKey' => $scopeKey,
            'vocabulary' => array_map(
                static fn ($item) => ['key' => $item->key(), 'label' => $item->label()],
                $timeline->knowledge()->byCategory(MemoryCategory::Vocabulary),
            ),
        ];
    }
}
