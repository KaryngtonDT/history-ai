<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning\Handlers;

use App\Application\Shadow\SessionLearning\DTO\SessionLearningView;
use App\Application\Shadow\SessionLearning\SessionLearningCoordinator;
use App\Application\Shadow\ShadowSessionResolver;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;

final class GetSessionLearningHandler
{
    public function __construct(
        private readonly ShadowSessionResolver $sessionResolver,
        private readonly SessionLearningCoordinator $coordinator,
    ) {
    }

    public function __invoke(string $videoId, string $sessionId): SessionLearningView
    {
        $session = $this->sessionResolver->resolve($videoId, $sessionId);
        $state = $this->coordinator->analyzeAndSave($session);

        return SessionLearningView::fromState($state);
    }
}
