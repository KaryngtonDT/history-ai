<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Shadow\ShadowChallengeLevel;
use App\Domain\Shadow\ShadowExplanationStyle;
use App\Domain\Shadow\ShadowInterventionPolicy;
use App\Domain\Shadow\ShadowTutorMode;

final class ShadowInterventionPolicyMapper
{
    /**
     * @param array<string, mixed> $payload
     */
    public function fromArray(array $payload, ShadowInterventionPolicy $current): ShadowInterventionPolicy
    {
        $policy = $current;

        if (isset($payload['tutorMode']) && is_string($payload['tutorMode'])) {
            $mode = ShadowTutorMode::tryFrom($payload['tutorMode']);

            if (null !== $mode) {
                $policy = $mode->toPolicy();
            }
        }

        if (array_key_exists('enabled', $payload)) {
            $policy = $policy->withEnabled((bool) $payload['enabled']);
        }

        if (isset($payload['challengeLevel']) && is_string($payload['challengeLevel'])) {
            $level = ShadowChallengeLevel::tryFrom($payload['challengeLevel']);

            if (null !== $level) {
                $policy = $policy->withChallengeLevel($level);
            }
        }

        if (isset($payload['explanationStyle']) && is_string($payload['explanationStyle'])) {
            $style = ShadowExplanationStyle::tryFrom($payload['explanationStyle']);

            if (null !== $style) {
                $policy = $policy->withExplanationStyle($style);
            }
        }

        if (isset($payload['maxInterventionsPerMinute']) && is_numeric($payload['maxInterventionsPerMinute'])) {
            $minSeconds = isset($payload['minSecondsBetweenInterventions'])
                && is_numeric($payload['minSecondsBetweenInterventions'])
                ? (float) $payload['minSecondsBetweenInterventions']
                : $policy->minSecondsBetweenInterventions();

            $policy = $policy->withFrequency(
                (int) $payload['maxInterventionsPerMinute'],
                $minSeconds,
            );
        } elseif (isset($payload['minSecondsBetweenInterventions'])
            && is_numeric($payload['minSecondsBetweenInterventions'])) {
            $policy = $policy->withFrequency(
                $policy->maxInterventionsPerMinute(),
                (float) $payload['minSecondsBetweenInterventions'],
            );
        }

        if (array_key_exists('autoResume', $payload)) {
            $policy = $policy->withAutoResume((bool) $payload['autoResume']);
        }

        if (array_key_exists('allowAutoPause', $payload)) {
            $policy = $policy->withAllowAutoPause((bool) $payload['allowAutoPause']);
        }

        return $policy;
    }
}
