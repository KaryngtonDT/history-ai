<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge\Handlers;

use App\Application\ShadowKnowledge\KnowledgeBuilder;
use App\Application\ShadowKnowledge\KnowledgeJsonMapper;

final class GetKnowledgeGraphHandler
{
    public function __construct(
        private readonly KnowledgeBuilder $builder,
        private readonly KnowledgeJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return $this->mapper->toArray($this->builder->readGraph($scopeKey));
    }
}
