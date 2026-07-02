<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning\Handlers;

use App\Application\Shadow\SessionLearning\DTO\SessionLearningView;
use App\Application\Shadow\SessionLearning\SessionLearningCoordinator;
use App\Application\Shadow\ShadowSessionResolver;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;

final class UpdateSessionLearningPreferencesHandler
{
    public function __construct(
        private readonly ShadowSessionResolver $sessionResolver,
        private readonly SessionLearningCoordinator $coordinator,
    ) {
    }

    public function __invoke(string $videoId, string $sessionId, bool $adaptiveEnabled): SessionLearningView
    {
        $session = $this->sessionResolver->resolve($videoId, $sessionId);
        $state = $this->coordinator->updatePreferences(
            $session->id(),
            $session->videoId(),
            $adaptiveEnabled,
        );

        return SessionLearningView::fromState($state);
    }
}
