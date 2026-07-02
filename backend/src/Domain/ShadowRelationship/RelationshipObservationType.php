<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

enum RelationshipObservationType: string
{
    case StartedTopic = 'started_topic';
    case PreferenceChanged = 'preference_changed';
    case TraitConfirmed = 'trait_confirmed';
    case TraitInferred = 'trait_inferred';
    case SharedReference = 'shared_reference';
    case ChallengeAdjusted = 'challenge_adjusted';
    case CommunicationShift = 'communication_shift';
    case Reset = 'reset';
}
