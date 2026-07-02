<?php

declare(strict_types=1);

namespace App\Infrastructure\Shadow\SessionLearning;

use App\Domain\Shadow\SessionLearning\SessionLearningState;
use App\Domain\Shadow\SessionLearning\SessionLearningStateRepositoryInterface;
use App\Domain\Shadow\ShadowSessionId;

final class InMemorySessionLearningStateRepository implements SessionLearningStateRepositoryInterface
{
    /** @var array<string, SessionLearningState> */
    private array $states = [];

    public function save(SessionLearningState $state): void
    {
        $this->states[$state->sessionId()->value] = $state;
    }

    public function findBySessionId(ShadowSessionId $sessionId): ?SessionLearningState
    {
        return $this->states[$sessionId->value] ?? null;
    }

    public function deleteBySessionId(ShadowSessionId $sessionId): void
    {
        unset($this->states[$sessionId->value]);
    }

    public function clear(): void
    {
        $this->states = [];
    }
}
