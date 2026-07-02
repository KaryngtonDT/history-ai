<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipObservation;
use App\Domain\ShadowRelationship\RelationshipProfile;
use App\Domain\ShadowRelationship\RelationshipTrait;

final class RelationshipJsonMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(RelationshipProfile $profile): array
    {
        return [
            'id' => $profile->id()->value,
            'scopeKey' => $profile->scopeKey(),
            'relationshipScore' => $profile->relationshipScore(),
            'preferences' => [
                'adaptiveEnabled' => $profile->preferences()->adaptiveEnabled(),
                'rememberRelationship' => $profile->preferences()->rememberRelationship(),
                'requireApprovalForInferences' => $profile->preferences()->requireApprovalForInferences(),
            ],
            'settings' => [
                'showHypotheses' => $profile->settings()->showHypotheses(),
                'showTimeline' => $profile->settings()->showTimeline(),
                'allowConversationalUpdates' => $profile->settings()->allowConversationalUpdates(),
            ],
            'traits' => array_map($this->traitToArray(...), $profile->traits()->all()),
            'timeline' => array_map($this->observationToArray(...), $profile->observations()->all()),
            'sharedReferences' => array_map(
                static fn ($reference): array => [
                    'id' => $reference->id(),
                    'kind' => $reference->kind()->value,
                    'label' => $reference->label(),
                    'detail' => $reference->detail(),
                    'sessionId' => $reference->sessionId(),
                    'videoId' => $reference->videoId(),
                    'recordedAt' => $reference->recordedAt()->format(DATE_ATOM),
                ],
                $profile->sharedReferences()->all(),
            ),
            'pendingChanges' => array_map(
                static fn ($change): array => [
                    'id' => $change->id(),
                    'label' => $change->label(),
                    'status' => $change->status(),
                    'createdAt' => $change->createdAt()->format(DATE_ATOM),
                    'trait' => [
                        'type' => $change->proposedTrait()->type()->value,
                        'key' => $change->proposedTrait()->key(),
                        'label' => $change->proposedTrait()->label(),
                        'strength' => $change->proposedTrait()->strength()->value,
                    ],
                ],
                $profile->pendingChanges()->all(),
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
            'source' => $trait->source(),
            'confirmed' => $trait->confirmed(),
            'enabled' => $trait->enabled(),
            'explanation' => $trait->explanation(),
        ];
    }

    /** @return array<string, string> */
    private function observationToArray(RelationshipObservation $observation): array
    {
        return [
            'id' => $observation->id(),
            'type' => $observation->type()->value,
            'label' => $observation->label(),
            'detail' => $observation->detail(),
            'recordedAt' => $observation->recordedAt()->format(DATE_ATOM),
        ];
    }
}
