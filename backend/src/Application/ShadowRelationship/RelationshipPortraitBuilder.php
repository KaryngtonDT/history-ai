<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipProfile;
use App\Domain\ShadowRelationship\RelationshipTrait;

final class RelationshipPortraitBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(RelationshipProfile $profile): array
    {
        $confirmed = [];
        $hypotheses = [];

        foreach ($profile->traits()->enabled() as $trait) {
            $item = $this->traitToArray($trait);

            if ($trait->confirmed()) {
                $confirmed[] = $item;
            } else {
                $hypotheses[] = $item;
            }
        }

        return [
            'relationshipScore' => $profile->relationshipScore(),
            'confirmed' => $confirmed,
            'hypotheses' => $hypotheses,
            'questions' => $this->suggestedQuestions($profile),
            'pendingChanges' => array_map(
                static fn ($change): array => [
                    'id' => $change->id(),
                    'label' => $change->label(),
                    'status' => $change->status(),
                    'trait' => [
                        'type' => $change->proposedTrait()->type()->value,
                        'key' => $change->proposedTrait()->key(),
                        'label' => $change->proposedTrait()->label(),
                    ],
                ],
                $profile->pendingChanges()->pending(),
            ),
        ];
    }

    /** @return array<string, mixed> */
    private function traitToArray(RelationshipTrait $trait): array
    {
        return [
            'type' => $trait->type()->value,
            'key' => $trait->key(),
            'label' => $trait->label(),
            'strength' => $trait->strength()->value,
            'explanation' => $trait->explanation(),
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function suggestedQuestions(RelationshipProfile $profile): array
    {
        $questions = [
            ['id' => 'session_length', 'text' => 'Do you prefer short or long learning sessions?'],
            ['id' => 'interruptions', 'text' => 'Do you like being interrupted with practice questions?'],
            ['id' => 'practice_vs_theory', 'text' => 'Do you prefer practice-first or theory-first explanations?'],
        ];

        if ([] === $profile->traits()->byType(\App\Domain\ShadowRelationship\RelationshipTraitType::Interest)) {
            $questions[] = ['id' => 'topics', 'text' => 'Which topics should Shadow prioritize in examples?'];
        }

        return $questions;
    }
}
