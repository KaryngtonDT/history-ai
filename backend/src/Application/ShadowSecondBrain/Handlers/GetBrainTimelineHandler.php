<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain\Handlers;

use App\Application\ShadowSecondBrain\BrainJsonMapper;
use App\Application\ShadowSecondBrain\WorkspaceBuilder;

final class GetBrainTimelineHandler
{
    public function __construct(
        private readonly WorkspaceBuilder $builder,
        private readonly BrainJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return $this->mapper->timeline($this->builder->getWorkspace($scopeKey));
    }
}
