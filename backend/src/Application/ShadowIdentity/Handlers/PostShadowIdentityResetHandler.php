<?php

declare(strict_types=1);

namespace App\Application\ShadowIdentity\Handlers;

use App\Application\ShadowIdentity\ShadowIdentityJsonMapper;
use App\Domain\ShadowIdentity\ShadowIdentityRepositoryInterface;

final class PostShadowIdentityResetHandler
{
    public function __construct(
        private readonly ShadowIdentityRepositoryInterface $repository,
        private readonly ShadowIdentityJsonMapper $mapper,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $identity = $this->repository->findByScope($scopeKey);

        if (null === $identity) {
            return $this->mapper->toArray(\App\Domain\ShadowIdentity\ShadowIdentity::create(scopeKey: $scopeKey));
        }

        $reset = $identity->reset();
        $this->repository->save($reset);

        return $this->mapper->toArray($reset);
    }
}
