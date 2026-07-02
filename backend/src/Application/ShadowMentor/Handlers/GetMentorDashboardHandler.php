<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor\Handlers;

use App\Application\ShadowKnowledge\KnowledgeBuilder;
use App\Application\ShadowMentor\MentorBuilder;
use App\Application\ShadowMentor\MentorJsonMapper;

final class GetMentorDashboardHandler
{
    public function __construct(
        private readonly MentorBuilder $builder,
        private readonly KnowledgeBuilder $knowledgeBuilder,
        private readonly MentorJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default', ?string $conceptKey = null): array
    {
        $portfolio = $this->builder->getPortfolio($scopeKey);
        $plan = $this->builder->syncPlan($scopeKey);
        $graph = $this->knowledgeBuilder->syncGraph($scopeKey);

        return $this->mapper->dashboard($portfolio, $plan, $graph, $conceptKey);
    }
}
