<?php

declare(strict_types=1);

namespace App\Application\Shadow\Handlers;

use App\Application\Shadow\Commands\ResumeShadowSessionCommand;
use App\Application\Shadow\DTO\ShadowSessionResult;
use App\Application\Shadow\ShadowSessionResolver;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;

final class ResumeShadowSessionHandler
{
    public function __construct(
        private readonly ShadowSessionRepositoryInterface $sessionRepository,
        private readonly ShadowSessionResolver $sessionResolver,
    ) {
    }

    public function __invoke(ResumeShadowSessionCommand $command): ShadowSessionResult
    {
        $session = $this->sessionResolver->resolve($command->videoId, $command->sessionId);
        $session = $this->sessionResolver->withOptionalTimestamp($session, $command->currentTimeSeconds);
        $session = $session->resume();

        $this->sessionRepository->save($session);

        return ShadowSessionResult::fromDomain($session);
    }
}
