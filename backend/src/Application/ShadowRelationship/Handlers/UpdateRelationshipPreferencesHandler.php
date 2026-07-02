<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship\Handlers;

use App\Application\ShadowRelationship\RelationshipJsonMapper;
use App\Application\ShadowRelationship\RelationshipProfileBuilder;
use App\Domain\ShadowRelationship\RelationshipPreferences;
use App\Domain\ShadowRelationship\RelationshipRepositoryInterface;

final class UpdateRelationshipPreferencesHandler
{
    public function __construct(
        private readonly RelationshipProfileBuilder $builder,
        private readonly RelationshipRepositoryInterface $repository,
        private readonly RelationshipJsonMapper $mapper,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $profile = $this->builder->getOrCreate($scopeKey);
        $preferences = $profile->preferences();

        if (array_key_exists('adaptiveEnabled', $payload)) {
            $preferences = $preferences->withAdaptiveEnabled((bool) $payload['adaptiveEnabled']);
        }

        if (array_key_exists('rememberRelationship', $payload)) {
            $preferences = $preferences->withRememberRelationship((bool) $payload['rememberRelationship']);
        }

        $profile = $profile->withPreferences($preferences);

        if (isset($payload['trait']) && is_array($payload['trait'])) {
            $type = \App\Domain\ShadowRelationship\RelationshipTraitType::tryFrom((string) ($payload['trait']['type'] ?? ''));
            $strength = \App\Domain\ShadowRelationship\RelationshipStrength::tryFrom((string) ($payload['trait']['strength'] ?? 'medium'));

            if (null !== $type && null !== $strength) {
                $trait = \App\Domain\ShadowRelationship\RelationshipTrait::explicit(
                    $type,
                    (string) ($payload['trait']['key'] ?? ''),
                    (string) ($payload['trait']['label'] ?? ''),
                    $strength,
                    (string) ($payload['trait']['explanation'] ?? 'Updated by user.'),
                );
                $profile = $profile->upsertTrait($trait);
            }
        }

        if (isset($payload['removeTrait']) && is_array($payload['removeTrait'])) {
            $type = \App\Domain\ShadowRelationship\RelationshipTraitType::tryFrom((string) ($payload['removeTrait']['type'] ?? ''));

            if (null !== $type) {
                $profile = $profile->removeTrait($type, (string) ($payload['removeTrait']['key'] ?? ''));
            }
        }

        $this->repository->save($profile);

        return $this->mapper->toArray($profile);
    }
}
