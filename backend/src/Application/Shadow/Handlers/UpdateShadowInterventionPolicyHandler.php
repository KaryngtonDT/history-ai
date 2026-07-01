<?php

declare(strict_types=1);

namespace App\Application\Shadow\Handlers;

use App\Application\Shadow\Commands\UpdateShadowInterventionPolicyCommand;
use App\Application\Shadow\DTO\ShadowInterventionPolicyResult;
use App\Application\Shadow\ShadowSessionResolver;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;

final class UpdateShadowInterventionPolicyHandler
{
    public function __construct(
        private readonly ShadowSessionRepositoryInterface $sessionRepository,
        private readonly ShadowSessionResolver $sessionResolver,
    ) {
    }

    public function __invoke(UpdateShadowInterventionPolicyCommand $command): ShadowInterventionPolicyResult
    {
        $session = $this->sessionResolver->resolve($command->videoId, $command->sessionId);
        $session = $session->withInterventionPolicy($command->policy);

        $this->sessionRepository->save($session);

        return ShadowInterventionPolicyResult::fromDomain($session->interventionPolicy());
    }
}
