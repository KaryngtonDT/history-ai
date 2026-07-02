<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipObservation;
use App\Domain\ShadowRelationship\RelationshipObservationType;
use App\Domain\ShadowRelationship\RelationshipPendingChange;
use App\Domain\ShadowRelationship\RelationshipProfile;
use App\Domain\ShadowRelationship\RelationshipSignal;
use App\Domain\ShadowRelationship\RelationshipTrait;
use App\Domain\ShadowRelationship\SharedReference;
use App\Domain\ShadowRelationship\SharedReferenceKind;

final class RelationshipEvolutionEngine
{
    public function __construct(
        private readonly InterestDetector $interestDetector,
        private readonly HabitDetector $habitDetector,
        private readonly MotivationDetector $motivationDetector,
        private readonly ConversationStyleDetector $conversationStyleDetector,
        private readonly RelationshipTraitResolver $traitResolver,
    ) {
    }

    public function evolve(RelationshipProfile $profile, RelationshipSignal $signal): RelationshipProfile
    {
        $payload = array_merge(['kind' => $signal->kind()], $signal->payload());
        $detected = [
            ...$this->interestDetector->detect($payload),
            ...$this->habitDetector->detect($payload),
            ...$this->motivationDetector->detect($payload),
            ...$this->conversationStyleDetector->detect($payload),
        ];

        $profile = $profile->recordSignal($signal);
        $traits = $profile->traits();

        foreach ($detected as $trait) {
            if ($profile->preferences()->requireApprovalForInferences() && !$trait->confirmed()) {
                $profile = $profile->proposeChange(
                    RelationshipPendingChange::propose($trait, sprintf('Apply %s trait: %s', $trait->type()->value, $trait->label())),
                );
                continue;
            }

            $traits = $this->traitResolver->mergeTraits($traits, $trait);
            $profile = $profile
                ->withTraits($traits)
                ->addObservation(
                    RelationshipObservation::record(
                        RelationshipObservationType::TraitInferred,
                        $trait->label(),
                        $trait->explanation(),
                    ),
                );
        }

        $profile = $this->maybeRecordSharedReference($profile, $payload);

        return $profile;
    }

    /** @param array<string, mixed> $payload */
    private function maybeRecordSharedReference(RelationshipProfile $profile, array $payload): RelationshipProfile
    {
        $question = is_string($payload['question'] ?? null) ? trim($payload['question']) : '';

        if ('' === $question) {
            return $profile;
        }

        $reference = SharedReference::create(
            SharedReferenceKind::Topic,
            mb_substr($question, 0, 120),
            'Recorded from a watch-session question.',
            is_string($payload['sessionId'] ?? null) ? $payload['sessionId'] : null,
            is_string($payload['videoId'] ?? null) ? $payload['videoId'] : null,
        );

        return $profile
            ->addSharedReference($reference)
            ->addObservation(
                RelationshipObservation::record(
                    RelationshipObservationType::SharedReference,
                    $reference->label(),
                    'Shared reference added from session activity.',
                ),
            );
    }

    public function applyTrait(RelationshipProfile $profile, RelationshipTrait $trait): RelationshipProfile
    {
        return $profile
            ->upsertTrait($trait->confirm())
            ->addObservation(
                RelationshipObservation::record(
                    RelationshipObservationType::TraitConfirmed,
                    $trait->label(),
                    'Trait confirmed by user.',
                ),
            );
    }
}
