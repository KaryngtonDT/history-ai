<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

use App\Domain\ShadowRelationship\Exception\InvalidRelationshipProfileException;

final readonly class RelationshipProfile
{
    public function __construct(
        private RelationshipProfileId $id,
        private string $scopeKey,
        private RelationshipPreferences $preferences,
        private RelationshipSettings $settings,
        private RelationshipTraitCollection $traits,
        private RelationshipSignalCollection $signals,
        private RelationshipObservationCollection $observations,
        private SharedReferenceCollection $sharedReferences,
        private RelationshipPendingChangeCollection $pendingChanges,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidRelationshipProfileException('Relationship profile scope cannot be empty.');
        }
    }

    public static function create(
        ?RelationshipProfileId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? RelationshipProfileId::generate(),
            trim($scopeKey),
            RelationshipPreferences::default(),
            RelationshipSettings::default(),
            RelationshipTraitCollection::empty(),
            RelationshipSignalCollection::empty(),
            RelationshipObservationCollection::empty()->append(
                RelationshipObservation::record(
                    RelationshipObservationType::StartedTopic,
                    'Relationship started',
                    'Initial relationship profile created.',
                ),
            ),
            SharedReferenceCollection::empty(),
            RelationshipPendingChangeCollection::empty(),
        );
    }

    public function id(): RelationshipProfileId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function preferences(): RelationshipPreferences
    {
        return $this->preferences;
    }

    public function settings(): RelationshipSettings
    {
        return $this->settings;
    }

    public function traits(): RelationshipTraitCollection
    {
        return $this->traits;
    }

    public function signals(): RelationshipSignalCollection
    {
        return $this->signals;
    }

    public function observations(): RelationshipObservationCollection
    {
        return $this->observations;
    }

    public function sharedReferences(): SharedReferenceCollection
    {
        return $this->sharedReferences;
    }

    public function pendingChanges(): RelationshipPendingChangeCollection
    {
        return $this->pendingChanges;
    }

    public function recordSignal(RelationshipSignal $signal): self
    {
        return $this->withSignals($this->signals->append($signal));
    }

    public function withTraits(RelationshipTraitCollection $traits): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->preferences,
            $this->settings,
            $traits,
            $this->signals,
            $this->observations,
            $this->sharedReferences,
            $this->pendingChanges,
        );
    }

    public function withSignals(RelationshipSignalCollection $signals): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->preferences,
            $this->settings,
            $this->traits,
            $signals,
            $this->observations,
            $this->sharedReferences,
            $this->pendingChanges,
        );
    }

    public function withObservations(RelationshipObservationCollection $observations): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->preferences,
            $this->settings,
            $this->traits,
            $this->signals,
            $observations,
            $this->sharedReferences,
            $this->pendingChanges,
        );
    }

    public function withSharedReferences(SharedReferenceCollection $references): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->preferences,
            $this->settings,
            $this->traits,
            $this->signals,
            $this->observations,
            $references,
            $this->pendingChanges,
        );
    }

    public function withPendingChanges(RelationshipPendingChangeCollection $pendingChanges): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->preferences,
            $this->settings,
            $this->traits,
            $this->signals,
            $this->observations,
            $this->sharedReferences,
            $pendingChanges,
        );
    }

    public function withPreferences(RelationshipPreferences $preferences): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $preferences,
            $this->settings,
            $this->traits,
            $this->signals,
            $this->observations,
            $this->sharedReferences,
            $this->pendingChanges,
        );
    }

    public function upsertTrait(RelationshipTrait $trait): self
    {
        return $this->withTraits($this->traits->upsert($trait));
    }

    public function removeTrait(RelationshipTraitType $type, string $key): self
    {
        return $this->withTraits($this->traits->remove($type->value, $key));
    }

    public function addObservation(RelationshipObservation $observation): self
    {
        return $this->withObservations($this->observations->append($observation));
    }

    public function addSharedReference(SharedReference $reference): self
    {
        return $this->withSharedReferences($this->sharedReferences->append($reference));
    }

    public function proposeChange(RelationshipPendingChange $change): self
    {
        return $this->withPendingChanges($this->pendingChanges->append($change));
    }

    public function relationshipScore(): int
    {
        $enabled = $this->traits->enabled();

        if ([] === $enabled) {
            return 0;
        }

        $score = 0;

        foreach ($enabled as $trait) {
            $score += $trait->strength()->score();
            if ($trait->confirmed()) {
                $score += 1;
            }
        }

        return min(100, (int) round(($score / max(1, count($enabled) * 5)) * 100));
    }

    public function reset(): self
    {
        $profile = self::create($this->id, $this->scopeKey);

        return $profile->addObservation(
            RelationshipObservation::record(
                RelationshipObservationType::Reset,
                'Relationship reset',
                'User reset the relationship profile.',
            ),
        );
    }
}
