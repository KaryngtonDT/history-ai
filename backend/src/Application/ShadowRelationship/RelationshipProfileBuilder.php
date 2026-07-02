<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowIdentity\ShadowIdentityRepositoryInterface;
use App\Domain\Learning\LearningProfileRepositoryInterface;
use App\Domain\ShadowRelationship\RelationshipProfile;
use App\Domain\ShadowRelationship\RelationshipRepositoryInterface;
use App\Domain\ShadowRelationship\RelationshipSignal;

final class RelationshipProfileBuilder
{
    public function __construct(
        private readonly RelationshipRepositoryInterface $repository,
        private readonly RelationshipSignalCollector $signalCollector,
        private readonly RelationshipEvolutionEngine $evolutionEngine,
        private readonly ?LearningProfileRepositoryInterface $learningRepository = null,
        private readonly ?ShadowIdentityRepositoryInterface $identityRepository = null,
    ) {
    }

    public function getOrCreate(string $scopeKey = 'default'): RelationshipProfile
    {
        return $this->repository->findByScope($scopeKey) ?? RelationshipProfile::create(scopeKey: $scopeKey);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function recordPayload(string $scopeKey, array $payload): RelationshipProfile
    {
        $profile = $this->getOrCreate($scopeKey);

        foreach ($this->signalCollector->collect($payload) as $signal) {
            $profile = $this->evolutionEngine->evolve($profile, $signal);
        }

        $this->repository->save($profile);

        return $profile;
    }

    public function ingestExistingSources(string $scopeKey = 'default'): RelationshipProfile
    {
        $profile = $this->getOrCreate($scopeKey);

        if ([] !== $profile->signals()->all() || count($profile->traits()->all()) > 0) {
            return $profile;
        }

        $signals = [];

        $identity = $this->identityRepository?->findByScope($scopeKey);

        if (null !== $identity) {
            $preferences = $identity->preferences();
            $signals[] = RelationshipSignal::create('identity', 'persona', [
                'persona' => $preferences->persona()->value,
                'narrationStyle' => $preferences->narrationStyle()->value,
                'interests' => $preferences->memoryPolicy()->interests(),
            ]);
        }

        $learning = $this->learningRepository?->findByScope($scopeKey);

        if (null !== $learning) {
            foreach ($learning->signals()->all() as $learningSignal) {
                $signals[] = RelationshipSignal::create('learning', $learningSignal->type()->value, $learningSignal->context());
            }
        }

        foreach ($signals as $signal) {
            $profile = $this->evolutionEngine->evolve($profile, $signal);
        }

        $this->repository->save($profile);

        return $profile;
    }

    public function reset(string $scopeKey = 'default'): RelationshipProfile
    {
        $profile = $this->getOrCreate($scopeKey)->reset();
        $this->repository->save($profile);

        return $profile;
    }
}
