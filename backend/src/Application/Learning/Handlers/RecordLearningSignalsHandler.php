<?php

declare(strict_types=1);

namespace App\Application\Learning\Handlers;

use App\Application\Learning\LearningProfileBuilder;
use App\Application\Learning\LearningProfileJsonMapper;

final class RecordLearningSignalsHandler
{
    public function __construct(
        private readonly LearningProfileBuilder $builder,
        private readonly LearningProfileJsonMapper $mapper,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $profile = $this->builder->recordPayload($scopeKey, $payload);

        return $this->mapper->toArray($profile);
    }
}
