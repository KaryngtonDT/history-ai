<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipStrength;
use App\Domain\ShadowRelationship\RelationshipTrait;
use App\Domain\ShadowRelationship\RelationshipTraitType;

final class HabitDetector
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return list<RelationshipTrait>
     */
    public function detect(array $payload): array
    {
        $traits = [];
        $kind = is_string($payload['kind'] ?? null) ? $payload['kind'] : '';

        if (in_array($kind, ['pause', 'rewind', 'replay'], true)) {
            $traits[] = RelationshipTrait::inferred(
                RelationshipTraitType::Habit,
                'frequent_pauses',
                'Pauses frequently',
                RelationshipStrength::Medium,
                'Observed playback pause or rewind behavior.',
            );
        }

        if ('question' === $kind && str_contains(strtolower((string) ($payload['question'] ?? '')), 'why')) {
            $traits[] = RelationshipTrait::inferred(
                RelationshipTraitType::Habit,
                'why_questions',
                'Asks many why questions',
                RelationshipStrength::High,
                'Repeated why-style questions detected.',
            );
        }

        if (in_array($kind, ['challenge_success', 'challenge_attempt'], true)) {
            $traits[] = RelationshipTrait::inferred(
                RelationshipTraitType::Habit,
                'likes_challenges',
                'Likes challenges',
                RelationshipStrength::High,
                'Challenge activity detected in session.',
            );
        }

        if ('examples_first' === ($payload['explanationStyle'] ?? null)) {
            $traits[] = RelationshipTrait::inferred(
                RelationshipTraitType::Habit,
                'examples_first',
                'Likes examples first',
                RelationshipStrength::High,
                'User prefers example-first explanations.',
            );
        }

        return $traits;
    }
}
