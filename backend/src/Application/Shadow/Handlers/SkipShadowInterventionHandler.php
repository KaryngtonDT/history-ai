<?php

declare(strict_types=1);

namespace App\Application\Shadow\Handlers;

use App\Application\Shadow\Commands\SkipShadowInterventionCommand;
use App\Application\Shadow\DTO\ShadowInterventionCheckResult;
use App\Application\Shadow\ShadowSessionResolver;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowInterventionId;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\Shadow\ShadowTimestamp;

final class SkipShadowInterventionHandler
{
    public function __construct(
        private readonly ShadowSessionRepositoryInterface $sessionRepository,
        private readonly ShadowSessionResolver $sessionResolver,
    ) {
    }

    public function __invoke(SkipShadowInterventionCommand $command): ShadowInterventionCheckResult
    {
        if ($command->currentTimeSeconds < 0) {
            throw new InvalidShadowSessionException('Shadow timestamp cannot be negative.');
        }

        $session = $this->sessionResolver->resolve($command->videoId, $command->sessionId);
        $interventionId = $this->resolveInterventionId($command->interventionId);
        $intervention = $session->interventions()->findById($interventionId);

        if (null === $intervention) {
            throw new InvalidShadowSessionException('Shadow intervention was not found.');
        }

        $session = $session
            ->withTimestamp(ShadowTimestamp::fromSeconds($command->currentTimeSeconds))
            ->replaceIntervention($intervention->markSkipped());

        $this->sessionRepository->save($session);

        return ShadowInterventionCheckResult::none($session);
    }

    private function resolveInterventionId(string $value): ShadowInterventionId
    {
        try {
            return new ShadowInterventionId($value);
        } catch (InvalidShadowSessionException) {
            throw new InvalidShadowSessionException('Shadow intervention was not found.');
        }
    }
}
