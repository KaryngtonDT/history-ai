<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

final class MentorContextComposer
{
    public function __construct(
        private readonly MentorBuilder $builder,
        private readonly GoalExplanationBuilder $explanationBuilder,
        private readonly MentorAdvisor $advisor,
    ) {
    }

    /** @return list<string> */
    public function promptLines(string $question = '', string $scopeKey = 'default'): array
    {
        $portfolio = $this->builder->getPortfolio($scopeKey);

        if (!$portfolio->mentorEnabled()) {
            return [];
        }

        $plan = $this->builder->syncPlan($scopeKey);
        $lines = $this->explanationBuilder->build($portfolio, $plan);
        $lines = [...$lines, ...$this->advisor->recommend($portfolio, $plan, $question)];

        if ([] !== $lines) {
            $lines[] = 'Connect answers to the learner goals and current mission when relevant.';
        }

        return $lines;
    }
}
