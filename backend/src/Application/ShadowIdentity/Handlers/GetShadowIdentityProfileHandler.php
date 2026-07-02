<?php

declare(strict_types=1);

namespace App\Application\ShadowIdentity\Handlers;

use App\Application\ShadowIdentity\ShadowIdentityJsonMapper;
use App\Domain\ShadowIdentity\ShadowIdentity;
use App\Domain\ShadowIdentity\ShadowIdentityRepositoryInterface;

final class GetShadowIdentityProfileHandler
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
            $identity = ShadowIdentity::create(scopeKey: $scopeKey);
            $this->repository->save($identity);
        }

        return $this->mapper->toArray($identity);
    }
}
