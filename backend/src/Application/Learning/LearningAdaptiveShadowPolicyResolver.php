<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Application\Learning\DTO\LearningAdaptiveHints;
use App\Domain\Shadow\ShadowInterventionPolicy;

final class LearningAdaptiveShadowPolicyResolver
{
    public function apply(
        ShadowInterventionPolicy $policy,
        LearningAdaptiveHints $hints,
    ): ShadowInterventionPolicy {
        if (!$hints->active || !$policy->enabled()) {
            return $policy;
        }

        $updated = $policy;

        if (null !== $hints->explanationStyle) {
            $updated = $updated->withExplanationStyle($hints->explanationStyle);
        }

        if (null !== $hints->challengeLevel) {
            $updated = $updated->withChallengeLevel($hints->challengeLevel);
        }

        return $updated;
    }
}
