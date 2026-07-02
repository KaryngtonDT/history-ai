<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory;

use App\Domain\ShadowMemory\ShadowMemoryRepositoryInterface;

final class MemoryContextComposer
{
    public function __construct(
        private readonly ShadowMemoryRepositoryInterface $repository,
        private readonly KnowledgeRecallEngine $recallEngine,
    ) {
    }

    /**
     * @return list<string>
     */
    public function promptLines(string $question, string $scopeKey = 'default'): array
    {
        $timeline = $this->repository->findByScope($scopeKey);

        if (null === $timeline || !$timeline->memoryEnabled()) {
            return [];
        }

        return $this->recallEngine->recall($timeline, $question)->promptLines();
    }
}
