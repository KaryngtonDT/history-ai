<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge\Handlers;

use App\Application\ShadowKnowledge\KnowledgeBuilder;
use App\Application\ShadowKnowledge\LearningGapDetector;

final class GetKnowledgeGapsHandler
{
    public function __construct(
        private readonly KnowledgeBuilder $builder,
        private readonly LearningGapDetector $gapDetector,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey, string $goalKey = 'kubernetes'): array
    {
        $graph = $this->builder->syncGraph($scopeKey);

        return [
            'scopeKey' => $scopeKey,
            'radar' => $this->gapDetector->radar($graph, $goalKey),
        ];
    }
}
