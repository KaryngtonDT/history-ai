<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning\Handlers;

use App\Application\Shadow\SessionLearning\DTO\SessionLearningView;
use App\Application\Shadow\SessionLearning\SessionLearningCoordinator;
use App\Application\Shadow\ShadowSessionResolver;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\SessionLearning\SessionObservationType;

final class RecordSessionObservationHandler
{
    public function __construct(
        private readonly ShadowSessionResolver $sessionResolver,
        private readonly SessionLearningCoordinator $coordinator,
    ) {
    }

    public function __invoke(
        string $videoId,
        string $sessionId,
        string $type,
        float $timeSeconds,
        ?string $detail = null,
    ): SessionLearningView {
        if ($timeSeconds < 0) {
            throw new InvalidShadowSessionException('Observation time cannot be negative.');
        }

        $session = $this->sessionResolver->resolve($videoId, $sessionId);

        try {
            $observationType = SessionObservationType::from($type);
        } catch (\ValueError) {
            throw new InvalidShadowSessionException('Invalid observation type.');
        }

        $this->coordinator->recordObservation(
            $session->id(),
            $session->videoId(),
            $observationType,
            $timeSeconds,
            $detail,
        );

        $analyzed = $this->coordinator->analyzeAndSave($session);

        return SessionLearningView::fromState($analyzed);
    }
}
