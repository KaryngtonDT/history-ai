<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipStrength;
use App\Domain\ShadowRelationship\RelationshipTrait;
use App\Domain\ShadowRelationship\RelationshipTraitType;

final class MotivationDetector
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return list<RelationshipTrait>
     */
    public function detect(array $payload): array
    {
        $text = strtolower(json_encode($payload, JSON_THROW_ON_ERROR));
        $traits = [];

        if (str_contains($text, 'curious') || str_contains($text, 'explore')) {
            $traits[] = RelationshipTrait::inferred(
                RelationshipTraitType::Motivator,
                'curiosity',
                'Curiosity',
                RelationshipStrength::High,
                'Exploration-oriented language detected.',
            );
        }

        if (str_contains($text, 'challenge') || str_contains($text, 'harder')) {
            $traits[] = RelationshipTrait::inferred(
                RelationshipTraitType::Motivator,
                'achievement',
                'Achievement',
                RelationshipStrength::Medium,
                'Challenge-seeking behavior detected.',
            );
        }

        if (str_contains($text, 'debate') || str_contains($text, 'argue')) {
            $traits[] = RelationshipTrait::inferred(
                RelationshipTraitType::Motivator,
                'debate',
                'Debate',
                RelationshipStrength::Medium,
                'Debate-oriented interaction detected.',
            );
        }

        return $traits;
    }
}
