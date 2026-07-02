<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipProfile;
use App\Domain\ShadowRelationship\RelationshipTrait;
use App\Domain\ShadowRelationship\RelationshipTraitCollection;

final class RelationshipTraitResolver
{
    public function mergeTraits(RelationshipTraitCollection $existing, RelationshipTrait ...$incoming): RelationshipTraitCollection
    {
        $traits = $existing;

        foreach ($incoming as $trait) {
            $current = $traits->find($trait->type()->value, $trait->key());

            if (null === $current) {
                $traits = $traits->upsert($trait);

                continue;
            }

            if ($current->confirmed() && !$trait->confirmed()) {
                continue;
            }

            $strength = $current->strength()->score() >= $trait->strength()->score()
                ? $current->strength()
                : $trait->strength();

            $traits = $traits->upsert(new RelationshipTrait(
                $trait->type(),
                $trait->key(),
                $trait->label(),
                $strength,
                $trait->confirmed() ? 'user' : $current->source(),
                $current->confirmed() || $trait->confirmed(),
                $current->enabled(),
                $trait->explanation(),
            ));
        }

        return $traits;
    }

    /** @return list<RelationshipTrait> */
    public function activeTraits(RelationshipProfile $profile): array
    {
        if (!$profile->preferences()->adaptiveEnabled() || !$profile->preferences()->rememberRelationship()) {
            return [];
        }

        return array_values(array_filter(
            $profile->traits()->enabled(),
            static fn (RelationshipTrait $trait): bool => $trait->confirmed() || $profile->settings()->showHypotheses(),
        ));
    }
}
