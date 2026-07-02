<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain\Handlers;

use App\Application\ShadowSecondBrain\BrainJsonMapper;
use App\Application\ShadowSecondBrain\WorkspaceBuilder;

final class GetBrainConceptHandler
{
    public function __construct(
        private readonly WorkspaceBuilder $builder,
        private readonly BrainJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey, string $id): array
    {
        return $this->mapper->conceptDetail($this->builder->getWorkspace($scopeKey), $id, $scopeKey);
    }
}
