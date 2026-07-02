<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipStrength;
use App\Domain\ShadowRelationship\RelationshipTrait;
use App\Domain\ShadowRelationship\RelationshipTraitType;

final class ConversationStyleDetector
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return list<RelationshipTrait>
     */
    public function detect(array $payload): array
    {
        $traits = [];
        $persona = is_string($payload['persona'] ?? null) ? $payload['persona'] : '';
        $narration = is_string($payload['narrationStyle'] ?? null) ? $payload['narrationStyle'] : '';

        $map = [
            'professor' => ['professor', RelationshipStrength::High],
            'storyteller' => ['storyteller', RelationshipStrength::High],
            'socratic_mentor' => ['socratic', RelationshipStrength::High],
            'debater' => ['debate', RelationshipStrength::Medium],
            'friendly_companion' => ['friendly', RelationshipStrength::Medium],
        ];

        if (isset($map[$persona])) {
            [$key, $strength] = $map[$persona];
            $traits[] = RelationshipTrait::inferred(
                RelationshipTraitType::Communication,
                $key,
                ucfirst($key),
                $strength,
                sprintf('Shadow identity persona "%s" suggests %s communication.', $persona, $key),
            );
        }

        if ('story' === $narration) {
            $traits[] = RelationshipTrait::inferred(
                RelationshipTraitType::Communication,
                'storyteller',
                'Storyteller',
                RelationshipStrength::VeryHigh,
                'Story narration mode is active.',
            );
        }

        return $traits;
    }
}
