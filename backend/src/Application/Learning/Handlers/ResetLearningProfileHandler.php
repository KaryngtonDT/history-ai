<?php

declare(strict_types=1);

namespace App\Application\Learning\Handlers;

use App\Application\Learning\LearningProfileBuilder;
use App\Application\Learning\LearningProfileJsonMapper;

final class ResetLearningProfileHandler
{
    public function __construct(
        private readonly LearningProfileBuilder $builder,
        private readonly LearningProfileJsonMapper $mapper,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return $this->mapper->toArray($this->builder->reset($scopeKey));
    }
}
