<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Shadow\ShadowIntervention;

final class ShadowInterventionPlanner
{
    public function __construct(
        private readonly ShadowInterventionDecider $decider,
    ) {
    }

    public function plan(ShadowInterventionContext $context): ?ShadowIntervention
    {
        if (!$context->policy->canScheduleIntervention(
            $context->currentTimeSeconds(),
            $context->recentInterventions,
        )) {
            return null;
        }

        return $this->decider->decide($context);
    }
}
