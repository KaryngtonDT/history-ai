<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning\Handlers;

use App\Application\Shadow\SessionLearning\DTO\SessionStrategyView;
use App\Application\Shadow\SessionLearning\SessionLearningCoordinator;
use App\Application\Shadow\ShadowSessionResolver;

final class GetSessionStrategyHandler
{
    public function __construct(
        private readonly ShadowSessionResolver $sessionResolver,
        private readonly SessionLearningCoordinator $coordinator,
    ) {
    }

    public function __invoke(string $videoId, string $sessionId): SessionStrategyView
    {
        $session = $this->sessionResolver->resolve($videoId, $sessionId);
        $state = $this->coordinator->analyzeAndSave($session);

        return SessionStrategyView::fromStrategy($this->coordinator->resolveStrategy($state));
    }
}
