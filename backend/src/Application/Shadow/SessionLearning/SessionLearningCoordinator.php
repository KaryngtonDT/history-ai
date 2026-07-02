<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning;

use App\Domain\Shadow\SessionLearning\SessionLearningState;
use App\Domain\Shadow\SessionLearning\SessionLearningStateRepositoryInterface;
use App\Domain\Shadow\SessionLearning\SessionObservationType;
use App\Domain\Shadow\SessionLearning\TeachingStrategy;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Video\VideoId;

final class SessionLearningCoordinator
{
    public function __construct(
        private readonly SessionLearningStateRepositoryInterface $repository,
        private readonly ShadowSessionLearningAnalyzer $analyzer,
        private readonly TeachingStrategyResolver $strategyResolver,
    ) {
    }

    public function ensureState(ShadowSession $session): SessionLearningState
    {
        $existing = $this->repository->findBySessionId($session->id());

        if (null !== $existing) {
            return $existing;
        }

        $state = SessionLearningState::start($session->id(), $session->videoId());
        $this->repository->save($state);

        return $state;
    }

    public function analyzeAndSave(ShadowSession $session): SessionLearningState
    {
        $state = $this->ensureState($session);
        $analyzed = $this->analyzer->analyze($session, $state);
        $this->repository->save($analyzed);

        return $analyzed;
    }

    public function recordObservation(
        ShadowSessionId $sessionId,
        VideoId $videoId,
        SessionObservationType $type,
        float $timeSeconds,
        ?string $detail = null,
    ): SessionLearningState {
        $state = $this->repository->findBySessionId($sessionId)
            ?? SessionLearningState::start($sessionId, $videoId);

        $state = $this->analyzer->recordObservation($state, $type, $timeSeconds, $detail);
        $this->repository->save($state);

        return $state;
    }

    public function updatePreferences(
        ShadowSessionId $sessionId,
        VideoId $videoId,
        bool $adaptiveEnabled,
    ): SessionLearningState {
        $state = $this->repository->findBySessionId($sessionId)
            ?? SessionLearningState::start($sessionId, $videoId);

        $state = $state->withPreferences($state->preferences()->withAdaptiveEnabled($adaptiveEnabled));
        $this->repository->save($state);

        return $state;
    }

    public function reset(ShadowSessionId $sessionId, VideoId $videoId): SessionLearningState
    {
        $this->repository->deleteBySessionId($sessionId);
        $state = SessionLearningState::start($sessionId, $videoId);
        $this->repository->save($state);

        return $state;
    }

    public function getState(ShadowSessionId $sessionId, VideoId $videoId): SessionLearningState
    {
        return $this->repository->findBySessionId($sessionId)
            ?? SessionLearningState::start($sessionId, $videoId);
    }

    public function resolveStrategy(SessionLearningState $state): TeachingStrategy
    {
        return $this->strategyResolver->resolve($state);
    }
}
