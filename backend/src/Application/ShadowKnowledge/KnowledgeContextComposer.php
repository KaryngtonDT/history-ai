<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

final class KnowledgeContextComposer
{
    public function __construct(
        private readonly KnowledgeBuilder $builder,
        private readonly ReasoningEngine $reasoningEngine,
    ) {
    }

    /** @return list<string> */
    public function promptLines(string $question, string $scopeKey = 'default'): array
    {
        $graph = $this->builder->syncGraph($scopeKey);

        if (!$graph->graphEnabled()) {
            return [];
        }

        return $this->reasoningEngine->reason($graph, $question)['promptLines'];
    }
}
