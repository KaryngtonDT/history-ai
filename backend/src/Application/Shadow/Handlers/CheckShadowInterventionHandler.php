<?php

declare(strict_types=1);

namespace App\Application\Shadow\Handlers;

use App\Application\Shadow\DTO\ShadowInterventionCheckResult;
use App\Application\Shadow\Queries\CheckShadowInterventionQuery;
use App\Application\Shadow\ShadowInterventionContextBuilder;
use App\Application\Shadow\ShadowInterventionPlanner;
use App\Application\Shadow\ShadowSessionResolver;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowIntervention;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\Shadow\ShadowTimestamp;

final class CheckShadowInterventionHandler
{
    private const float PENDING_WINDOW_SECONDS = 15.0;

    public function __construct(
        private readonly ShadowSessionRepositoryInterface $sessionRepository,
        private readonly ShadowSessionResolver $sessionResolver,
        private readonly ShadowInterventionContextBuilder $contextBuilder,
        private readonly ShadowInterventionPlanner $planner,
    ) {
    }

    public function __invoke(CheckShadowInterventionQuery $query): ShadowInterventionCheckResult
    {
        if ($query->currentTimeSeconds < 0) {
            throw new InvalidShadowSessionException('Shadow timestamp cannot be negative.');
        }

        $session = $this->sessionResolver->resolve($query->videoId, $query->sessionId);
        $session = $session->withTimestamp(ShadowTimestamp::fromSeconds($query->currentTimeSeconds));

        $pending = $this->findPendingIntervention($session, $query->currentTimeSeconds);

        if (null !== $pending) {
            $this->sessionRepository->save($session);

            return ShadowInterventionCheckResult::fromIntervention($pending, $session);
        }

        $context = $this->contextBuilder->build($session, $query->currentTimeSeconds);
        $planned = $this->planner->plan($context);

        if (null === $planned) {
            $this->sessionRepository->save($session);

            return ShadowInterventionCheckResult::none($session);
        }

        $session = $session->recordIntervention($planned);
        $this->sessionRepository->save($session);

        return ShadowInterventionCheckResult::fromIntervention($planned, $session);
    }

    private function findPendingIntervention(
        ShadowSession $session,
        float $currentTimeSeconds,
    ): ?ShadowIntervention {
        $interventions = $session->interventions()->all();

        if ([] === $interventions) {
            return null;
        }

        $last = $interventions[array_key_last($interventions)];

        if ($last->isSkipped() || $last->isAnswered()) {
            return null;
        }

        $delta = abs($currentTimeSeconds - $last->videoTimestamp()->seconds());

        return $delta <= self::PENDING_WINDOW_SECONDS ? $last : null;
    }
}
