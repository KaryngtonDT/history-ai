<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipObservation;
use App\Domain\ShadowRelationship\RelationshipObservationCollection;
use App\Domain\ShadowRelationship\RelationshipObservationType;
use App\Domain\ShadowRelationship\RelationshipPendingChange;
use App\Domain\ShadowRelationship\RelationshipPendingChangeCollection;
use App\Domain\ShadowRelationship\RelationshipPreferences;
use App\Domain\ShadowRelationship\RelationshipProfile;
use App\Domain\ShadowRelationship\RelationshipProfileId;
use App\Domain\ShadowRelationship\RelationshipSettings;
use App\Domain\ShadowRelationship\RelationshipSignal;
use App\Domain\ShadowRelationship\RelationshipSignalCollection;
use App\Domain\ShadowRelationship\RelationshipStrength;
use App\Domain\ShadowRelationship\RelationshipTrait;
use App\Domain\ShadowRelationship\RelationshipTraitCollection;
use App\Domain\ShadowRelationship\RelationshipTraitType;
use App\Domain\ShadowRelationship\SharedReference;
use App\Domain\ShadowRelationship\SharedReferenceCollection;
use App\Domain\ShadowRelationship\SharedReferenceKind;
use App\Domain\ShadowRelationship\Exception\InvalidRelationshipProfileException;
use JsonException;

final class ShadowRelationshipPersistenceMapper
{
    /** @return array<string, mixed> */
    public function toArray(RelationshipProfile $profile): array
    {
        return [
            'id' => $profile->id()->value,
            'scopeKey' => $profile->scopeKey(),
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
            'signals' => array_map($this->signalToArray(...), $profile->signals()->all()),
            'observations' => array_map($this->observationToArray(...), $profile->observations()->all()),
            'sharedReferences' => array_map($this->referenceToArray(...), $profile->sharedReferences()->all()),
            'pendingChanges' => array_map($this->pendingChangeToArray(...), $profile->pendingChanges()->all()),
        ];
    }

    public function fromJson(string $json): RelationshipProfile
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidRelationshipProfileException('Stored relationship profile is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidRelationshipProfileException('Stored relationship profile must be a JSON object.');
        }

        $id = is_string($decoded['id'] ?? null) ? $decoded['id'] : null;
        $scopeKey = is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default';

        if (null === $id) {
            throw new InvalidRelationshipProfileException('Stored relationship profile is missing id.');
        }

        $preferences = is_array($decoded['preferences'] ?? null) ? $decoded['preferences'] : [];
        $settings = is_array($decoded['settings'] ?? null) ? $decoded['settings'] : [];

        return new RelationshipProfile(
            new RelationshipProfileId($id),
            $scopeKey,
            new RelationshipPreferences(
                (bool) ($preferences['adaptiveEnabled'] ?? true),
                (bool) ($preferences['rememberRelationship'] ?? true),
                (bool) ($preferences['requireApprovalForInferences'] ?? true),
            ),
            new RelationshipSettings(
                (bool) ($settings['showHypotheses'] ?? true),
                (bool) ($settings['showTimeline'] ?? true),
                (bool) ($settings['allowConversationalUpdates'] ?? true),
            ),
            $this->traitsFromArray(is_array($decoded['traits'] ?? null) ? $decoded['traits'] : []),
            $this->signalsFromArray(is_array($decoded['signals'] ?? null) ? $decoded['signals'] : []),
            $this->observationsFromArray(is_array($decoded['observations'] ?? null) ? $decoded['observations'] : []),
            $this->referencesFromArray(is_array($decoded['sharedReferences'] ?? null) ? $decoded['sharedReferences'] : []),
            $this->pendingChangesFromArray(is_array($decoded['pendingChanges'] ?? null) ? $decoded['pendingChanges'] : []),
        );
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

    /** @param list<array<string, mixed>> $rows */
    private function traitsFromArray(array $rows): RelationshipTraitCollection
    {
        $traits = [];

        foreach ($rows as $row) {
            $type = RelationshipTraitType::tryFrom((string) ($row['type'] ?? ''));
            $strength = RelationshipStrength::tryFrom((string) ($row['strength'] ?? ''));

            if (null === $type || null === $strength) {
                continue;
            }

            $traits[] = new RelationshipTrait(
                $type,
                (string) ($row['key'] ?? ''),
                (string) ($row['label'] ?? ''),
                $strength,
                (string) ($row['source'] ?? 'signal'),
                (bool) ($row['confirmed'] ?? false),
                (bool) ($row['enabled'] ?? true),
                (string) ($row['explanation'] ?? ''),
            );
        }

        return new RelationshipTraitCollection($traits);
    }

    /** @return array<string, mixed> */
    private function signalToArray(RelationshipSignal $signal): array
    {
        return [
            'id' => $signal->id(),
            'source' => $signal->source(),
            'kind' => $signal->kind(),
            'payload' => $signal->payload(),
            'recordedAt' => $signal->recordedAt()->format(DATE_ATOM),
        ];
    }

