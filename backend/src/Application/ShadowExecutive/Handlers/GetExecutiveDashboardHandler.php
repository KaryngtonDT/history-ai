<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive\Handlers;

use App\Application\ShadowExecutive\ExecutiveCoordinator;
use App\Application\ShadowExecutive\ExecutiveJsonMapper;
use App\Application\ShadowKnowledge\KnowledgeBuilder;
use App\Application\ShadowMentor\MentorBuilder;

final class GetExecutiveDashboardHandler
{
    public function __construct(
        private readonly ExecutiveCoordinator $coordinator,
        private readonly MentorBuilder $mentorBuilder,
        private readonly KnowledgeBuilder $knowledgeBuilder,
        private readonly ExecutiveJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $plan = $this->coordinator->syncPlan($scopeKey);
        $portfolio = $this->mentorBuilder->getPortfolio($scopeKey);
        $mentorPlan = $this->mentorBuilder->getPlan($scopeKey);
        $graph = $this->knowledgeBuilder->syncGraph($scopeKey);

        return $this->mapper->dashboard($plan, $portfolio, $mentorPlan, $graph);
    }
}
