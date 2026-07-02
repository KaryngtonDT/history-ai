<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Domain\ShadowExecutive\DecisionStatus;

final class ExecutiveContextComposer
{
    public function __construct(
        private readonly ExecutiveCoordinator $coordinator,
    ) {
    }

    /** @return list<string> */
    public function promptLines(string $scopeKey = 'default'): array
    {
        $plan = $this->coordinator->syncPlan($scopeKey);

        if (!$plan->executiveEnabled()) {
            return [];
        }

        $lines = ['Executive plan (proposals only — user must approve):'];

        $pending = $plan->pendingDecisions()->all();

        if ([] === $pending) {
            $lines[] = 'No pending executive decisions. Continue with the current agenda.';

            return $lines;
        }

        usort(
            $pending,
            static fn ($left, $right): int => array_search($left->priority()->value, ['critical', 'high', 'normal', 'low'], true)
                <=> array_search($right->priority()->value, ['critical', 'high', 'normal', 'low'], true),
        );

        $top = $pending[0];
        $lines[] = sprintf(
            'Top pending decision [%s]: %s — %s',
            $top->priority()->value,
            $top->title(),
            $top->reason()->summary(),
        );

        if (DecisionStatus::Pending === $top->status()) {
            $lines[] = 'Mention this as a recommendation only; never auto-apply executive decisions.';
        }

        $today = $plan->agenda()->today()->all();

        if ([] !== $today) {
            $lines[] = sprintf('Today agenda starts with: %s.', $today[0]->label());
        }

        return $lines;
    }
}
