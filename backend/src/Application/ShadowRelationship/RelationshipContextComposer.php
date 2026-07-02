<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipProfile;
use App\Domain\ShadowRelationship\RelationshipRepositoryInterface;
use App\Domain\ShadowRelationship\RelationshipTrait;
use App\Domain\ShadowRelationship\RelationshipTraitType;

final class RelationshipContextComposer
{
    public function __construct(
        private readonly RelationshipRepositoryInterface $repository,
        private readonly RelationshipTraitResolver $traitResolver,
    ) {
    }

    /**
     * @return list<string>
     */
    public function promptLines(string $scopeKey = 'default'): array
    {
        $profile = $this->repository->findByScope($scopeKey);

        if (null === $profile || !$profile->preferences()->rememberRelationship()) {
            return [];
        }

        $lines = [];
        $traits = $this->traitResolver->activeTraits($profile);

        foreach ($traits as $trait) {
            $prefix = $trait->confirmed() ? 'Known learner preference' : 'Working hypothesis';
            $lines[] = sprintf('%s: %s (%s, %s).', $prefix, $trait->label(), $trait->type()->value, $trait->strength()->value);
        }

        foreach ($profile->sharedReferences()->recent(3) as $reference) {
            $lines[] = sprintf(
                'Shared reference from a previous session: %s (%s).',
                $reference->label(),
                $reference->kind()->value,
            );
        }

        if ([] !== $lines) {
            $lines[] = 'Reuse relevant shared references naturally when they help the current explanation.';
        }

        return $lines;
    }

    /** @return list<RelationshipTrait> */
    public function interests(string $scopeKey = 'default'): array
    {
        $profile = $this->repository->findByScope($scopeKey);

        if (null === $profile) {
            return [];
        }

        return $profile->traits()->byType(RelationshipTraitType::Interest);
    }

    public function profileOrNull(string $scopeKey = 'default'): ?RelationshipProfile
    {
        return $this->repository->findByScope($scopeKey);
    }
}