    /** @param list<array<string, mixed>> $rows */
    private function signalsFromArray(array $rows): RelationshipSignalCollection
    {
        $signals = [];

        foreach ($rows as $row) {
            $recordedAt = \DateTimeImmutable::createFromFormat(DATE_ATOM, (string) ($row['recordedAt'] ?? ''))
                ?: new \DateTimeImmutable();

            $signals[] = new RelationshipSignal(
                (string) ($row['id'] ?? bin2hex(random_bytes(8))),
                (string) ($row['source'] ?? 'unknown'),
                (string) ($row['kind'] ?? 'generic'),
                is_array($row['payload'] ?? null) ? $row['payload'] : [],
                $recordedAt,
            );
        }

        return new RelationshipSignalCollection($signals);
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

    /** @param list<array<string, mixed>> $rows */
    private function observationsFromArray(array $rows): RelationshipObservationCollection
    {
        $observations = [];

        foreach ($rows as $row) {
            $type = RelationshipObservationType::tryFrom((string) ($row['type'] ?? ''));

            if (null === $type) {
                continue;
            }

            $recordedAt = \DateTimeImmutable::createFromFormat(DATE_ATOM, (string) ($row['recordedAt'] ?? ''))
                ?: new \DateTimeImmutable();

            $observations[] = new RelationshipObservation(
                (string) ($row['id'] ?? bin2hex(random_bytes(8))),
                $type,
                (string) ($row['label'] ?? ''),
                (string) ($row['detail'] ?? ''),
                $recordedAt,
            );
        }

        return new RelationshipObservationCollection($observations);
    }

    /** @return array<string, mixed> */
    private function referenceToArray(SharedReference $reference): array
    {
        return [
            'id' => $reference->id(),
            'kind' => $reference->kind()->value,
            'label' => $reference->label(),
            'detail' => $reference->detail(),
            'sessionId' => $reference->sessionId(),
            'videoId' => $reference->videoId(),
            'recordedAt' => $reference->recordedAt()->format(DATE_ATOM),
        ];
    }

    /** @param list<array<string, mixed>> $rows */
    private function referencesFromArray(array $rows): SharedReferenceCollection
    {
        $references = [];

        foreach ($rows as $row) {
            $kind = SharedReferenceKind::tryFrom((string) ($row['kind'] ?? ''));

            if (null === $kind) {
                continue;
            }

            $recordedAt = \DateTimeImmutable::createFromFormat(DATE_ATOM, (string) ($row['recordedAt'] ?? ''))
                ?: new \DateTimeImmutable();

            $references[] = new SharedReference(
                (string) ($row['id'] ?? bin2hex(random_bytes(8))),
                $kind,
                (string) ($row['label'] ?? ''),
                (string) ($row['detail'] ?? ''),
                is_string($row['sessionId'] ?? null) ? $row['sessionId'] : null,
                is_string($row['videoId'] ?? null) ? $row['videoId'] : null,
                $recordedAt,
            );
        }

        return new SharedReferenceCollection($references);
    }

    /** @return array<string, mixed> */
    private function pendingChangeToArray(RelationshipPendingChange $change): array
    {
        return [
            'id' => $change->id(),
            'label' => $change->label(),
            'status' => $change->status(),
            'createdAt' => $change->createdAt()->format(DATE_ATOM),
            'trait' => $this->traitToArray($change->proposedTrait()),
        ];
    }

    /** @param list<array<string, mixed>> $rows */
    private function pendingChangesFromArray(array $rows): RelationshipPendingChangeCollection
    {
        $changes = [];

        foreach ($rows as $row) {
            $traitRow = is_array($row['trait'] ?? null) ? $row['trait'] : null;
            $type = RelationshipTraitType::tryFrom((string) ($traitRow['type'] ?? ''));
            $strength = RelationshipStrength::tryFrom((string) ($traitRow['strength'] ?? ''));

            if (null === $traitRow || null === $type || null === $strength) {
                continue;
            }

            $createdAt = \DateTimeImmutable::createFromFormat(DATE_ATOM, (string) ($row['createdAt'] ?? ''))
                ?: new \DateTimeImmutable();

            $changes[] = new RelationshipPendingChange(
                (string) ($row['id'] ?? bin2hex(random_bytes(8))),
                (string) ($row['label'] ?? ''),
                new RelationshipTrait(
                    $type,
                    (string) ($traitRow['key'] ?? ''),
                    (string) ($traitRow['label'] ?? ''),
                    $strength,
                    (string) ($traitRow['source'] ?? 'signal'),
                    (bool) ($traitRow['confirmed'] ?? false),
                    (bool) ($traitRow['enabled'] ?? true),
                    (string) ($traitRow['explanation'] ?? ''),
                ),
                (string) ($row['status'] ?? 'pending'),
                $createdAt,
            );
        }

        return new RelationshipPendingChangeCollection($changes);
    }
}
