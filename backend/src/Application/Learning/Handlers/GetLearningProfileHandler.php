<?php

declare(strict_types=1);

namespace App\Application\Learning\Handlers;

use App\Application\Learning\LearningProfileJsonMapper;
use App\Application\Learning\LearningProfileBuilder;
use App\Domain\Learning\LearningProfileRepositoryInterface;

final class GetLearningProfileHandler
{
    public function __construct(
        private readonly LearningProfileRepositoryInterface $repository,
        private readonly LearningProfileBuilder $builder,
        private readonly LearningProfileJsonMapper $mapper,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $profile = $this->repository->findByScope($scopeKey) ?? $this->builder->getOrCreate($scopeKey);

        return $this->mapper->toArray($profile);
    }
}
