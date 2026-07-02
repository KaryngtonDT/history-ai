<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipExplanation;
use App\Domain\ShadowRelationship\RelationshipProfile;
use App\Domain\ShadowRelationship\RelationshipTrait;

final class RelationshipExplanationBuilder
{
    public function forTrait(RelationshipTrait $trait): RelationshipExplanation
    {
        return new RelationshipExplanation(
            sprintf('Shadow treats %s as a %s trait.', $trait->label(), $trait->type()->value),
            $trait->explanation(),
            $trait->confirmed() ? 'confirmed' : 'inferred',
        );
    }

    public function forProfile(RelationshipProfile $profile): RelationshipExplanation
    {
        $enabled = count($profile->traits()->enabled());
        $confirmed = count(array_filter(
            $profile->traits()->enabled(),
            static fn (RelationshipTrait $trait): bool => $trait->confirmed(),
        ));

        return new RelationshipExplanation(
            sprintf('Shadow relationship score is %d%%.', $profile->relationshipScore()),
            sprintf('%d enabled traits (%d confirmed, %d hypotheses).', $enabled, $confirmed, $enabled - $confirmed),
            'profile',
        );
    }
}
